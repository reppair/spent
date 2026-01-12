<?php

namespace App\Livewire\Metrics;

use App\Models\Expense;
use App\Models\Group;
use App\Models\User;
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

        // Query aggregates expenses by each unique combination of (group, user, currency)
        // This creates separate totals for each user within each group
        $stats = Expense::query()
            ->whereIn('group_id', $this->selectedGroups)
            ->whereBetween('created_at', $this->dateRange)
            ->selectRaw('group_id, user_id, SUM(amount) as total, currency')
            ->groupBy('group_id', 'user_id', 'currency')
            ->get();

        if ($stats->isEmpty()) {
            return collect();
        }

        // Load all needed models in two queries (avoids N+1)
        $groups = Group::find($stats->pluck('group_id')->unique())->keyBy('id');
        $users = User::find($stats->pluck('user_id')->unique())->keyBy('id');

        // Grand total is used to calculate each group's percentage of overall spending
        $grandTotal = $stats->sum('total');

        if ($grandTotal == 0) {
            return collect();
        }

        // Build nested structure: groups containing users
        $grouped = $stats->groupBy('group_id')->map(function ($groupStats) use ($groups, $users, $grandTotal) {
            // Calculate this specific group's total (sum of all users in this group)
            $groupTotal = $groupStats->sum('total');
            $group = $groups[$groupStats->first()->group_id];
            $currency = $groupStats->first()->currency;

            return (object) [
                'name' => $group->name,
                'total' => $groupTotal,
                'formatted_amount' => Number::currency($groupTotal / 100, $currency->value, app()->getLocale()),
                'percentage' => (int) round(($groupTotal / $grandTotal) * 100), // Group's % of all spending
                'users' => $groupStats->map(fn ($stat) => (object) [
                    'name' => $users[$stat->user_id]->name,
                    'total' => $stat->total,
                    'formatted_amount' => Number::currency($stat->total / 100, $currency->value, app()->getLocale()),
                    'percentage' => (int) round(($stat->total / $groupTotal) * 100), // User's % within THIS group
                ])->sortByDesc('total')->values(),
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
