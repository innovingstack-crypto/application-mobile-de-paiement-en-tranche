<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rappel d'Échéance</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:24px;">
    <div style="max-width:640px; margin:0 auto; background:#ffffff; border-radius:12px; padding:24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color:#ffffff; border-radius:8px; padding:20px; text-align:center; margin-bottom:24px;">
            <h1 style="margin:0; font-size:24px;">SmallPay</h1>
            <p style="margin:8px 0 0 0; font-size:14px; opacity:0.9;">Votre Plateforme de Paiement</p>
        </div>

        <h2 style="color:#111827; margin:0 0 16px 0; font-size:20px;">⏰ Rappel d'Échéance</h2>
        
        <p style="color:#4b5563; margin:0 0 16px 0; line-height:1.6;">
            Bonjour <strong>{{ $user->name }}</strong>,
        </p>

        <p style="color:#4b5563; margin:0 0 16px 0; line-height:1.6;">
            Ceci est un rappel concernant un versement à effectuer dans <strong>{{ $daysRemaining }} jours</strong>.
        </p>

        <!-- Détails de l'échéance -->
        <div style="background:#fef3c7; border-left:4px solid #f59e0b; padding:16px; margin:16px 0; border-radius:4px;">
            <p style="margin:0 0 12px 0; color:#111827;">
                <strong>Détails de l'échéance:</strong>
            </p>
            <table style="width:100%; color:#374151; font-size:14px;">
                <tr style="border-bottom:1px solid #fde68a;">
                    <td style="padding:8px 0;"><strong>Versement #</strong></td>
                    <td style="text-align:right; padding:8px 0;">{{ $schedule->installment_number }}</td>
                </tr>
                <tr style="border-bottom:1px solid #fde68a;">
                    <td style="padding:8px 0;"><strong>Montant dû:</strong></td>
                    <td style="text-align:right; padding:8px 0;"><strong>{{ number_format($schedule->amount, 0, ',', ' ') }} FCFA</strong></td>
                </tr>
                <tr style="border-bottom:1px solid #fde68a;">
                    <td style="padding:8px 0;"><strong>Date limite:</strong></td>
                    <td style="text-align:right; padding:8px 0;">{{ $schedule->due_date->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0;"><strong>Commande:</strong></td>
                    <td style="text-align:right; padding:8px 0;">#{{ $order->id }}</td>
                </tr>
            </table>
        </div>

        <!-- Moyens de paiement -->
        <p style="color:#4b5563; margin:16px 0 8px 0; line-height:1.6;">
            <strong>Vous pouvez effectuer le paiement via:</strong>
        </p>

        <ul style="color:#4b5563; margin:0 0 16px 0; padding-left:20px;">
            <li style="margin:6px 0;">Mobile Money MTN</li>
            <li style="margin:6px 0;">Mobile Money Orange</li>
            <li style="margin:6px 0;">Carte bancaire</li>
        </ul>

        <!-- Important -->
        <div style="background:#fee2e2; border:1px solid #fca5a5; border-radius:8px; padding:12px; margin:16px 0;">
            <p style="margin:0; color:#991b1b; font-size:13px;">
                ⚠️ <strong>Important:</strong> Veuillez effectuer le paiement avant la date limite pour éviter des frais additionnels ou un impact sur votre dossier.
            </p>
        </div>

        <!-- Besoin d'aide -->
        <div style="background:#f3f4f6; border-radius:8px; padding:12px; margin:16px 0;">
            <p style="margin:0; color:#374151; font-size:13px;">
                📞 <strong>Avez-vous des questions?</strong> Contactez-nous à contact@godloveshop.cm
            </p>
        </div>

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
