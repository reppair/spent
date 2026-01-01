<?php

use App\Models\User;

describe('updateSetting', function () {
    test('stores a string value', function () {
        $user = User::factory()->create();

        $user->updateSetting('theme', 'dark');

        expect($user->fresh()->settings['theme'])->toBe('dark');
    });

    test('stores an int value', function () {
        $user = User::factory()->create();

        $user->updateSetting('items_per_page', 25);

        expect($user->fresh()->settings['items_per_page'])->toBe(25);
    });

    test('stores an array value', function () {
        $user = User::factory()->create();

        $user->updateSetting('selected_groups', [1, 2, 3]);

        expect($user->fresh()->settings['selected_groups'])->toBe([1, 2, 3]);
    });

    test('preserves existing settings', function () {
        $user = User::factory()->create(['settings' => ['theme' => 'dark']]);

        $user->updateSetting('items_per_page', 25);

        expect($user->fresh()->settings)->toBe([
            'theme' => 'dark',
            'items_per_page' => 25,
        ]);
    });
});
