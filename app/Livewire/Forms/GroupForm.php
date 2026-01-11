<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class GroupForm extends Form
{
    #[Validate('required|string|max:255')]
    public string $name = '';
}
