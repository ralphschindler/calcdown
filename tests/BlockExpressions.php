<?php

use Calcdown\CalcdownParser;

// lets create a pest data provider:
it('parses calcdown blocks (muli-line) correctly', function (string $block, array $expected): void {
    $parser = new CalcdownParser;

    $block = $parser->parseBlock($block);
    $lines = $block->toArray();

    expect($lines)->toEqual($expected);
})->with([
    [
        "price = $2 + 2\nprice + 8%",
        [
            ['expression' => 'price = $2 + 2', 'result' => 4, 'units' => 'USD', 'assigned_variables' => ['price' => 4]],
            ['expression' => 'price + 8%', 'result' => 4.32, 'units' => 'USD'],
        ],
    ],
]);
