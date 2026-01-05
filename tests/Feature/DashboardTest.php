<?php

use App\Livewire\Dashboard;
use App\Models\Expense;
use App\Models\Group;
use App\Models\User;
use Flux\DateRange;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

use function Pest\Livewire\livewire;

test('guests are redirected to the login page', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $group = Group::factory()->hasCategories(1)->create();
    $user->groups()->attach($group);

    $this->actingAs($user)->get('/dashboard')->assertStatus(200);
});

describe('selected groups', function () {
    test('selected groups are persisted to user settings', function () {
        $user = User::factory()->create();
        $group1 = Group::factory()->hasCategories(1)->create();
        $group2 = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach([$group1->id, $group2->id]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('selectedGroups', [$group1->id, $group2->id]);

        $user->refresh();
        expect($user->settings['dashboard_selected_groups'])->toBe([$group1->id, $group2->id]);
    });

    test('selected groups are loaded from user settings on mount', function () {
        $user = User::factory()->create();
        $group1 = Group::factory()->hasCategories(1)->create();
        $group2 = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach([$group1->id, $group2->id]);
        $user->update(['settings' => ['dashboard_selected_groups' => [$group2->id]]]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->assertSet('selectedGroups', [$group2->id]);
    });

    test('defaults to first group when no saved selection exists', function () {
        $user = User::factory()->create();
        $group1 = Group::factory()->hasCategories(1)->create();
        $group2 = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach([$group1->id, $group2->id]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->assertSet('selectedGroups', [$group1->id]);
    });
});

describe('date range', function () {
    test('defaults to this month when no session exists', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        $component = Livewire::actingAs($user)
            ->test(Dashboard::class);

        $dateRange = $component->get('dateRange');
        expect($dateRange)
            ->toBeInstanceOf(DateRange::class)
            ->and($dateRange->start()->toDateString())->toBe(now()->startOfMonth()->toDateString())
            ->and($dateRange->end()->toDateString())->toBe(now()->endOfMonth()->toDateString());
    });

    test('can be set to a custom range', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        $start = now()->subDays(7)->toDateString();
        $end = now()->toDateString();

        $component = Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('dateRange', ['start' => $start, 'end' => $end]);

        $dateRange = $component->get('dateRange');
        expect($dateRange->start()->toDateString())->toBe($start)
            ->and($dateRange->end()->toDateString())->toBe($end);
    });

    test('can use preset ranges', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        $lastMonth = DateRange::lastMonth();

        $component = Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('dateRange', [
                'start' => $lastMonth->start()->toDateString(),
                'end' => $lastMonth->end()->toDateString(),
                'preset' => 'lastMonth',
            ]);

        $dateRange = $component->get('dateRange');
        expect($dateRange->start()->toDateString())->toBe(now()->startOfMonth()->subMonth()->toDateString())
            ->and($dateRange->end()->toDateString())->toBe(now()->startOfMonth()->subMonth()->endOfMonth()->toDateString());
    });
});

describe('selectedGroupsLabel', function () {
    test('returns "Select a group" when no groups are selected', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('selectedGroups', [])
            ->assertSet('selectedGroupsLabel', __('Select a group'));
    });

    test('returns single group name', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create(['name' => 'Family']);
        $user->groups()->attach($group);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('selectedGroups', [$group->id])
            ->assertSet('selectedGroupsLabel', 'Family');
    });

    test('returns comma-separated names for multiple groups', function () {
        $user = User::factory()->create();
        $group1 = Group::factory()->hasCategories(1)->create(['name' => 'Family']);
        $group2 = Group::factory()->hasCategories(1)->create(['name' => 'Work']);
        $user->groups()->attach([$group1->id, $group2->id]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('selectedGroups', [$group1->id, $group2->id])
            ->assertSet('selectedGroupsLabel', 'Family, Work');
    });

    test('truncates long labels', function () {
        $user = User::factory()->create();
        $longName = str_repeat('A', 100);
        $group = Group::factory()->hasCategories(1)->create(['name' => $longName]);
        $user->groups()->attach($group);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('selectedGroups', [$group->id])
            ->assertSet('selectedGroupsLabel', Str::limit($longName));
    });
});

