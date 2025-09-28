<?php

namespace App\Livewire\Orders\Carpets;

use App\Models\OrderCarpet;
use App\Observers\OrderCarpetObserver;
use App\Rules\ValidImageFile;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class ShowPage extends Component
{
    use WithFileUploads;

    public OrderCarpet $orderCarpet;
    public bool $isGeneratingQr = false;
    public $newPhotos = [];

    public function mount(OrderCarpet $carpet)
    {
        $this->orderCarpet = OrderCarpet::with([
            'order',
            'orderCarpetPhotos',
            'services' => function ($q) {
                $q->withPivot(['total_price']);
            },
            'histories' => function ($q) {
                $q->with('user')->orderBy('created_at', 'desc');
            }
        ])->findOrFail($carpet->id);
    }

    public function removePhoto($photoIndex)
    {
        if (isset($this->newPhotos[$photoIndex])) {
            unset($this->newPhotos[$photoIndex]);
            $this->newPhotos = array_values($this->newPhotos);
        }
    }

    public function saveNewPhotos()
    {
        $this->validate([
            'newPhotos' => 'required|array|min:1|max:10',
            'newPhotos.*' => [
                'required',
                'max:5128',
                ValidImageFile::carpet(5),
            ],
        ], [
            'newPhotos.required' => __('validation.carpet_photos.required'),
            'newPhotos.array' => __('validation.carpet_photos.invalid_format'),
            'newPhotos.min' => __('validation.carpet_photos.min_photos'),
            'newPhotos.max' => __('validation.carpet_photos.max_photos'),
            'newPhotos.*.required' => __('validation.carpet_photos.photo_required'),
            'newPhotos.*.max' => __('validation.image_file.file_too_large'),
        ], [
            'newPhotos' => 'Carpet Photos',
            'newPhotos.*' => 'Photo File',
        ]);

        try {
            $photoCount = count($this->newPhotos);

            foreach ($this->newPhotos as $photo) {
                $path = $photo->store('carpet-photos', 'public');
                $this->orderCarpet->orderCarpetPhotos()->create([
                    'photo_path' => $path,
                    'user_id' => Auth::user()->id,
                ]);
            }

            OrderCarpetObserver::logPhotoAdded($this->orderCarpet, $photoCount);

            $this->reset('newPhotos');
            $this->orderCarpet->refresh();

            session()->flash('message', 'Zdjęcia zostały pomyślnie przesłane.');
            session()->flash('message-type', 'success');

            $this->dispatch('photos-uploaded');
            $this->dispatch('close-photo-modal');
        } catch (\Exception $e) {
            session()->flash('message', 'Nie udało się przesłać zdjęć: ' . $e->getMessage());
            session()->flash('message-type', 'error');
        }
    }


    public function generateQrCode()
    {
        $this->isGeneratingQr = true;
        try {
            $this->orderCarpet->generateQrCode();
            $this->orderCarpet->refresh();

            OrderCarpetObserver::logQrGenerated($this->orderCarpet);

            session()->flash('message', 'Kod QR został pomyślnie wygenerowany.');
            session()->flash('message-type', 'success');
        } catch (\Exception $e) {
            session()->flash('message', 'Nie udało się wygenerować kodu QR: ' . $e->getMessage());
            session()->flash('message-type', 'error');
        } finally {
            $this->isGeneratingQr = false;
        }
    }

    public function downloadQrCode()
    {
        $this->authorize('view', $this->orderCarpet);
        if (!$this->orderCarpet->hasValidQrCode()) {
            session()->flash('message', 'Kod QR nie jest dostępny.');
            session()->flash('message-type', 'error');
            return null;
        }
        try {
            return Storage::disk('public')->download(
                $this->orderCarpet->qr_code_path,
                "carpet-{$this->orderCarpet->id}-qr-code.png"
            );
        } catch (\Exception $e) {
            session()->flash('message', 'Nie udało się pobrać kodu QR: ' . $e->getMessage());
            session()->flash('message-type', 'error');
            return null;
        }
    }

    public function render()
    {
        $this->authorize('view', $this->orderCarpet);
        return view('livewire.orders.carpets.show-page', [
            'orderCarpet' => $this->orderCarpet,
        ]);
    }
}
