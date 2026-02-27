<?php

namespace App\Services;

use App\Models\KYC;
use App\Models\User;
use App\Notifications\NewKYCSubmissionNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KYCService
{
    /**
     * Soumettre ou mettre à jour un KYC
     * 
     * @param User $user
     * @param Request $request (with hierarchical data structure)
     * @return KYC
     */
    public function submitKYC(User $user, Request $request): KYC
    {
        // Extraire les données structurées de la requête
        $clientData = $request->input('client', []);
        $guarantorData = $request->input('guarantor', []);

        // Traiter les fichiers
        $filePaths = $this->processFiles($request);

        // Vérifier si un KYC existe déjà
        $kyc = $user->kyc()->first();

        if ($kyc) {
            return $this->updateKYC($kyc, $clientData, $guarantorData, $filePaths);
        }

        return $this->createKYC($user, $clientData, $guarantorData, $filePaths);
    }

    /**
     * Créer un nouveau KYC
     */
    private function createKYC(User $user, array $clientData, array $guarantorData, array $filePaths): KYC
    {
        // Construire la structure de données hiérarchique
        $data = [
            'client' => [
                'fullName' => $clientData['fullName'] ?? null,
                'phoneNumber' => $clientData['phoneNumber'] ?? null,
                'idNumber' => $clientData['idNumber'] ?? null,
                'address' => $clientData['address'] ?? null,
                'email' => $clientData['email'] ?? null,
                'documents' => [
                    'idFront' => $filePaths['id_front_path'] ?? null,
                    'idBack' => $filePaths['id_back_path'] ?? null,
                    'photo' => $filePaths['client_photo_path'] ?? null,
                ]
            ],
            'signedDocument' => $filePaths['signed_document_path'] ?? null,
            'guarantor' => [
                'name' => $guarantorData['name'] ?? null,
                'phoneNumber' => $guarantorData['phoneNumber'] ?? null,
                'documents' => [
                    'idFront' => $filePaths['guarantor_id_front_path'] ?? null,
                    'idBack' => $filePaths['guarantor_id_back_path'] ?? null,
                ]
            ]
        ];

        $kyc = KYC::create([
            'user_id' => $user->id,
            'data' => $data,
            'client_id_number' => $clientData['idNumber'] ?? null,
            'client_phone' => $clientData['phoneNumber'] ?? null,
            'guarantor_name' => $guarantorData['name'] ?? null,
            'guarantor_phone' => $guarantorData['phoneNumber'] ?? null,
            'id_front_path' => $filePaths['id_front_path'] ?? null,
            'id_back_path' => $filePaths['id_back_path'] ?? null,
            'client_photo_path' => $filePaths['client_photo_path'] ?? null,
            'signed_document_path' => $filePaths['signed_document_path'] ?? null,
            'guarantor_id_front_path' => $filePaths['guarantor_id_front_path'] ?? null,
            'guarantor_id_back_path' => $filePaths['guarantor_id_back_path'] ?? null,
            'status' => 'pending',
        ]);

        // Notifier les admins
        $this->notifyAdmins($kyc);

        return $kyc;
    }

    /**
     * Mettre à jour un KYC existant
     */
    private function updateKYC(KYC $kyc, array $clientData, array $guarantorData, array $filePaths): KYC
    {
        // Supprimer les anciens fichiers s'ils sont remplacés
        $this->deleteOldFiles($kyc, $filePaths);

        // Construire la structure de données hiérarchique (fusionner avec les données existantes)
        $existingData = $kyc->data ?? [];
        
        $data = [
            'client' => [
                'fullName' => $clientData['fullName'] ?? $existingData['client']['fullName'] ?? null,
                'phoneNumber' => $clientData['phoneNumber'] ?? $existingData['client']['phoneNumber'] ?? null,
                'idNumber' => $clientData['idNumber'] ?? $existingData['client']['idNumber'] ?? null,
                'address' => $clientData['address'] ?? $existingData['client']['address'] ?? null,
                'email' => $clientData['email'] ?? $existingData['client']['email'] ?? null,
                'documents' => [
                    'idFront' => $filePaths['id_front_path'] ?? $existingData['client']['documents']['idFront'] ?? null,
                    'idBack' => $filePaths['id_back_path'] ?? $existingData['client']['documents']['idBack'] ?? null,
                    'photo' => $filePaths['client_photo_path'] ?? $existingData['client']['documents']['photo'] ?? null,
                ]
            ],
            'signedDocument' => $filePaths['signed_document_path'] ?? $existingData['signedDocument'] ?? null,
            'guarantor' => [
                'name' => $guarantorData['name'] ?? $existingData['guarantor']['name'] ?? null,
                'phoneNumber' => $guarantorData['phoneNumber'] ?? $existingData['guarantor']['phoneNumber'] ?? null,
                'documents' => [
                    'idFront' => $filePaths['guarantor_id_front_path'] ?? $existingData['guarantor']['documents']['idFront'] ?? null,
                    'idBack' => $filePaths['guarantor_id_back_path'] ?? $existingData['guarantor']['documents']['idBack'] ?? null,
                ]
            ]
        ];

        $kyc->update([
            'data' => $data,
            'client_id_number' => $clientData['idNumber'] ?? $kyc->client_id_number,
            'client_phone' => $clientData['phoneNumber'] ?? $kyc->client_phone,
            'guarantor_name' => $guarantorData['name'] ?? $kyc->guarantor_name,
            'guarantor_phone' => $guarantorData['phoneNumber'] ?? $kyc->guarantor_phone,
            'id_front_path' => $filePaths['id_front_path'] ?? $kyc->id_front_path,
            'id_back_path' => $filePaths['id_back_path'] ?? $kyc->id_back_path,
            'client_photo_path' => $filePaths['client_photo_path'] ?? $kyc->client_photo_path,
            'signed_document_path' => $filePaths['signed_document_path'] ?? $kyc->signed_document_path,
            'guarantor_id_front_path' => $filePaths['guarantor_id_front_path'] ?? $kyc->guarantor_id_front_path,
            'guarantor_id_back_path' => $filePaths['guarantor_id_back_path'] ?? $kyc->guarantor_id_back_path,
            'status' => 'under_review',
        ]);

        // Notifier les admins
        $this->notifyAdmins($kyc);

        return $kyc;
    }

    /**
     * Traiter les fichiers uploadés
     */
    private function processFiles(Request $request): array
    {
        $paths = [];

        // Client Documents
        if ($request->hasFile('client.documents.idFront')) {
            $paths['id_front_path'] = $request->file('client.documents.idFront')
                ->store('kyc/client/id_documents', 'public');
        }

        if ($request->hasFile('client.documents.idBack')) {
            $paths['id_back_path'] = $request->file('client.documents.idBack')
                ->store('kyc/client/id_documents', 'public');
        }

        if ($request->hasFile('client.documents.photo')) {
            $paths['client_photo_path'] = $request->file('client.documents.photo')
                ->store('kyc/client/photos', 'public');
        }

        // Signed Document
        if ($request->hasFile('signedDocument')) {
            $paths['signed_document_path'] = $request->file('signedDocument')
                ->store('kyc/documents', 'public');
        }

        // Guarantor Documents
        if ($request->hasFile('guarantor.documents.idFront')) {
            $paths['guarantor_id_front_path'] = $request->file('guarantor.documents.idFront')
                ->store('kyc/guarantor/id_documents', 'public');
        }

        if ($request->hasFile('guarantor.documents.idBack')) {
            $paths['guarantor_id_back_path'] = $request->file('guarantor.documents.idBack')
                ->store('kyc/guarantor/id_documents', 'public');
        }

        return $paths;
    }

    /**
     * Supprimer les anciens fichiers
     */
    private function deleteOldFiles(KYC $kyc, array $newPaths): void
    {
        $fieldMappings = [
            'id_front_path' => 'id_front_path',
            'id_back_path' => 'id_back_path',
            'client_photo_path' => 'client_photo_path',
            'signed_document_path' => 'signed_document_path',
            'guarantor_id_front_path' => 'guarantor_id_front_path',
            'guarantor_id_back_path' => 'guarantor_id_back_path',
        ];

        foreach ($fieldMappings as $key => $field) {
            if (isset($newPaths[$key]) && $kyc->$field) {
                Storage::disk('public')->delete($kyc->$field);
            }
        }
    }

    /**
     * Notifier les admins d'une nouvelle soumission
     */
    private function notifyAdmins(KYC $kyc): void
    {
        $superAdmins = User::where('role', 'super_admin')->get();
        foreach ($superAdmins as $admin) {
            $admin->notify(new NewKYCSubmissionNotification($kyc));
        }
    }

    /**
     * Obtenir le statut KYC d'un utilisateur
     */
    public function getUserKYCStatus(User $user): array
    {
        $kyc = $user->kyc()->first();

        return [
            'has_kyc' => $kyc !== null,
            'kyc' => $kyc ? [
                'id' => $kyc->id,
                'status' => $kyc->status,
                'created_at' => $kyc->created_at,
                'approved_at' => $kyc->approved_at,
                'rejection_reason' => $kyc->rejection_reason,
            ] : null,
        ];
    }

    /**
     * Obtenir les détails complets d'un KYC
     */
    public function getKYCDetails(KYC $kyc): array
    {
        return [
            'id' => $kyc->id,
            'user' => [
                'id' => $kyc->user->id,
                'name' => $kyc->user->name,
                'email' => $kyc->user->email,
                'phone' => $kyc->user->phone,
            ],
            'client' => $this->formatClientData($kyc),
            'guarantor' => $this->formatGuarantorData($kyc),
            'signedDocument' => $kyc->signed_document_path ? asset('storage/' . $kyc->signed_document_path) : null,
            'status' => $kyc->status,
            'rejection_reason' => $kyc->rejection_reason,
            'approved_by' => $kyc->approvedBy ? [
                'id' => $kyc->approvedBy->id,
                'name' => $kyc->approvedBy->name,
            ] : null,
            'approved_at' => $kyc->approved_at,
            'created_at' => $kyc->created_at,
        ];
    }

    /**
     * Formater les données client pour la réponse
     */
    private function formatClientData(KYC $kyc): array
    {
        $client = $kyc->data['client'] ?? [];
        
        return [
            'fullName' => $client['fullName'] ?? null,
            'phoneNumber' => $client['phoneNumber'] ?? null,
            'idNumber' => $client['idNumber'] ?? null,
            'address' => $client['address'] ?? null,
            'email' => $client['email'] ?? null,
            'documents' => [
                'idFront' => $kyc->id_front_path ? asset('storage/' . $kyc->id_front_path) : null,
                'idBack' => $kyc->id_back_path ? asset('storage/' . $kyc->id_back_path) : null,
                'photo' => $kyc->client_photo_path ? asset('storage/' . $kyc->client_photo_path) : null,
            ]
        ];
    }

    /**
     * Formater les données garant pour la réponse
     */
    private function formatGuarantorData(KYC $kyc): array
    {
        $guarantor = $kyc->data['guarantor'] ?? [];
        
        return [
            'name' => $guarantor['name'] ?? null,
            'phoneNumber' => $guarantor['phoneNumber'] ?? null,
            'documents' => [
                'idFront' => $kyc->guarantor_id_front_path ? asset('storage/' . $kyc->guarantor_id_front_path) : null,
                'idBack' => $kyc->guarantor_id_back_path ? asset('storage/' . $kyc->guarantor_id_back_path) : null,
            ]
        ];
    }
}
