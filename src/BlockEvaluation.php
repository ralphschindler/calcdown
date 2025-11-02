<?php

declare(strict_types=1);

namespace Calcdown;

class BlockEvaluation
{
    /**
     * @param  array<LineEvaluation>  $lines
     */
    public function __construct(
        public array $lines,
    ) {}

    public function finalLine(): ?LineEvaluation
    {
        if (empty($this->lines)) {
            return null;
        }

        return $this->lines[array_key_last($this->lines)];
    }

    /**
     * @return array<array{expression: string, result: int|float|string, units: string|null, assigned_variables?: array<string, int|float|string>}>
     */
    public function toArray(): array
    {
        return array_map(fn (LineEvaluation $line) => $line->toArray(), $this->lines);
    }
}
