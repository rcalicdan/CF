<?php

namespace App\Livewire\Clients;

use App\ActionService\ClientService;
use App\Models\Client;
use Illuminate\Validation\Rule;
use Livewire\Component;

class UpdatePage extends Component
{
    public Client $client;
    public $first_name = '';
    public $last_name = '';
    public $phone_number = '';
    public $street_name = '';
    public $street_number = '';
    public $city = '';
    public $postal_code = '';
    public $remarks = '';

    protected ClientService $clientService;

    public function boot(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    public function mount(Client $client)
    {
        $this->client = $client;

        $this->first_name = $client->first_name;
        $this->last_name = $client->last_name;
        $this->phone_number = $client->phone_number;
        $this->street_name = $client->street_name;
        $this->street_number = $client->street_number;
        $this->city = $client->city;
        $this->postal_code = $client->postal_code;
        $this->notes = $client->notes;
    }

    public function rules()
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'street_name' => 'required|string|max:255',
            'street_number' => 'required|string|max:20',
            'city' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'remarks' => 'nullable|string|max:1000',
        ];
    }

    public function validationAttributes()
    {
        return [
            'first_name' => 'first name',
            'last_name' => 'last name',
            'phone_number' => 'phone number',
            'street_name' => 'street name',
            'street_number' => 'street number',
            'postal_code' => 'postal code',
        ];
    }

    public function update()
    {
        $this->authorize('update', $this->client);
        $this->validate();

        $data = [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone_number' => $this->phone_number,
            'street_name' => $this->street_name,
            'street_number' => $this->street_number,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'remarks' => $this->remarks ?: null,
        ];

        try {
            $this->clientService->updateClient($this->client->id, $data);

            session()->flash('success', 'Klient został pomyślnie zaktualizowany!');

            return redirect()->route('clients.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Wystąpił błąd podczas aktualizacji klienta. Proszę spróbować ponownie.');
        }
    }

    public function render()
    {
        $this->authorize('update', $this->client);
        return view('livewire.clients.update-page');
    }
}