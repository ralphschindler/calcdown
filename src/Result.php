<?php

declare(strict_types=1);

namespace Calcdown;

class Result
{
    public function __construct(
        public array $linesWithResults,
        public array $assignedVariables,
        public int|float $finalResult
    ) {}
}
