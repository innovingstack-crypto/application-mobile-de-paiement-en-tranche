<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification d'identité rejetée</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:24px;">
    <div style="max-width:640px; margin:0 auto; background:#ffffff; border-radius:12px; padding:24px; border-left:4px solid #ef4444;">
        <h2 style="margin:0 0 12px; color:#ef4444;">Vérification rejetée</h2>
        <p style="margin:0 0 16px; color:#374151;">Bonjour {{ $user->name ?? 'client' }},</p>
        
        <p style="margin:0 0 16px; color:#374151;">
            Nous regrettons de vous informer que votre vérification d'identité (KYC) a été <strong>rejetée</strong>.
        </p>

        <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:10px; padding:16px; margin:16px 0;">
            <p style="margin:0 0 12px; color:#7f1d1d;"><strong>⚠️ Raison du rejet:</strong></p>
            <p style="margin:0; color:#7f1d1d; line-height:1.6;">{{ $kyc->rejection_reason }}</p>
        </div>

        <p style="margin:16px 0; color:#374151;">
            <strong>Que faire maintenant?</strong>
        </p>
        
        <ul style="margin:0 0 16px; color:#374151; padding-left:20px;">
            <li style="margin:8px 0;">Vérifiez que toutes les informations sont exactes et complètes</li>
            <li style="margin:8px 0;">Assurez-vous que vos documents sont lisibles et valides</li>
            <li style="margin:8px 0;">Soumettez à nouveau votre demande avec les corrections</li>
        </ul>

        <p style="margin:16px 0; color:#6b7280; font-size:13px;">
            Vous pouvez soumettre une nouvelle demande de vérification dès que vous avez apporté les corrections nécessaires.
        </p>

        <hr style="margin:24px 0; border:none; border-top:1px solid #e5e7eb;">
        
        <p style="margin:0; color:#6b7280; font-size:12px;">
            Pour plus d'assistance, veuillez contacter notre équipe support à <strong>support@smallpay.com</strong>
        </p>

        <p style="margin:12px 0 0; color:#9ca3af; font-size:11px; text-align:center;">
            SmallPay – Facilitons vos paiements
        </p>
    </div>
</body>
</html>
