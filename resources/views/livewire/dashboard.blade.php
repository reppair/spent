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
        <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            By category (for selected period and group)
        </div>
        <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            Spent in Group (for selected period and group)
        </div>
        <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            Total Spent (for selected period)
        </div>
    </div>
    <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
        <p>list expenses for selected group and period</p>
    </div>
</div>
