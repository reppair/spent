<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="flex items-center">
        <flux:dropdown>
            <flux:button size="sm" icon:trailing="chevron-down">Personal, Household</flux:button>

            <flux:menu keep-open>
                <flux:menu.checkbox checked>Personal</flux:menu.checkbox>
                <flux:menu.checkbox checked>Household</flux:menu.checkbox>
                <flux:menu.checkbox>Hobie</flux:menu.checkbox>
            </flux:menu>
        </flux:dropdown>

        <flux:spacer />

        <div class="flex space-x-2">
            <flux:dropdown>
                <flux:button size="sm">Current Month</flux:button>

                <flux:menu>
                    <div class="flex gap-4">
                        <div>
                            <flux:calendar mode="range" />
                        </div>

                        <div class="pt-12">
                            <flux:menu.group heading="Quick Filters">
                                <flux:menu.item>Current Week</flux:menu.item>
                                <flux:menu.item>Last Week</flux:menu.item>
                                <flux:menu.item>Current Month</flux:menu.item>
                                <flux:menu.item>Last Month</flux:menu.item>
                                <flux:menu.item>Current Year</flux:menu.item>
                                <flux:menu.item>Last Year</flux:menu.item>
                                <flux:menu.item>All Time</flux:menu.item>
                            </flux:menu.group>
                        </div>
                    </div>
                </flux:menu>
            </flux:dropdown>

            <flux:button size="sm" icon:trailing="chevron-left"></flux:button>
            <flux:button size="sm" icon:trailing="chevron-right"></flux:button>
        </div>
    </div>

    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <!-- By category (for selected period and group) -->
        <flux:card class="overflow-hidden min-w-[12rem]">
            <flux:text>By Category</flux:text>

            <flux:heading size="xl" class="mt-2 tabular-nums">$12,345</flux:heading>

            <flux:chart class="-mx-8 -mb-8 h-[3rem]" :value="[10, 12, 11, 13, 15, 14, 16, 18, 17, 19, 21, 20]">
                <flux:chart.svg gutter="0">
                    <flux:chart.line class="text-sky-200 dark:text-sky-400" />
                    <flux:chart.area class="text-sky-100 dark:text-sky-400/30" />
                </flux:chart.svg>
            </flux:chart>
        </flux:card>

        <!-- Spent in Group (for selected period and group) -->
        <flux:card class="overflow-hidden min-w-[12rem]">
            <flux:text>Spent (Personal)</flux:text>

            <flux:heading size="xl" class="mt-2 tabular-nums">$12,345</flux:heading>

            <flux:chart class="-mx-8 -mb-8 h-[3rem]" :value="[10, 12, 11, 13, 15, 14, 16, 18, 17, 19, 21, 20]">
                <flux:chart.svg gutter="0">
                    <flux:chart.line class="text-sky-200 dark:text-sky-400" />
                    <flux:chart.area class="text-sky-100 dark:text-sky-400/30" />
                </flux:chart.svg>
            </flux:chart>
        </flux:card>

        <!-- Total Spent (for selected period) -->
        <flux:card class="overflow-hidden min-w-[12rem]">
            <flux:text>Spent Total</flux:text>
            <flux:heading size="xl" class="mt-2 tabular-nums">$12,345</flux:heading>
            <flux:chart class="-mx-8 -mb-8 h-[3rem]" :value="[10, 12, 11, 13, 15, 14, 16, 18, 17, 19, 21, 20]">
                <flux:chart.svg gutter="0">
                    <flux:chart.line class="text-sky-200 dark:text-sky-400" />
                    <flux:chart.area class="text-sky-100 dark:text-sky-400/30" />
                </flux:chart.svg>
            </flux:chart>
        </flux:card>
    </div>

    <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
        <flux:table :paginate="$this->expenses">
            <flux:table.columns>
                <flux:table.column>Category</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'amount'" :direction="$sortDirection" wire:click="sort('amount')">Amount</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">Date</flux:table.column>
                <flux:table.column>Description</flux:table.column>
            </flux:table.columns>

{{--            <flux:table.rows>--}}
{{--                @foreach ($this->expenses as $order)--}}
{{--                    <flux:table.row :key="$order->id">--}}
{{--                        <flux:table.cell class="flex items-center gap-3">--}}
{{--                            <flux:avatar size="xs" src="{{ $order->customer_avatar }}" />--}}

{{--                            {{ $order->customer }}--}}
{{--                        </flux:table.cell>--}}

{{--                        <flux:table.cell class="whitespace-nowrap">{{ $order->date }}</flux:table.cell>--}}

{{--                        <flux:table.cell>--}}
{{--                            <flux:badge size="sm" :color="$order->status_color" inset="top bottom">{{ $order->status }}</flux:badge>--}}
{{--                        </flux:table.cell>--}}

{{--                        <flux:table.cell variant="strong">{{ $order->amount }}</flux:table.cell>--}}

{{--                        <flux:table.cell>--}}
{{--                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom"></flux:button>--}}
{{--                        </flux:table.cell>--}}
{{--                    </flux:table.row>--}}
{{--                @endforeach--}}
{{--            </flux:table.rows>--}}
        </flux:table>
    </div>
</div>
