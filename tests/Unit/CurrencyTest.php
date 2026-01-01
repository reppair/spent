<?php

use App\Currency;

it('returns correct sign for EUR', function () {
    expect(Currency::EUR->sign())->toBe('€');
});

it('returns correct sign for USD', function () {
    expect(Currency::USD->sign())->toBe('$');
});
