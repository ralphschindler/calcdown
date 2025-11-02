<?php

use Calcdown\CalcdownParser;

// lets create a pest data provider:
it('parses calcdown lines correctly', function (string $line, array $expected): void {
    $parser = new CalcdownParser;

    $evaluation = $parser->parseLine($line)->toArray();

    expect($evaluation)->toEqual($expected);
})->with([
    ['2 + 2', ['expression' => '2 + 2', 'result' => 4, 'units' => null]],
    ['5 * (3 + 1)', ['expression' => '5 * (3 + 1)', 'result' => 20, 'units' => null]],
    ['58 columns * 11 blocks', ['expression' => '58 columns * 11 blocks', 'result' => 638, 'units' => null]],
    ['$7 * 4', ['expression' => '$7 * 4', 'result' => '28', 'units' => 'USD']],
    ['4 GBP in Euros', ['expression' => '4 GBP in Euros', 'result' => 4.60, 'units' => 'EUR']],
    ['sum in USD - 4%', ['expression' => 'sum in USD - 4%', 'result' => 0, 'units' => 'USD']],
    ['today + 17 days', ['expression' => 'today + 17 days', 'result' => date('Y-m-d', strtotime('+17 days')), 'units' => 'date']],
    ['20 ml in teaspoons', ['expression' => '20 ml in teaspoons', 'result' => '4.05', 'units' => 'tsp']],
    ['20% of what is 30 cm', ['expression' => '20% of what is 30 cm', 'result' => 150, 'units' => 'cm']],
    ['(25 cm x 6 + 5%) in m', ['expression' => '(25 cm x 6 + 5%) in m', 'result' => 1.58, 'units' => 'm']],
    ['price = $8 times 3', ['expression' => 'price = $8 times 3', 'result' => '24', 'units' => 'USD', 'assigned_variables' => ['price' => '24']]],
    ['10 / 2', ['expression' => '10 / 2', 'result' => 5, 'units' => null]],
    ['10 - 3', ['expression' => '10 - 3', 'result' => 7, 'units' => null]],
    ['2 ^ 3', ['expression' => '2 ^ 3', 'result' => 8, 'units' => null]],
    ['5 + 10 EUR', ['expression' => '5 + 10 EUR', 'result' => 15, 'units' => 'EUR']],
    ['5 cm + 10', ['expression' => '5 cm + 10', 'result' => 15, 'units' => 'cm']],
    ['$ 10 + 5', ['expression' => '$ 10 + 5', 'result' => '15', 'units' => 'USD']],
    ['100 GBP', ['expression' => '100 GBP', 'result' => 100, 'units' => 'GBP']],
    ['50 cm in inches', ['expression' => '50 cm in inches', 'result' => 50, 'units' => 'inches']],
    ['5 + 10 cm', ['expression' => '5 + 10 cm', 'result' => 15, 'units' => 'cm']],
    ['10m times 2', ['expression' => '10m times 2', 'result' => 20, 'units' => 'm']],
]);
