<?php

use Calcdown\CalcdownParser;
use Livewire\Volt\Component;

new class extends Component {
    public string $expressions = "price = $8 times 3\ntax = price + 15%\ntotal = tax";
    public array $results = [];

    public function mount(): void
    {
        $this->calculate();
    }

    public function updatedExpressions(): void
    {
        $this->calculate();
    }

    public function calculate(): void
    {
        $calcdown = new CalcdownParser();
        $this->results = [];

        try {
            $blockResult = $calcdown->parseBlock($this->expressions);

            foreach ($blockResult->lines as $index => $lineEvaluation) {
                $trimmed = trim($lineEvaluation->expression);

                // Check if empty or comment
                if (empty($trimmed) || str_starts_with($trimmed, '#')) {
                    $this->results[$index] = [
                        'expression' => $lineEvaluation->expression,
                        'result' => '',
                        'units' => '',
                        'isEmpty' => true
                    ];
                } else {
                    $this->results[$index] = [
                        'expression' => $lineEvaluation->expression,
                        'result' => $lineEvaluation->result,
                        'units' => $lineEvaluation->resultUnits ?? '',
                        'isEmpty' => false
                    ];
                }
            }
        } catch (Exception $e) {
            // If entire block fails, show error
            $this->results[0] = [
                'expression' => '',
                'result' => 'Error',
                'units' => '',
                'isEmpty' => false
            ];
        }
    }
}
?>

<div class="w-full min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 p-8">
    <div class="max-w-7xl mx-auto">
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Calcdown</h1>
            <p class="text-gray-600">A Numi-style calculator for the web</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
            <div class="grid grid-cols-2 divide-x divide-gray-200">
                <!-- Left side: Expressions input -->
                <div class="p-6">
                    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Expressions</h2>
                    <textarea
                        wire:model.live.debounce.300ms="expressions"
                        class="w-full h-[600px] font-mono text-base p-4 bg-gray-50 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none resize-none"
                        placeholder="Enter your calculations here...&#10;&#10;Examples:&#10;price = $8 times 3&#10;tax = price + 15%&#10;total = tax in EUR"
                    ></textarea>
                </div>

                <!-- Right side: Results display -->
                <div class="p-6 bg-gray-50">
                    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Results</h2>
                    <div class="font-mono text-base space-y-1">
                        @foreach($results as $index => $result)
                            <div class="min-h-[1.5rem] px-4 py-1 flex items-center justify-end">
                                @if($result['isEmpty'])
                                    <span class="text-gray-300">&nbsp;</span>
                                @else
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-2xl font-semibold {{ $result['result'] === 'Error' ? 'text-red-500' : 'text-gray-900' }}">
                                            {{ $result['result'] }}
                                        </span>
                                        @if($result['units'])
                                            <span class="text-sm text-gray-500">{{ $result['units'] }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Examples and tips -->
        <div class="mt-8 grid grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="font-semibold text-gray-900 mb-3">💰 Currency</h3>
                <code class="text-sm text-gray-600 block">$100 + $50<br>€200 - 10%<br>£50 in EUR</code>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="font-semibold text-gray-900 mb-3">📐 Units</h3>
                <code class="text-sm text-gray-600 block">100 cm in m<br>20 ml in tsp<br>50 cm in inches</code>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="font-semibold text-gray-900 mb-3">📅 Dates</h3>
                <code class="text-sm text-gray-600 block">today + 7 days<br>today + 30 days</code>
            </div>
        </div>
    </div>
</div>

