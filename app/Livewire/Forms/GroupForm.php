<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class GroupForm extends Form
{
    // we don't care about groups with same name per user whatsoever
    #[Validate('required|string|max:255')]
    public string $name = '';
}
