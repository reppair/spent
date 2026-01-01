<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public $selectedGroups = [];

    public function mount(): void
    {
        $savedGroups = auth()->user()->settings['dashboard_selected_groups'] ?? null;

        if ($savedGroups) {
            $this->selectedGroups = $savedGroups;
        } else {
            $this->selectedGroups[] = $this->groups->first()->id;
        }
    }

    public function updatedSelectedGroups(): void
    {
        $user = auth()->user();
        $settings = $user->settings ?? [];
        $settings['dashboard_selected_groups'] = $this->selectedGroups;
        $user->update(['settings' => $settings]);
    }

    #[Computed(cache: true)]
    public function groups(): Collection
    {
        return auth()->user()->groups;
    }

    #[Computed]
    public function selectedGroupsLabel(): string
    {
        return $this->groups
            ->whereIn('id', $this->selectedGroups)
            ->pluck('name')
            ->implode(', ') ?: __('Select a group');
    }

    public function sort($column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    #[Computed]
    public function expenses(): LengthAwarePaginator
    {
        return auth()->user()->expenses()
            ->with('category')
            ->whereIn('group_id', $this->selectedGroups)
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate();
    }

    public function render(): View
    {
        return view('livewire.dashboard')->layout('components.layouts.app', ['title' => __('Dashboard')]);
    }
}