describe('sorting', function () {
    test('defaults to created_at descending', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->assertSet('sortBy', 'created_at')
            ->assertSet('sortDirection', 'desc');
    });

    test('toggles direction when sorting same column', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->call('sort', 'created_at')
            ->assertSet('sortBy', 'created_at')
            ->assertSet('sortDirection', 'asc')
            ->call('sort', 'created_at')
            ->assertSet('sortDirection', 'desc');
    });

    test('sets new column with ascending direction', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->assertSet('sortBy', 'created_at')
            ->assertSet('sortDirection', 'desc')
            ->call('sort', 'amount')
            ->assertSet('sortBy', 'amount')
            ->assertSet('sortDirection', 'asc');
    });
});

describe('expenses querying', function () {
    test('returns only expenses from selected groups', function () {
        $user = User::factory()->create();
        $group1 = Group::factory()->hasCategories(1)->create();
        $group2 = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach([$group1->id, $group2->id]);

        $expense1 = Expense::factory()->for($user)->for($group1)->create();
        $expense2 = Expense::factory()->for($user)->for($group2)->create();

        $component = Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('selectedGroups', [$group1->id]);

        expect($component->get('expenses')->pluck('id')->all())->toBe([$expense1->id]);
    });

    test('excludes expenses from non-selected groups', function () {
        $user = User::factory()->create();
        $group1 = Group::factory()->hasCategories(1)->create();
        $group2 = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach([$group1->id, $group2->id]);

        Expense::factory()->for($user)->for($group1)->create();
        Expense::factory()->for($user)->for($group2)->create();

        $component = Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('selectedGroups', [$group1->id]);

        expect($component->get('expenses'))->toHaveCount(1);
    });

    test('eager loads category relationship', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        Expense::factory()->for($user)->for($group)->create();

        $component = Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('selectedGroups', [$group->id]);

        expect($component->get('expenses')->first()->relationLoaded('category'))->toBeTrue();
    });

    test('respects sort order', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        $expense1 = Expense::factory()->for($user)->for($group)->create(['amount' => 100]);
        $expense2 = Expense::factory()->for($user)->for($group)->create(['amount' => 300]);
        $expense3 = Expense::factory()->for($user)->for($group)->create(['amount' => 200]);

        $component = Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('selectedGroups', [$group->id])
            ->set('sortBy', 'amount')
            ->set('sortDirection', 'asc');

        expect($component->get('expenses')->pluck('id')->all())
            ->toBe([$expense1->id, $expense3->id, $expense2->id]);
    });

    test('returns paginated results', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        Expense::factory()->for($user)->for($group)->count(20)->create();

        $component = Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('selectedGroups', [$group->id]);

        $expenses = $component->get('expenses');
        expect($expenses)->toBeInstanceOf(LengthAwarePaginator::class)
            ->and($expenses->total())->toBe(20)
            ->and($expenses)->toHaveCount(10); // default perPage is 10
    });

    test('filters expenses by date range', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        // Create expense within this month
        $thisMonthExpense = Expense::factory()->for($user)->for($group)->create([
            'created_at' => now()->startOfMonth()->addDays(5),
        ]);

        // Create expense from last month (outside default range)
        Expense::factory()->for($user)->for($group)->create([
            'created_at' => now()->subMonth(),
        ]);

        $component = Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('selectedGroups', [$group->id]);

        expect($component->get('expenses')->pluck('id')->all())->toBe([$thisMonthExpense->id]);
    });

    test('excludes expenses outside the date range', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        // Create expense within this month
        Expense::factory()->for($user)->for($group)->create([
            'created_at' => now(),
        ]);

        // Create expenses outside the range
        Expense::factory()->for($user)->for($group)->create([
            'created_at' => now()->subMonths(2),
        ]);
        Expense::factory()->for($user)->for($group)->create([
            'created_at' => now()->addMonths(2),
        ]);

        $component = Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('selectedGroups', [$group->id]);

        expect($component->get('expenses'))->toHaveCount(1);
    });

    test('respects custom date range when filtering expenses', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        // Create expense from last month
        $lastMonthExpense = Expense::factory()->for($user)->for($group)->create([
            'created_at' => now()->subMonth()->startOfMonth()->addDays(5),
        ]);

        // Create expense from this month
        Expense::factory()->for($user)->for($group)->create([
            'created_at' => now(),
        ]);

        $lastMonth = DateRange::lastMonth();

        $component = Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('selectedGroups', [$group->id])
            ->set('dateRange', [
                'start' => $lastMonth->start()->toDateString(),
                'end' => $lastMonth->end()->toDateString(),
                'preset' => 'lastMonth',
            ]);

        expect($component->get('expenses')->pluck('id')->all())->toBe([$lastMonthExpense->id]);
    });

    test('uses default date range of this month', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        // Create expense at start of this month
        $startOfMonthExpense = Expense::factory()->for($user)->for($group)->create([
            'created_at' => now()->startOfMonth(),
        ]);

        // Create expense at end of this month
        $endOfMonthExpense = Expense::factory()->for($user)->for($group)->create([
            'created_at' => now()->endOfMonth(),
        ]);

        // Create expense from previous month (should be excluded)
        Expense::factory()->for($user)->for($group)->create([
            'created_at' => now()->subMonth()->endOfMonth(),
        ]);

        $component = Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('selectedGroups', [$group->id]);

        $expenseIds = $component->get('expenses')->pluck('id')->all();
        expect($expenseIds)->toContain($startOfMonthExpense->id)
            ->and($expenseIds)->toContain($endOfMonthExpense->id)
            ->and($expenseIds)->toHaveCount(2);
    });
});

