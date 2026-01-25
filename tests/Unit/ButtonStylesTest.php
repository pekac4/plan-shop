<?php

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

uses(TestCase::class);

it('renders button variants with consistent colors', function () {
    $add = Blade::render('<x-ui.button variant="primary">Add</x-ui.button>');
    $edit = Blade::render('<x-ui.button variant="edit">Edit</x-ui.button>');
    $danger = Blade::render('<x-ui.button variant="danger">Delete</x-ui.button>');
    $success = Blade::render('<x-ui.button variant="success">Add to my recipe book</x-ui.button>');

    expect($add)->toContain('bg-emerald-100')
        ->and($edit)->toContain('bg-amber-100')
        ->and($danger)->toContain('bg-rose-100')
        ->and($success)->toContain('bg-emerald-100');
});
