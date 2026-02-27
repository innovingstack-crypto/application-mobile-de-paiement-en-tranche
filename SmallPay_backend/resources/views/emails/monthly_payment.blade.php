<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Versement Mensuel Confirmé</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:24px;">
    <div style="max-width:640px; margin:0 auto; background:#ffffff; border-radius:12px; padding:24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color:#ffffff; border-radius:8px; padding:20px; text-align:center; margin-bottom:24px;">
            <h1 style="margin:0; font-size:24px;">SmallPay</h1>
            <p style="margin:8px 0 0 0; font-size:14px; opacity:0.9;">Votre Plateforme de Paiement</p>
        </div>

        <h2 style="color:#111827; margin:0 0 16px 0; font-size:20px;">✓ Versement Mensuel Confirmé</h2>
        
        <p style="color:#4b5563; margin:0 0 16px 0; line-height:1.6;">
            Bonjour <strong>{{ $user->name }}</strong>,
        </p>

        <p style="color:#4b5563; margin:0 0 16px 0; line-height:1.6;">
            Merci pour votre versement mensuel! Nous confirmons la réception de votre paiement.
        </p>

        <!-- Détails du paiement -->
        <div style="background:#f0f9ff; border-left:4px solid #667eea; padding:16px; margin:16px 0; border-radius:4px;">
            <p style="margin:0 0 12px 0; color:#111827;">
                <strong>Détails du versement:</strong>
            </p>
            <table style="width:100%; color:#374151; font-size:14px;">
                <tr style="border-bottom:1px solid #e0e7ff;">
                    <td style="padding:8px 0;"><strong>Montant:</strong></td>
                    <td style="text-align:right; padding:8px 0;">{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</td>
                </tr>
                <tr style="border-bottom:1px solid #e0e7ff;">
                    <td style="padding:8px 0;"><strong>Méthode:</strong></td>
                    <td style="text-align:right; padding:8px 0;">{{ $payment->getMethodLabel() }}</td>
                </tr>
                <tr style="border-bottom:1px solid #e0e7ff;">
                    <td style="padding:8px 0;"><strong>Date:</strong></td>
                    <td style="text-align:right; padding:8px 0;">{{ $payment->payment_date->format('d/m/Y') }}</td>
                </tr>
                @if($payment->schedule)
                <tr style="border-bottom:1px solid #e0e7ff;">
                    <td style="padding:8px 0;"><strong>Versement #</strong></td>
                    <td style="text-align:right; padding:8px 0;">{{ $payment->schedule->installment_number }}</td>
                </tr>
                @endif
                <tr>
                    <td style="padding:8px 0;"><strong>Commande:</strong></td>
                    <td style="text-align:right; padding:8px 0;">#{{ $order->id }}</td>
                </tr>
            </table>
        </div>

        <!-- Statut -->
        <div style="background:#f0fdf4; border:1px solid #86efac; border-radius:8px; padding:12px; margin:16px 0; text-align:center;">
            <p style="margin:0; color:#166534; font-weight:bold;">
                ✓ Paiement Reçu et Confirmé
            </p>
        </div>

        <!-- Prochaines étapes -->
        <p style="color:#4b5563; margin:16px 0 8px 0; line-height:1.6;">
            <strong>Vos prochains versements:</strong>
        </p>

        <div style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:12px; margin:0 0 16px 0;">
            <p style="margin:0; color:#374151; font-size:13px;">
                Continuez à effectuer vos versements conformément au calendrier de remboursement. Vous recevrez des rappels automatiques avant chaque échéance.
            </p>
        </div>

        <!-- Conseil -->
        <div style="background:#f3f4f6; border-radius:8px; padding:12px; margin:16px 0;">
            <p style="margin:0; color:#374151; font-size:13px;">
                💡 <strong>Conseil:</strong> Mettez en place un rappel pour vos versements mensuels afin de ne jamais oublier une date limite.
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
