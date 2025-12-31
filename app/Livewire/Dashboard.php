<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class Dashboard extends Component
{
    public function render(): View
    {
        return view('livewire.dashboard')->layout('components.layouts.app', ['title' => __('Dashboard')]);
    }
}
