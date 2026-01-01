<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="flex items-center">
        <flux:dropdown>
            <flux:button size="sm" icon:trailing="chevron-down">{{ $this->selectedGroupsLabel }}</flux:button>

            <flux:menu keep-open>
                <flux:menu.checkbox.group wire:model.live="selectedGroups">
                    @foreach($this->groups as $group)
                        <flux:menu.checkbox :value="$group->id" :label="$group->name" />
                    @endforeach
                </flux:menu.checkbox.group>
            </flux:menu>
        </flux:dropdown>

        <flux:spacer/>

        <div class="flex space-x-2">
            <flux:date-picker
                mode="range"
                with-presets
                presets="today yesterday thisWeek lastWeek thisMonth lastMonth yearToDate lastYear allTime"
                :min="$this->allTimeMin"
                with-today
                start-day="1"
                size="sm"
                :locale="app()->getLocale()"
                wire:model.live="dateRange"
            />

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
                    <flux:chart.line class="text-sky-200 dark:text-sky-400"/>
                    <flux:chart.area class="text-sky-100 dark:text-sky-400/30"/>
                </flux:chart.svg>
            </flux:chart>
        </flux:card>

        <!-- Spent in Group (for selected period and group) -->
        <flux:card class="overflow-hidden min-w-[12rem]">
            <flux:text>Spent (Personal)</flux:text>

            <flux:heading size="xl" class="mt-2 tabular-nums">$12,345</flux:heading>

            <flux:chart class="-mx-8 -mb-8 h-[3rem]" :value="[10, 12, 11, 13, 15, 14, 16, 18, 17, 19, 21, 20]">
                <flux:chart.svg gutter="0">
                    <flux:chart.line class="text-sky-200 dark:text-sky-400"/>
                    <flux:chart.area class="text-sky-100 dark:text-sky-400/30"/>
                </flux:chart.svg>
            </flux:chart>
        </flux:card>

        <!-- Total Spent (for selected period) -->
        <flux:card class="overflow-hidden min-w-[12rem]">
            <flux:text>Spent Total</flux:text>
            <flux:heading size="xl" class="mt-2 tabular-nums">$12,345</flux:heading>
            <flux:chart class="-mx-8 -mb-8 h-[3rem]" :value="[10, 12, 11, 13, 15, 14, 16, 18, 17, 19, 21, 20]">
                <flux:chart.svg gutter="0">
                    <flux:chart.line class="text-sky-200 dark:text-sky-400"/>
                    <flux:chart.area class="text-sky-100 dark:text-sky-400/30"/>
                </flux:chart.svg>
            </flux:chart>
        </flux:card>
    </div>

    <div class="relative h-full flex-1 overflow-hidden">
        <flux:table :paginate="$this->expenses">
            <flux:table.columns>
                <flux:table.column
                    sortable
                    :sorted="$sortBy === 'created_at'"
                    :direction="$sortDirection"
                    wire:click="sort('created_at')"
                >
                    Date
                </flux:table.column>

                <flux:table.column>Category</flux:table.column>

                <flux:table.column class="hidden md:table-cell">Note</flux:table.column>

                <flux:table.column
                    sortable
                    :sorted="$sortBy === 'amount'"
                    :direction="$sortDirection"
                    wire:click="sort('amount')"
                    align="end"
                >
                    Amount
                </flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach($this->expenses as $expense)
                    <flux:table.row :key="$expense->id">
                        <flux:table.cell class="whitespace-nowrap">
                            {{ $expense->created_at->toFormattedDateString() }}
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge size="sm" inset="top bottom">{{ $expense->category->name }}</flux:badge>
                        </flux:table.cell>

                        <flux:table.cell
                            class="hidden md:table-cell">{{ Str::limit($expense->note, 40) }}</flux:table.cell>

                        <flux:table.cell align="end">
                            <span class="pr-2">{{ $expense->formatted_amount }}</span>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>
</div>
