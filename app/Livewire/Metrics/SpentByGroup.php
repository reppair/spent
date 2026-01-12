<?php

namespace App\Livewire\Metrics;

use App\Models\Expense;
use App\Models\Group;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;
use Livewire\Attributes\Computed;

class SpentByGroup extends DashboardMetric
{
    #[Computed(persist: true)]
    public function stats(): Collection
    {
        if (empty($this->selectedGroups)) {
            return collect();
        }

        // Get all expenses grouped by group with totals
        $stats = Expense::query()
            ->whereIn('group_id', $this->selectedGroups)
            ->whereBetween('created_at', $this->dateRange)
            ->selectRaw('group_id, SUM(amount) as total, currency')
            ->groupBy('group_id', 'currency')
            ->get();

        if ($stats->isEmpty()) {
            return collect();
        }

        // Calculate grand total across all groups
        $grandTotal = $stats->sum('total');

        if ($grandTotal == 0) {
            return collect();
        }

        // Group by group and calculate percentages
        $grouped = $stats->groupBy('group_id')->map(function ($group) use ($grandTotal) {
            $groupTotal = $group->sum('total');
            $expense = $group->first();
            $groupModel = Group::find($expense->group_id);
            $currency = $expense->currency;

            return (object) [
                'name' => $groupModel->name,
                'total' => $groupTotal,
                'formatted_amount' => Number::currency($groupTotal, $currency->value, app()->getLocale()),
                'percentage' => (int) round(($groupTotal / $grandTotal) * 100),
            ];
        });

        // Sort by total descending
        return $grouped->sortByDesc('total')->values();
    }

    public function render(): View
    {
        return view('livewire.metrics.spent-by-group');
    }
}
