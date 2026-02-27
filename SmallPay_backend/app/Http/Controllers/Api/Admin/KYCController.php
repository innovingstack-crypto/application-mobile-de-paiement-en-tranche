<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\KYC;
use App\Models\Notification;
use App\Notifications\KYCApprovedNotification;
use App\Notifications\KYCRejectedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class KYCController extends Controller
{
    /**
     * Obtenir tous les KYCs en attente et sous examen
     */
    public function index(Request $request)
    {
        $this->authorizeAdmin();

        $query = KYC::with(['user', 'approvedBy']);

        // Filtrer par statut
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        } else {
            // Par défaut, montrer les en attente et sous examen
            $query->whereIn('status', ['pending', 'under_review']);
        }

        // Recherche par nom ou email
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('client_id_number', 'like', '%' . $search . '%')
                    ->orWhere('client_phone', 'like', '%' . $search . '%')
                    ->orWhere('guarantor_name', 'like', '%' . $search . '%')
                    ->orWhere('guarantor_phone', 'like', '%' . $search . '%')
                    ->orWhere('data->client->fullName', 'like', '%' . $search . '%')
                    ->orWhere('data->client->email', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    });
            });
        }

        $kycs = $query->orderBy('created_at', 'asc')
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'data' => $kycs->map(fn($kyc) => $this->formatKYC($kyc))->all(),
            'pagination' => [
                'total' => $kycs->total(),
                'per_page' => $kycs->perPage(),
                'current_page' => $kycs->currentPage(),
                'last_page' => $kycs->lastPage(),
            ],
        ]);
    }

    /**
     * Afficher les détails d'un KYC
     */
    public function show($id)
    {
        $this->authorizeAdmin();

        $kyc = KYC::with(['user', 'approvedBy'])->findOrFail($id);

        return response()->json([
            'id' => $kyc->id,
            'user' => [
                'id' => $kyc->user->id,
                'name' => $kyc->user->name,
                'email' => $kyc->user->email,
                'phone' => $kyc->user->phone,
            ],
            'client' => [
                'fullName' => data_get($kyc->data, 'client.fullName', $kyc->user->name),
                'phoneNumber' => data_get($kyc->data, 'client.phoneNumber', $kyc->client_phone),
                'idNumber' => data_get($kyc->data, 'client.idNumber', $kyc->client_id_number),
                'address' => data_get($kyc->data, 'client.address'),
                'email' => data_get($kyc->data, 'client.email', $kyc->user->email),
                'documents' => [
                    'idFront' => $kyc->id_front_path ? asset('storage/' . $kyc->id_front_path) : null,
                    'idBack' => $kyc->id_back_path ? asset('storage/' . $kyc->id_back_path) : null,
                    'photo' => $kyc->client_photo_path ? asset('storage/' . $kyc->client_photo_path) : null,
                ],
            ],
            'signedDocument' => $kyc->signed_document_path ? asset('storage/' . $kyc->signed_document_path) : null,
            'guarantor' => [
                'name' => data_get($kyc->data, 'guarantor.name', $kyc->guarantor_name),
                'phoneNumber' => data_get($kyc->data, 'guarantor.phoneNumber', $kyc->guarantor_phone),
                'documents' => [
                    'idFront' => $kyc->guarantor_id_front_path ? asset('storage/' . $kyc->guarantor_id_front_path) : null,
                    'idBack' => $kyc->guarantor_id_back_path ? asset('storage/' . $kyc->guarantor_id_back_path) : null,
                ],
            ],
            'status' => $kyc->status,
            'rejection_reason' => $kyc->rejection_reason,
            'approved_by' => $kyc->approvedBy ? [
                'id' => $kyc->approvedBy->id,
                'name' => $kyc->approvedBy->name,
            ] : null,
            'approved_at' => $kyc->approved_at,
            'created_at' => $kyc->created_at,
        ]);
    }

    /**
     * Approuver un KYC
     */
    public function approve(Request $request, $id)
    {
        $this->authorizeAdmin();

        $kyc = KYC::findOrFail($id);
        $admin = Auth::user();

        // Approuver
        $kyc->approve($admin->id);

        // Créer une notification dans la base de données
        Notification::create([
            'user_id' => $kyc->user_id,
            'type' => 'kyc_approved',
            'title' => 'Vérification approuvée',
            'message' => 'Votre vérification d\'identité a été approuvée avec succès',
            'data' => json_encode(['kyc_id' => $kyc->id]),
            'is_read' => false,
        ]);

        // Envoyer un email
        $kyc->user->notify(new KYCApprovedNotification($kyc));

        return response()->json([
            'message' => 'KYC approuvé avec succès',
            'kyc' => $this->formatKYC($kyc->fresh(['user', 'approvedBy'])),
        ]);
    }

    /**
     * Rejetter un KYC
     */
    public function reject(Request $request, $id)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:10|max:500',
        ]);

        $kyc = KYC::findOrFail($id);
        $admin = Auth::user();

        // Rejetter
        $kyc->reject($admin->id, $validated['rejection_reason']);

        // Créer une notification dans la base de données
        Notification::create([
            'user_id' => $kyc->user_id,
            'type' => 'kyc_rejected',
            'title' => 'Vérification rejetée',
            'message' => 'Votre vérification d\'identité a été rejetée. Raison: ' . $validated['rejection_reason'],
            'data' => json_encode(['kyc_id' => $kyc->id, 'reason' => $validated['rejection_reason']]),
            'is_read' => false,
        ]);

        // Envoyer un email
        $kyc->user->notify(new KYCRejectedNotification($kyc));

        return response()->json([
            'message' => 'KYC rejeté avec succès',
            'kyc' => $this->formatKYC($kyc->fresh(['user', 'approvedBy'])),
        ]);
    }

    /**
     * Statistiques des KYCs
     */
    public function stats(Request $request)
    {
        $this->authorizeAdmin();

        $total = KYC::count();
        $pending = KYC::pending()->count();
        $approved = KYC::approved()->count();
        $rejected = KYC::rejected()->count();
        $underReview = KYC::underReview()->count();

        return response()->json([
            'total' => $total,
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
            'under_review' => $underReview,
            'approval_rate' => $total > 0 ? round(($approved / $total) * 100, 2) : 0,
        ]);
    }

    /**
     * Formater un KYC pour la réponse
     */
    private function formatKYC($kyc)
    {
        return [
            'id' => $kyc->id,
            'user' => [
                'id' => $kyc->user->id,
                'name' => $kyc->user->name,
                'email' => $kyc->user->email,
            ],
            'client' => [
                'fullName' => data_get($kyc->data, 'client.fullName', $kyc->user->name),
                'phoneNumber' => data_get($kyc->data, 'client.phoneNumber', $kyc->client_phone),
                'idNumber' => data_get($kyc->data, 'client.idNumber', $kyc->client_id_number),
                'email' => data_get($kyc->data, 'client.email', $kyc->user->email),
            ],
            'status' => $kyc->status,
            'guarantor' => [
                'name' => data_get($kyc->data, 'guarantor.name', $kyc->guarantor_name),
                'phoneNumber' => data_get($kyc->data, 'guarantor.phoneNumber', $kyc->guarantor_phone),
            ],
            'created_at' => $kyc->created_at,
            'approved_at' => $kyc->approved_at,
            'rejected_at' => $kyc->rejected_at,
        ];
    }

    /**
     * Vérifier l'accès admin
     */
    private function authorizeAdmin()
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['admin', 'super_admin'])) {
            abort(403, 'Accès non autorisé');
        }
    }
}
