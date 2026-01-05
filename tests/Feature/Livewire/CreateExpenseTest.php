<?php

use App\Currency;
use App\Livewire\CreateExpense;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Group;
use App\Models\User;

use function Pest\Livewire\livewire;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('mount', function () {
    it('sets user_id from passed user', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        livewire(CreateExpense::class, ['user' => $user, 'groups' => $user->groups])
            ->assertSet('expenseForm.user_id', $user->id);
    });

    it('sets currency from app config default', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        livewire(CreateExpense::class, ['user' => $user, 'groups' => $user->groups])
            ->assertSet('expenseForm.currency', config('app.currency'));
    });

    it('sets group_id to first group', function () {
        $user = User::factory()->create();
        $group1 = Group::factory()->hasCategories(1)->create();
        $group2 = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach([$group1->id, $group2->id]);

        livewire(CreateExpense::class, ['user' => $user, 'groups' => $user->groups])
            ->assertSet('expenseForm.group_id', $user->groups->first()->id);
    });

    it('sets category_id to first category of selected group', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(3)->create();
        $user->groups()->attach($group);

        $firstCategory = Category::whereGroupId($group->id)->first();

        livewire(CreateExpense::class, ['user' => $user, 'groups' => $user->groups])
            ->assertSet('expenseForm.category_id', $firstCategory->id);
    });
});

describe('group selection', function () {
    it('updates category_id when group changes', function () {
        $user = User::factory()->create();
        $group1 = Group::factory()->hasCategories(2)->create();
        $group2 = Group::factory()->hasCategories(2)->create();
        $user->groups()->attach([$group1->id, $group2->id]);

        $group2FirstCategory = Category::whereGroupId($group2->id)->first();

        livewire(CreateExpense::class, ['user' => $user, 'groups' => $user->groups])
            ->set('expenseForm.group_id', $group2->id)
            ->assertSet('expenseForm.category_id', $group2FirstCategory->id);
    });

    it('categories computed returns only categories for selected group', function () {
        $user = User::factory()->create();
        $group1 = Group::factory()->hasCategories(2)->create();
        $group2 = Group::factory()->hasCategories(3)->create();
        $user->groups()->attach([$group1->id, $group2->id]);

        $component = livewire(CreateExpense::class, ['user' => $user, 'groups' => $user->groups])
            ->set('expenseForm.group_id', $group2->id);

        expect($component->get('categories'))->toHaveCount(3)
            ->and($component->get('categories')->pluck('group_id')->unique()->first())->toBe($group2->id);
    });
});

describe('formats amount', function () {
    it('to 2 decimal places', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(2)->create();
        $user->groups()->attach($group);

        livewire(CreateExpense::class, ['user' => $user, 'groups' => $user->groups])
            ->set('expenseForm.amount', '12.3456')
            ->assertSet('expenseForm.amount', '12.35');
    });

    it('with trailing zeros', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        livewire(CreateExpense::class, ['user' => $user, 'groups' => $user->groups])
            ->set('expenseForm.amount', '.1')
            ->assertSet('expenseForm.amount', '0.10');
    });

    it('unchanged when already 2 decimals', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        livewire(CreateExpense::class, ['user' => $user, 'groups' => $user->groups])
            ->set('expenseForm.amount', '12.34')
            ->assertSet('expenseForm.amount', '12.34');
    });

    it('rounds down with more than 2 decimals when third decimal is less than 5', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        livewire(CreateExpense::class, ['user' => $user, 'groups' => $user->groups])
            ->set('expenseForm.amount', '12.344')
            ->assertSet('expenseForm.amount', '12.34');
    });

    it('NOT for empty amount', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        livewire(CreateExpense::class, ['user' => $user, 'groups' => $user->groups])
            ->set('expenseForm.amount', '')
            ->assertSet('expenseForm.amount', '');
    });

    it('converts comma decimal separator to dot', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        livewire(CreateExpense::class, ['user' => $user, 'groups' => $user->groups])
            ->set('expenseForm.amount', '12,34')
            ->assertSet('expenseForm.amount', '12.34');
    });
});

