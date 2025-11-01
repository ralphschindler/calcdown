<?php

use Calcdown\CalcdownParser;

// lets create a pest data provider:
it('parses calcdown lines correctly', function (string $line, array $expected): void {
    $parser = new CalcdownParser;

    $result = $parser->parseLine($line);

    expect($result)->toEqual($expected);
})->with([
    ['5 * 3', ['expression' => '5 * 3', 'result' => 15]],
    ['10 / 2', ['expression' => '10 / 2', 'result' => 5]],
    // ['20% of $10', ['expression' => '20% of $10', 'result' => '$2']],
    // ['5% on $30', ['expression' => '5% on $30', 'result' => '$31.50']],
    // ['$50 as a % of $100', ['expression' => '$50 as a % of $100', 'result' => '50%']],
    // ['5% of what is 6 EUR', ['expression' => '5% of what is 6 EUR', 'result' => '€ 120']],
    // ['5% off what is 6 EUR', ['expression' => '5% off what is 6 EUR', 'result' => '€ 6.32']],
    // ['v = $20', ['expression' => 'v = $20', 'result' => '$20', ['v' => '$20']]],
    // ['20 inches in cm', ['expression' => '20 inches in cm', 'result' => '50.8 cm']]
]);
