<?php

namespace App\Livewire\Forms;

use App\Currency;
use App\Models\Category;
use App\Models\GroupUser;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ExpenseForm extends Form
{
    #[Validate]
    public int $user_id;

    #[Validate]
    public string $amount = '';

    #[Validate]
    public Currency $currency;

    #[Validate]
    public ?int $category_id = null;

    #[Validate]
    public int $group_id;

    #[Validate]
    public string $note = '';

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', Rule::exists(User::class, 'id')],
            'amount' => ['required', 'numeric', 'decimal:2', 'min:0.01'],
            'currency' => ['required', Rule::enum(Currency::class)],
            'category_id' => [
                'nullable', 'integer',
                Rule::exists(Category::class, 'id')->where('group_id', $this->group_id),
            ],
            'group_id' => [
                'required', 'integer',
                Rule::exists(GroupUser::class)->where('user_id', $this->user_id),
            ],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function updated($property): void
    {
        if ($property === 'amount' && $this->amount !== '') {
            // replace ',' with '.' (mobile keyboards, bad user input)
            $this->amount = str($this->amount)->replace(',', '.');

            if (is_numeric($this->amount)) {
                // format the amount for display consistency
                $this->amount = number_format($this->amount, 2, '.', '');
            }
        }
    }
}
