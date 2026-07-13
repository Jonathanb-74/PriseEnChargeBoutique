<div>
    <x-page-header title="Paramètres" />

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-3xl mx-auto space-y-6">
        @if (session('status'))
            <div class="rounded-md bg-green-50 dark:bg-green-900/30 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('status') }}</div>
        @endif

        <form wire:submit="save" class="space-y-6">
            {{-- APPARENCE --}}
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 sm:p-6 space-y-6">
                <h3 class="font-medium text-gray-900 dark:text-gray-100">Apparence</h3>

                <div>
                    <x-input-label value="Logo" />
                    <div class="mt-2 flex items-center gap-4">
                        @if ($newLogo)
                            <img src="{{ $newLogo->temporaryUrl() }}" class="h-12 w-auto max-w-[160px] object-contain bg-gray-50 dark:bg-gray-900 rounded-md p-1">
                        @elseif ($currentLogoUrl)
                            <img src="{{ $currentLogoUrl }}" class="h-12 w-auto max-w-[160px] object-contain bg-gray-50 dark:bg-gray-900 rounded-md p-1">
                        @else
                            <x-application-logo class="h-12 w-12 fill-current text-gray-400" />
                        @endif

                        <div class="space-y-1">
                            <input type="file" wire:model="newLogo" accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                class="block text-sm text-gray-600 dark:text-gray-300">
                            <div wire:loading wire:target="newLogo" class="text-xs text-gray-500 dark:text-gray-400">Chargement…</div>

                            @if ($currentLogoUrl)
                                <button type="button" wire:click="removeLogo" wire:confirm="Revenir au logo par défaut ?" class="text-xs text-red-600 dark:text-red-400">
                                    Revenir au logo par défaut
                                </button>
                            @endif
                        </div>
                    </div>
                    <x-input-error :messages="$errors->get('newLogo')" class="mt-2" />
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">PNG, JPG, WebP ou SVG, 1 Mo maximum. Utilisé dans l'application (menu, page de connexion).</p>
                </div>

                <div>
                    <x-input-label value="Logo pour les fiches PDF et les emails" />
                    <div class="mt-2 flex items-center gap-4">
                        @if ($newPdfLogo)
                            <img src="{{ $newPdfLogo->temporaryUrl() }}" class="h-12 w-auto max-w-[160px] object-contain bg-gray-50 dark:bg-gray-900 rounded-md p-1">
                        @elseif ($currentPdfLogoUrl)
                            <img src="{{ $currentPdfLogoUrl }}" class="h-12 w-auto max-w-[160px] object-contain bg-gray-50 dark:bg-gray-900 rounded-md p-1">
                        @else
                            <div class="h-12 w-12 flex items-center justify-center text-xs text-gray-400 border border-dashed border-gray-300 dark:border-gray-600 rounded-md">
                                Aucun
                            </div>
                        @endif

                        <div class="space-y-1">
                            <input type="file" wire:model="newPdfLogo" accept="image/png,image/jpeg,image/webp"
                                class="block text-sm text-gray-600 dark:text-gray-300">
                            <div wire:loading wire:target="newPdfLogo" class="text-xs text-gray-500 dark:text-gray-400">Chargement…</div>

                            @if ($currentPdfLogoUrl)
                                <button type="button" wire:click="removePdfLogo" wire:confirm="Supprimer ce logo ?" class="text-xs text-red-600 dark:text-red-400">
                                    Supprimer
                                </button>
                            @endif
                        </div>
                    </div>
                    <x-input-error :messages="$errors->get('newPdfLogo')" class="mt-2" />
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        PNG, JPG ou WebP, 1 Mo maximum. Utilisé sur la fiche de prise en charge PDF et en en-tête des emails envoyés au client. Si vide, le logo de l'application est utilisé.
                    </p>
                </div>

                <div>
                    <x-input-label for="accentColor" value="Couleur de mise en avant" />
                    <div class="mt-1 flex items-center gap-3">
                        <input id="accentColor" type="color" wire:model="accentColor" class="h-10 w-14 rounded-md border-gray-300 dark:border-gray-700 p-1 bg-white dark:bg-gray-900">
                        <x-text-input type="text" wire:model="accentColor" class="w-32" maxlength="7" />
                        <button type="button" wire:click="resetAccentColor" class="text-xs text-gray-500 dark:text-gray-400 underline">
                            Réinitialiser
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Utilisée pour les liens, les menus actifs et les mises en avant dans toute l'application.
                    </p>
                    <x-input-error :messages="$errors->get('accentColor')" class="mt-2" />
                </div>
            </div>

            {{-- NOTIFICATIONS --}}
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 sm:p-6">
                <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Email de création de prise en charge</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Modèle utilisé pour l'email envoyé au client à la création d'une prise en charge. Les modèles sont gérables dans
                    <a href="{{ route('email-templates.index') }}" wire:navigate class="text-[rgb(var(--color-accent))]">Modèles email</a>.
                </p>
                <select wire:model="intakeCreatedTemplateId"
                    class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-[rgb(var(--color-accent))] focus:ring-[rgb(var(--color-accent))]">
                    <option value="">Modèle intégré par défaut</option>
                    @foreach ($emailTemplates as $template)
                        <option value="{{ $template->id }}">{{ $template->name }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Variables disponibles dans le modèle : <code>&#123;&#123;reference&#125;&#125;</code>, <code>&#123;&#123;client&#125;&#125;</code>, <code>&#123;&#123;machine&#125;&#125;</code>, <code>&#123;&#123;statut&#125;&#125;</code>, <code>&#123;&#123;panne&#125;&#125;</code>
                </p>
                <x-input-error :messages="$errors->get('intakeCreatedTemplateId')" class="mt-2" />
            </div>

            {{-- EMAIL SORTANT (SMTP) --}}
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 sm:p-6 space-y-4">
                <div>
                    <h3 class="font-medium text-gray-900 dark:text-gray-100">Email sortant (SMTP)</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Configure le serveur utilisé pour l'envoi des emails (notifications client, prises en charge…).
                    </p>
                </div>

                <div>
                    <x-input-label for="mailMailer" value="Mode d'envoi" />
                    <select id="mailMailer" wire:model.live="mailMailer"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-[rgb(var(--color-accent))] focus:ring-[rgb(var(--color-accent))]">
                        <option value="log">Journal uniquement (aucun envoi réel, pour test)</option>
                        <option value="smtp">Serveur SMTP</option>
                    </select>
                    <x-input-error :messages="$errors->get('mailMailer')" class="mt-2" />
                </div>

                @if ($mailMailer === 'smtp')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="mailHost" value="Serveur (host)" />
                            <x-text-input id="mailHost" type="text" class="mt-1 block w-full" wire:model="mailHost" placeholder="smtp.example.com" />
                            <x-input-error :messages="$errors->get('mailHost')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="mailPort" value="Port" />
                            <x-text-input id="mailPort" type="number" class="mt-1 block w-full" wire:model="mailPort" placeholder="587" />
                            <x-input-error :messages="$errors->get('mailPort')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="mailEncryption" value="Chiffrement" />
                        <select id="mailEncryption" wire:model="mailEncryption"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-[rgb(var(--color-accent))] focus:ring-[rgb(var(--color-accent))]">
                            <option value="tls">TLS (STARTTLS, port 587 en général)</option>
                            <option value="ssl">SSL (port 465 en général)</option>
                            <option value="none">Aucun</option>
                        </select>
                        <x-input-error :messages="$errors->get('mailEncryption')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="mailUsername" value="Identifiant" />
                            <x-text-input id="mailUsername" type="text" class="mt-1 block w-full" wire:model="mailUsername" autocomplete="off" />
                            <x-input-error :messages="$errors->get('mailUsername')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="mailPassword" value="Mot de passe" />
                            <x-text-input id="mailPassword" type="password" class="mt-1 block w-full" wire:model="mailPassword" autocomplete="new-password"
                                placeholder="{{ $hasStoredMailPassword ? '•••••••• (laisser vide pour conserver)' : '' }}" />
                            <x-input-error :messages="$errors->get('mailPassword')" class="mt-2" />
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="mailFromAddress" value="Adresse d'expédition" />
                        <x-text-input id="mailFromAddress" type="email" class="mt-1 block w-full" wire:model="mailFromAddress" />
                        <x-input-error :messages="$errors->get('mailFromAddress')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="mailFromName" value="Nom d'expédition" />
                        <x-text-input id="mailFromName" type="text" class="mt-1 block w-full" wire:model="mailFromName" />
                        <x-input-error :messages="$errors->get('mailFromName')" class="mt-2" />
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                    <x-input-label for="testEmailAddress" value="Envoyer un email de test" />
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                        Enregistrez d'abord vos paramètres ci-dessus, puis testez-les ici.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <x-text-input id="testEmailAddress" type="email" class="block w-full sm:max-w-xs" wire:model="testEmailAddress" placeholder="vous@example.com" />
                        <button type="button" wire:click="sendTestEmail" wire:loading.attr="disabled" wire:target="sendTestEmail"
                            class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-widest">
                            Envoyer un test
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('testEmailAddress')" class="mt-2" />

                    @if ($testStatus === 'success')
                        <div class="mt-2 text-sm text-green-700 dark:text-green-300">Email de test envoyé avec succès.</div>
                    @elseif ($testStatus === 'error')
                        <div class="mt-2 text-sm text-red-600 dark:text-red-400">Échec de l'envoi : {{ $testError }}</div>
                    @endif
                </div>
            </div>

            {{-- MENTIONS / TARIFS --}}
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 sm:p-6">
                <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Mentions et tarifs affichés avant la signature client</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Ce texte est affiché juste au-dessus de la signature du client lors de la prise en charge (tarifs minimum, conditions, mentions légales…) et repris sur la fiche PDF.
                </p>
                <textarea wire:model="intakeTermsText" rows="8"
                    class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-[rgb(var(--color-accent))] focus:ring-[rgb(var(--color-accent))]"></textarea>
                <x-input-error :messages="$errors->get('intakeTermsText')" class="mt-2" />
            </div>

            <div class="flex justify-end">
                <x-primary-button>Enregistrer</x-primary-button>
            </div>
        </form>
    </div>
</div>
