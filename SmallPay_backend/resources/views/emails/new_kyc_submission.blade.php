<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle demande de vÃ©rification d'identitÃ©</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:24px;">
    <div style="max-width:640px; margin:0 auto; background:#ffffff; border-radius:12px; padding:24px; border-left:4px solid #3b82f6;">
        <h2 style="margin:0 0 12px; color:#3b82f6;">Nouvelle demande de vÃ©rification</h2>
        <p style="margin:0 0 16px; color:#374151;">Bonjour {{ $admin->name ?? 'Administrateur' }},</p>
        
        <p style="margin:0 0 16px; color:#374151;">
            <strong>Une nouvelle demande de vÃ©rification d'identitÃ© (KYC) a Ã©tÃ© soumise et requiert votre attention.</strong>
        </p>

        <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; padding:16px; margin:16px 0;">
            <h4 style="margin:0 0 12px; color:#1e40af;">Informations de l'utilisateur:</h4>
            <p style="margin:0 0 8px; color:#1e3a8a;"><strong>Nom:</strong> {{ $user->name }}</p>
            <p style="margin:0 0 8px; color:#1e3a8a;"><strong>Email:</strong> {{ $user->email }}</p>
            <p style="margin:0 0 8px; color:#1e3a8a;"><strong>TÃ©lÃ©phone:</strong> {{ $user->phone }}</p>
            
            <hr style="margin:12px 0; border:none; border-top:1px solid #93c5fd;">
            
            <h4 style="margin:12px 0 8px; color:#1e40af;">Informations KYC:</h4>
            <p style="margin:0 0 8px; color:#1e3a8a;"><strong>Nom complet:</strong> {{ data_get($kyc->data, 'client.fullName', '-') }}</p>
            <p style="margin:0 0 8px; color:#1e3a8a;"><strong>Numero d'identite:</strong> {{ data_get($kyc->data, 'client.idNumber', '-') }}</p>
            <p style="margin:0 0 8px; color:#1e3a8a;"><strong>Email client:</strong> {{ data_get($kyc->data, 'client.email', '-') }}</p>
            <p style="margin:0 0 8px; color:#1e3a8a;"><strong>Telephone client:</strong> {{ data_get($kyc->data, 'client.phoneNumber', '-') }}</p>
            <p style="margin:0 0 8px; color:#1e3a8a;"><strong>Adresse:</strong> {{ data_get($kyc->data, 'client.address', '-') }}</p>
            <p style="margin:0 0 8px; color:#1e3a8a;"><strong>Garant:</strong> {{ data_get($kyc->data, 'guarantor.name', '-') }} ({{ data_get($kyc->data, 'guarantor.phoneNumber', '-') }})</p>
            <p style="margin:0; color:#1e3a8a;"><strong>Soumise le:</strong> {{ $kyc->created_at->format('d/m/Y Ã  H:i') }}</p>
        </div>

        <p style="margin:16px 0; color:#374151; font-weight:bold;">
            âš ï¸ ACTION REQUISE
        </p>

        <p style="margin:0 0 16px; color:#374151;">
            Veuillez examiner attentivement les documents fournis et prendre une dÃ©cision (approuver ou rejetter) 
            dans les meilleurs dÃ©lais. L'utilisateur attend sa validation avant de pouvoir procÃ©der Ã  ses achats.
        </p>

        <div style="background:#fef3c7; border:1px solid #fcd34d; border-radius:10px; padding:12px; margin:16px 0;">
            <p style="margin:0; color:#78350f; font-size:13px;">
                <strong>Checklist:</strong><br>
                âœ“ Documents d'identitÃ© clairs et lisibles<br>
                âœ“ Informations cohÃ©rentes<br>
                âœ“ Pas de tentative de fraude Ã©vidente
            </p>
        </div>

        <hr style="margin:24px 0; border:none; border-top:1px solid #e5e7eb;">
        
        <p style="margin:0; color:#6b7280; font-size:11px; text-align:center;">
            SmallPay Dashboard Admin â€“ SystÃ¨me de gestion KYC
        </p>
    </div>
</body>
</html>

