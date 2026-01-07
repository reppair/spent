<?php

namespace App\Livewire\Forms;

use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class CategoryForm extends Form
{
    #[Validate]
    public string $name = '';

    #[Validate]
    public int $group_id;

    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('categories')->where('group_id', $this->group_id),
            ],
            'group_id' => ['required', 'integer', Rule::exists('groups', 'id')],
        ];
    }
}
