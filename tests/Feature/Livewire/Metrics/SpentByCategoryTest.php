<?php

use App\Livewire\Metrics\SpentByCategory;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Group;
use App\Models\User;
use Flux\DateRange;

use function Pest\Livewire\livewire;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('categoryStats', function () {
    it('returns empty collection when no groups selected', function () {
        $component = livewire(SpentByCategory::class, [
            'selectedGroups' => [],
            'dateRange' => DateRange::thisMonth(),
        ]);

        expect($component->get('categoryStats'))->toBeEmpty();
    });

    it('returns empty collection when no expenses exist', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories()->create();
        $user->groups()->attach($group);

        $component = livewire(SpentByCategory::class, [
            'selectedGroups' => [$group->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        expect($component->get('categoryStats'))->toBeEmpty();
    });

    it('calculates stats for single category', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories()->create();
        $user->groups()->attach($group);
        $category = Category::whereGroupId($group->id)->first();

        Expense::factory()->for($user)->for($group)->for($category)->create(['amount' => 100]); // $100.00

        $component = livewire(SpentByCategory::class, [
            'selectedGroups' => [$group->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        $stats = $component->get('categoryStats');

        expect($stats)->toHaveCount(1)
            ->and($stats->first()->name)->toBe($category->name)
            ->and($stats->first()->total)->toBe(10000) // cents
            ->and($stats->first()->percentage)->toBe(100);
    });

    it('calculates stats for multiple categories', function () {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $user->groups()->attach($group);

        $category1 = Category::factory()->for($group)->create(['name' => 'Food']);
        $category2 = Category::factory()->for($group)->create(['name' => 'Transport']);

        Expense::factory()->for($user)->for($group)->for($category1)->create(['amount' => 60]); // $60
        Expense::factory()->for($user)->for($group)->for($category2)->create(['amount' => 40]); // $40

        $component = livewire(SpentByCategory::class, [
            'selectedGroups' => [$group->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        $stats = $component->get('categoryStats');

        expect($stats)->toHaveCount(2)
            ->and($stats->first()->name)->toBe('Food')
            ->and($stats->first()->total)->toBe(6000) // cents
            ->and($stats->first()->percentage)->toBe(60)
            ->and($stats->last()->name)->toBe('Transport')
            ->and($stats->last()->total)->toBe(4000) // cents
            ->and($stats->last()->percentage)->toBe(40);
    });

    it('includes uncategorized expenses', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories()->create();
        $user->groups()->attach($group);
        $category = Category::whereGroupId($group->id)->first();

        Expense::factory()->for($user)->for($group)->for($category)->create(['amount' => 60]); // $60
        Expense::factory()->for($user)->for($group)->create(['amount' => 40, 'category_id' => null]); // $40, uncategorized

        $component = livewire(SpentByCategory::class, [
            'selectedGroups' => [$group->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        $stats = $component->get('categoryStats');

        expect($stats)->toHaveCount(2)
            ->and($stats->pluck('name')->toArray())->toContain(__('Uncategorized'));
    });

    it('filters by selected groups', function () {
        $user = User::factory()->create();
        $group1 = Group::factory()->hasCategories()->create();
        $group2 = Group::factory()->hasCategories()->create();
        $user->groups()->attach([$group1->id, $group2->id]);

        $category1 = Category::whereGroupId($group1->id)->first();
        $category2 = Category::whereGroupId($group2->id)->first();

        Expense::factory()->for($user)->for($group1)->for($category1)->create(['amount' => 50]);
        Expense::factory()->for($user)->for($group2)->for($category2)->create(['amount' => 30]);

        $component = livewire(SpentByCategory::class, [
            'selectedGroups' => [$group1->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        $stats = $component->get('categoryStats');

        expect($stats)->toHaveCount(1)
            ->and($stats->first()->name)->toBe($category1->name)
            ->and($stats->first()->total)->toBe(5000); // cents
    });

    it('filters by date range', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories()->create();
        $user->groups()->attach($group);
        $category = Category::whereGroupId($group->id)->first();

        // Create expense in this month
        Expense::factory()->for($user)->for($group)->for($category)->create([
            'amount' => 50,
            'created_at' => now(),
        ]);

        // Create expense in last month
        Expense::factory()->for($user)->for($group)->for($category)->create([
            'amount' => 30,
            'created_at' => now()->subMonth(),
        ]);

        $component = livewire(SpentByCategory::class, [
            'selectedGroups' => [$group->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        $stats = $component->get('categoryStats');

        expect($stats)->toHaveCount(1)
            ->and($stats->first()->total)->toBe(5000); // Only this month's expense (cents)
    });

    it('sorts categories by total descending', function () {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $user->groups()->attach($group);

        $category1 = Category::factory()->for($group)->create(['name' => 'Low']);
        $category2 = Category::factory()->for($group)->create(['name' => 'High']);
        $category3 = Category::factory()->for($group)->create(['name' => 'Medium']);

        Expense::factory()->for($user)->for($group)->for($category1)->create(['amount' => 20]);
        Expense::factory()->for($user)->for($group)->for($category2)->create(['amount' => 80]);
        Expense::factory()->for($user)->for($group)->for($category3)->create(['amount' => 50]);

        $component = livewire(SpentByCategory::class, [
            'selectedGroups' => [$group->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        $stats = $component->get('categoryStats');

        expect($stats->pluck('name')->toArray())->toBe(['High', 'Medium', 'Low']);
    });

    it('formats amounts with currency sign', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories()->create();
        $user->groups()->attach($group);
        $category = Category::whereGroupId($group->id)->first();

        Expense::factory()->for($user)->for($group)->for($category)->create([
            'amount' => 123.45, // $123.45
            'currency' => \App\Currency::USD,
        ]);

        $component = livewire(SpentByCategory::class, [
            'selectedGroups' => [$group->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        $stats = $component->get('categoryStats');

        expect($stats->first()->formatted_amount)->toBe('$12,345.00');
    });
});

describe('reactivity', function () {
    it('responds to different selectedGroups', function () {
        $user = User::factory()->create();
        $group1 = Group::factory()->hasCategories()->create();
        $group2 = Group::factory()->hasCategories()->create();
        $user->groups()->attach([$group1->id, $group2->id]);

        $category1 = Category::whereGroupId($group1->id)->first();
        $category2 = Category::whereGroupId($group2->id)->first();

        Expense::factory()->for($user)->for($group1)->for($category1)->create(['amount' => 50]);
        Expense::factory()->for($user)->for($group2)->for($category2)->create(['amount' => 30]);

        // Test with group1
        $component1 = livewire(SpentByCategory::class, [
            'selectedGroups' => [$group1->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        $stats1 = $component1->get('categoryStats');

        expect($stats1)->toHaveCount(1)
            ->and($stats1->first()->name)->toBe($category1->name)
            ->and($stats1->first()->total)->toBe(5000); // cents

        // Test with group2
        $component2 = livewire(SpentByCategory::class, [
            'selectedGroups' => [$group2->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        $stats2 = $component2->get('categoryStats');

        expect($stats2)->toHaveCount(1)
            ->and($stats2->first()->name)->toBe($category2->name)
            ->and($stats2->first()->total)->toBe(3000); // cents
    });

    it('responds to different dateRanges', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories()->create();
        $user->groups()->attach($group);
        $category = Category::whereGroupId($group->id)->first();

        // Create expense in this month
        Expense::factory()->for($user)->for($group)->for($category)->create([
            'amount' => 50,
            'created_at' => now(),
        ]);

        // Create expense in last month
        Expense::factory()->for($user)->for($group)->for($category)->create([
            'amount' => 30,
            'created_at' => now()->subMonth(),
        ]);

        // Test with this month
        $component1 = livewire(SpentByCategory::class, [
            'selectedGroups' => [$group->id],
            'dateRange' => DateRange::thisMonth(),
        ]);

        expect($component1->get('categoryStats')->first()->total)->toBe(5000); // cents

        // Test with last month
        $component2 = livewire(SpentByCategory::class, [
            'selectedGroups' => [$group->id],
            'dateRange' => DateRange::lastMonth(),
        ]);

        expect($component2->get('categoryStats')->first()->total)->toBe(3000); // cents
    });
});
