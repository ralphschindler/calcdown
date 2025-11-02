<?php

use Calcdown\CalcdownParser;

it('handles empty stack during conversion', function (): void {
    $parser = new CalcdownParser;
    $reflection = new ReflectionClass($parser);
    $method = $reflection->getMethod('evaluateTokens');
    $method->setAccessible(true);

    // Test empty stack with convert operator (line 197)
    $tokens = [
        ['type' => 'operator', 'value' => 'convert', 'target_units' => 'USD'],
    ];

    $result = $method->invoke($parser, $tokens);
    expect($result)->toEqual(['value' => 0, 'units' => null]);
});

it('handles conversion without target unit', function (): void {
    $parser = new CalcdownParser;
    $reflection = new ReflectionClass($parser);
    $method = $reflection->getMethod('evaluateTokens');
    $method->setAccessible(true);

    // Test convert operator without target_units (line 201)
    $tokens = [
        ['type' => 'number', 'value' => 10, 'units' => null],
        ['type' => 'operator', 'value' => 'convert'],  // missing target_units
    ];

    $result = $method->invoke($parser, $tokens);
    // When conversion skips due to null target_units, stack has remaining number
    expect($result)->toEqual(['value' => 0, 'units' => null]);
});

it('handles empty stack with binary operator', function (): void {
    $parser = new CalcdownParser;
    $reflection = new ReflectionClass($parser);
    $method = $reflection->getMethod('evaluateTokens');
    $method->setAccessible(true);

    // Test binary operator with insufficient operands (line 244)
    $tokens = [
        ['type' => 'number', 'value' => 10, 'units' => null],
        ['type' => 'operator', 'value' => '+'],  // only one operand
    ];

    $result = $method->invoke($parser, $tokens);
    // When operator skips due to null operands, remaining value is returned
    expect($result)->toEqual(['value' => 0, 'units' => null]);
});
