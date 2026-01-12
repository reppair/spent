<flux:card class="overflow-hidden min-w-[12rem]">
    <flux:text>{{ __('Spent Total') }}</flux:text>

    @if($this->stats['total'] > 0)
        <flux:heading size="xl" class="mt-2 tabular-nums">{{ $this->stats['formatted_total'] }}</flux:heading>

        @if(!empty($this->stats['chart_data']))
            <flux:chart :value="$this->stats['chart_data']" class="aspect-[3/2] mt-2 -mx-2 -mb-2">
                <flux:chart.svg>
                    <flux:chart.line field="amount" class="text-sky-500 dark:text-sky-400" />
                    <flux:chart.area field="amount" class="text-sky-100 dark:text-sky-400/30" />
                    <flux:chart.axis axis="x" field="date">
                        <flux:chart.axis.line />
                        <flux:chart.axis.tick />
                    </flux:chart.axis>
                    <flux:chart.axis axis="y">
                        <flux:chart.axis.grid />
                        <flux:chart.axis.tick />
                    </flux:chart.axis>
                    <flux:chart.cursor />
                </flux:chart.svg>
                <flux:chart.tooltip>
                    <flux:chart.tooltip.heading field="date" :format="['year' => 'numeric', 'month' => 'short', 'day' => 'numeric']" />
                    <flux:chart.tooltip.value field="amount" label="Amount" :format="['style' => 'currency', 'currency' => $this->stats['currency']]" />
                </flux:chart.tooltip>
            </flux:chart>
        @endif
    @else
        <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
            {{ __('No expenses in selected group(s) and period') }}
        </flux:text>
    @endif
</flux:card>