describe('validation', function () {
    it('requires amount', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        livewire(CreateExpense::class, ['user' => $user, 'groups' => $user->groups])
            ->set('expenseForm.amount', '')
            ->call('saveExpense')
            ->assertHasErrors(['expenseForm.amount' => 'required']);
    });

    it('requires amount to be numeric', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        livewire(CreateExpense::class, ['user' => $user, 'groups' => $user->groups])
            ->set('expenseForm.amount', 'not-a-number')
            ->call('saveExpense')
            ->assertHasErrors(['expenseForm.amount' => 'numeric']);
    });

    it('requires amount to be at least 0.01', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        livewire(CreateExpense::class, ['user' => $user, 'groups' => $user->groups])
            ->set('expenseForm.amount', '0.00')
            ->call('saveExpense')
            ->assertHasErrors(['expenseForm.amount' => 'min']);
    });

    it('requires category to belong to selected group', function () {
        $user = User::factory()->create();
        $group1 = Group::factory()->hasCategories(1)->create();
        $group2 = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach([$group1->id, $group2->id]);

        $categoryFromOtherGroup = Category::whereGroupId($group2->id)->first();

        livewire(CreateExpense::class, ['user' => $user, 'groups' => $user->groups])
            ->set('expenseForm.group_id', $group1->id)
            ->set('expenseForm.category_id', $categoryFromOtherGroup->id)
            ->set('expenseForm.amount', '10.00')
            ->call('saveExpense')
            ->assertHasErrors(['expenseForm.category_id']);
    });

    it('requires group to be in user groups', function () {
        $user = User::factory()->create();
        $userGroup = Group::factory()->hasCategories(1)->create();
        $otherGroup = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($userGroup);

        livewire(CreateExpense::class, ['user' => $user, 'groups' => $user->groups])
            ->set('expenseForm.group_id', $otherGroup->id)
            ->set('expenseForm.amount', '10.00')
            ->call('saveExpense')
            ->assertHasErrors(['expenseForm.group_id']);
    });

    it('allows note to be empty', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        livewire(CreateExpense::class, ['user' => $user, 'groups' => $user->groups])
            ->set('expenseForm.amount', '10.00')
            ->set('expenseForm.note', '')
            ->call('saveExpense')
            ->assertHasNoErrors(['expenseForm.note']);
    });

    it('requires note to be max 255 characters', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        livewire(CreateExpense::class, ['user' => $user, 'groups' => $user->groups])
            ->set('expenseForm.amount', '10.00')
            ->set('expenseForm.note', str_repeat('a', 256))
            ->call('saveExpense')
            ->assertHasErrors(['expenseForm.note' => 'max']);
    });
});

describe('saveExpense', function () {
    it('creates expense with form data', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);
        $category = Category::whereGroupId($group->id)->first();

        livewire(CreateExpense::class, ['user' => $user, 'groups' => $user->groups])
            ->set('expenseForm.amount', '25.50')
            ->set('expenseForm.note', 'Test expense')
            ->call('saveExpense');

        $this->assertDatabaseHas('expenses', [
            'user_id' => $user->id,
            'group_id' => $group->id,
            'category_id' => $category->id,
            'amount' => 2550,
            'note' => 'Test expense',
        ]);
    });

    it('resets amount after save', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        livewire(CreateExpense::class, ['user' => $user, 'groups' => $user->groups])
            ->set('expenseForm.amount', '25.50')
            ->call('saveExpense')
            ->assertSet('expenseForm.amount', '');
    });

    it('resets note after save', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        livewire(CreateExpense::class, ['user' => $user, 'groups' => $user->groups])
            ->set('expenseForm.amount', '25.50')
            ->set('expenseForm.note', 'Test note')
            ->call('saveExpense')
            ->assertSet('expenseForm.note', '');
    });

    it('dispatches expense-created event', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        livewire(CreateExpense::class, ['user' => $user, 'groups' => $user->groups])
            ->set('expenseForm.amount', '25.50')
            ->call('saveExpense')
            ->assertDispatched('expense-created');
    });

    it('expense belongs to correct user, group and category', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(2)->create();
        $user->groups()->attach($group);
        $category = Category::whereGroupId($group->id)->first();

        livewire(CreateExpense::class, ['user' => $user, 'groups' => $user->groups])
            ->set('expenseForm.amount', '15.00')
            ->call('saveExpense');

        $expense = Expense::first();

        expect($expense->user_id)->toBe($user->id)
            ->and($expense->group_id)->toBe($group->id)
            ->and($expense->category_id)->toBe($category->id);
    });
});

describe('computed', function () {
    it('currencies returns all Currency cases', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        $component = livewire(CreateExpense::class, ['user' => $user, 'groups' => $user->groups]);

        expect($component->get('currencies'))->toBe(Currency::cases());
    });
});
