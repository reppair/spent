<div>
    <flux:modal name="create-category" class="md:w-96">
        <form wire:submit="createCategory" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Create new category') }}</flux:heading>
                <flux:text class="mt-2">{{ __('Enter the name of the new category.') }}</flux:text>
            </div>

            <flux:select
                variant="listbox"
                :label="__('Group')"
                wire:model="categoryForm.group_id"
                disabled
            >
                @foreach($groups as $group)
                    <flux:select.option :wire:key="'group_' . $group->id" :value="$group->id" :label="$group->name" />
                @endforeach
            </flux:select>

            <flux:input
                wire:model="categoryForm.name"
                :label="__('Name')"
                placeholder="e.g. 'Hardware Store'"
                autofocus
            />

            <div class="flex">
                <flux:spacer />
                <flux:button type="submit" variant="primary">{{ __('Create') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
