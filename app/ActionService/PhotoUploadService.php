<?php

namespace App\ActionService;

use App\Models\OrderCarpetPhoto;
use Auth;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PhotoUploadService
{
    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected $disk;

    /**
     * Create a new photo upload service instance.
     *
     * @param  \Illuminate\Contracts\Filesystem\Filesystem|null  $disk
     */
    public function __construct()
    {
        $this->disk = Storage::disk('public');
    }

    /**
     * Upload a photo file.
     *
     * @param  string  $directory  The directory where the file should be stored
     * @return string The file path of the stored photo
     */
    public function upload(UploadedFile $file, string $directory): string
    {
        $filename = $file->hashName();

        $this->disk->putFileAs($directory, $file, $filename);

        return $directory.'/'.$filename;
    }

    public function deleteCarpetPhotoInStorage(OrderCarpetPhoto $carpetPhoto)
    {
        if (Storage::exists($carpetPhoto->photo_path)) {
            Storage::delete($carpetPhoto->photo_path);

            return true;
        }
    }

    public function deleteUserPhotoInStorage()
    {
        $user = Auth::user();

        if ($user->profile_path && Storage::exists($user->profile_path)) {
            Storage::delete($user->profile_path);

            return true;
        }

        return false;
    }

    public function handleProfilePictureUpdate(array &$data, $profilePictureFile)
    {
        $this->deleteUserPhotoInStorage();
        $data['profile_path'] = $this->upload($profilePictureFile, 'profile_pictures');
        unset($data['profile_picture']);
    }

    public function handleConfirmationSignatureDataImageUpload(array &$data, $signatureImageFile)
    {
        $data['signature_url'] = $this->upload($signatureImageFile, 'delivery_signatures');
        unset($data['signature_image']);
    }
}
