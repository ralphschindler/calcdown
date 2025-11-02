<?php

declare(strict_types=1);

namespace Calcdown;

class LineEvaluation
{
    /**
     * @param array<string, int|float|string> $assignedVariables
     */
    public function __construct(
        public string $expression,
        public int|float|string $result,
        public ?string $resultUnits,
        public array $assignedVariables = [],
    ) {}

    /**
     * @return array{expression: string, result: int|float|string, units: string|null, assigned_variables?: array<string, int|float|string>}
     */
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
