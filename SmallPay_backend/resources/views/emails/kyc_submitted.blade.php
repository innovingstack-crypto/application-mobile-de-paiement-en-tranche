<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KYC Soumis</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:24px;">
    <div style="max-width:640px; margin:0 auto; background:#ffffff; border-radius:12px; padding:24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color:#ffffff; border-radius:8px; padding:20px; text-align:center; margin-bottom:24px;">
            <h1 style="margin:0; font-size:24px;">SmallPay</h1>
            <p style="margin:8px 0 0 0; font-size:14px; opacity:0.9;">Votre Plateforme de Paiement</p>
        </div>

        <h2 style="color:#111827; margin:0 0 16px 0; font-size:20px;">Formulaire KYC Soumis</h2>
        
        <p style="color:#4b5563; margin:0 0 16px 0; line-height:1.6;">
            Bonjour <strong>{{ $user->name }}</strong>,
        </p>

        <p style="color:#4b5563; margin:0 0 16px 0; line-height:1.6;">
            Nous avons bien reçu votre formulaire KYC. Nous procédons actuellement à la vérification de vos documents.
        </p>

        <div style="background:#f0f4ff; border-left:4px solid #667eea; padding:16px; margin:16px 0; border-radius:4px;">
            <p style="margin:0 0 8px 0; color:#374151;">
                <strong>Statut:</strong> <span style="color:#667eea; font-weight:bold;">En Attente de Vérification</span>
            </p>
            <p style="margin:0; color:#374151;">
                <strong>Date de soumission:</strong> {{ $kyc->created_at->format('d/m/Y à H:i') }}
            </p>
        </div>

        <p style="color:#4b5563; margin:0 0 16px 0; line-height:1.6;">
            <strong>Prochaines étapes:</strong>
        </p>

        <ol style="color:#4b5563; margin:0 0 16px 0; padding-left:20px;">
            <li style="margin:8px 0;">Nos équipes vérifient vos documents</li>
            <li style="margin:8px 0;">Nous vous informerons du résultat par email</li>
            <li style="margin:8px 0;">Une fois approuvé, vous pourrez accéder à tous nos services</li>
        </ol>

        <div style="background:#fffbeb; border:1px solid #fcd34d; border-radius:8px; padding:12px; margin:16px 0;">
            <p style="margin:0; color:#92400e; font-size:13px;">
                ℹ️ Le traitement des demandes KYC prend généralement 24 à 48 heures. Vous recevrez une notification dès que votre dossier sera approuvé ou s'il y a besoin de documents supplémentaires.
            </p>
        </div>

        <p style="color:#4b5563; margin:16px 0 0 0; line-height:1.6;">
            Si vous avez des questions, n'hésitez pas à nous contacter.
        </p>

        <!-- Footer -->
        <div style="border-top:1px solid #e5e7eb; margin-top:24px; padding-top:16px; color:#6b7280; font-size:12px; text-align:center;">
            <p style="margin:0 0 8px 0;">
                SmallPay - {{ date('Y') }}
            </p>
            <p style="margin:0; color:#9ca3af;">
                contact@godloveshop.cm
            </p>
        </div>
    </div>
</body>
</html>
