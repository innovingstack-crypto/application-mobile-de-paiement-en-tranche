<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VÃ©rification d'identitÃ© approuvÃ©e</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:24px;">
    <div style="max-width:640px; margin:0 auto; background:#ffffff; border-radius:12px; padding:24px; border-left:4px solid #10b981;">
        <h2 style="margin:0 0 12px; color:#10b981;">FÃ©licitations!</h2>
        <p style="margin:0 0 16px; color:#374151;">Bonjour {{ $user->name ?? 'client' }},</p>
        
        <p style="margin:0 0 16px; color:#374151;">
            Votre vÃ©rification d'identitÃ© (KYC) a Ã©tÃ© <strong>approuvÃ©e avec succÃ¨s!</strong>
        </p>

        <div style="background:#ecfdf5; border:1px solid #a7f3d0; border-radius:10px; padding:16px; margin:16px 0;">
            <p style="margin:0 0 8px; color:#065f46;"><strong>âœ“ Statut de vÃ©rification:</strong> APPROUVÃ‰</p>
            <p style="margin:0 0 8px; color:#065f46;"><strong>Nom vÃ©rifiÃ©:</strong> {{ data_get($kyc->data, 'client.fullName', $user->name ?? '-') }}</p>
            <p style="margin:0 0 8px; color:#065f46;"><strong>Numero d'identite:</strong> {{ data_get($kyc->data, 'client.idNumber', '-') }}</p>
            <p style="margin:0; color:#065f46;"><strong>Date de vÃ©rification:</strong> {{ $kyc->approved_at->format('d/m/Y Ã  H:i') }}</p>
        </div>

        <p style="margin:16px 0; color:#374151;">
            Vous pouvez dÃ©sormais procÃ©der Ã  vos achats sur <strong>SmallPay</strong> sans restriction.
        </p>

        <p style="margin:0; color:#6b7280; font-size:12px;">
            Si vous avez des questions, n'hÃ©sitez pas Ã  nous contacter Ã  support@smallpay.com
        </p>

        <hr style="margin:24px 0; border:none; border-top:1px solid #e5e7eb;">
        
        <p style="margin:0; color:#9ca3af; font-size:11px; text-align:center;">
            SmallPay â€“ Facilitons vos paiements
        </p>
    </div>
</body>
</html>

