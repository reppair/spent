<div>
    <flux:modal name="create-group" class="md:w-96">
        <form wire:submit="createGroup" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Create new group') }}</flux:heading>
                <flux:text class="mt-2">{{ __('Enter the name of the new group.') }}</flux:text>
            </div>

            <flux:input
                wire:model="groupForm.name"
                :label="__('Name')"
                placeholder="e.g. 'Household'"
                autofocus
            />

            <div class="flex">
                <flux:spacer />
                <flux:button type="submit" variant="primary">{{ __('Create') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
