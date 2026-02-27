<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\KYC;
use App\Models\Notification;
use App\Notifications\KYCApprovedNotification;
use App\Notifications\KYCRejectedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KYCController extends Controller
{
    /**
     * Afficher la liste de tous les KYCs avec filtres
     */
    public function index()
    {
        $query = KYC::with(['user', 'approvedBy']);

        // Filtre par statut
        $status = request()->string('status')->toString();
        if ($status && in_array($status, ['pending', 'approved', 'rejected', 'under_review'])) {
            $query->where('status', $status);
        }

        // Recherche
        $q = request()->string('q')->toString();
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('client_id_number', 'like', "%{$q}%")
                    ->orWhere('client_phone', 'like', "%{$q}%")
                    ->orWhere('guarantor_name', 'like', "%{$q}%")
                    ->orWhere('guarantor_phone', 'like', "%{$q}%")
                    ->orWhere('data->client->fullName', 'like', "%{$q}%")
                    ->orWhere('data->client->email', 'like', "%{$q}%")
                    ->orWhereHas('user', function ($subQuery) use ($q) {
                        $subQuery->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%");
                    });
            });
        }

        $kycs = $query->latest()->paginate(20)->withQueryString();
        
        // Statistiques
        $stats = [
            'total' => KYC::count(),
            'pending' => KYC::where('status', 'pending')->count(),
            'under_review' => KYC::where('status', 'under_review')->count(),
            'approved' => KYC::where('status', 'approved')->count(),
            'rejected' => KYC::where('status', 'rejected')->count(),
        ];

        return view('Admin.kyc.index', compact('kycs', 'stats'));
    }

    /**
     * Afficher les détails complets d'un KYC
     */
    public function show(KYC $kyc)
    {
        // Charger les relations
        $kyc->load(['user', 'approvedBy']);

        return view('Admin.kyc.show', compact('kyc'));
    }

    /**
     * Approuver un KYC
     */
    public function approve(Request $request, KYC $kyc)
    {
        // Vérifier que c'est un super admin
        if (Auth::user()->role !== 'super_admin') {
            abort(403, 'Unauthorized');
        }

        $admin = Auth::user();

        // Approuver le KYC
        $kyc->approve($admin->id);

        // Créer une notification en base de données
        Notification::create([
            'user_id' => $kyc->user_id,
            'type' => 'kyc_approved',
            'title' => 'Vérification approuvée',
            'message' => 'Votre vérification d\'identité a été approuvée avec succès. Vous pouvez maintenant procéder à vos achats.',
            'is_read' => false,
        ]);

        // Envoyer un email
        $kyc->user->notify(new KYCApprovedNotification($kyc));

        return redirect()->route('admin.kyc.show', $kyc)
            ->with('success', 'KYC approuvé avec succès. Une notification a été envoyée à l\'utilisateur.');
    }

    /**
     * Rejeter un KYC
     */
    public function reject(Request $request, KYC $kyc)
    {
        // Vérifier que c'est un super admin
        if (Auth::user()->role !== 'super_admin') {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:10|max:500',
        ]);

        $admin = Auth::user();

        // Rejeter le KYC
        $kyc->reject($admin->id, $validated['rejection_reason']);

        // Créer une notification en base de données
        Notification::create([
            'user_id' => $kyc->user_id,
            'type' => 'kyc_rejected',
            'title' => 'Vérification rejetée',
            'message' => 'Votre vérification d\'identité a été rejetée. Raison: ' . $validated['rejection_reason'],
            'is_read' => false,
        ]);

        // Envoyer un email
        $kyc->user->notify(new KYCRejectedNotification($kyc));

        return redirect()->route('admin.kyc.show', $kyc)
            ->with('success', 'KYC rejeté. Une notification a été envoyée à l\'utilisateur avec la raison du rejet.');
    }
}
