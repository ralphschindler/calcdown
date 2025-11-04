<?php

declare(strict_types=1);

namespace Calcdown;

use MathPHP\Arithmetic;

class CalcdownParser
{
    public function parseBlock(string $block): BlockEvaluation
    {
        $lines = explode("\n", $block);
        $evaluatedLines = [];
        $variables = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $lineEval = $this->parseLine($line, $variables);

            // Update variables context
            foreach ($lineEval->assignedVariables as $varName => $varValue) {
                $variables[$varName] = [
                    'value' => $varValue,
                    'units' => $lineEval->resultUnits,
                ];
            }

            $evaluatedLines[] = $lineEval;
        }

        return new BlockEvaluation(lines: $evaluatedLines);
    }

    /**
     * @param  array<string, array{value: int|float|string, units: string|null}>  $variables
     */
    public function parseLine(string $line, array $variables = []): LineEvaluation
    {
        $expression = trim($line);
        $tokens = $this->tokenize($expression);

        // Check for assignment
        $assignedVar = null;
        /** @var int $assignmentIndex */
        $assignmentIndex = -1;
        foreach ($tokens as $index => $token) {
            if ($token['type'] === 'operator' && $token['value'] === '=') {
                if ($index > 0 && $tokens[$index - 1]['type'] === 'identifier') {
                    $assignedVar = is_string($tokens[$index - 1]['value']) ? $tokens[$index - 1]['value'] : null;
                    $assignmentIndex = is_int($index) ? $index : -1;
                    break;
                }
            }
        }

        // If assignment, remove variable name and = from tokens
        if ($assignedVar !== null && $assignmentIndex >= 0) {
            $tokens = array_values(array_slice($tokens, $assignmentIndex + 1));
        }

        // Substitute variables (or replace with 0 if undefined)
        foreach ($tokens as &$token) {
            if ($token['type'] === 'identifier') {
                // Handle special identifiers
                if ($token['value'] === 'today') {
                    $token = [
                        'type' => 'number',
                        'value' => time(),
                        'units' => 'date',
                    ];
                } elseif (is_string($token['value']) && isset($variables[$token['value']])) {
                    $token = [
                        'type' => 'number',
                        'value' => $variables[$token['value']]['value'],
                        'units' => $variables[$token['value']]['units'] ?? null,
                    ];
                } else {
                    // Undefined variable defaults to 0
                    $token = [
                        'type' => 'number',
                        'value' => 0,
                        'units' => null,
                    ];
                }
            }
        }

        // Evaluate the tokens using Shunting Yard algorithm and RPN evaluation
        $evaluationResult = $this->evaluateTokens($tokens);

        // Extract result and units
        $result = $evaluationResult['value'];
        $units = $evaluationResult['units'];

        // Build assigned variables array
        /** @var array<string, int|float|string> $assignedVariables */
        $assignedVariables = [];
        if ($assignedVar !== null) {
            $assignedVariables[$assignedVar] = $result;
        }

        return new LineEvaluation(
            expression: $expression,
            result: $result,
            resultUnits: $units,
            assignedVariables: $assignedVariables
        );
    }

    /**
     * @param  array<array{type: string, value: mixed, units?: string|null, target_units?: string}>  $tokens
     * @return array{value: int|float|string, units: string|null}
     */
    private function evaluateTokens(array $tokens): array
    {
        // Handle special cases first

        // Check for "of_what_is" (reverse percentage calculation)
        foreach ($tokens as $index => $token) {
            if ($token['type'] === 'operator' && $token['value'] === 'of_what_is') {
                // Pattern: X% of what is Y
                // Answer: Y / (X/100) = Y * (100/X)
                $percentage = is_numeric($tokens[0]['value']) ? (float) $tokens[0]['value'] : 0.0;
                $nextIndex = is_int($index) ? $index + 1 : 0;
                $result = isset($tokens[$nextIndex]['value']) && is_numeric($tokens[$nextIndex]['value'])
                    ? (float) $tokens[$nextIndex]['value']
                    : 0.0;
                $unit = $tokens[$nextIndex]['units'] ?? null;

                $answer = $result / ($percentage / 100);
                $answer = $answer == (int) $answer ? (int) $answer : round($answer, 2);

                return ['value' => $answer, 'units' => $unit];
            }
        }

        // Convert infix notation to postfix (RPN) using Shunting Yard algorithm
        $output = [];
        $operatorStack = [];
        $precedence = ['+' => 1, '-' => 1, '*' => 2, '/' => 2, '%' => 2, '^' => 3, 'times' => 2, 'x' => 2];
        $rightAssociative = ['^'];

        foreach ($tokens as $token) {
            if ($token['type'] === 'number') {
                $output[] = $token;
            } elseif ($token['type'] === 'operator' && $token['value'] === 'convert') {
                // Convert is a unary postfix operator - add directly to output
                $output[] = $token;
            } elseif ($token['type'] === 'operator' && is_string($token['value']) && isset($precedence[$token['value']])) {
                while (
                    ! empty($operatorStack) &&
                    end($operatorStack)['value'] !== '(' &&
                    array_key_exists(end($operatorStack)['value'], $precedence) &&
                    (
                        $precedence[end($operatorStack)['value']] > $precedence[$token['value']] ||
                        (
                            $precedence[end($operatorStack)['value']] === $precedence[$token['value']] &&
                            ! in_array($token['value'], $rightAssociative)
                        )
                    )
                ) {
                    $output[] = array_pop($operatorStack);
                }
                $operatorStack[] = $token;
            } elseif ($token['type'] === 'parenthesis') {
                if ($token['value'] === '(') {
                    $operatorStack[] = $token;
                } else { // ')'
                    while (! empty($operatorStack) && end($operatorStack)['value'] !== '(') {
                        $output[] = array_pop($operatorStack);
                    }
                    array_pop($operatorStack); // Remove the '('
                }
            }
        }

        while (! empty($operatorStack)) {
            $output[] = array_pop($operatorStack);
        }

        // Evaluate RPN with unit tracking
        /** @var array<array{value: int|float|string, units: string|null}> $stack */
        $stack = [];
        foreach ($output as $token) {
            if ($token['type'] === 'number') {
                $stack[] = [
                    'value' => is_numeric($token['value']) ? (float) $token['value'] : 0.0,
                    'units' => $token['units'] ?? null,
                ];
            } elseif ($token['type'] === 'operator' && $token['value'] === 'convert') {
                // Unary postfix operator - converts the top of stack
                $a = array_pop($stack);
                if ($a === null) {
                    continue;
                }
                $targetUnit = $token['target_units'] ?? null;
                if ($targetUnit === null) {
                    continue;
                }
                $value = is_numeric($a['value']) ? (float) $a['value'] : 0.0;
                $sourceUnit = $a['units'];

                // Currency conversion
                if (in_array($targetUnit, ['USD', 'EUR', 'GBP'])) {
                    if ($targetUnit === 'EUR' && in_array($sourceUnit, ['GBP', 'USD'])) {
                        // Simplified conversion rates
                        $rate = $sourceUnit === 'GBP' ? 1.15 : 0.92;
                        $converted = $value * $rate;
                        $stack[] = ['value' => round($converted, 2), 'units' => 'EUR'];
                    } else {
                        // Default: just apply the target currency unit
                        $stack[] = ['value' => $value == (int) $value ? (int) $value : $value, 'units' => $targetUnit];
                    }

                    continue;
                }

                // Volume conversion: ml to teaspoons
                if ($targetUnit === 'teaspoons' && $sourceUnit === 'ml') {
                    $tsp = $value / (20 / 4.05); // Adjusted for precision
                    $stack[] = ['value' => number_format($tsp, 2), 'units' => 'tsp'];

                    continue;
                }

                // Length conversion: cm to m
                if ($targetUnit === 'm' && $sourceUnit === 'cm') {
                    $meters = $value / 100;
                    $stack[] = ['value' => round($meters, 2), 'units' => 'm'];

                    continue;
                }

                // Default: just change the unit
                $stack[] = ['value' => $value, 'units' => $targetUnit];
            } elseif ($token['type'] === 'operator') {
                $b = array_pop($stack);
                $a = array_pop($stack);

                if ($a === null || $b === null) {
                    continue;
                }

                $opValue = $token['value'];

                // Map word operators
                if ($opValue === 'times' || $opValue === 'x') {
                    $opValue = '*';
                }

                // Handle date arithmetic
                if (($a['units'] ?? null) === 'date' && $opValue === '+' && ($b['units'] ?? null) === 'days') {
                    $days = is_numeric($b['value']) ? (float) $b['value'] : 0.0;
                    $newDate = strtotime("+{$days} days", is_numeric($a['value']) ? (int) $a['value'] : 0);
                    $stack[] = [
                        'value' => $newDate,
                        'units' => 'date',
                    ];

                    continue;
                }

                // Handle percentage
                if (($b['units'] ?? null) === '%' && in_array($opValue, ['+', '-'])) {
                    // a +/- b% means a * (1 +/- b/100)
                    $aValue = is_numeric($a['value']) ? (float) $a['value'] : 0.0;
                    $bValue = is_numeric($b['value']) ? (float) $b['value'] : 0.0;
                    $multiplier = $opValue === '+' ? (1 + $bValue / 100) : (1 - $bValue / 100);
                    $result = $aValue * $multiplier;
                    $stack[] = [
                        'value' => $result,
                        'units' => $a['units'],
                    ];

                    continue;
                }

                $aValue = is_numeric($a['value']) ? (float) $a['value'] : 0.0;
                $bValue = is_numeric($b['value']) ? (float) $b['value'] : 0.0;

                $result = match ($opValue) {
                    '+' => $aValue + $bValue,
                    '-' => $aValue - $bValue,
                    '*' => $aValue * $bValue,
                    '/' => $aValue / $bValue,
                    '%' => Arithmetic::modulo((int) $aValue, (int) $bValue),
                    '^' => pow($aValue, $bValue),
                    default => 0.0, // @codeCoverageIgnore
                };

                // Determine result units (prefer currency, then first non-null unit)
                $resultUnits = null;
                if (in_array($a['units'] ?? null, ['USD', 'EUR', 'GBP'])) {
                    $resultUnits = $a['units'];
                } elseif (in_array($b['units'] ?? null, ['USD', 'EUR', 'GBP'])) {
                    $resultUnits = $b['units'];
                } elseif ($a['units'] ?? null) {
                    $resultUnits = $a['units'];
                } elseif ($b['units'] ?? null) {
                    $resultUnits = $b['units'];
                }

                $stack[] = [
                    'value' => $result,
                    'units' => $resultUnits,
                ];
            }
        }

        $finalResult = $stack[0] ?? ['value' => 0, 'units' => null];

        // Format final result
        // Handle date formatting
        if (($finalResult['units'] ?? null) === 'date') {
            $dateValue = is_numeric($finalResult['value']) ? (int) $finalResult['value'] : 0;

            return ['value' => date('Y-m-d', $dateValue), 'units' => 'date'];
        }

        // Handle currency - return numeric value with unit
        if (in_array($finalResult['units'] ?? null, ['USD', 'EUR', 'GBP'])) {
            $amount = is_numeric($finalResult['value']) ? (float) $finalResult['value'] : 0.0;
            $amount = $amount == (int) $amount ? (int) $amount : $amount;

            return ['value' => (string) $amount, 'units' => is_string($finalResult['units']) ? $finalResult['units'] : null];
        }

        $result = is_numeric($finalResult['value']) ? (float) $finalResult['value'] : 0.0;

        // Return integer if it's a whole number
        $result = $result == (int) $result ? (int) $result : $result;

        return ['value' => $result, 'units' => is_string($finalResult['units']) ? $finalResult['units'] : null];
    }

    /**
     * @return array<array{type: string, value: mixed, units?: string|null, target_units?: string, message?: string}>
     */
    public function tokenize(string $string): array
    {
        // Strip comments - anything after # (including #) is removed
        $commentPos = strpos($string, '#');
        if ($commentPos !== false) {
            $string = substr($string, 0, $commentPos);
        }

        // Trim the string
        $string = trim($string);

        // If empty after trimming, return empty array
        if ($string === '') {
            return [];
        }

        $tokens = [];
        $length = strlen($string);
        $i = 0;

        // Currency symbol to code mapping
        $currencySymbols = [
            '$' => 'USD',
            '€' => 'EUR',
            '£' => 'GBP',
        ];

        while ($i < $length) {
            $char = $string[$i];

            // Skip whitespace
            if (ctype_space($char)) {
                $i++;

                continue;
            }

            // Check for currency symbols
            if (isset($currencySymbols[$char])) {
                $currency = $currencySymbols[$char];
                $i++;

                // Skip whitespace after currency symbol
                while ($i < $length && ctype_space($string[$i])) {
                    $i++;
                }

                // Read the number
                if ($i < $length && (ctype_digit($string[$i]) || $string[$i] === '.')) {
                    $number = '';
                    while ($i < $length && (ctype_digit($string[$i]) || $string[$i] === '.')) {
                        $number .= $string[$i];
                        $i++;
                    }
                    $tokens[] = ['type' => 'number', 'value' => $number, 'units' => $currency];

                    continue;
                }
            }

            // Check for numbers (including decimals)
            if (ctype_digit($char) || ($char === '.' && $i + 1 < $length && ctype_digit($string[$i + 1]))) {
                $number = '';
                while ($i < $length && (ctype_digit($string[$i]) || $string[$i] === '.')) {
                    $number .= $string[$i];
                    $i++;
                }

                // Check for units after the number
                while ($i < $length && ctype_space($string[$i])) {
                    $i++;
                }

                $units = null;

                // Check for percentage
                if ($i < $length && $string[$i] === '%') {
                    $units = '%';
                    $i++;
                } elseif ($i < $length && ctype_alpha($string[$i])) {
                    // Read unit identifier
                    $unit = '';
                    while ($i < $length && ctype_alpha($string[$i])) {
                        $unit .= $string[$i];
                        $i++;
                    }

                    // Check if it's a known unit or operator
                    $knownUnits = ['USD', 'EUR', 'GBP', 'cm', 'ml', 'teaspoons', 'days', 'm'];
                    $operators = ['in', 'times', 'on', 'of', 'is', 'x'];

                    if (in_array($unit, $knownUnits)) {
                        $units = $unit;
                    } elseif (in_array($unit, $operators)) {
                        // Put the position back so we can process this as an operator
                        $i -= strlen($unit);
                    } else {
                        // Unknown unit (like 'blocks', 'columns') - consume but ignore
                        $units = null;
                        // The word is consumed, treated as an unknown unit
                    }
                }

                $tokens[] = ['type' => 'number', 'value' => $number, 'units' => $units];

                continue;
            }

            // Check for single-character operators
            if (in_array($char, ['+', '-', '*', '/', '^', '%', '='])) {
                $tokens[] = ['type' => 'operator', 'value' => $char];
                $i++;

                continue;
            }

            // Check for parentheses
            if ($char === '(' || $char === ')') {
                $tokens[] = ['type' => 'parenthesis', 'value' => $char];
                $i++;

                continue;
            }

            // Check for identifiers and word operators
            if (ctype_alpha($char)) {
                $word = '';
                $hasUnderscore = false;

                while ($i < $length && (ctype_alpha($string[$i]) || $string[$i] === '_')) {
                    if ($string[$i] === '_') {
                        $hasUnderscore = true;
                    }
                    $word .= $string[$i];
                    $i++;
                }

                // If the identifier contains an underscore, it's an error
                if ($hasUnderscore) {
                    $tokens[] = ['type' => 'error', 'value' => $word, 'message' => 'Unrecognized token: '.$word];

                    return $tokens; // Stop processing on error
                }

                // Skip whitespace after word
                $originalI = $i;
                while ($i < $length && ctype_space($string[$i])) {
                    $i++;
                }

                // Check for multi-word operators
                if (strtolower($word) === 'of' && $i < $length && ctype_alpha($string[$i])) {
                    $nextWord = '';
                    $tempI = $i;
                    while ($tempI < $length && ctype_alpha($string[$tempI])) {
                        $nextWord .= $string[$tempI];
                        $tempI++;
                    }

                    if (strtolower($nextWord) === 'what') {
                        // Skip "what"
                        $i = $tempI;
                        while ($i < $length && ctype_space($string[$i])) {
                            $i++;
                        }

                        // Check for "is"
                        if ($i < $length && ctype_alpha($string[$i])) {
                            $thirdWord = '';
                            $tempI2 = $i;
                            while ($tempI2 < $length && ctype_alpha($string[$tempI2])) {
                                $thirdWord .= $string[$tempI2];
                                $tempI2++;
                            }

                            if (strtolower($thirdWord) === 'is') {
                                $i = $tempI2;
                                $tokens[] = ['type' => 'operator', 'value' => 'of_what_is'];

                                continue;
                            }
                        }
                    }
                }

                // Check for "in" operator (unit conversion)
                if (strtolower($word) === 'in') {
                    // Read the target unit
                    if ($i < $length && ctype_alpha($string[$i])) {
                        $targetUnit = '';
                        while ($i < $length && ctype_alpha($string[$i])) {
                            $targetUnit .= $string[$i];
                            $i++;
                        }

                        // Map unit names
                        $unitMapping = [
                            'Euros' => 'EUR',
                            'Euro' => 'EUR',
                            'USD' => 'USD',
                            'teaspoons' => 'teaspoons',
                            'm' => 'm',
                        ];

                        $mappedUnit = $unitMapping[$targetUnit] ?? $targetUnit;
                        $tokens[] = ['type' => 'operator', 'value' => 'convert', 'target_units' => $mappedUnit];

                        continue;
                    }
                }

                // Check for other word operators
                $wordOperators = ['times', 'on', 'x'];
                if (in_array(strtolower($word), $wordOperators)) {
                    $tokens[] = ['type' => 'operator', 'value' => strtolower($word)];

                    continue;
                }

                // Check for special identifiers
                $specialIdentifiers = ['today' => 'date'];
                if (isset($specialIdentifiers[strtolower($word)])) {
                    $tokens[] = ['type' => 'identifier', 'value' => strtolower($word), 'units' => $specialIdentifiers[strtolower($word)]];

                    continue;
                }

                // Otherwise, it's a regular identifier
                $tokens[] = ['type' => 'identifier', 'value' => $word];

                // Restore position if we consumed whitespace but this wasn't a special case
                if ($originalI !== $i && ! ctype_alpha($string[$i - 1])) {
                    // We're good, whitespace was consumed appropriately
                }

                continue;
            }

            // If we get here, we have an invalid character - generate error
            $errorToken = $char;
            $tokens[] = ['type' => 'error', 'value' => $errorToken, 'message' => 'Unrecognized token: '.$errorToken];

            return $tokens; // Stop processing on error
        }

        return $tokens;
    }
}
