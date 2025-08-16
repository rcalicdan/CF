<?php

namespace App\Models;

use App\ActionService\EnumTranslationService;
use App\Enums\OrderCarpetStatus;
use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;

class OrderCarpet extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'qr_code',
        'height',
        'width',
        'total_area',
        'measured_at',
        'status',
        'remarks',
    ];

    protected $appends = ['services_count'];

    public function casts()
    {
        return [
            'measured_at' => 'datetime',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status ? EnumTranslationService::translate(OrderCarpetStatus::from($this->status)) : '';
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function orderCarpetPhotos()
    {
        return $this->hasMany(OrderCarpetPhoto::class);
    }

    public function complaint()
    {
        return $this->hasOne(Complaint::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'carpet_services', 'order_carpet_id', 'service_id')
            ->using(CarpetService::class)
            ->withPivot(['id', 'total_price']);
    }

    public function getTotalPriceAttribute()
    {
        return $this->services->sum('pivot.total_price');
    }

    public function getServicesCountAttribute()
    {
        return (int) DB::table('carpet_services')
            ->selectRaw('COUNT(*) AS service_count')
            ->where('order_carpet_id', $this->id)
            ->value('service_count');
    }

    public function getQrCodePathAttribute(): ?string
    {
        if (!$this->qr_code) {
            return null;
        }

        return "qr-codes/{$this->qr_code}.png";
    }

    /**
     * Generate QR code with order ID prefix and unique number.
     */
    public function generateQrCode(): string
    {
        if (!extension_loaded('gd')) {
            throw new \Exception('The GD PHP extension is required to generate QR codes.');
        }

        try {
            $referenceCode = $this->generateUniqueReference();
            $qrCodePath = "qr-codes/{$referenceCode}.png";
            $qrCode = $referenceCode;

            $result = Builder::create()
                ->writer(new PngWriter())
                ->data($referenceCode)
                ->encoding(new Encoding('UTF-8'))
                ->errorCorrectionLevel(ErrorCorrectionLevel::High)
                ->size(400)
                ->margin(20)
                ->build();

            $qrCodeContent = $result->getString();

            if ($this->qr_code && Storage::disk('public')->exists($this->qr_code)) {
                Storage::disk('public')->delete($this->qr_code);
            }

            Storage::disk('public')->put($qrCodePath, $qrCodeContent);
            $this->update(['qr_code' => $qrCode]);

            return $qrCodePath;
        } catch (\Throwable $e) {
            \Log::error('QR Code generation failed', [
                'order_carpet_id' => $this->id,
                'error' => $e->getMessage()
            ]);

            throw new \Exception('Failed to generate QR code. Please try again.');
        }
    }

    /**
     * Generate unique reference with order ID prefix.
     */
    private function generateUniqueReference(): string
    {
        do {
            $uniqueNumber = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
            $reference = "ORD-{$this->order_id}-{$uniqueNumber}";
            $exists = static::where('qr_code', 'like', "%{$reference}%")->exists();
        } while ($exists);

        return $reference;
    }

    /**
     * Get the reference code from the QR code path.
     */
    public function getReferenceCodeAttribute(): ?string
    {
        if (!$this->qr_code) {
            return null;
        }

        $filename = pathinfo($this->qr_code, PATHINFO_FILENAME);
        return $filename;
    }

    /**
     * Find carpet by reference code.
     */
    public static function findByReference(string $reference): ?self
    {
        return static::where('qr_code', 'like', "%{$reference}.png")->first();
    }

    /**
     * Get all carpets for a specific order by reference pattern.
     */
    public static function findByOrderReference(int $orderId): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('qr_code', 'like', "%ORD-{$orderId}-%")->get();
    }

    /**
     * Generate a unique ID for the QR code filename.
     */
    private function generateUniqueQrId(): string
    {
        do {
            $uniqueId = strtoupper(Str::random(8));
            $exists = static::where('qr_code', 'like', "%{$uniqueId}%")->exists();
        } while ($exists);

        return $uniqueId;
    }

    /**
     * Get the full URL for the QR code image.
     */
    public function getQrCodeUrlAttribute(): ?string
    {
        return $this->qr_code ? Storage::disk('public')->url($this->qr_code) : null;
    }

    /**
     * Check if the QR code exists and is valid.
     */
    public function hasValidQrCode(): bool
    {
        return !empty($this->qr_code) && Storage::disk('public')->exists($this->getQrCodePathAttribute());
    }
}
