<?php

namespace App\Livewire;

use App\Models\User;
use Flux\DateRange;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Session;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public array $selectedGroups = [];

    #[Session]
    public ?DateRange $dateRange = null;

    public function mount(): void
    {
        $savedGroups = $this->user->settings['dashboard_selected_groups'] ?? null;

        if ($savedGroups) {
            $this->selectedGroups = $savedGroups;
        } else {
            $this->selectedGroups[] = $this->groups->first()->id;
        }

        if (! $this->dateRange) {
            $this->dateRange = DateRange::thisMonth();
        }
    }

    #[Computed(persist: true)]
    public function user(): User
    {
        return auth()->user();
    }

    #[Computed(persist: true)]
    public function allTimeMin(): string
    {
        return $this->user->created_at->format('Y-m-d');
    }

    public function updatedSelectedGroups(): void
    {
        $this->user->updateSetting('dashboard_selected_groups', $this->selectedGroups);
    }

    #[Computed(persist: true)]
    public function groups(): Collection
    {
        return $this->user->groups;
    }

    #[Computed]
    public function selectedGroupsLabel(): string
    {
        $label = $this->groups
            ->whereIn('id', $this->selectedGroups)
            ->pluck('name')
            ->implode(', ');

        if (! $label) {
            return __('Select a group');
        }

        return Str::limit($label);
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
        return $this->user->expenses()
            ->with('category')
            ->whereIn('group_id', $this->selectedGroups)
            ->whereBetween('created_at', $this->dateRange)
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate();
    }

    public function render(): View
    {
        return view('livewire.dashboard')->layout('components.layouts.app', ['title' => __('Dashboard')]);
    }
}
