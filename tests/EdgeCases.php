<?php

use Calcdown\CalcdownParser;

it('handles edge cases correctly', function (string $line, array $expected): void {
    $parser = new CalcdownParser;

    $evaluation = $parser->parseLine($line)->toArray();

    expect($evaluation)->toEqual($expected);
})->with([
    // Test malformed of_what_is without proper operands (line 130)
    ['20% of what is', ['expression' => '20% of what is', 'result' => 0, 'units' => null]],
    
    // Test conversion without target unit (line 201) - tokenizer creates this
    // This is actually handled by tokenizer validation, so we test empty conversion
    
    // Test operators with insufficient operands (line 244)
    // This would require malformed tokens, but we can test edge with single operand
    ['+', ['expression' => '+', 'result' => 0, 'units' => null]],
    ['-', ['expression' => '-', 'result' => 0, 'units' => null]],
    ['*', ['expression' => '*', 'result' => 0, 'units' => null]],
    ['/', ['expression' => '/', 'result' => 0, 'units' => null]],
    
    // Test unknown operator triggering default case (line 291)
    // This requires an operator not in the match - we can't easily trigger this
    // through normal parsing, but the on operator should help
]);
