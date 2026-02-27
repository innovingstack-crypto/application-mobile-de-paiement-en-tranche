<?php

namespace App\Http\Controllers\Api;

use App\Models\KYC;
use App\Models\User;
use App\Http\Requests\SubmitKYCRequest;
use App\Services\KYCService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class KYCController extends Controller
{
    private KYCService $kycService;

    public function __construct(KYCService $kycService)
    {
        $this->kycService = $kycService;
    }

    /**
     * Obtenir le statut KYC de l'utilisateur authentifié
     */
    public function status(Request $request)
    {
        $user = Auth::user();
        $result = $this->kycService->getUserKYCStatus($user);

        return response()->json($result);
    }

    /**
     * Soumettre ou mettre à jour le KYC avec structure hiérarchique
     * 
     * Accepte la structure:
     * {
     *   "client": {
     *     "fullName": string,
     *     "phoneNumber": string,
     *     "idNumber": string,
     *     "address": string,
     *     "email": string (optionnel),
     *     "documents": {
     *       "idFront": file,
     *       "idBack": file,
     *       "photo": file
     *     }
     *   },
     *   "signedDocument": file,
     *   "guarantor": {
     *     "name": string,
     *     "phoneNumber": string,
     *     "documents": {
     *       "idFront": file,
     *       "idBack": file
     *     }
     *   }
     * }
     */
    public function submit(SubmitKYCRequest $request)
    {
        $user = Auth::user();

        // Log pour debugging
        Log::info('KYC Submit Request', [
            'user_id' => $user->id,
            'has_client' => $request->has('client'),
            'has_guarantor' => $request->has('guarantor'),
            'content_type' => $request->header('Content-Type'),
            'origin' => $request->header('Origin'),
        ]);

        // Vérifier que l'utilisateur est authentifié
        if (!$user) {
            return response()->json([
                'message' => 'Utilisateur non authentifié',
            ], 401);
        }

        try {
            // Soumettre le KYC via le service
            $kyc = $this->kycService->submitKYC($user, $request);

            return response()->json([
                'message' => 'KYC soumis avec succès',
                'kyc' => [
                    'id' => $kyc->id,
                    'status' => $kyc->status,
                    'user_id' => $kyc->user_id,
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('KYC Submission Error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Erreur lors de la soumission du KYC',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtenir les détails complets du KYC
     */
    public function show($id)
    {
        $user = Auth::user();
        $kyc = KYC::with(['user', 'approvedBy'])->findOrFail($id);

        // Vérifier que l'utilisateur peut voir ce KYC
        if ($user->id !== $kyc->user_id && !in_array($user->role, ['admin', 'super_admin'])) {
            abort(403, 'Unauthorized');
        }

        $result = $this->kycService->getKYCDetails($kyc);
        return response()->json($result);
    }

    /**
     * Obtenir la liste des KYCs en attente (pour admin)
     */
    public function pending(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'super_admin'])) {
            abort(403, 'Unauthorized');
        }

        $kycs = KYC::with('user')
            ->whereIn('status', ['pending', 'under_review'])
            ->orderBy('created_at', 'asc')
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'data' => $kycs->map(fn($kyc) => [
                'id' => $kyc->id,
                'user' => [
                    'id' => $kyc->user->id,
                    'name' => $kyc->user->name,
                    'email' => $kyc->user->email,
                ],
                'client' => [
                    'fullName' => data_get($kyc->data, 'client.fullName', $kyc->user->name),
                    'idNumber' => data_get($kyc->data, 'client.idNumber', $kyc->client_id_number),
                ],
                'status' => $kyc->status,
                'created_at' => $kyc->created_at,
            ])->all(),
            'pagination' => [
                'total' => $kycs->total(),
                'per_page' => $kycs->perPage(),
                'current_page' => $kycs->currentPage(),
                'last_page' => $kycs->lastPage(),
            ],
        ]);
    }
}
