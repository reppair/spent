<?php

namespace App\Livewire;

use App\Models\Expense;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    public $sortBy = 'created_at';

    public $sortDirection = 'desc';

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
    public function expenses()
    {
        return Expense::query()
            ->where('user_id', auth()->id())
            ->with('category')
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate();
    }

    public function render(): View
    {
        return view('livewire.dashboard')->layout('components.layouts.app', ['title' => __('Dashboard')]);
    }
}
