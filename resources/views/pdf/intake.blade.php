<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $intake->reference }}</title>
    <style>
        body { font-family: 'Helvetica', Arial, sans-serif; font-size: 12px; color: #1f2937; }
        h1 { font-size: 18px; margin-bottom: 0; }
        h2 { font-size: 13px; text-transform: uppercase; color: #6b7280; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        td { padding: 4px 0; vertical-align: top; }
        td.label { width: 160px; color: #6b7280; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; color: #fff; font-size: 11px; }
        .password-box { background: #fef3c7; border: 1px solid #f59e0b; padding: 8px; margin-top: 6px; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Fiche de prise en charge {{ $intake->reference }}</h1>
    <p>Créée le {{ $intake->created_at->format('d/m/Y à H:i') }} par {{ $intake->createdBy->name }}</p>
    <span class="badge" style="background-color: {{ $intake->status->color }}">{{ $intake->status->label }}</span>

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
    @if ($intake->machine->password)
        <div class="password-box">Mot de passe machine : {{ $intake->machine->password }}</div>
    @endif

    <h2>Panne signalée</h2>
    <p>{{ $intake->reported_issue ?: 'Non précisé.' }}</p>

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
                    <br><span style="font-size: 10px; color: #6b7280;">{{ $intake->client_signature_name }} — {{ $intake->client_signed_at->format('d/m/Y H:i') }}</span>
                @else
                    <br><span style="font-size: 10px; color: #6b7280;">Non signé</span>
                @endif
            </td>
            <td style="width: 50%;">
                <strong>Employé</strong>
                @if ($staffSignaturePath)
                    <br><img src="{{ $staffSignaturePath }}" style="height: 70px; margin-top: 4px;">
                    <br><span style="font-size: 10px; color: #6b7280;">{{ $intake->staffSignedBy?->name }} — {{ $intake->staff_signed_at->format('d/m/Y H:i') }}</span>
                @else
                    <br><span style="font-size: 10px; color: #6b7280;">Non signé</span>
                @endif
            </td>
        </tr>
    </table>
</body>
</html>
