<?php

namespace App\Livewire;

use App\Currency;
use App\Livewire\Forms\ExpenseForm;
use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CreateExpense extends Component
{
    public User $user;

    /**
     * @var Collection<\App\Models\Group>
     */
    public Collection $groups;

    public ExpenseForm $expenseForm;

    public $categorySearch = '';

    public function mount(): void
    {
        // set form defaults
        $this->expenseForm->user_id = $this->user->id;

        $this->expenseForm->currency = config('app.currency');

        $this->expenseForm->group_id = $this->groups->first()->id;

        $this->selectCategory();
    }

    public function updated($property): void
    {
        // on group change bust computed categories cache and set selected category
        if ($property === 'expenseForm.group_id') {
            unset($this->categories);
            $this->selectCategory();
        }
    }

    protected function selectCategory(): void
    {
        // todo: init from the category with most expenses
        $this->expenseForm->category_id = $this->categories->first()->id;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<\App\Models\Category>
     */
    #[Computed(persist: true)]
    public function categories(): Collection
    {
        return Category::whereGroupId($this->expenseForm->group_id)->get();
    }

    #[Computed(persist: true)]
    public function currencies(): array
    {
        // todo: compute from group - $this->group->currencies;
        return Currency::cases();
    }

    public function saveExpense(): void
    {
        Expense::create($this->expenseForm->validate());

        $this->reset('expenseForm.amount');
        $this->reset('expenseForm.note');

        Flux::modal('create-expense')->close();

        $this->dispatch('expense-created');
    }

    public function render(): View
    {
        return view('livewire.create-expense');
    }
}
