<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Import extends Component
{
    use WithFileUploads;

    public $file;

    public array $rows = [];

    public ?string $error = null;

    public ?array $result = null;

    public function mount(): void
    {
        $this->authorize('create', Client::class);
    }

    public function parse(): void
    {
        $this->reset(['error', 'result']);

        $this->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $handle = fopen($this->file->getRealPath(), 'r');

        $header = fgetcsv($handle, 0, ';');

        if ($header === false) {
            $this->error = 'Le fichier est vide ou illisible.';
            fclose($handle);

            return;
        }

        $header = array_map(fn ($value) => $this->toUtf8(ltrim(trim((string) $value), "\xEF\xBB\xBF")), $header);

        $rows = [];

        while (($line = fgetcsv($handle, 0, ';')) !== false) {
            if (count($line) <= 1 && trim((string) ($line[0] ?? '')) === '') {
                continue;
            }

            $data = array_combine($header, array_pad($line, count($header), null));

            $companyName = $this->toUtf8(trim((string) ($data['Raison sociale'] ?? '')));

            if ($companyName === '') {
                continue;
            }

            $rows[] = [
                'code_client' => $this->value($data, 'Code'),
                'company_name' => $companyName,
                'email' => $this->value($data, 'E-mail'),
                'phone' => $this->value($data, 'Tél'),
                'address_line1' => $this->value($data, 'Adresse1'),
                'address_line2' => $this->value($data, 'Adresse2'),
                'postal_code' => $this->value($data, 'CP'),
                'city' => $this->value($data, 'Ville'),
                'siret' => $this->value($data, 'SIRET'),
                'actif' => strtolower((string) ($data['Actif'] ?? 'oui')) !== 'non',
            ];
        }

        fclose($handle);

        if (empty($rows)) {
            $this->error = 'Aucune ligne exploitable trouvée dans le fichier.';

            return;
        }

        $this->rows = $rows;
    }

    public function import(): void
    {
        if (empty($this->rows)) {
            return;
        }

        $created = 0;
        $updated = 0;

        foreach ($this->rows as $row) {
            $attributes = [
                'first_name' => '',
                'last_name' => $row['company_name'],
                'type' => 'pro',
                'company_name' => $row['company_name'],
                'email' => $row['email'],
                'phone' => $row['phone'],
                'address_line1' => $row['address_line1'],
                'address_line2' => $row['address_line2'],
                'postal_code' => $row['postal_code'],
                'city' => $row['city'],
                'code_client' => $row['code_client'],
                'siret' => $row['siret'],
                'actif' => $row['actif'],
            ];

            $client = $row['code_client']
                ? Client::withTrashed()->where('code_client', $row['code_client'])->first()
                : null;

            if ($client) {
                $client->update($attributes);
                $updated++;
            } else {
                Client::create($attributes);
                $created++;
            }
        }

        $this->reset(['rows', 'file']);
        $this->result = ['created' => $created, 'updated' => $updated];
    }

    public function cancel(): void
    {
        $this->reset(['rows', 'file', 'error']);
    }

    private function value(array $data, string $key): ?string
    {
        $value = $this->toUtf8(trim((string) ($data[$key] ?? '')));

        return $value !== '' ? $value : null;
    }

    private function toUtf8(?string $value): ?string
    {
        if ($value === null || $value === '' || mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        return mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
    }

    public function render()
    {
        return view('livewire.clients.import');
    }
}
