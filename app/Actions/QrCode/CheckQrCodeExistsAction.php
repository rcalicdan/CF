<?php

namespace App\Actions\QrCode;

use App\Models\OrderCarpet;

class CheckQrCodeExistsAction
{
    /**
     * Check if a QR code reference exists and return carpet data if found.
     *
     * @param string $referenceCode
     * @return array
     */
    public function execute(string $referenceCode): array
    {
        $carpet = OrderCarpet::findByReference($referenceCode);

        $result = [
            'exists' => $carpet !== null,
            'qr_code' => $referenceCode,
            'carpet' => null
        ];

        if ($carpet !== null) {
            $result['carpet'] = $this->formatCarpetData($carpet);
        }

        return $result;
    }

    /**
     * Format carpet data for response.
     *
     * @param OrderCarpet $carpet
     * @return array
     */
    private function formatCarpetData(OrderCarpet $carpet): array
    {
        return [
            'id' => $carpet->id,
            'order_id' => $carpet->order_id,
            'status' => $carpet->status,
            'status_label' => $carpet->status_label,
            'height' => $carpet->height,
            'width' => $carpet->width,
            'total_area' => $carpet->total_area,
            'measured_at' => $carpet->measured_at,
            'qr_code' => $carpet->qr_code,
            'qr_code_url' => $carpet->qr_code_url,
            'reference_code' => $carpet->reference_code,
            'services_count' => $carpet->services_count,
            'total_price' => $carpet->total_price,
            'remarks' => $carpet->remarks,
            'created_at' => $carpet->created_at,
            'updated_at' => $carpet->updated_at,
        ];
    }
}