describe('perPage', function () {
    test('defaults to 10 items per page', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        Expense::factory()->for($user)->for($group)->count(15)->create();

        $component = Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('selectedGroups', [$group->id]);

        $paginator = $component->get('expenses');
        expect($component->get('perPage'))->toBe(10)
            ->and($paginator->perPage())->toBe(10)
            ->and($paginator->currentPage())->toBe(1)
            ->and($paginator->total())->toBe(15)
            ->and($paginator->lastPage())->toBe(2)
            ->and($paginator->hasMorePages())->toBeTrue()
            ->and($paginator)->toHaveCount(10);
    });

    test('can change items per page', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        Expense::factory()->for($user)->for($group)->count(30)->create();

        $component = Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('selectedGroups', [$group->id])
            ->set('perPage', 25);

        $paginator = $component->get('expenses');
        expect($paginator->perPage())->toBe(25)
            ->and($paginator->currentPage())->toBe(1)
            ->and($paginator->total())->toBe(30)
            ->and($paginator->lastPage())->toBe(2)
            ->and($paginator->hasMorePages())->toBeTrue()
            ->and($paginator)->toHaveCount(25);
    });

    test('respects perPage when fewer items exist', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        Expense::factory()->for($user)->for($group)->count(5)->create();

        $component = Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('selectedGroups', [$group->id])
            ->set('perPage', 25);

        $paginator = $component->get('expenses');
        expect($paginator->perPage())->toBe(25)
            ->and($paginator->currentPage())->toBe(1)
            ->and($paginator->total())->toBe(5)
            ->and($paginator->lastPage())->toBe(1)
            ->and($paginator->hasMorePages())->toBeFalse()
            ->and($paginator)->toHaveCount(5);
    });
});

describe('allTimeMin', function () {
    test('returns user created_at date formatted as Y-m-d', function () {
        $user = User::factory()->create([
            'created_at' => '2024-06-15 10:30:00',
        ]);
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        $this->actingAs($user);

        $component = livewire(Dashboard::class);

        expect($component->get('allTimeMin'))->toBe('2024-06-15');
    });
});

describe('bustExpenses', function () {
    test('refreshes expenses when expense-created event is dispatched', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        $this->actingAs($user);

        // Start with no expenses
        $component = livewire(Dashboard::class)
            ->set('selectedGroups', [$group->id]);

        expect($component->get('expenses'))->toHaveCount(0);

        // Create an expense directly in database
        Expense::factory()->for($user)->for($group)->create();

        // Dispatch the event
        $component->dispatch('expense-created');

        // Expenses should now include the new one
        expect($component->get('expenses'))->toHaveCount(1);
    });
});
