<div>
    <x-page-header title="Nouvelle prise en charge" />

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-3xl mx-auto">
        <form wire:submit="save" class="space-y-6">
            {{-- CLIENT --}}
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 sm:p-6">
                <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-4">1. Client</h3>

                @if ($this->selectedClient)
                    <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-900 rounded-md p-3">
                        <div>
                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ $this->selectedClient->full_name }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $this->selectedClient->email }} · {{ $this->selectedClient->phone }}</div>
                        </div>
                        <button type="button" wire:click="clearClient" class="text-sm text-[rgb(var(--color-accent))] dark:text-[rgb(var(--color-accent))]">Changer</button>
                    </div>
                @elseif ($creatingNewClient)
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Nouveau client</span>
                            <button type="button" wire:click="cancelNewClient" class="text-sm text-[rgb(var(--color-accent))] dark:text-[rgb(var(--color-accent))]">Rechercher un client existant</button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="nc_type" value="Type" />
                                <select id="nc_type" wire:model="nc_type" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-[rgb(var(--color-accent))] focus:ring-[rgb(var(--color-accent))]">
                                    <option value="particulier">Particulier</option>
                                    <option value="pro">Professionnel</option>
                                </select>
                            </div>
                            @if ($nc_type === 'pro')
                                <div>
                                    <x-input-label for="nc_company_name" value="Société" />
                                    <x-text-input id="nc_company_name" type="text" class="mt-1 block w-full" wire:model="nc_company_name" />
                                    <x-input-error :messages="$errors->get('nc_company_name')" class="mt-2" />
                                </div>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="nc_first_name" value="Prénom" />
                                <x-text-input id="nc_first_name" type="text" class="mt-1 block w-full" wire:model="nc_first_name" />
                                <x-input-error :messages="$errors->get('nc_first_name')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="nc_last_name" value="Nom" />
                                <x-text-input id="nc_last_name" type="text" class="mt-1 block w-full" wire:model="nc_last_name" />
                                <x-input-error :messages="$errors->get('nc_last_name')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="nc_email" value="Email" />
                                <x-text-input id="nc_email" type="email" class="mt-1 block w-full" wire:model="nc_email" />
                                <x-input-error :messages="$errors->get('nc_email')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="nc_phone" value="Téléphone" />
                                <x-text-input id="nc_phone" type="text" class="mt-1 block w-full" wire:model="nc_phone" />
                                <x-input-error :messages="$errors->get('nc_phone')" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="nc_address_line1" value="Adresse" />
                            <x-text-input id="nc_address_line1" type="text" class="mt-1 block w-full" wire:model="nc_address_line1" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="nc_postal_code" value="Code postal" />
                                <x-text-input id="nc_postal_code" type="text" class="mt-1 block w-full" wire:model="nc_postal_code" />
                            </div>
                            <div>
                                <x-input-label for="nc_city" value="Ville" />
                                <x-text-input id="nc_city" type="text" class="mt-1 block w-full" wire:model="nc_city" />
                            </div>
                        </div>
                    </div>
                @else
                    <div class="space-y-3">
                        <input type="search" wire:model.live.debounce.300ms="clientSearch" placeholder="Rechercher un client (nom, société, email, téléphone)…"
                            class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-[rgb(var(--color-accent))] focus:ring-[rgb(var(--color-accent))]" />

                        @if ($this->clientResults->isNotEmpty())
                            <div class="divide-y divide-gray-200 dark:divide-gray-700 border border-gray-200 dark:border-gray-700 rounded-md">
                                @foreach ($this->clientResults as $client)
                                    <button type="button" wire:click="pickClient({{ $client->id }})" class="w-full text-left px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-900">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $client->full_name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $client->email }} · {{ $client->phone }}</div>
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        <button type="button" wire:click="startNewClient" class="text-sm text-[rgb(var(--color-accent))] dark:text-[rgb(var(--color-accent))]">
                            + Créer un nouveau client
                        </button>
                    </div>
                @endif
            </div>

            {{-- MACHINE --}}
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 sm:p-6">
                <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-4">2. Machine</h3>

                @if ($this->selectedClient && $this->clientMachines->isNotEmpty() && ! $creatingNewMachine)
                    <div class="space-y-2">
                        @foreach ($this->clientMachines as $machine)
                            <label class="flex items-center gap-3 border border-gray-200 dark:border-gray-700 rounded-md p-3 cursor-pointer {{ $selectedMachineId === $machine->id ? 'ring-2 ring-[rgb(var(--color-accent))]' : '' }}">
                                <input type="radio" name="machine" wire:click="pickMachine({{ $machine->id }})" @checked($selectedMachineId === $machine->id)>
                                <span class="text-sm text-gray-900 dark:text-gray-100">{{ $machine->brand }} {{ $machine->model }} <span class="text-gray-500 dark:text-gray-400">({{ $machine->serial_number }})</span></span>
                            </label>
                        @endforeach
                        <button type="button" wire:click="startNewMachine" class="text-sm text-[rgb(var(--color-accent))] dark:text-[rgb(var(--color-accent))]">
                            + Ajouter une nouvelle machine
                        </button>
                    </div>
                @else
                    <div class="space-y-4">
                        @if ($this->selectedClient && $this->clientMachines->isNotEmpty())
                            <button type="button" wire:click="$set('creatingNewMachine', false)" class="text-sm text-[rgb(var(--color-accent))] dark:text-[rgb(var(--color-accent))]">
                                Choisir une machine existante
                            </button>
                        @endif

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="m_brand" value="Marque" />
                                <x-text-input id="m_brand" type="text" class="mt-1 block w-full" wire:model="m_brand" />
                                <x-input-error :messages="$errors->get('m_brand')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="m_model" value="Modèle" />
                                <x-text-input id="m_model" type="text" class="mt-1 block w-full" wire:model="m_model" />
                                <x-input-error :messages="$errors->get('m_model')" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="m_serial_number" value="Numéro de série" />
                            <x-text-input id="m_serial_number" type="text" class="mt-1 block w-full" wire:model="m_serial_number" />
                        </div>

                        <div x-data="{ show: false }">
                            <x-input-label for="m_password" value="Mot de passe de la machine" />
                            <div class="mt-1 relative">
                                <input :type="show ? 'text' : 'password'" id="m_password" wire:model="m_password"
                                    class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[rgb(var(--color-accent))] focus:ring-[rgb(var(--color-accent))] rounded-md shadow-sm pr-16" />
                                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 px-3 text-xs text-gray-500 dark:text-gray-400">
                                    <span x-text="show ? 'Masquer' : 'Afficher'"></span>
                                </button>
                            </div>
                        </div>

                        <div>
                            <x-input-label for="newPhotos" value="Photos de la machine" />
                            <input type="file" id="newPhotos" wire:model="newPhotos" multiple accept="image/png,image/jpeg,image/webp"
                                class="mt-1 block w-full text-sm text-gray-600 dark:text-gray-300" />
                            <div wire:loading wire:target="newPhotos" class="text-xs text-gray-500 dark:text-gray-400 mt-1">Chargement…</div>
                            <x-input-error :messages="$errors->get('newPhotos.*')" class="mt-2" />

                            @if ($newPhotos)
                                <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 mt-3">
                                    @foreach ($newPhotos as $photo)
                                        <img src="{{ $photo->temporaryUrl() }}" class="h-20 w-full object-cover rounded-md">
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                <x-input-error :messages="$errors->get('selectedMachineId')" class="mt-2" />
            </div>

            {{-- PANNE --}}
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 sm:p-6">
                <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-4">3. Panne signalée</h3>
                <textarea wire:model="reported_issue" rows="4" placeholder="Description du problème signalé par le client…"
                    class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-[rgb(var(--color-accent))] focus:ring-[rgb(var(--color-accent))]"></textarea>
            </div>

            {{-- NOTIFICATION --}}
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 sm:p-6">
                <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-4">4. Email de confirmation au client</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                    Envoyé automatiquement en copie à vous-même ({{ auth()->user()->email }}). Vous pouvez ajouter un destinataire en copie.
                </p>
                <x-input-label for="cc_email" value="CC (facultatif)" />
                <x-text-input id="cc_email" type="email" class="mt-1 block w-full" wire:model="cc_email" placeholder="collegue@boutique.fr" />
                <x-input-error :messages="$errors->get('cc_email')" class="mt-2" />
            </div>

            {{-- SIGNATURES --}}
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 sm:p-6 space-y-6">
                <h3 class="font-medium text-gray-900 dark:text-gray-100">5. Signatures</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Facultatif ici : si le client doit partir, vous pourrez recueillir les signatures plus tard depuis la fiche de la prise en charge.
                </p>

                @if ($intakeTermsText)
                    <div class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line bg-gray-50 dark:bg-gray-900 rounded-md p-3 border border-gray-200 dark:border-gray-700">
                        {{ $intakeTermsText }}
                    </div>
                @endif

                <div>
                    <x-input-label for="clientSignatureName" value="Nom du signataire (client)" />
                    <x-text-input id="clientSignatureName" type="text" class="mt-1 block w-full" wire:model="clientSignatureName" />
                    <x-input-error :messages="$errors->get('clientSignatureName')" class="mt-2" />
                </div>

                <x-signature-pad property="clientSignatureData" label="Signature du client" />

                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <x-signature-pad property="staffSignatureData" label="Signature de l'employé" />
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('intakes.index') }}" wire:navigate class="text-sm text-gray-600 dark:text-gray-400">Annuler</a>
                <x-primary-button>Créer la prise en charge</x-primary-button>
            </div>
        </form>
    </div>
</div>
