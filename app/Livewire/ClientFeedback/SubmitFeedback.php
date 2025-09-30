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
    public ?string $redirectUrl = null;

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

        if ($this->shouldRedirectToGoogle()) {
            return redirect()->away(config('services.google_reviews.review_url'));
        }

        $this->dispatch('feedback-submitted');
    }

    protected function shouldRedirectToGoogle(): bool
    {
        return $this->rating === 5
            && config('services.google_reviews.enabled', true)
            && config('services.google_reviews.redirect_on_five_stars', true);
    }

    public function resetForm()
    {
        $this->reset(['name', 'opinion', 'rating', 'submitted', 'redirectUrl']);
    }

    public function render()
    {
        return view('livewire.client-feedback.submit-feedback');
    }
}
