<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $intake->reference }}</title>
    <style>
        body { font-family: 'Helvetica', Arial, sans-serif; font-size: 12px; color: #1f2937; }
        h1 { font-size: 19px; margin: 0 0 4px; }
        h2 { font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; border-bottom: 2px solid {{ $accentColor }}; padding-bottom: 4px; margin: 24px 0 0; }
        p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        td { padding: 5px 0; vertical-align: top; }
        td.label { width: 160px; color: #6b7280; }
        .header-table td { padding: 0; vertical-align: middle; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 10px; color: #fff; font-size: 11px; margin-top: 6px; }
        .password-box { background: #fef3c7; border: 1px solid #f59e0b; border-radius: 4px; padding: 8px 10px; margin-top: 8px; font-weight: bold; }
        .muted { color: #6b7280; font-size: 10px; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td>
                <h1>Fiche de prise en charge {{ $intake->reference }}</h1>
                <p class="muted">Créée le {{ $intake->created_at->format('d/m/Y à H:i') }} par {{ $intake->createdBy->name }}</p>
                <span class="badge" style="background-color: {{ $intake->status->color }}">{{ $intake->status->label }}</span>
            </td>
            @if ($pdfLogoPath)
                <td style="width: 160px; text-align: right;">
                    <img src="{{ $pdfLogoPath }}" style="max-height: 60px; max-width: 160px;">
                </td>
            @endif
        </tr>
    </table>

    <h2>Client</h2>
    <table>
        <tr><td class="label">Nom</td><td>{{ $intake->client->full_name }}</td></tr>
        @if ($intake->client->company_name)
            <tr><td class="label">Société</td><td>{{ $intake->client->company_name }}</td></tr>
        @endif
        <tr><td class="label">Type</td><td>{{ $intake->client->type === 'pro' ? 'Professionnel' : 'Particulier' }}</td></tr>
        <tr><td class="label">Email</td><td>{{ $intake->client->email }}</td></tr>
        <tr><td class="label">Téléphone</td><td>{{ $intake->client->phone }}</td></tr>
        <tr><td class="label">Adresse</td><td>{{ $intake->client->address_line1 }} {{ $intake->client->address_line2 }}<br>{{ $intake->client->postal_code }} {{ $intake->client->city }}</td></tr>
    </table>

    <h2>Machine</h2>
    <table>
        <tr><td class="label">Marque / Modèle</td><td>{{ $intake->machine->brand }} {{ $intake->machine->model }}</td></tr>
        <tr><td class="label">Numéro de série</td><td>{{ $intake->machine->serial_number }}</td></tr>
    </table>
    @if ($includePassword && $intake->machine->password)
        <div class="password-box">Mot de passe machine : {{ $intake->machine->password }}</div>
    @endif

    <h2>Panne signalée</h2>
    <p>{{ $intake->reported_issue ?: 'Non précisé.' }}</p>
    @if (! empty($issuePhotoPaths))
        <p class="muted">{{ count($issuePhotoPaths) }} photo(s) jointe(s) — voir en fin de document.</p>
    @endif

    <h2>Suivi</h2>
    <table>
        <tr><td class="label">Technicien</td><td>{{ $intake->technician?->name ?? 'Non assigné' }}</td></tr>
    </table>

    @if ($intakeTermsText)
        <h2>Conditions</h2>
        <p style="white-space: pre-line;">{{ $intakeTermsText }}</p>
    @endif

    <h2>Signatures</h2>
    <table>
        <tr>
            <td style="width: 50%;">
                <strong>Client</strong>
                @if ($clientSignaturePath)
                    <br><img src="{{ $clientSignaturePath }}" style="height: 70px; margin-top: 4px;">
                    <br><span class="muted">{{ $intake->client_signature_name }} — {{ $intake->client_signed_at->format('d/m/Y H:i') }}</span>
                @else
                    <br><span class="muted">Non signé</span>
                @endif
            </td>
            <td style="width: 50%;">
                <strong>Employé</strong>
                @if ($staffSignaturePath)
                    <br><img src="{{ $staffSignaturePath }}" style="height: 70px; margin-top: 4px;">
                    <br><span class="muted">{{ $intake->staffSignedBy?->name }} — {{ $intake->staff_signed_at->format('d/m/Y H:i') }}</span>
                @else
                    <br><span class="muted">Non signé</span>
                @endif
            </td>
        </tr>
    </table>

    @foreach ($issuePhotoPaths as $index => $path)
        <div style="page-break-before: always; text-align: center; padding-top: 40px;">
            <p class="muted">Photo {{ $index + 1 }} / {{ count($issuePhotoPaths) }} — Panne signalée</p>
            <img src="{{ $path }}" style="max-width: 100%; max-height: 700px;">
        </div>
    @endforeach
</body>
</html>
