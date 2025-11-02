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
    [
        "2 + 2\n\n5 * 3",
        [
            ['expression' => '2 + 2', 'result' => 4, 'units' => null],
            ['expression' => '5 * 3', 'result' => 15, 'units' => null],
        ],
    ],
    [
        "a = 10\nb = 3\na % b",
        [
            ['expression' => 'a = 10', 'result' => 10, 'units' => null, 'assigned_variables' => ['a' => 10]],
            ['expression' => 'b = 3', 'result' => 3, 'units' => null, 'assigned_variables' => ['b' => 3]],
            ['expression' => 'a % b', 'result' => 1, 'units' => null],
        ],
    ],
]);

it('returns null for empty block finalLine', function (): void {
    $parser = new CalcdownParser;
    $block = $parser->parseBlock('');

    expect($block->finalLine())->toBeNull();
});

it('returns finalLine for non-empty block', function (): void {
    $parser = new CalcdownParser;
    $block = $parser->parseBlock('2 + 2');

    expect($block->finalLine())->not->toBeNull();
    expect($block->finalLine()->result)->toBe(4);
});
