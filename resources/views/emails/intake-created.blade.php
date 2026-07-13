<x-mail::message>
# Prise en charge {{ $intake->reference }}

Bonjour {{ $intake->client->first_name }},

Nous vous confirmons la prise en charge de votre matériel :

<x-mail::panel>
**Référence :** {{ $intake->reference }}<br>
**Matériel :** {{ $intake->machine->brand }} {{ $intake->machine->model }}<br>
@if ($intake->machine->serial_number)
**Numéro de série :** {{ $intake->machine->serial_number }}<br>
@endif
**Statut :** {{ $intake->status->label }}
</x-mail::panel>

@if ($intake->reported_issue)
**Problème signalé :** {{ $intake->reported_issue }}
@endif

Nous reviendrons vers vous dès qu'il y aura du nouveau concernant votre matériel.

{!! nl2br(e(\App\Models\Setting::emailSignature())) !!}
</x-mail::message>
