<?php

namespace App\Http\Controllers\Api\Sms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sms\StoreSmsFormRequest;
use App\Jobs\SendSmsJob;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="SMS",
 *     description="Endpoints for managing SMS functionality"
 * )
 */
class SendCustomSmsController extends Controller
{
    /**
     * @OA\Post(
     *     path="/send-sms",
     *     summary="Send a custom SMS message",
     *     description="This endpoint allows sending a custom SMS message to a specified phone number.",
     *     operationId="sendSms",
     *     tags={"SMS"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="SMS details",
     *
     *         @OA\JsonContent(
     *             required={"phone_number", "message"},
     *
     *             @OA\Property(property="phone_number", type="string", example="+1234567890", description="Recipient's phone number"),
     *             @OA\Property(property="message", type="string", example="Hello, this is a test message!", description="The content of the SMS message")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="SMS sent successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="SMS sent successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Failed to send SMS",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to send SMS: Error message")
     *         )
     *     )
     * )
     */
    public function sendSms(StoreSmsFormRequest $request)
    {
        $phoneNumber = $request->input('phone_number');
        $smsMessage = $request->input('message');
        try {
            dispatch(new SendSmsJob($phoneNumber, $smsMessage));

            return response()->json([
                'success' => true,
                'message' => 'SMS sent successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send SMS: '.$e->getMessage(),
            ], 500);
        }
    }
}
