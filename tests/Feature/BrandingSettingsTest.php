<?php

use App\Enums\UserRole;
use App\Livewire\Settings\Index as SettingsIndex;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
    $this->admin = User::factory()->create(['role' => UserRole::Admin]);
});

test('admin can set a custom accent color', function () {
    $this->actingAs($this->admin);

    Livewire::test(SettingsIndex::class)
        ->set('accentColor', '#ff0000')
        ->call('save')
        ->assertHasNoErrors();

    expect(Setting::accentColor())->toBe('#ff0000')
        ->and(Setting::accentColorRgb())->toBe('255 0 0');
});

test('an invalid accent color is rejected', function () {
    $this->actingAs($this->admin);

    Livewire::test(SettingsIndex::class)
        ->set('accentColor', 'not-a-color')
        ->call('save')
        ->assertHasErrors('accentColor');
});

test('admin can upload and remove a custom logo', function () {
    $this->actingAs($this->admin);

    $file = UploadedFile::fake()->image('logo.png', 200, 200)->size(50);

    Livewire::test(SettingsIndex::class)
        ->set('newLogo', $file)
        ->call('save')
        ->assertHasNoErrors();

    $path = Setting::get(Setting::BRAND_LOGO_PATH);
    expect($path)->not->toBeNull();
    Storage::disk('public')->assertExists($path);

    Livewire::test(SettingsIndex::class)->call('removeLogo');

    Storage::disk('public')->assertMissing($path);
    expect(Setting::get(Setting::BRAND_LOGO_PATH))->toBeNull();
});
