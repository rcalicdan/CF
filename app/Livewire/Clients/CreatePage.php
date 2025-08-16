<?php

namespace App\Livewire\Clients;

use App\ActionService\ClientService;
use App\Models\Client;
use Livewire\Component;

class CreatePage extends Component
{
    public $first_name = '';
    public $last_name = '';
    public $email = '';
    public $phone_number = '';
    public $street_name = '';
    public $street_number = '';
    public $city = '';
    public $postal_code = '';
    public $notes = '';

    protected ClientService $clientService;

    public function boot(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    public function rules()
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:clients,email',
            'phone_number' => 'required|string|max:20',
            'street_name' => 'required|string|max:255',
            'street_number' => 'required|string|max:20',
            'city' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'notes' => 'nullable|string|max:1000',
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

    public function save()
    {
        $this->authorize('create', Client::class);
        $this->validate();

        $data = [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email ?: null,
            'phone_number' => $this->phone_number,
            'street_name' => $this->street_name,
            'street_number' => $this->street_number,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'notes' => $this->notes ?: null,
        ];

        try {
            $client = $this->clientService->createClient($data);

            session()->flash('success', 'Client created successfully!');

            return redirect()->route('clients.index');
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while creating the client. Please try again.');
        }
    }

    public function render()
    {
        $this->authorize('create', Client::class);
        return view('livewire.clients.create-page');
    }
}