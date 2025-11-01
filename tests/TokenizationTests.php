<?php

use Calcdown\CalcdownParser;

// lets create a pest data provider:
it('parses calcdown lines correctly', function (string $string, array $expected): void {
    $parser = new CalcdownParser;

    $result = $parser->tokenize($string);

    expect($result)->toEqual($expected);
})->with([
    [
        '2 + 2',
        [
            ['type' => 'number', 'value' => '2', 'units' => null],
            ['type' => 'operator', 'value' => '+'],
            ['type' => 'number', 'value' => '2', 'units' => null],
        ],
    ],
    [
        '5 * (3 + 1)',
        [
            ['type' => 'number', 'value' => '5', 'units' => null],
            ['type' => 'operator', 'value' => '*'],
            ['type' => 'parenthesis', 'value' => '('],
            ['type' => 'number', 'value' => '3', 'units' => null],
            ['type' => 'operator', 'value' => '+'],
            ['type' => 'number', 'value' => '1', 'units' => null],
            ['type' => 'parenthesis', 'value' => ')'],
        ],
    ],
    [
        '58 columns * 11 blocks', // unknown units
        [
            ['type' => 'number', 'value' => '58', 'units' => null],
            ['type' => 'operator', 'value' => '*'],
            ['type' => 'number', 'value' => '11', 'units' => null],
        ],
    ],
    [
        '$7 * 4', // known units
        [
            ['type' => 'number', 'value' => '7', 'units' => 'USD'],
            ['type' => 'operator', 'value' => '*'],
            ['type' => 'number', 'value' => '4', 'units' => null],
        ],
    ],
    [
        '4 GBP in Euros', // known units
        [
            ['type' => 'number', 'value' => '4', 'units' => 'GBP'],
            ['type' => 'operator', 'value' => 'convert', 'target_units' => 'EUR'],
        ],
    ],
    [
        'sum in USD - 4%', // function with units and percentage
        [
            ['type' => 'identifier', 'value' => 'sum'],
            ['type' => 'operator', 'value' => 'convert', 'target_units' => 'USD'],
            ['type' => 'operator', 'value' => '-'],
            ['type' => 'number', 'value' => '4', 'units' => '%'],
        ],
    ],
    [
        'today + 17 days', // date manipulation
        [
            ['type' => 'identifier', 'value' => 'today', 'units' => 'date'],
            ['type' => 'operator', 'value' => '+'],
            ['type' => 'number', 'value' => '17', 'units' => 'days'],
        ],
    ],
    [
        '20 ml in teaspoons', // volume conversion
        [
            ['type' => 'number', 'value' => '20', 'units' => 'ml'],
            ['type' => 'operator', 'value' => 'convert', 'target_units' => 'teaspoons'],
        ],
    ],
    [
        '20% of what is 30 cm', // algebraic percentage (150cm)
        [
            ['type' => 'number', 'value' => '20', 'units' => '%'],
            ['type' => 'operator', 'value' => 'of_what_is'],
            ['type' => 'number', 'value' => '30', 'units' => 'cm'],
        ],
    ],
    [
        '(25 cm x 6 + 5%) in m', // complex expression with conversion
        [
            ['type' => 'parenthesis', 'value' => '('],
            ['type' => 'number', 'value' => '25', 'units' => 'cm'],
            ['type' => 'operator', 'value' => 'x'],
            ['type' => 'number', 'value' => '6', 'units' => null],
            ['type' => 'operator', 'value' => '+'],
            ['type' => 'number', 'value' => '5', 'units' => '%'],
            ['type' => 'parenthesis', 'value' => ')'],
            ['type' => 'operator', 'value' => 'convert', 'target_units' => 'm'],
        ],
    ],
    [
        'price = $8 times 3',
        [
            ['type' => 'identifier', 'value' => 'price'],
            ['type' => 'operator', 'value' => '='],
            ['type' => 'number', 'value' => '8', 'units' => 'USD'],
            ['type' => 'operator', 'value' => 'times'],
            ['type' => 'number', 'value' => '3', 'units' => null],
        ],
    ],
    [
        'fee on price in Euro',
        [
            ['type' => 'identifier', 'value' => 'fee'],
            ['type' => 'operator', 'value' => 'on'],
            ['type' => 'identifier', 'value' => 'price'],
            ['type' => 'operator', 'value' => 'convert', 'target_units' => 'EUR'],
        ],
    ],
    [
        '# This is a comment line',
        [],
    ],
    [
        '2+2 # inline comment',
        [
            ['type' => 'number', 'value' => '2', 'units' => null],
            ['type' => 'operator', 'value' => '+'],
            ['type' => 'number', 'value' => '2', 'units' => null],
        ],
    ],
    [
        '   ', // empty space
        [],
    ],
    [
        'invalid_token @ here', // stop on first invalid token
        [
            ['type' => 'error', 'value' => 'invalid_token', 'message' => 'Unrecognized token: invalid_token'],
        ],
    ],
]);
