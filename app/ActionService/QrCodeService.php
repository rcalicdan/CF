<?php

namespace App\ActionService;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use App\Models\OrderCarpet;

class QrCodeService
{
    private const QR_CODE_DIRECTORY = 'qr-codes';

    /**
     * Generate a standalone QR code.
     */
    public function generateQrCode(): array
    {
        if (!extension_loaded('gd')) {
            throw new \Exception('The GD PHP extension is required to generate QR codes.');
        }

        try {
            $referenceCode = $this->generateUniqueReference();
            $qrCodePath = self::QR_CODE_DIRECTORY . "/{$referenceCode}.png";

            $result = Builder::create()
                ->writer(new PngWriter())
                ->data($referenceCode)
                ->encoding(new Encoding('UTF-8'))
                ->errorCorrectionLevel(ErrorCorrectionLevel::High)
                ->size(400)
                ->margin(20)
                ->build();

            $qrCodeContent = $result->getString();
            Storage::disk('public')->put($qrCodePath, $qrCodeContent);

            return [
                'reference_code' => $referenceCode,
                'qr_code_path' => $qrCodePath,
                'qr_code_url' => Storage::disk('public')->url($qrCodePath),
                'created_at' => now(),
            ];
        } catch (\Throwable $e) {
            \Log::error('QR Code generation failed', [
                'error' => $e->getMessage()
            ]);

            throw new \Exception('Failed to generate QR code. Please try again.');
        }
    }

    /**
     * Generate multiple QR codes.
     */
    public function generateBulkQrCodes(int $count): array
    {
        $qrCodes = [];

        for ($i = 0; $i < $count; $i++) {
            $qrCodes[] = $this->generateQrCode();
        }

        return $qrCodes;
    }

    /**
     * Get paginated QR codes with filters and search.
     */
    public function getPaginatedQrCodes(string $filter = 'all', string $search = '', int $perPage = 12, int $page = 1): array
    {
        $allQrCodes = $this->getAllQrCodes();
        $filteredQrCodes = $this->applyFilter($allQrCodes, $filter);

        if (!empty($search)) {
            $filteredQrCodes = array_filter($filteredQrCodes, function ($qrCode) use ($search) {
                return str_contains(strtolower($qrCode['reference_code']), strtolower($search));
            });
        }

        foreach ($filteredQrCodes as &$qrCode) {
            if ($qrCode['is_assigned']) {
                $qrCode['assigned_info'] = $this->getAssignedCarpet($qrCode['reference_code']);
            }
        }

        $total = count($filteredQrCodes);
        $offset = ($page - 1) * $perPage;
        $paginatedData = array_slice($filteredQrCodes, $offset, $perPage);

        return [
            'data' => array_values($paginatedData),
            'total' => $total,
            'current_page' => $page,
            'per_page' => $perPage,
            'last_page' => ceil($total / $perPage),
            'from' => $total > 0 ? $offset + 1 : 0,
            'to' => min($offset + $perPage, $total),
        ];
    }

    /**
     * Apply filter to QR codes.
     */
    private function applyFilter(array $qrCodes, string $filter): array
    {
        switch ($filter) {
            case 'assigned':
                return array_filter($qrCodes, fn($qrCode) => $qrCode['is_assigned']);
            case 'unassigned':
                return array_filter($qrCodes, fn($qrCode) => !$qrCode['is_assigned']);
            default:
                return $qrCodes;
        }
    }

