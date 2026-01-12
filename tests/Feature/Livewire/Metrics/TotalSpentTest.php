<?php

use App\Livewire\Metrics\TotalSpent;
use App\Models\Expense;
use App\Models\Group;
use App\Models\User;
use Flux\DateRange;

use function Pest\Livewire\livewire;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('stats', function () {
    it('returns zero total when no groups selected', function () {
        $component = livewire(TotalSpent::class, [
            'selectedGroups' => [],
            'dateRange' => DateRange::thisMonth(),
        ]);

        $stats = $component->get('stats');

        expect($stats['total'])->toBe(0)
            ->and($stats['formatted_total'])->toBe('$0.00')
            ->and($stats['chart_data'])->toBeEmpty();
    });

    it('returns zero total when no expenses exist', function () {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $user->groups()->attach($group);

        $component = livewire(TotalSpent::class, [
            'selectedGroups' => [$group->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        $stats = $component->get('stats');

        expect($stats['total'])->toBe(0)
            ->and($stats['formatted_total'])->toBe('$0.00')
            ->and($stats['chart_data'])->toBeEmpty();
    });

    it('calculates total for single group', function () {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $user->groups()->attach($group);

        Expense::factory()->for($user)->for($group)->create(['amount' => 100]); // $100.00

        $component = livewire(TotalSpent::class, [
            'selectedGroups' => [$group->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        $stats = $component->get('stats');

        expect($stats['total'])->toBe(10000); // cents
    });

    it('calculates total for multiple groups', function () {
        $user = User::factory()->create();
        $group1 = Group::factory()->create();
        $group2 = Group::factory()->create();
        $user->groups()->attach([$group1->id, $group2->id]);

        Expense::factory()->for($user)->for($group1)->create(['amount' => 60]); // $60
        Expense::factory()->for($user)->for($group2)->create(['amount' => 40]); // $40

        $component = livewire(TotalSpent::class, [
            'selectedGroups' => [$group1->id, $group2->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        $stats = $component->get('stats');

        expect($stats['total'])->toBe(10000); // $100 total (cents)
    });

    it('filters by selected groups', function () {
        $user = User::factory()->create();
        $group1 = Group::factory()->create();
        $group2 = Group::factory()->create();
        $user->groups()->attach([$group1->id, $group2->id]);

        Expense::factory()->for($user)->for($group1)->create(['amount' => 50]);
        Expense::factory()->for($user)->for($group2)->create(['amount' => 30]);

        $component = livewire(TotalSpent::class, [
            'selectedGroups' => [$group1->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        $stats = $component->get('stats');

        expect($stats['total'])->toBe(5000); // Only group1's $50 (cents)
    });

    it('filters by date range', function () {
        $user = User::factory()->create();
        $group = Group::factory()->create();
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

        $component = livewire(TotalSpent::class, [
            'selectedGroups' => [$group->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        $stats = $component->get('stats');

        expect($stats['total'])->toBe(5000); // Only this month's $50 (cents)
    });

    it('includes chart data with dates and amounts', function () {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $user->groups()->attach($group);

        // Create expenses on specific days
        Expense::factory()->for($user)->for($group)->create([
            'amount' => 10,
            'created_at' => now()->startOfMonth()->addDays(0),
        ]);

        Expense::factory()->for($user)->for($group)->create([
            'amount' => 20,
            'created_at' => now()->startOfMonth()->addDays(1),
        ]);

        Expense::factory()->for($user)->for($group)->create([
            'amount' => 15,
            'created_at' => now()->startOfMonth()->addDays(1),
        ]);

        $component = livewire(TotalSpent::class, [
            'selectedGroups' => [$group->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        $stats = $component->get('stats');

        expect($stats['chart_data'])->toBeArray()
            ->and($stats['chart_data'])->not->toBeEmpty()
            // First day should have date, amount, and formatted amount
            ->and($stats['chart_data'][0]['date'])->toBe(now()->startOfMonth()->format('Y-m-d'))
            ->and($stats['chart_data'][0]['amount'])->toBe(10.0)
            ->and($stats['chart_data'][0]['formatted_amount'])->toBeString()
            ->and($stats['chart_data'][0]['formatted_amount'])->not->toBeEmpty()
            // Second day should have date, amount, and formatted amount ($20 + $15 = $35)
            ->and($stats['chart_data'][1]['date'])->toBe(now()->startOfMonth()->addDay()->format('Y-m-d'))
            ->and($stats['chart_data'][1]['amount'])->toBe(35.0)
            ->and($stats['chart_data'][1]['formatted_amount'])->toBeString()
            ->and($stats['chart_data'][1]['formatted_amount'])->not->toBeEmpty();
    });

    it('formats total with currency sign', function () {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $user->groups()->attach($group);

        Expense::factory()->for($user)->for($group)->create([
            'amount' => 123.45, // $123.45
            'currency' => \App\Currency::USD,
        ]);

        $component = livewire(TotalSpent::class, [
            'selectedGroups' => [$group->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        $stats = $component->get('stats');

        expect($stats['formatted_total'])->toBe('$123.45');
    });
});

describe('caching', function () {
    it('persists stats between requests with same filters', function () {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $user->groups()->attach($group);

        Expense::factory()->for($user)->for($group)->create(['amount' => 100]);

        $component = livewire(TotalSpent::class, [
            'selectedGroups' => [$group->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        // First request - compute stats
        $firstStats = $component->get('stats');
        expect($firstStats['total'])->toBe(10000);

        // Simulate a subsequent request with same filters
        $component->call('$refresh');

        // Stats should still be correct (whether from cache or recomputed)
        $secondStats = $component->get('stats');
        expect($secondStats['total'])->toBe(10000);
    });

    it('busts cache when selectedGroups changes', function () {
        $user = User::factory()->create();
        $group1 = Group::factory()->create();
        $group2 = Group::factory()->create();
        $user->groups()->attach([$group1->id, $group2->id]);

        Expense::factory()->for($user)->for($group1)->create(['amount' => 100]);
        Expense::factory()->for($user)->for($group2)->create(['amount' => 50]);

        // First component with group1
        $component1 = livewire(TotalSpent::class, [
            'selectedGroups' => [$group1->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        $stats1 = $component1->get('stats');
        $checksum1 = $component1->get('filterChecksum');

        expect($stats1['total'])->toBe(10000);

        // Second component with group2 - different filters should have different checksum
        $component2 = livewire(TotalSpent::class, [
            'selectedGroups' => [$group2->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        $stats2 = $component2->get('stats');
        $checksum2 = $component2->get('filterChecksum');

        expect($stats2['total'])->toBe(5000)
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
        $component1 = livewire(TotalSpent::class, [
            'selectedGroups' => [$group->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        $stats1 = $component1->get('stats');
        $checksum1 = $component1->get('filterChecksum');

        expect($stats1['total'])->toBe(10000);

        // Second component with last month - different date range should have different checksum
        $component2 = livewire(TotalSpent::class, [
            'selectedGroups' => [$group->id],
            'dateRange' => DateRange::lastMonth(),
        ]);

        $stats2 = $component2->get('stats');
        $checksum2 = $component2->get('filterChecksum');

        expect($stats2['total'])->toBe(5000)
            ->and($checksum2)->not->toBe($checksum1);
    });

    it('busts cache when expense-created event is dispatched', function () {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $user->groups()->attach($group);

        // Create initial expense
        Expense::factory()->for($user)->for($group)->create(['amount' => 100]);

        $component = livewire(TotalSpent::class, [
            'selectedGroups' => [$group->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        // First request - compute stats
        $firstStats = $component->get('stats');
        expect($firstStats['total'])->toBe(10000);

        // Create new expense in the database (simulating expense creation)
        Expense::factory()->for($user)->for($group)->create(['amount' => 50]);

        // Dispatch the expense-created event
        $component->dispatch('expense-created');

        // Stats should now include the new expense
        $secondStats = $component->get('stats');
        expect($secondStats['total'])->toBe(15000); // 100 + 50 = 150 dollars (15000 cents)
    });
});
