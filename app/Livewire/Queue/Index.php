<?php

namespace App\Livewire\Queue;

use App\Models\ClientNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public function mount(): void
    {
        abort_unless(Auth::user()->isAdmin(), 403);
    }

    public function retry(string $uuid): void
    {
        Artisan::call('queue:retry', ['id' => [$uuid]]);

        session()->flash('status', 'Tâche remise en file d\'attente.');
    }

    public function forget(string $uuid): void
    {
        DB::table('failed_jobs')->where('uuid', $uuid)->delete();

        session()->flash('status', 'Tâche échouée supprimée.');
    }

    public function retryAll(): void
    {
        Artisan::call('queue:retry', ['id' => ['all']]);

        session()->flash('status', 'Toutes les tâches échouées ont été remises en file d\'attente.');
    }

    /**
     * Traite la file immédiatement, sans passer par le planificateur — utile en dépannage
     * (ex. verrou de chevauchement resté coincé) ou sur un hébergement où le cron/l'URL
     * publique n'est pas encore en place.
     */
    public function processNow(): void
    {
        $before = DB::table('jobs')->count();

        Artisan::call('queue:work', [
            '--stop-when-empty' => true,
            '--tries' => 3,
            '--max-time' => 25,
        ]);

        $after = DB::table('jobs')->count();
        $processed = $before - $after;

        session()->flash(
            'status',
            $processed > 0
                ? "{$processed} tâche(s) traitée(s)."
                : 'Aucune tâche en attente n\'a pu être traitée (voir les tâches échouées ci-dessous s\'il y en a de nouvelles).'
        );
    }

    public function render()
    {
        $pendingJobs = DB::table('jobs')->orderBy('id')->get()->map(function ($job) {
            $payload = json_decode($job->payload, true);

            return (object) [
                'id' => $job->id,
                'queue' => $job->queue,
                'name' => $payload['displayName'] ?? 'Inconnu',
                'attempts' => $job->attempts,
                'created_at' => Carbon::createFromTimestamp($job->created_at),
            ];
        });

        $failedJobs = DB::table('failed_jobs')->orderByDesc('failed_at')->get()->map(function ($job) {
            $payload = json_decode($job->payload, true);

            return (object) [
                'uuid' => $job->uuid,
                'queue' => $job->queue,
                'name' => $payload['displayName'] ?? 'Inconnu',
                'exception' => $job->exception,
                'failed_at' => $job->failed_at,
            ];
        });

        $notifications = ClientNotification::with(['intake', 'sentBy'])
            ->latest()
            ->paginate(15);

        return view('livewire.queue.index', [
            'pendingJobs' => $pendingJobs,
            'failedJobs' => $failedJobs,
            'notifications' => $notifications,
        ]);
    }
}
