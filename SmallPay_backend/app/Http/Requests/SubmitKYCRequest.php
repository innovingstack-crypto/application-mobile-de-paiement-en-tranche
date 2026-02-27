<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SubmitKYCRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Préparer les données pour la validation
     */
    protected function prepareForValidation()
    {
        // Log pour déboguer
        Log::debug('KYC Request Data:', [
            'method' => $this->method(),
            'has_client_documents' => $this->hasFile('client.documents.idFront'),
            'all_files' => array_keys($this->files->all()),
            'all_inputs' => array_keys($this->input()),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     * 
     * Accepts hierarchical structure:
     * {
     *   "client": {
     *     "fullName": string,
     *     "phoneNumber": string,
     *     "idNumber": string,
     *     "address": string,
     *     "email": string (optional),
     *     "documents": {
     *       "photo": file,
     *       "idFront": file,
     *       "idBack": file
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
    public function rules(): array
    {
        $userKyc = Auth::user()->kyc()->first();
        $uniqueIdNumber = $userKyc 
            ? "required|string|max:50|unique:kycs,client_id_number,{$userKyc->id}"
            : "required|string|max:50|unique:kycs,client_id_number";

        return [
            // ===== CLIENT INFO =====
            'client.fullName' => 'required|string|max:255',
            'client.phoneNumber' => 'required|string|max:20',
            'client.idNumber' => $uniqueIdNumber,
            'client.address' => 'required|string|max:255',
            'client.email' => 'nullable|email|max:255',
            
            // ===== CLIENT DOCUMENTS =====
            'client.documents.idFront' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'client.documents.idBack' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'client.documents.photo' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            
            // ===== SIGNED DOCUMENT =====
            'signedDocument' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            
            // ===== GUARANTOR INFO =====
            'guarantor.name' => 'required|string|max:255',
            'guarantor.phoneNumber' => 'required|string|max:20',
            
            // ===== GUARANTOR DOCUMENTS =====
            'guarantor.documents.idFront' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'guarantor.documents.idBack' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'client.fullName.required' => 'Le nom complet du client est requis',
            'client.fullName.string' => 'Le nom complet doit être un texte',
            'client.fullName.max' => 'Le nom complet ne peut pas dépasser 255 caractères',
            
            'client.phoneNumber.required' => 'Le numéro de téléphone du client est requis',
            'client.phoneNumber.string' => 'Le numéro de téléphone doit être un texte',
            'client.phoneNumber.max' => 'Le numéro de téléphone ne peut pas dépasser 20 caractères',
            
            'client.idNumber.required' => 'Le numéro de pièce d\'identité est requis',
            'client.idNumber.unique' => 'Ce numéro de pièce d\'identité est déjà utilisé',
            'client.idNumber.max' => 'Le numéro ne peut pas dépasser 50 caractères',
            
            'client.address.required' => 'L\'adresse complète est requise',
            'client.address.string' => 'L\'adresse doit être un texte',
            'client.address.max' => 'L\'adresse ne peut pas dépasser 255 caractères',
            
            'client.email.email' => 'L\'adresse e-mail n\'est pas valide',
            'client.email.max' => 'L\'adresse e-mail ne peut pas dépasser 255 caractères',
            
            // Client Documents
            'client.documents.idFront.required' => 'L\'image recto de la pièce d\'identité est requise',
            'client.documents.idFront.file' => 'Le fichier recto n\'est pas valide',
            'client.documents.idFront.mimes' => 'Le recto doit être en format jpg, jpeg ou png',
            'client.documents.idFront.max' => 'Le fichier recto ne peut pas dépasser 5 MB',
            
            'client.documents.idBack.required' => 'L\'image verso de la pièce d\'identité est requise',
            'client.documents.idBack.file' => 'Le fichier verso n\'est pas valide',
            'client.documents.idBack.mimes' => 'Le verso doit être en format jpg, jpeg ou png',
            'client.documents.idBack.max' => 'Le fichier verso ne peut pas dépasser 5 MB',
            
            'client.documents.photo.required' => 'La photo du client est requise',
            'client.documents.photo.file' => 'Le fichier photo n\'est pas valide',
            'client.documents.photo.mimes' => 'La photo doit être en format jpg, jpeg ou png',
            'client.documents.photo.max' => 'Le fichier photo ne peut pas dépasser 5 MB',
            
            // Signed Document
            'signedDocument.required' => 'Le document signé est requis',
            'signedDocument.file' => 'Le fichier document n\'est pas valide',
            'signedDocument.mimes' => 'Le document doit être en format pdf, doc ou docx',
            'signedDocument.max' => 'Le fichier document ne peut pas dépasser 5 MB',
            
            // Guarantor Info
            'guarantor.name.required' => 'Le nom du garant est requis',
            'guarantor.name.string' => 'Le nom du garant doit être un texte',
            'guarantor.name.max' => 'Le nom du garant ne peut pas dépasser 255 caractères',
            
            'guarantor.phoneNumber.required' => 'Le numéro de téléphone du garant est requis',
            'guarantor.phoneNumber.string' => 'Le numéro de téléphone doit être un texte',
            'guarantor.phoneNumber.max' => 'Le numéro de téléphone ne peut pas dépasser 20 caractères',
            
            // Guarantor Documents
            'guarantor.documents.idFront.required' => 'L\'image recto de la pièce d\'identité du garant est requise',
            'guarantor.documents.idFront.file' => 'Le fichier recto du garant n\'est pas valide',
            'guarantor.documents.idFront.mimes' => 'Le recto du garant doit être en format jpg, jpeg ou png',
            'guarantor.documents.idFront.max' => 'Le fichier recto du garant ne peut pas dépasser 5 MB',
            
            'guarantor.documents.idBack.required' => 'L\'image verso de la pièce d\'identité du garant est requise',
            'guarantor.documents.idBack.file' => 'Le fichier verso du garant n\'est pas valide',
            'guarantor.documents.idBack.mimes' => 'Le verso du garant doit être en format jpg, jpeg ou png',
            'guarantor.documents.idBack.max' => 'Le fichier verso du garant ne peut pas dépasser 5 MB',
        ];
    }
}
