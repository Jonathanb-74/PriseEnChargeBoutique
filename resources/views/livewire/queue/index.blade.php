<div wire:poll.15s>
    <x-page-header title="File d'attente" />

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">
        @if (session('status'))
            <div class="rounded-md bg-green-50 dark:bg-green-900/30 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('status') }}</div>
        @endif

        {{-- DOCUMENTATION --}}
        <x-collapsible-panel title="Comment ça marche ?">
            <div class="space-y-4 text-sm text-gray-600 dark:text-gray-400">
                <div>
                    <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-1">Principe</h4>
                    <p>
                        Les emails (notification client, prise en charge créée…) ne partent pas instantanément : ils sont
                        déposés dans une file d'attente (table <code>jobs</code>), puis envoyés par un processus séparé.
                        Tant que ce processus ne tourne pas, les tâches restent au statut <span class="text-amber-600 dark:text-amber-400 font-medium">En attente</span>
                        indéfiniment — rien n'est perdu, mais rien ne part non plus.
                    </p>
                </div>

                <div>
                    <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-1">Deux façons de traiter la file</h4>
                    <ul class="list-disc list-inside space-y-1">
                        <li>
                            <strong>Worker permanent</strong> — un processus qui tourne en continu et traite les tâches dès qu'elles arrivent :
                            <code>php artisan queue:work</code>
                        </li>
                        <li>
                            <strong>Cron / tâche planifiée</strong> (recommandé, déjà configuré dans cette appli) — une commande
                            <code>php artisan schedule:run</code> lancée chaque minute par le système, qui elle-même déclenche
                            <code>queue:work --stop-when-empty</code> : la file est vidée puis le processus s'arrête, jusqu'à la minute suivante.
                            Pas de processus à surveiller en permanence.
                        </li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-1">Mise en place — Windows (ce poste, WAMP)</h4>
                    <p class="mb-1">Planificateur de tâches Windows → créer une tâche :</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Déclencheur : répéter toutes les 1 minute, indéfiniment</li>
                        <li>Action : <code>C:\wamp64\bin\php\php8.4.15\php.exe</code> avec arguments <code>artisan schedule:run</code>, dossier de démarrage <code>C:\wamp64\www\GitHub\PriseEnChargeBoutique</code></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-1">Mise en place — serveur de production avec accès crontab (Linux/cPanel)</h4>
                    <p>Une seule ligne de cron, dans le gestionnaire de cron du serveur :</p>
                    <pre class="mt-1 bg-gray-50 dark:bg-gray-900 rounded-md p-2 overflow-x-auto text-xs">* * * * * cd /chemin/vers/l'appli && php artisan schedule:run >> /dev/null 2>&1</pre>
                </div>

                <div>
                    <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-1">Mise en place — hébergement sans crontab SSH (ex. Infomaniak mutualisé)</h4>
                    <p class="mb-1">
                        Certains hébergeurs mutualisés (Infomaniak, notamment) n'autorisent pas de crontab classique en SSH et proposent
                        à la place de « pinger » une URL publique à intervalle régulier. L'application expose exactement ça :
                    </p>
                    <ol class="list-decimal list-inside space-y-1">
                        <li>
                            Générez un secret aléatoire (par exemple avec <code>php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"</code>)
                            et renseignez-le dans <code>.env</code> sous la clé <code>CRON_SECRET</code>.
                        </li>
                        <li>
                            Dans le panneau d'administration de l'hébergeur (ex. Infomaniak → « Tâches planifiées » / « CRON »), configurez
                            l'appel de l'URL suivante toutes les minutes :
                            <pre class="mt-1 bg-gray-50 dark:bg-gray-900 rounded-md p-2 overflow-x-auto text-xs">{{ url('/cron/VOTRE_SECRET') }}</pre>
                        </li>
                    </ol>
                    <p class="mt-1">
                        Chaque appel exécute <code>schedule:run</code>, exactement comme le ferait un vrai cron — aucune autre différence
                        avec la mise en place classique. L'URL répond <code>404</code> (comme une page inexistante) si le secret est absent
                        ou incorrect, pour ne pas révéler son existence à qui ne le connaît pas.
                    </p>
                </div>

                <div>
                    <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-1">Cette page</h4>
                    <ul class="list-disc list-inside space-y-1">
                        <li><strong>Tâches en attente</strong> : pas encore traitées par le worker/cron.</li>
                        <li><strong>Tâches échouées</strong> : ont épuisé leurs tentatives (3 par défaut). "Réessayer" les remet en attente, "Supprimer" les efface définitivement.</li>
                        <li><strong>Historique des notifications</strong> : trace de tous les emails envoyés aux clients, quel que soit leur statut final.</li>
                    </ul>
                    <p class="mt-1">La page se rafraîchit automatiquement toutes les 15 secondes.</p>
                </div>
            </div>
        </x-collapsible-panel>

        {{-- TÂCHES EN ATTENTE --}}
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 sm:p-6">
            <div class="flex items-center justify-between mb-1">
                <h3 class="font-medium text-gray-900 dark:text-gray-100">Tâches en attente ({{ $pendingJobs->count() }})</h3>
                @if ($pendingJobs->isNotEmpty())
                    <button type="button" wire:click="processNow" wire:loading.attr="disabled" class="text-xs text-[rgb(var(--color-accent))] disabled:opacity-50">
                        <span wire:loading.remove wire:target="processNow">Traiter maintenant</span>
                        <span wire:loading wire:target="processNow">Traitement en cours…</span>
                    </button>
                @endif
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                Ces tâches sont normalement traitées automatiquement (cron / URL planifiée). Le bouton ci-dessus permet de forcer
                le traitement immédiatement, sans attendre — utile en dépannage.
            </p>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tâche</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">File</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tentatives</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Créée le</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($pendingJobs as $job)
                            <tr wire:key="pending-{{ $job->id }}">
                                <td class="px-3 py-2 text-gray-900 dark:text-gray-100">{{ class_basename($job->name) }}</td>
                                <td class="px-3 py-2 text-gray-600 dark:text-gray-300">{{ $job->queue }}</td>
                                <td class="px-3 py-2 text-gray-600 dark:text-gray-300">{{ $job->attempts }}</td>
                                <td class="px-3 py-2 text-gray-600 dark:text-gray-300">{{ $job->created_at->format('d/m/Y H:i:s') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">Aucune tâche en attente.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TÂCHES ÉCHOUÉES --}}
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 sm:p-6">
            <div class="flex items-center justify-between mb-1">
                <h3 class="font-medium text-gray-900 dark:text-gray-100">Tâches échouées ({{ $failedJobs->count() }})</h3>
                @if ($failedJobs->isNotEmpty())
                    <button type="button" wire:click="retryAll" wire:confirm="Réessayer toutes les tâches échouées ?" class="text-xs text-[rgb(var(--color-accent))]">
                        Tout réessayer
                    </button>
                @endif
            </div>

            <div class="space-y-3 mt-4">
                @forelse ($failedJobs as $job)
                    <div wire:key="failed-{{ $job->uuid }}" class="border border-gray-200 dark:border-gray-700 rounded-md p-3">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ class_basename($job->name) }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">{{ $job->queue }} · {{ \Illuminate\Support\Carbon::parse($job->failed_at)->format('d/m/Y H:i:s') }}</span>
                            </div>
                            <div class="flex gap-3">
                                <button type="button" wire:click="retry('{{ $job->uuid }}')" class="text-xs text-[rgb(var(--color-accent))]">
                                    Réessayer
                                </button>
                                <button type="button" wire:click="forget('{{ $job->uuid }}')" wire:confirm="Supprimer cette tâche échouée ?" class="text-xs text-red-600 dark:text-red-400">
                                    Supprimer
                                </button>
                            </div>
                        </div>
                        <details class="mt-2">
                            <summary class="text-xs text-gray-500 dark:text-gray-400 cursor-pointer">Détail de l'erreur</summary>
                            <pre class="mt-1 text-xs text-red-600 dark:text-red-400 whitespace-pre-wrap break-words max-h-40 overflow-y-auto">{{ \Illuminate\Support\Str::limit($job->exception, 2000) }}</pre>
                        </details>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">Aucune tâche échouée.</p>
                @endforelse
            </div>
        </div>

        {{-- HISTORIQUE DES NOTIFICATIONS --}}
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 sm:p-6">
            <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-4">Historique des notifications client</h3>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Sujet</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Destinataire</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Prise en charge</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Envoyée par</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Statut</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($notifications as $notif)
                            @php
                                $style = match ($notif->status) {
                                    'sent' => 'text-green-600 dark:text-green-400',
                                    'queued' => 'text-amber-600 dark:text-amber-400',
                                    default => 'text-red-600 dark:text-red-400',
                                };
                                $label = match ($notif->status) {
                                    'sent' => 'Envoyé',
                                    'queued' => 'En attente',
                                    default => 'Échec',
                                };
                            @endphp
                            <tr wire:key="notif-{{ $notif->id }}">
                                <td class="px-3 py-2 text-gray-900 dark:text-gray-100">{{ $notif->subject }}</td>
                                <td class="px-3 py-2 text-gray-600 dark:text-gray-300">{{ $notif->recipient_email }}</td>
                                <td class="px-3 py-2">
                                    @if ($notif->intake)
                                        <a href="{{ route('intakes.show', $notif->intake) }}" wire:navigate class="text-[rgb(var(--color-accent))]">
                                            {{ $notif->intake->reference }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-gray-600 dark:text-gray-300">{{ $notif->sentBy?->name ?? '—' }}</td>
                                <td class="px-3 py-2">
                                    <span class="text-xs {{ $style }}" title="{{ $notif->error_message }}">{{ $label }}</span>
                                </td>
                                <td class="px-3 py-2 text-gray-600 dark:text-gray-300">{{ $notif->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">Aucune notification envoyée.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $notifications->links() }}
        </div>
    </div>
</div>
