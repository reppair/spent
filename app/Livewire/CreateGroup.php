<?php

namespace App\Livewire;

use App\Livewire\Forms\GroupForm;
use App\Models\Group;
use App\Role;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CreateGroup extends Component
{
    public GroupForm $groupForm;

    public function createGroup(): void
    {
        $group = Group::create($this->groupForm->validate());

        $group->users()->attach(auth()->id(), ['role' => Role::Admin]);

        $this->reset('groupForm.name');

        Flux::modal('create-group')->close();

        $this->dispatch('group-created', groupId: $group->id);
    }

    public function render(): View
    {
        return view('livewire.create-group');
    }
}
