<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte bloqué</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:24px;">
    <div style="max-width:640px; margin:0 auto; background:#ffffff; border-radius:12px; padding:24px;">
        <h2 style="margin:0 0 12px;">Bonjour {{ $user->name ?? 'client' }},</h2>
        <p style="margin:0 0 12px; color:#374151;">Votre compte a été bloqué suite à des retards de paiement répétés. Merci de contacter le support.</p>
        <p style="margin:0; color:#6b7280; font-size:12px;">SmallPay – Support.</p>
    </div>
</body>
</html>