    /**
     * Get all QR code files from the qr-codes directory.
     */
    public function getAllQrCodes(): array
    {
        $files = Storage::disk('public')->files(self::QR_CODE_DIRECTORY);
        $qrCodes = [];

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'png') {
                $referenceCode = pathinfo($file, PATHINFO_FILENAME);
                $isAssigned = $this->isQrCodeAssigned($referenceCode);

                $qrCodes[] = [
                    'reference_code' => $referenceCode,
                    'file_name' => basename($file),
                    'qr_code_path' => $file,
                    'qr_code_url' => Storage::disk('public')->url($file),
                    'is_assigned' => $isAssigned,
                    'created_at' => Storage::disk('public')->lastModified($file),
                ];
            }
        }

        usort($qrCodes, function ($a, $b) {
            return $b['created_at'] <=> $a['created_at'];
        });

        return $qrCodes;
    }

    /**
     * Get only unassigned QR codes.
     */
    public function getUnassignedQrCodes(): array
    {
        return array_filter($this->getAllQrCodes(), function ($qrCode) {
            return !$qrCode['is_assigned'];
        });
    }

    /**
     * Get only assigned QR codes.
     */
    public function getAssignedQrCodes(): array
    {
        return array_filter($this->getAllQrCodes(), function ($qrCode) {
            return $qrCode['is_assigned'];
        });
    }

    /**
     * Check if a QR code is assigned to any carpet by comparing the reference code.
     */
    public function isQrCodeAssigned(string $referenceCode): bool
    {
        return OrderCarpet::where('qr_code', $referenceCode)->exists();
    }

    /**
     * Get assigned carpet info for a QR code reference.
     */
    public function getAssignedCarpet(string $referenceCode): ?array
    {
        $carpet = OrderCarpet::where('qr_code', $referenceCode)
            ->with(['order.client'])
            ->first();

        if (!$carpet) {
            return null;
        }

        return [
            'carpet_id' => $carpet->id,
            'order_id' => $carpet->order_id,
            'client_name' => $carpet->order->client->full_name ?? 'N/A',
            'assigned_at' => $carpet->updated_at,
        ];
    }

    /**
     * Delete QR code files by reference codes.
     */
    public function deleteQrCodes(array $referenceCodes): int
    {
        $deletedCount = 0;

        foreach ($referenceCodes as $referenceCode) {
            $filePath = self::QR_CODE_DIRECTORY . "/{$referenceCode}.png";

            if (Storage::disk('public')->exists($filePath)) {
                if (!$this->isQrCodeAssigned($referenceCode)) {
                    Storage::disk('public')->delete($filePath);
                    $deletedCount++;
                }
            }
        }

        return $deletedCount;
    }

    /**
     * Get QR codes by reference codes.
     */
    public function getQrCodesByReferences(array $referenceCodes): array
    {
        $allQrCodes = $this->getAllQrCodes();

        return array_filter($allQrCodes, function ($qrCode) use ($referenceCodes) {
            return in_array($qrCode['reference_code'], $referenceCodes);
        });
    }

    /**
     * Generate unique reference code (same pattern as OrderCarpet model).
     */
    private function generateUniqueReference(): string
    {
        do {
            $uniqueNumber = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
            $reference = "QR-{$uniqueNumber}";
            $filePath = self::QR_CODE_DIRECTORY . "/{$reference}.png";
            $exists = Storage::disk('public')->exists($filePath);
        } while ($exists);
        return $reference;
    }

    /**
     * Get statistics about QR codes.
     */
    public function getQrCodeStats(): array
    {
        $allQrCodes = $this->getAllQrCodes();
        $unassigned = array_filter($allQrCodes, fn($qr) => !$qr['is_assigned']);
        $assigned = array_filter($allQrCodes, fn($qr) => $qr['is_assigned']);

        return [
            'total' => count($allQrCodes),
            'unassigned' => count($unassigned),
            'assigned' => count($assigned),
        ];
    }

    /**
     * Find available QR codes that can be assigned to carpets.
     */
    public function getAvailableQrCodesForAssignment(): array
    {
        return $this->getUnassignedQrCodes();
    }

    /**
     * Check if a specific reference code exists and is available.
     */
    public function isReferenceCodeAvailable(string $referenceCode): bool
    {
        $filePath = self::QR_CODE_DIRECTORY . "/{$referenceCode}.png";

        return Storage::disk('public')->exists($filePath) && !$this->isQrCodeAssigned($referenceCode);
    }

    /**
     * Assign a QR code to a carpet.
     */
    public function assignQrCodeToCarpet(string $referenceCode, int $carpetId): bool
    {
        $carpet = OrderCarpet::find($carpetId);
        if ($carpet && !$carpet->qr_code) {
            $qrCodePath = self::QR_CODE_DIRECTORY . "/{$referenceCode}.png";

            if (Storage::disk('public')->exists($qrCodePath)) {
                $carpet->update(['qr_code' => $referenceCode]); // Save only the reference code
                return true;
            }
        }
        return false;
    }
}
