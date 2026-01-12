<?php

namespace App\Livewire\Metrics;

use Flux\DateRange;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Component;

abstract class DashboardMetric extends Component
{
    #[Reactive]
    public array $selectedGroups;

    #[Reactive]
    public DateRange $dateRange;

    public string $filterChecksum = '';

    #[Computed(persist: true)]
    abstract public function stats(): Collection;

    public function hydrate(): void
    {
        if ($this->filterChecksum !== $this->getFilterChecksum()) {
            unset($this->stats);
        }
    }

    public function dehydrate(): void
    {
        $this->filterChecksum = $this->getFilterChecksum();
    }

    protected function getFilterChecksum(): string
    {
        return md5(json_encode([$this->selectedGroups, $this->dateRange->start(), $this->dateRange->end()]));
    }

    #[On('expense-created')]
    public function onExpenseCreated(): void
    {
        unset($this->stats);
    }
}
