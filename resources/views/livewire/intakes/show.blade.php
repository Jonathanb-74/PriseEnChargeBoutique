<div>
    <x-page-header :title="$intake->reference">
        <x-slot name="actions">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold text-white" style="background-color: {{ $intake->status->color }}">
                {{ $intake->status->label }}
            </span>
            @can('downloadPdf', $intake)
                <a href="{{ route('intakes.pdf', $intake) }}" class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-widest">
                    Fiche PDF
                </a>
            @endcan
        </x-slot>
    </x-page-header>

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto space-y-6">
        @if (session('status'))
            <div class="rounded-md bg-green-50 dark:bg-green-900/30 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="rounded-md bg-red-50 dark:bg-red-900/30 px-4 py-3 text-sm text-red-700 dark:text-red-300">{{ session('error') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                {{-- CLIENT --}}
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 sm:p-6">
                    <div class="text-xs uppercase text-gray-500 dark:text-gray-400">Client</div>
                    <a href="{{ route('clients.show', $intake->client) }}" wire:navigate class="text-[rgb(var(--color-accent))] dark:text-[rgb(var(--color-accent))] font-medium">
                        {{ $intake->client->full_name }}
                    </a>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $intake->client->email }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $intake->client->phone }}</div>
                </div>

                {{-- MACHINE --}}
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 sm:p-6">
                    <div class="text-xs uppercase text-gray-500 dark:text-gray-400">Machine</div>
                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ $intake->machine->brand }} {{ $intake->machine->model }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">N° série : {{ $intake->machine->serial_number ?: '—' }}</div>

                    @can('viewMachinePassword', $intake)
                        <div class="mt-2 flex items-center gap-2">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Mot de passe :</span>
                            @if ($showMachinePassword)
                                <span class="text-sm font-mono text-gray-900 dark:text-gray-100">{{ $intake->machine->password ?: '—' }}</span>
                            @else
                                <span class="text-sm text-gray-400">••••••••</span>
                            @endif
                            <button type="button" wire:click="toggleMachinePassword" class="text-xs text-[rgb(var(--color-accent))] dark:text-[rgb(var(--color-accent))]">
                                {{ $showMachinePassword ? 'Masquer' : 'Afficher' }}
                            </button>
                        </div>
                    @endcan

                    @if ($intake->machine->photos->isNotEmpty())
                        <div class="grid grid-cols-4 gap-2 mt-3">
                            @foreach ($intake->machine->photos as $photo)
                                <a href="{{ $photo->viewUrl() }}" target="_blank">
                                    <img src="{{ $photo->viewUrl() }}" class="h-16 w-full object-cover rounded-md">
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- PANNE SIGNALEE --}}
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 sm:p-6">
                    <div class="text-xs uppercase text-gray-500 dark:text-gray-400">Panne signalée</div>
                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ $intake->reported_issue ?: 'Non précisé.' }}</p>
                </div>

                {{-- NOTES --}}
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 sm:p-6">
                    <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-4">Notes de suivi</h3>

                    <form wire:submit="addNote" class="flex flex-col sm:flex-row gap-2 mb-4">
                        <textarea wire:model="newNote" rows="2" placeholder="Ajouter une note…"
                            class="flex-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-[rgb(var(--color-accent))] focus:ring-[rgb(var(--color-accent))]"></textarea>
                        <x-primary-button class="self-end">Ajouter</x-primary-button>
                    </form>
                    <x-input-error :messages="$errors->get('newNote')" class="mb-2" />

                    <div class="space-y-3">
                        @forelse ($intake->notes as $note)
                            <div class="border-l-2 border-[rgb(var(--color-accent))] pl-3">
                                <p class="text-sm text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ $note->body }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $note->user->name }} · {{ $note->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">Aucune note pour le moment.</p>
                        @endforelse
                    </div>
                </div>

                {{-- NOTIFICATION CLIENT --}}
                @can('sendClientNotification', $intake)
                    <x-collapsible-panel title="Notifier le client" :summary="$intake->notifications->isNotEmpty() ? $intake->notifications->count().' envoi(s)' : null">
                        <form wire:submit="sendNotification" class="space-y-3">
                            @if ($templates->isNotEmpty())
                                <div>
                                    <x-input-label for="selectedTemplateId" value="Modèle" />
                                    <select id="selectedTemplateId" wire:model.live="selectedTemplateId" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-[rgb(var(--color-accent))] focus:ring-[rgb(var(--color-accent))]">
                                        <option value="">— Message libre —</option>
                                        @foreach ($templates as $template)
                                            <option value="{{ $template->id }}">{{ $template->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div>
                                <x-input-label for="notif_subject" value="Sujet" />
                                <x-text-input id="notif_subject" type="text" class="mt-1 block w-full" wire:model="notif_subject" />
                                <x-input-error :messages="$errors->get('notif_subject')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="notif_body" value="Message" />
                                <textarea id="notif_body" wire:model="notif_body" rows="4"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-[rgb(var(--color-accent))] focus:ring-[rgb(var(--color-accent))]"></textarea>
                                <x-input-error :messages="$errors->get('notif_body')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="notif_cc" value="CC (emails séparés par une virgule)" />
                                <x-text-input id="notif_cc" type="text" class="mt-1 block w-full" wire:model="notif_cc" />
                            </div>

                            <div class="flex justify-end">
                                <x-primary-button>Envoyer</x-primary-button>
                            </div>
                        </form>

                        @if ($intake->notifications->isNotEmpty())
                            <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Historique des notifications</h4>
                                <div class="space-y-2">
                                    @foreach ($intake->notifications as $notif)
                                        <div class="text-sm flex items-center justify-between">
                                            <span class="text-gray-900 dark:text-gray-100">{{ $notif->subject }}</span>
                                            <span class="text-xs {{ $notif->status === 'sent' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                {{ $notif->status === 'sent' ? 'Envoyé' : 'Échec' }} · {{ $notif->created_at->format('d/m/Y H:i') }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </x-collapsible-panel>
                @endcan
            </div>

            <div class="space-y-6">
                {{-- STATUT --}}
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 sm:p-6">
                    <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Statut</h3>
                    <form wire:submit="changeStatus" class="space-y-3">
                        <select wire:model="statusId" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-[rgb(var(--color-accent))] focus:ring-[rgb(var(--color-accent))]">
                            @foreach ($statuses as $status)
                                <option value="{{ $status->id }}">{{ $status->label }}</option>
                            @endforeach
                        </select>
                        <x-primary-button class="w-full justify-center">Mettre à jour</x-primary-button>
                    </form>
                </div>

                {{-- TECHNICIEN --}}
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 sm:p-6">
                    <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Technicien assigné</h3>
                    <form wire:submit="assignTechnician" class="space-y-3">
                        <select wire:model="technicianId" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-[rgb(var(--color-accent))] focus:ring-[rgb(var(--color-accent))]">
                            <option value="">Non assigné</option>
                            @foreach ($technicians as $technician)
                                <option value="{{ $technician->id }}">{{ $technician->name }}</option>
                            @endforeach
                        </select>
                        <x-primary-button class="w-full justify-center">Assigner</x-primary-button>
                    </form>
                </div>

                {{-- SIGNATURES --}}
                @can('update', $intake)
                    @php
                        $signatureSummary = ($intake->client_signature_path ? 'Client ✓' : 'Client —').' · '.($intake->staff_signature_path ? 'Employé ✓' : 'Employé —');
                    @endphp
                    <x-collapsible-panel title="Signatures" :summary="$signatureSummary">
                        <div class="space-y-6">
                            <div>
                                <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Client</div>

                                @if ($intake->client_signature_path)
                                    <img src="{{ route('intakes.signature', [$intake, 'client']) }}" class="h-20 border border-gray-200 dark:border-gray-700 rounded-md bg-white">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Signé par {{ $intake->client_signature_name }} le {{ $intake->client_signed_at->format('d/m/Y H:i') }}
                                    </p>
                                @else
                                    @if ($intakeTermsText)
                                        <div class="text-xs text-gray-700 dark:text-gray-300 whitespace-pre-line bg-gray-50 dark:bg-gray-900 rounded-md p-3 border border-gray-200 dark:border-gray-700 mb-3">
                                            {{ $intakeTermsText }}
                                        </div>
                                    @endif

                                    <form wire:submit="saveClientSignature" class="space-y-3">
                                        <div>
                                            <x-input-label for="clientSignatureName" value="Nom du signataire" />
                                            <x-text-input id="clientSignatureName" type="text" class="mt-1 block w-full" wire:model="clientSignatureName" />
                                            <x-input-error :messages="$errors->get('clientSignatureName')" class="mt-2" />
                                        </div>
                                        <x-signature-pad property="clientSignatureData" label="Signature" />
                                        <x-input-error :messages="$errors->get('clientSignatureData')" class="mt-2" />
                                        <x-primary-button>Enregistrer la signature du client</x-primary-button>
                                    </form>
                                @endif
                            </div>

                            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                                <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Employé</div>

                                @if ($intake->staff_signature_path)
                                    <img src="{{ route('intakes.signature', [$intake, 'staff']) }}" class="h-20 border border-gray-200 dark:border-gray-700 rounded-md bg-white">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Signé par {{ $intake->staffSignedBy->name }} le {{ $intake->staff_signed_at->format('d/m/Y H:i') }}
                                    </p>
                                @else
                                    <form wire:submit="saveStaffSignature" class="space-y-3">
                                        <x-signature-pad property="staffSignatureData" label="Signature" />
                                        <x-input-error :messages="$errors->get('staffSignatureData')" class="mt-2" />
                                        <x-primary-button>Enregistrer ma signature</x-primary-button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </x-collapsible-panel>
                @endcan

                {{-- HISTORIQUE --}}
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 sm:p-6">
                    <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Historique des statuts</h3>
                    <div class="space-y-2">
                        @foreach ($intake->statusHistories as $history)
                            <div class="text-sm">
                                <span class="inline-block h-2 w-2 rounded-full mr-1" style="background-color: {{ $history->status->color }}"></span>
                                <span class="text-gray-900 dark:text-gray-100">{{ $history->status->label }}</span>
                                <div class="text-xs text-gray-500 dark:text-gray-400 ml-3">
                                    {{ $history->changedBy->name }} · {{ $history->changed_at->format('d/m/Y H:i') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="text-xs text-gray-500 dark:text-gray-400">
                    Créée le {{ $intake->created_at->format('d/m/Y à H:i') }} par {{ $intake->createdBy->name }}
                </div>
            </div>
        </div>
    </div>
</div>
