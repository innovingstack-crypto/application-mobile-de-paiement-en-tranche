<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement échoué</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:24px;">
    <div style="max-width:640px; margin:0 auto; background:#ffffff; border-radius:12px; padding:24px;">
        <h2 style="margin:0 0 12px;">Bonjour {{ $user->name ?? 'client' }},</h2>
        <p style="margin:0 0 12px; color:#374151;">Votre paiement n'a pas pu être traité. Vous pouvez réessayer.</p>

        <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:10px; padding:16px; margin:16px 0;">
            <p style="margin:0 0 8px;"><strong>Commande :</strong> {{ $order->order_number ?? ('#'.$order->id) }}</p>
            <p style="margin:0 0 8px;"><strong>Montant :</strong> {{ number_format(($payment->amount ?? 0), 0, ',', ' ') }} FCFA</p>
            <p style="margin:0;"><strong>Statut :</strong> {{ ucfirst($payment->status ?? 'failed') }}</p>
        </div>

        <p style="margin:0; color:#6b7280; font-size:12px;">SmallPay – Si le problème persiste, contactez le support.</p>
    </div>
</body>
</html>
