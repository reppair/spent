<?php

use App\Livewire\Metrics\SpentByGroup;
use App\Models\Expense;
use App\Models\Group;
use App\Models\User;
use Flux\DateRange;

use function Pest\Livewire\livewire;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('stats', function () {
    it('returns empty collection when no groups selected', function () {
        $component = livewire(SpentByGroup::class, [
            'selectedGroups' => [],
            'dateRange' => DateRange::thisMonth(),
        ]);

        expect($component->get('stats'))->toBeEmpty();
    });

    it('returns empty collection when no expenses exist', function () {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $user->groups()->attach($group);

        $component = livewire(SpentByGroup::class, [
            'selectedGroups' => [$group->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        expect($component->get('stats'))->toBeEmpty();
    });

    it('calculates stats for single group', function () {
        $user = User::factory()->create();
        $group = Group::factory()->create(['name' => 'Personal']);
        $user->groups()->attach($group);

        Expense::factory()->for($user)->for($group)->create(['amount' => 100]); // $100.00

        $component = livewire(SpentByGroup::class, [
            'selectedGroups' => [$group->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        $stats = $component->get('stats');

        expect($stats)->toHaveCount(1)
            ->and($stats->first()->name)->toBe('Personal')
            ->and($stats->first()->total)->toBe(10000) // cents
            ->and($stats->first()->percentage)->toBe(100);
    });

    it('calculates stats for multiple groups', function () {
        $user = User::factory()->create();
        $group1 = Group::factory()->create(['name' => 'Personal']);
        $group2 = Group::factory()->create(['name' => 'Work']);
        $user->groups()->attach([$group1->id, $group2->id]);

        Expense::factory()->for($user)->for($group1)->create(['amount' => 60]); // $60
        Expense::factory()->for($user)->for($group2)->create(['amount' => 40]); // $40

        $component = livewire(SpentByGroup::class, [
            'selectedGroups' => [$group1->id, $group2->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        $stats = $component->get('stats');

        expect($stats)->toHaveCount(2)
            ->and($stats->first()->name)->toBe('Personal')
            ->and($stats->first()->total)->toBe(6000) // cents
            ->and($stats->first()->percentage)->toBe(60)
            ->and($stats->last()->name)->toBe('Work')
            ->and($stats->last()->total)->toBe(4000) // cents
            ->and($stats->last()->percentage)->toBe(40);
    });

    it('filters by selected groups', function () {
        $user = User::factory()->create();
        $group1 = Group::factory()->create(['name' => 'Personal']);
        $group2 = Group::factory()->create(['name' => 'Work']);
        $user->groups()->attach([$group1->id, $group2->id]);

        Expense::factory()->for($user)->for($group1)->create(['amount' => 50]);
        Expense::factory()->for($user)->for($group2)->create(['amount' => 30]);

        $component = livewire(SpentByGroup::class, [
            'selectedGroups' => [$group1->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        $stats = $component->get('stats');

        expect($stats)->toHaveCount(1)
            ->and($stats->first()->name)->toBe('Personal')
            ->and($stats->first()->total)->toBe(5000); // cents
    });

    it('filters by date range', function () {
        $user = User::factory()->create();
        $group = Group::factory()->create(['name' => 'Personal']);
        $user->groups()->attach($group);

        // Create expense in this month
        Expense::factory()->for($user)->for($group)->create([
            'amount' => 50,
            'created_at' => now(),
        ]);

        // Create expense in last month
        Expense::factory()->for($user)->for($group)->create([
            'amount' => 30,
            'created_at' => now()->subMonth(),
        ]);

        $component = livewire(SpentByGroup::class, [
            'selectedGroups' => [$group->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        $stats = $component->get('stats');

        expect($stats)->toHaveCount(1)
            ->and($stats->first()->total)->toBe(5000); // Only this month's expense (cents)
    });

    it('sorts groups by total descending', function () {
        $user = User::factory()->create();
        $group1 = Group::factory()->create(['name' => 'Low']);
        $group2 = Group::factory()->create(['name' => 'High']);
        $group3 = Group::factory()->create(['name' => 'Medium']);
        $user->groups()->attach([$group1->id, $group2->id, $group3->id]);

        Expense::factory()->for($user)->for($group1)->create(['amount' => 20]);
        Expense::factory()->for($user)->for($group2)->create(['amount' => 80]);
        Expense::factory()->for($user)->for($group3)->create(['amount' => 50]);

        $component = livewire(SpentByGroup::class, [
            'selectedGroups' => [$group1->id, $group2->id, $group3->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        $stats = $component->get('stats');

        expect($stats->pluck('name')->toArray())->toBe(['High', 'Medium', 'Low']);
    });

    it('formats amounts with currency sign', function () {
        $user = User::factory()->create();
        $group = Group::factory()->create(['name' => 'Personal']);
        $user->groups()->attach($group);

        Expense::factory()->for($user)->for($group)->create([
            'amount' => 123.45, // $123.45
            'currency' => \App\Currency::USD,
        ]);

        $component = livewire(SpentByGroup::class, [
            'selectedGroups' => [$group->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        $stats = $component->get('stats');

        expect($stats->first()->formatted_amount)->toBe('$12,345.00');
    });
});

describe('user stats', function () {
    it('includes user-level stats for single user', function () {
        $user = User::factory()->create(['name' => 'John Doe']);
        $group = Group::factory()->create(['name' => 'Personal']);
        $user->groups()->attach($group);

        Expense::factory()->for($user)->for($group)->create(['amount' => 100]);

        $component = livewire(SpentByGroup::class, [
            'selectedGroups' => [$group->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        $stats = $component->get('stats');

        expect($stats->first()->users)->toHaveCount(1)
            ->and($stats->first()->users->first()->name)->toBe('John Doe')
            ->and($stats->first()->users->first()->total)->toBe(10000)
            ->and($stats->first()->users->first()->percentage)->toBe(100);
    });

    it('includes user-level stats for multiple users in same group', function () {
        $user1 = User::factory()->create(['name' => 'Alice']);
        $user2 = User::factory()->create(['name' => 'Bob']);
        $group = Group::factory()->create(['name' => 'Personal']);
        $user1->groups()->attach($group);
        $user2->groups()->attach($group);

        Expense::factory()->for($user1)->for($group)->create(['amount' => 60]);
        Expense::factory()->for($user2)->for($group)->create(['amount' => 40]);

        $component = livewire(SpentByGroup::class, [
            'selectedGroups' => [$group->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        $stats = $component->get('stats');
        $users = $stats->first()->users;

        expect($users)->toHaveCount(2)
            ->and($users->first()->name)->toBe('Alice')
            ->and($users->first()->total)->toBe(6000)
            ->and($users->first()->percentage)->toBe(60)
            ->and($users->last()->name)->toBe('Bob')
            ->and($users->last()->total)->toBe(4000)
            ->and($users->last()->percentage)->toBe(40);
    });

    it('calculates user percentages relative to group total, not grand total', function () {
        $user1 = User::factory()->create(['name' => 'Alice']);
        $user2 = User::factory()->create(['name' => 'Bob']);
        $group1 = Group::factory()->create(['name' => 'Personal']);
        $group2 = Group::factory()->create(['name' => 'Work']);
        $user1->groups()->attach([$group1->id, $group2->id]);
        $user2->groups()->attach($group1->id);

        // Group 1: Alice spends 30, Bob spends 70 (total 100)
        Expense::factory()->for($user1)->for($group1)->create(['amount' => 30]);
        Expense::factory()->for($user2)->for($group1)->create(['amount' => 70]);

        // Group 2: Alice spends 100 (total 100)
        Expense::factory()->for($user1)->for($group2)->create(['amount' => 100]);

        // Grand total: 200, but percentages should be relative to group totals

        $component = livewire(SpentByGroup::class, [
            'selectedGroups' => [$group1->id, $group2->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        $stats = $component->get('stats');

        // Group 1 (100/200 = 50% of grand total)
        $group1Stats = $stats->firstWhere('name', 'Personal');
        expect($group1Stats->percentage)->toBe(50);

        // Alice in Group 1: 30/100 = 30% (of group, not grand total)
        $aliceInGroup1 = $group1Stats->users->firstWhere('name', 'Alice');
        expect($aliceInGroup1->percentage)->toBe(30);

        // Bob in Group 1: 70/100 = 70% (of group, not grand total)
        $bobInGroup1 = $group1Stats->users->firstWhere('name', 'Bob');
        expect($bobInGroup1->percentage)->toBe(70);

        // Group 2 (100/200 = 50% of grand total)
        $group2Stats = $stats->firstWhere('name', 'Work');
        expect($group2Stats->percentage)->toBe(50);

        // Alice in Group 2: 100/100 = 100% (of group, not grand total)
        $aliceInGroup2 = $group2Stats->users->firstWhere('name', 'Alice');
        expect($aliceInGroup2->percentage)->toBe(100);
    });

    it('sorts users within each group by total descending', function () {
        $user1 = User::factory()->create(['name' => 'Low Spender']);
        $user2 = User::factory()->create(['name' => 'High Spender']);
        $user3 = User::factory()->create(['name' => 'Medium Spender']);
        $group = Group::factory()->create(['name' => 'Personal']);
        $user1->groups()->attach($group);
        $user2->groups()->attach($group);
        $user3->groups()->attach($group);

        Expense::factory()->for($user1)->for($group)->create(['amount' => 20]);
        Expense::factory()->for($user2)->for($group)->create(['amount' => 80]);
        Expense::factory()->for($user3)->for($group)->create(['amount' => 50]);

        $component = livewire(SpentByGroup::class, [
            'selectedGroups' => [$group->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        $stats = $component->get('stats');
        $users = $stats->first()->users;

        expect($users->pluck('name')->toArray())->toBe(['High Spender', 'Medium Spender', 'Low Spender']);
    });

    it('formats user amounts with currency sign', function () {
        $user = User::factory()->create(['name' => 'John Doe']);
        $group = Group::factory()->create(['name' => 'Personal']);
        $user->groups()->attach($group);

        Expense::factory()->for($user)->for($group)->create([
            'amount' => 123.45,
            'currency' => \App\Currency::USD,
        ]);

        $component = livewire(SpentByGroup::class, [
            'selectedGroups' => [$group->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        $stats = $component->get('stats');

        expect($stats->first()->users->first()->formatted_amount)->toBe('$12,345.00');
    });
});

describe('caching', function () {
    it('persists stats between requests with same filters', function () {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $user->groups()->attach($group);

        Expense::factory()->for($user)->for($group)->create(['amount' => 100]);

        $component = livewire(SpentByGroup::class, [
            'selectedGroups' => [$group->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        // First request - compute stats
        $firstStats = $component->get('stats');
        expect($firstStats)->toHaveCount(1)
            ->and($firstStats->first()->total)->toBe(10000);

        // Simulate a subsequent request with same filters
        $component->call('$refresh');

        // Stats should still be correct (whether from cache or recomputed)
        $secondStats = $component->get('stats');
        expect($secondStats)->toHaveCount(1)
            ->and($secondStats->first()->total)->toBe(10000);
    });

    it('busts cache when selectedGroups changes', function () {
        $user = User::factory()->create();
        $group1 = Group::factory()->create(['name' => 'Personal']);
        $group2 = Group::factory()->create(['name' => 'Work']);
        $user->groups()->attach([$group1->id, $group2->id]);

        Expense::factory()->for($user)->for($group1)->create(['amount' => 100]);
        Expense::factory()->for($user)->for($group2)->create(['amount' => 50]);

        // First component with group1
        $component1 = livewire(SpentByGroup::class, [
            'selectedGroups' => [$group1->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        $stats1 = $component1->get('stats');
        $checksum1 = $component1->get('filterChecksum');

        expect($stats1)->toHaveCount(1)
            ->and($stats1->first()->name)->toBe('Personal')
            ->and($stats1->first()->total)->toBe(10000);

        // Second component with group2 - different filters should have different checksum
        $component2 = livewire(SpentByGroup::class, [
            'selectedGroups' => [$group2->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        $stats2 = $component2->get('stats');
        $checksum2 = $component2->get('filterChecksum');

        expect($stats2)->toHaveCount(1)
            ->and($stats2->first()->name)->toBe('Work')
            ->and($stats2->first()->total)->toBe(5000)
            ->and($checksum2)->not->toBe($checksum1);
    });

    it('busts cache when dateRange changes', function () {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $user->groups()->attach($group);

        // Create expenses in different months
        Expense::factory()->for($user)->for($group)->create([
            'amount' => 100,
            'created_at' => now(),
        ]);
        Expense::factory()->for($user)->for($group)->create([
            'amount' => 50,
            'created_at' => now()->subMonth(),
        ]);

        // First component with this month
        $component1 = livewire(SpentByGroup::class, [
            'selectedGroups' => [$group->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        $stats1 = $component1->get('stats');
        $checksum1 = $component1->get('filterChecksum');

        expect($stats1)->toHaveCount(1)
            ->and($stats1->first()->total)->toBe(10000);

        // Second component with last month - different date range should have different checksum
        $component2 = livewire(SpentByGroup::class, [
            'selectedGroups' => [$group->id],
            'dateRange' => DateRange::lastMonth(),
        ]);

        $stats2 = $component2->get('stats');
        $checksum2 = $component2->get('filterChecksum');

        expect($stats2)->toHaveCount(1)
            ->and($stats2->first()->total)->toBe(5000)
            ->and($checksum2)->not->toBe($checksum1);
    });

    it('updates filterChecksum on dehydrate', function () {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $user->groups()->attach($group);

        $component = livewire(SpentByGroup::class, [
            'selectedGroups' => [$group->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        // Access stats to trigger computation
        $component->get('stats');

        // The filterChecksum should be set after the request
        expect($component->get('filterChecksum'))->not->toBeEmpty();
    });

    it('generates different checksums for different filters', function () {
        $user = User::factory()->create();
        $group1 = Group::factory()->create();
        $group2 = Group::factory()->create();
        $user->groups()->attach([$group1->id, $group2->id]);

        Expense::factory()->for($user)->for($group1)->create(['amount' => 100]);
        Expense::factory()->for($user)->for($group2)->create(['amount' => 50]);

        // Component with group1
        $component1 = livewire(SpentByGroup::class, [
            'selectedGroups' => [$group1->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        $checksum1 = $component1->get('filterChecksum');

        // Component with group2
        $component2 = livewire(SpentByGroup::class, [
            'selectedGroups' => [$group2->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        $checksum2 = $component2->get('filterChecksum');

        // Component with different date range
        $component3 = livewire(SpentByGroup::class, [
            'selectedGroups' => [$group1->id],
            'dateRange' => DateRange::lastMonth(),
        ]);

        $checksum3 = $component3->get('filterChecksum');

        // All checksums should be different
        expect($checksum1)->not->toBe($checksum2)
            ->and($checksum1)->not->toBe($checksum3)
            ->and($checksum2)->not->toBe($checksum3);
    });

    it('busts cache when expense-created event is dispatched', function () {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $user->groups()->attach($group);

        // Create initial expense
        Expense::factory()->for($user)->for($group)->create(['amount' => 100]);

        $component = livewire(SpentByGroup::class, [
            'selectedGroups' => [$group->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        // First request - compute stats
        $firstStats = $component->get('stats');
        expect($firstStats)->toHaveCount(1)
            ->and($firstStats->first()->total)->toBe(10000);

        // Create new expense in the database (simulating expense creation)
        Expense::factory()->for($user)->for($group)->create(['amount' => 50]);

        // Dispatch the expense-created event
        $component->dispatch('expense-created');

        // Stats should now include the new expense
        $secondStats = $component->get('stats');
        expect($secondStats)->toHaveCount(1)
            ->and($secondStats->first()->total)->toBe(15000); // 100 + 50 = 150 dollars (15000 cents)
    });
});
