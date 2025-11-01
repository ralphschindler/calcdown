<?php

declare(strict_types=1);

namespace Calcdown;

class CalcdownParser
{
    public function parseLines(array $lines): Result
    {
        // process $lines
        return new Result([], [], 0);
    }

    public function parseLine(string $line): array
    {
        $expression = trim($line);
        $tokens = $this->tokenize($expression);

        // Evaluate the tokens using Shunting Yard algorithm and RPN evaluation
        $result = $this->evaluateTokens($tokens);

        // parse a line and return ['expression' => ..., 'result' => ...]
        return [
            'expression' => $expression,
            'result' => $result,
        ];
    }

    private function evaluateTokens(array $tokens): float|int
    {
        // Convert infix notation to postfix (RPN) using Shunting Yard algorithm
        $output = [];
        $operatorStack = [];
        $precedence = ['+' => 1, '-' => 1, '*' => 2, '/' => 2, '%' => 2, '^' => 3];
        $rightAssociative = ['^'];

        foreach ($tokens as $token) {
            if ($token['type'] === 'number') {
                $output[] = $token;
            } elseif ($token['type'] === 'operator') {
                while (
                    ! empty($operatorStack) &&
                    end($operatorStack)['value'] !== '(' &&
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

        // Evaluate RPN
        $stack = [];
        foreach ($output as $token) {
            if ($token['type'] === 'number') {
                $stack[] = (float) $token['value'];
            } elseif ($token['type'] === 'operator') {
                $b = array_pop($stack);
                $a = array_pop($stack);
                $result = match ($token['value']) {
                    '+' => $a + $b,
                    '-' => $a - $b,
                    '*' => $a * $b,
                    '/' => $a / $b,
                    '%' => \MathPHP\Arithmetic::modulo((int) $a, (int) $b),
                    '^' => pow($a, $b),
                };
                $stack[] = $result;
            }
        }

        $result = $stack[0];

        // Return integer if it's a whole number
        return $result == (int) $result ? (int) $result : $result;
    }

    public function tokenize($string): array
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
                $startPos = $i;

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
