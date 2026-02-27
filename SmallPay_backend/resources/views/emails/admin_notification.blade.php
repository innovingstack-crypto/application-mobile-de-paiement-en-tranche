<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Administrateur</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:24px;">
    <div style="max-width:800px; margin:0 auto; background:#ffffff; border-radius:12px; padding:24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color:#ffffff; border-radius:8px; padding:20px; text-align:center; margin-bottom:24px;">
            <h1 style="margin:0; font-size:24px;">SmallPay</h1>
            <p style="margin:8px 0 0 0; font-size:14px; opacity:0.9;">Notification Administrateur</p>
        </div>

        @if($type === 'kyc_submitted')
            <h2 style="color:#111827; margin:0 0 16px 0; font-size:18px;">📋 Nouveau KYC Soumis</h2>
            
            <div style="background:#f0f4ff; border-left:4px solid #667eea; padding:16px; margin:16px 0; border-radius:4px;">
                <table style="width:100%; color:#374151; font-size:14px;">
                    <tr style="border-bottom:1px solid #e0e7ff;">
                        <td style="padding:8px 0; width:40%;"><strong>Client:</strong></td>
                        <td style="padding:8px 0;">{{ $data['user_name'] }}</td>
                    </tr>
                    <tr style="border-bottom:1px solid #e0e7ff;">
                        <td style="padding:8px 0;"><strong>Email:</strong></td>
                        <td style="padding:8px 0;">{{ $data['user_email'] }}</td>
                    </tr>
                    <tr style="border-bottom:1px solid #e0e7ff;">
                        <td style="padding:8px 0;"><strong>Téléphone:</strong></td>
                        <td style="padding:8px 0;">{{ $data['user_phone'] }}</td>
                    </tr>
                    <tr style="border-bottom:1px solid #e0e7ff;">
                        <td style="padding:8px 0;"><strong>Statut:</strong></td>
                        <td style="padding:8px 0;">{{ ucfirst($data['status']) }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0;"><strong>Soumis le:</strong></td>
                        <td style="padding:8px 0;">{{ $data['submission_date'] }}</td>
                    </tr>
                </table>
            </div>

            <p style="color:#374151; margin:16px 0; font-size:13px;">
                ⚠️ <strong>Action requise:</strong> Veuillez réviser et approuver/rejeter ce KYC dans l'interface administrateur.
            </p>

        @elseif($type === 'first_payment')
            <h2 style="color:#111827; margin:0 0 16px 0; font-size:18px;">💰 Premier Versement Confirmé</h2>
            
            <div style="background:#f0fdf4; border-left:4px solid #16a34a; padding:16px; margin:16px 0; border-radius:4px;">
                <table style="width:100%; color:#374151; font-size:14px;">
                    <tr style="border-bottom:1px solid #dcfce7;">
                        <td style="padding:8px 0; width:40%;"><strong>Client:</strong></td>
                        <td style="padding:8px 0;">{{ $data['user_name'] }}</td>
                    </tr>
                    <tr style="border-bottom:1px solid #dcfce7;">
                        <td style="padding:8px 0;"><strong>Montant:</strong></td>
                        <td style="padding:8px 0; color:#16a34a; font-weight:bold;">{{ $data['payment_amount'] }} FCFA</td>
                    </tr>
                    <tr style="border-bottom:1px solid #dcfce7;">
                        <td style="padding:8px 0;"><strong>Méthode:</strong></td>
                        <td style="padding:8px 0;">{{ $data['payment_method'] }}</td>
                    </tr>
                    <tr style="border-bottom:1px solid #dcfce7;">
                        <td style="padding:8px 0;"><strong>Commande:</strong></td>
                        <td style="padding:8px 0;">#{{ $data['order_id'] }}</td>
                    </tr>
                    <tr style="border-bottom:1px solid #dcfce7;">
                        <td style="padding:8px 0;"><strong>Date:</strong></td>
                        <td style="padding:8px 0;">{{ $data['payment_date'] }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0;"><strong>Transaction ID:</strong></td>
                        <td style="padding:8px 0;">{{ $data['transaction_id'] }}</td>
                    </tr>
                </table>
            </div>

        @elseif($type === 'monthly_payment')
            <h2 style="color:#111827; margin:0 0 16px 0; font-size:18px;">📅 Versement Mensuel Confirmé</h2>
            
            <div style="background:#f0fdf4; border-left:4px solid #16a34a; padding:16px; margin:16px 0; border-radius:4px;">
                <table style="width:100%; color:#374151; font-size:14px;">
                    <tr style="border-bottom:1px solid #dcfce7;">
                        <td style="padding:8px 0; width:40%;"><strong>Client:</strong></td>
                        <td style="padding:8px 0;">{{ $data['user_name'] }}</td>
                    </tr>
                    <tr style="border-bottom:1px solid #dcfce7;">
                        <td style="padding:8px 0;"><strong>Montant:</strong></td>
                        <td style="padding:8px 0; color:#16a34a; font-weight:bold;">{{ $data['payment_amount'] }} FCFA</td>
                    </tr>
                    <tr style="border-bottom:1px solid #dcfce7;">
                        <td style="padding:8px 0;"><strong>Versement #</strong></td>
                        <td style="padding:8px 0;">{{ $data['installment_number'] }}</td>
                    </tr>
                    <tr style="border-bottom:1px solid #dcfce7;">
                        <td style="padding:8px 0;"><strong>Méthode:</strong></td>
                        <td style="padding:8px 0;">{{ $data['payment_method'] }}</td>
                    </tr>
                    <tr style="border-bottom:1px solid #dcfce7;">
                        <td style="padding:8px 0;"><strong>Commande:</strong></td>
                        <td style="padding:8px 0;">#{{ $data['order_id'] }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0;"><strong>Date:</strong></td>
                        <td style="padding:8px 0;">{{ $data['payment_date'] }}</td>
                    </tr>
                </table>
            </div>

        @elseif($type === 'payment_reminder')
            <h2 style="color:#111827; margin:0 0 16px 0; font-size:18px;">⏰ Rappel d'Échéance à Venir</h2>
            
            <div style="background:#fef3c7; border-left:4px solid #f59e0b; padding:16px; margin:16px 0; border-radius:4px;">
                <table style="width:100%; color:#374151; font-size:14px;">
                    <tr style="border-bottom:1px solid #fde68a;">
                        <td style="padding:8px 0; width:40%;"><strong>Client:</strong></td>
                        <td style="padding:8px 0;">{{ $data['user_name'] }}</td>
                    </tr>
                    <tr style="border-bottom:1px solid #fde68a;">
                        <td style="padding:8px 0;"><strong>Montant:</strong></td>
                        <td style="padding:8px 0;">{{ $data['payment_amount'] }} FCFA</td>
                    </tr>
                    <tr style="border-bottom:1px solid #fde68a;">
                        <td style="padding:8px 0;"><strong>Versement #</strong></td>
                        <td style="padding:8px 0;">{{ $data['installment_number'] }}</td>
                    </tr>
                    <tr style="border-bottom:1px solid #fde68a;">
                        <td style="padding:8px 0;"><strong>Date limite:</strong></td>
                        <td style="padding:8px 0;">{{ $data['due_date'] }}</td>
                    </tr>
                    <tr style="border-bottom:1px solid #fde68a;">
                        <td style="padding:8px 0;"><strong>Jours restants:</strong></td>
                        <td style="padding:8px 0; color:#f59e0b; font-weight:bold;">{{ $data['days_remaining'] }} jour(s)</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0;"><strong>Commande:</strong></td>
                        <td style="padding:8px 0;">#{{ $data['order_id'] }}</td>
                    </tr>
                </table>
            </div>

        @elseif($type === 'payment_overdue')
            <h2 style="color:#111827; margin:0 0 16px 0; font-size:18px;">⚠️ Échéance Dépassée - Urgent</h2>
            
            <div style="background:#fee2e2; border-left:4px solid #dc2626; padding:16px; margin:16px 0; border-radius:4px;">
                <table style="width:100%; color:#374151; font-size:14px;">
                    <tr style="border-bottom:1px solid #fecaca;">
                        <td style="padding:8px 0; width:40%;"><strong>Client:</strong></td>
                        <td style="padding:8px 0;">{{ $data['user_name'] }}</td>
                    </tr>
                    <tr style="border-bottom:1px solid #fecaca;">
                        <td style="padding:8px 0;"><strong>Montant:</strong></td>
                        <td style="padding:8px 0; color:#dc2626; font-weight:bold;">{{ $data['payment_amount'] }} FCFA</td>
                    </tr>
                    <tr style="border-bottom:1px solid #fecaca;">
                        <td style="padding:8px 0;"><strong>Versement #</strong></td>
                        <td style="padding:8px 0;">{{ $data['installment_number'] }}</td>
                    </tr>
                    <tr style="border-bottom:1px solid #fecaca;">
                        <td style="padding:8px 0;"><strong>Date limite:</strong></td>
                        <td style="padding:8px 0;">{{ $data['due_date'] }}</td>
                    </tr>
                    <tr style="border-bottom:1px solid #fecaca;">
                        <td style="padding:8px 0;"><strong>Jours de retard:</strong></td>
                        <td style="padding:8px 0; color:#dc2626; font-weight:bold;">{{ $data['days_overdue'] }} jour(s)</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0;"><strong>Commande:</strong></td>
                        <td style="padding:8px 0;">#{{ $data['order_id'] }}</td>
                    </tr>
                </table>
            </div>

            <p style="color:#374151; margin:16px 0; font-size:13px;">
                🚨 <strong>Action requise:</strong> Contactez le client immédiatement pour régulariser la situation.
            </p>
        @endif

        <!-- Footer -->
        <div style="border-top:1px solid #e5e7eb; margin-top:24px; padding-top:16px; color:#6b7280; font-size:12px; text-align:center;">
            <p style="margin:0 0 8px 0;">
                SmallPay - {{ date('Y') }}
            </p>
            <p style="margin:0; color:#9ca3af;">
                Ceci est une notification automatique. Veuillez consulter votre tableau de bord administrateur pour plus de détails.
            </p>
        </div>
    </div>
</body>
</html>
