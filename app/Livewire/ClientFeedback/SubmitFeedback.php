<?php

namespace App\Livewire\ClientFeedback;

use App\Models\ClientFeedback;
use Livewire\Component;
use Livewire\Attributes\Validate;

class SubmitFeedback extends Component
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string|max:1000')]
    public string $opinion = '';

    #[Validate('required|integer|min:1|max:5')]
    public int $rating = 0;

    public bool $submitted = false;

    public function submit()
    {
        $this->validate();

        ClientFeedback::create([
            'name' => $this->name,
            'opinion' => $this->opinion,
            'rating' => $this->rating,
            'is_featured' => false, 
        ]);

        $this->submitted = true;
        
        $this->dispatch('feedback-submitted');
    }

    public function resetForm()
    {
        $this->reset(['name', 'opinion', 'rating', 'submitted']);
    }

    public function render()
    {
        return view('livewire.client-feedback.submit-feedback');
    }
}