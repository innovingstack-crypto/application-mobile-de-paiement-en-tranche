<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commande annulée</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:24px;">
    <div style="max-width:640px; margin:0 auto; background:#ffffff; border-radius:12px; padding:24px;">
        <h2 style="margin:0 0 12px;">Bonjour {{ $user->name ?? 'client' }},</h2>
        <p style="margin:0 0 12px; color:#374151;">Votre commande <strong>{{ $order->order_number ?? ('#'.$order->id) }}</strong> a été annulée.</p>
        <p style="margin:0; color:#6b7280; font-size:12px;">SmallPay – Si vous avez des questions, contactez le support.</p>
    </div>
</body>
</html>
