<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl mb-12 md:mb-0">
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

        <!-- Create Expense Modal Triggers -->
        <flux:modal.trigger name="create-expense">
            <div class="fixed bottom-4 right-4 z-50">
                <flux:button icon:trailing="banknotes" variant="primary" class="md:hidden mr-4" square />
            </div>

            <flux:button icon:trailing="banknotes" variant="primary" size="sm" class="!hidden md:!inline-flex mr-4">
                {{ __('Spent') }}
            </flux:button>
        </flux:modal.trigger>

        <!-- Create Expense Modal -->
        <flux:modal name="create-expense" class="md:w-96">
            <div>
                <flux:heading size="lg">{{ __('Create Expense') }}</flux:heading>
                <flux:text class="mt-2">{{ __('How much did you spend and on what?') }}</flux:text>
            </div>

            <livewire:create-expense
                :user="$this->user"
                :groups="$this->groups"
            />
        </flux:modal>

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
    </div>

    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <!-- Total Spent -->
        <livewire:metrics.total-spent
            :selected-groups="$selectedGroups"
            :date-range="$dateRange"
        />

        <!-- By Group -->
        <livewire:metrics.spent-by-group
            :selected-groups="$selectedGroups"
            :date-range="$dateRange"
        />

        <!-- By category -->
        <livewire:metrics.spent-by-category
            :selected-groups="$selectedGroups"
            :date-range="$dateRange"
        />
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
                            <flux:badge size="sm" inset="top bottom">{{ $expense->category?->name ?? __('Uncategorized') }}</flux:badge>
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

        <div class="flex mt-2">
            <flux:field>
                <div class="flex items-center">
                    <flux:select variant="listbox" wire:model.live="perPage" size="xs">
                        <flux:select.option value="10">10</flux:select.option>
                        <flux:select.option value="25">25</flux:select.option>
                        <flux:select.option value="50">50</flux:select.option>
                        <flux:select.option value="100">100</flux:select.option>
                    </flux:select>

                    <flux:label class="text-nowrap ml-4 text-xs">{{ __('Per Page') }}</flux:label>
                </div>
            </flux:field>

            <flux:spacer />
        </div>
    </div>
</div>
