<div>
    <form wire:submit="saveExpense" class="mt-6 space-y-6">
        <!-- Amount and Currency -->
        <flux:input.group :label="__('Amount')" name="expenseForm.amount" wire:model.blur="expenseForm.amount">
            <flux:input
                inputmode="decimal"
                step="0.01"
                autofocus
                placeholder="19.99"
                wire:model.blur="expenseForm.amount"
            />

            <flux:select
                variant="listbox"
                class="max-w-fit"
                wire:model.blur="expenseForm.currency"
            >
                @foreach($this->currencies as $currency)
                    <flux:select.option :value="$currency->value" :label="$currency->value" />
                @endforeach
            </flux:select>
        </flux:input.group>

        <!-- Category -->
        <flux:select
            variant="listbox"
            :label="__('Category')"
            wire:model.change="expenseForm.category_id"
            wire:key="categories_for_group_{{ $this->expenseForm->group_id }}"
        >
            @foreach ($this->categories as $category)
                <flux:select.option :wire:key="'category_' . $category->id" :value="$category->id" :label="$category->name" />
            @endforeach

            <flux:select.option.create modal="create-category">{{ __('New category') }}</flux:select.option.create>
        </flux:select>

        <!-- Group -->
        <flux:select
            variant="listbox"
            :label="__('Group')"
            wire:model.change="expenseForm.group_id"
        >
            @foreach($this->groups as $group)
                <flux:select.option :wire:key="'group_' . $group->id" :value="$group->id" :label="$group->name" />
            @endforeach

            <flux:select.option.create modal="create-group">{{ __('New group') }}</flux:select.option.create>
        </flux:select>

        <!-- Note -->
        <flux:textarea
            :label="__('Note')"
            :placeholder="__('Fruits and veggies...')"
            rows="auto"
            resize="vertical"
            wire:model.change="expenseForm.note"
        />

        <!-- Actions -->
        <div class="flex">
            <flux:spacer />

            <flux:button type="submit" variant="primary">{{ __('Create') }}</flux:button>
        </div>
    </form>

    <!-- Create Category Modal -->
    <livewire:create-category
        wire:key="category_modal_{{ $expenseForm->group_id }}"
        :group-id="$expenseForm->group_id"
        :groups="$groups"
    />

    <!-- Create Group Modal -->
    <livewire:create-group />
</div>
