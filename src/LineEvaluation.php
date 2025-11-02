<?php

declare(strict_types=1);

namespace Calcdown;

class LineEvaluation
{
    public function __construct(
        public string $expression,
        public int|float|string $result,
        public ?string $resultUnits,
        public array $assignedVariables = [],
    ) {}

    public function toArray(): array
    {
        $output = [
            'expression' => $this->expression,
            'result' => $this->result,
            'units' => $this->resultUnits,
        ];

        if (! empty($this->assignedVariables)) {
            $output['assigned_variables'] = $this->assignedVariables;
        }

        return $output;
    }
}
