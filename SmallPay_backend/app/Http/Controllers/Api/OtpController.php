<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OtpController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Envoyer un code OTP pour test
     * POST /api/otp/test-send
     *
     * @bodyParam identifier string required Email ou téléphone du destinataire. Example: test@example.com ou +237679060606
     * @bodyParam type string required Type d'OTP (registration, password_reset, verification). Example: registration
     * @bodyParam channel string required Canal de livraison (email ou sms). Example: sms
     * @response {
     *   "success": true,
     *   "data": {
     *     "identifier": "test@example.com",
     *     "type": "registration",
     *     "channel": "email",
     *     "message": "OTP sent successfully"
     *   }
     * }
     */
    public function testSend(Request $request)
    {
        try {
            $request->validate([
                'identifier' => 'required|string',
                'type' => 'required|in:registration,password_reset,verification',
                'channel' => 'required|in:email,sms',
            ]);

            $result = $this->otpService->sendOtp(
                $request->identifier,
                $request->type,
                $request->channel
            );

            return response()->json([
                'success' => $result['success'],
                'data' => [
                    'identifier' => $request->identifier,
                    'type' => $request->type,
                    'channel' => $request->channel,
                    'message' => $result['message'],
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'error_code' => 'OTP_TEST_SEND_FAILED',
            ], 400);
        }
    }

    /**
     * Vérifier un code OTP pour test
     * POST /api/otp/test-verify
     *
     * @bodyParam identifier string required Email ou téléphone du destinataire. Example: test@example.com ou +237679060606
     * @bodyParam code string required Code OTP à vérifier. Example: 123456
     * @bodyParam type string required Type d'OTP (registration, password_reset, verification). Example: registration
     * @response {
     *   "success": true,
     *   "data": {
     *     "identifier": "test@example.com",
     *     "type": "registration",
     *     "message": "OTP verified successfully"
     *   }
     * }
     */
    public function testVerify(Request $request)
    {
        try {
            $request->validate([
                'identifier' => 'required|string',
                'code' => 'required|string',
                'type' => 'required|in:registration,password_reset,verification',
            ]);

            $result = $this->otpService->verifyOtp(
                $request->identifier,
                $request->code,
                $request->type
            );

            return response()->json([
                'success' => $result['success'],
                'data' => [
                    'identifier' => $request->identifier,
                    'type' => $request->type,
                    'message' => $result['message'],
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'error_code' => 'OTP_TEST_VERIFY_FAILED',
            ], 400);
        }
    }

    /**
     * Vérifier le statut d'un OTP
     * POST /api/otp/status
     *
     * @bodyParam identifier string required Email ou téléphone du destinataire. Example: test@example.com ou +237679060606
     * @bodyParam type string required Type d'OTP (registration, password_reset, verification). Example: registration
     * @response {
     *   "success": true,
     *   "data": {
     *     "identifier": "test@example.com",
     *     "type": "registration",
     *     "is_valid": true,
     *     "message": "OTP is valid"
     *   }
     * }
     */
    public function status(Request $request)
    {
        try {
            $request->validate([
                'identifier' => 'required|string',
                'type' => 'required|in:registration,password_reset,verification',
            ]);

            $isValid = $this->otpService->isOtpValid($request->identifier, $request->type);

            return response()->json([
                'success' => true,
                'data' => [
                    'identifier' => $request->identifier,
                    'type' => $request->type,
                    'is_valid' => $isValid,
                    'message' => $isValid ? 'OTP is valid' : 'OTP is not valid or expired',
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'error_code' => 'OTP_STATUS_CHECK_FAILED',
            ], 400);
        }
    }

    /**
     * Nettoyer les codes OTP expirés
     * POST /api/otp/cleanup
     *
     * @response {
     *   "success": true,
     *   "data": {
     *     "deleted_count": 10,
     *     "message": "Expired OTP codes cleaned up"
     *   }
     * }
     */
    public function cleanup(Request $request)
    {
        try {
            $deletedCount = $this->otpService->cleanupExpiredOtp();

            return response()->json([
                'success' => true,
                'data' => [
                    'deleted_count' => $deletedCount,
                    'message' => 'Expired OTP codes cleaned up',
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'error_code' => 'OTP_CLEANUP_FAILED',
            ], 400);
        }
    }
}