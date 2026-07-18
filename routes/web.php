<?php

use App\Http\Controllers\CronController;
use App\Http\Controllers\IntakeClientPdfController;
use App\Http\Controllers\IntakePdfController;
use App\Http\Controllers\IntakePhotoController;
use App\Http\Controllers\IntakeSignatureController;
use App\Http\Controllers\MachinePhotoController;
use App\Livewire\Clients\Form as ClientForm;
use App\Livewire\Dashboard;
use App\Livewire\Clients\Import as ClientImport;
use App\Livewire\Clients\Index as ClientsIndex;
use App\Livewire\Clients\Show as ClientShow;
use App\Livewire\EmailTemplates\Index as EmailTemplatesIndex;
use App\Livewire\Intakes\Create as IntakeCreate;
use App\Livewire\Intakes\Index as IntakesIndex;
use App\Livewire\Intakes\Show as IntakeShow;
use App\Livewire\Machines\Form as MachineForm;
use App\Livewire\Queue\Index as QueueIndex;
use App\Livewire\Settings\Index as SettingsIndex;
use App\Livewire\Statuses\Index as StatusesIndex;
use App\Livewire\Users\Index as UsersIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Déclenchement du planificateur (`schedule:run`) via une URL publique, pour les hébergeurs
// qui n'autorisent pas de crontab classique (ex. Infomaniak mutualisé) et proposent à la
// place de pinger une URL périodiquement. Protégé par un secret (CRON_SECRET) : voir la
// documentation sur la page "File d'attente" de l'administration.
Route::get('cron/{token}', CronController::class)
    ->middleware('throttle:30,1')
    ->name('cron.run');

Route::get('dashboard', Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('clients', ClientsIndex::class)->name('clients.index');
    Route::get('clients/create', ClientForm::class)->name('clients.create');
    Route::get('clients/import', ClientImport::class)->name('clients.import');
    Route::get('clients/{client}', ClientShow::class)->name('clients.show');
    Route::get('clients/{client}/edit', ClientForm::class)->name('clients.edit');

    Route::get('clients/{client}/machines/create', MachineForm::class)->name('machines.create');
    Route::get('machines/{machine}/edit', MachineForm::class)->name('machines.edit');
    Route::get('machine-photos/{machinePhoto}', MachinePhotoController::class)->name('machine-photos.show');

    Route::get('intakes', IntakesIndex::class)->name('intakes.index');
    Route::get('intakes/create', IntakeCreate::class)->name('intakes.create');
    Route::get('intakes/{intake}', IntakeShow::class)->name('intakes.show');
    Route::get('intakes/{intake}/pdf', IntakePdfController::class)->name('intakes.pdf');
    Route::get('intakes/{intake}/pdf/client', IntakeClientPdfController::class)->name('intakes.pdf.client');
    Route::get('intakes/{intake}/signature/{type}', IntakeSignatureController::class)->name('intakes.signature');
    Route::get('intake-photos/{intakePhoto}', IntakePhotoController::class)->name('intake-photos.show');

    Route::get('statuses', StatusesIndex::class)->name('statuses.index');
    Route::get('email-templates', EmailTemplatesIndex::class)->name('email-templates.index');
    Route::get('settings', SettingsIndex::class)->name('settings.index');
    Route::get('queue', QueueIndex::class)->name('queue.index');
    Route::get('users', UsersIndex::class)->name('users.index');
});

require __DIR__.'/auth.php';
