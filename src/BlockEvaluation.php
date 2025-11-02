<?php

declare(strict_types=1);

namespace Calcdown;

class BlockEvaluation
{
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

    public function toArray(): array
    {
        return array_map(fn (LineEvaluation $line) => $line->toArray(), $this->lines);
    }
}
