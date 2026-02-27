# Exemples d'Intégration du Système d'Email

## 1. Contrôleur KYC - Soumission

### Dans `app/Http/Controllers/KYCController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Events\KYCSubmitted;
use App\Models\KYC;
use App\Models\User;
use Illuminate\Http\Request;

class KYCController extends Controller
{
    /**
     * Soumettre un formulaire KYC
     */
    public function submit(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'client_name' => 'required|string',
            'client_email' => 'required|email',
            // ... autres validations
        ]);

        try {
            // Créer le KYC
            $kyc = KYC::create([
                'user_id' => auth()->id(),
                'data' => $validated,
                'status' => 'pending',
            ]);

            // 📧 DÉCLENCHER L'ÉVÉNEMENT EMAIL
            $user = auth()->user();
            event(new KYCSubmitted($kyc, $user));

            return response()->json([
                'message' => 'KYC soumis avec succès',
                'kyc_id' => $kyc->id
            ], 201);
        } catch (\Exception $e) {
            \Log::error("Error submitting KYC: {$e->getMessage()}");
            return response()->json([
                'message' => 'Erreur lors de la soumission',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
```

---

## 2. Contrôleur Payment - Premier Versement

### Dans `app/Http/Controllers/PaymentController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Events\FirstPaymentProcessed;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentSchedule;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Traiter un paiement
     */
    public function processPayment(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:MTN,ORANGE,CARD',
            'transaction_id' => 'required|string',
        ]);

        try {
            $order = Order::find($validated['order_id']);
            $user = $order->user;

            // Créer le paiement
            $payment = Payment::create([
                'order_id' => $order->id,
                'schedule_id' => $validated['schedule_id'] ?? null,
                'amount' => $validated['amount'],
                'method' => $validated['method'],
                'transaction_id' => $validated['transaction_id'],
                'status' => 'completed',
                'payment_date' => now(),
            ]);

            // Marquer comme complété
            $payment->markAsCompleted();

            // 📧 VÉRIFIER SI C'EST LE PREMIER VERSEMENT
            $paymentsCount = $order->payments()->count();
            
            if ($paymentsCount === 1) {
                // C'est le premier versement!
                event(new FirstPaymentProcessed($payment, $user, $order));
            }

            return response()->json([
                'message' => 'Paiement traité avec succès',
                'payment_id' => $payment->id
            ], 200);
        } catch (\Exception $e) {
            \Log::error("Error processing payment: {$e->getMessage()}");
            return response()->json([
                'message' => 'Erreur lors du traitement du paiement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Traiter tous les paiements mensuels
     */
    public function processMonthlyPayments(Request $request)
    {
        $validated = $request->validate([
            'payments' => 'required|array',
            'payments.*.order_id' => 'required|exists:orders,id',
            'payments.*.schedule_id' => 'required|exists:payment_schedules,id',
            'payments.*.amount' => 'required|numeric|min:0.01',
            'payments.*.method' => 'required|in:MTN,ORANGE,CARD',
            'payments.*.transaction_id' => 'required|string',
        ]);

        $results = [];

        foreach ($validated['payments'] as $paymentData) {
            try {
                $order = Order::find($paymentData['order_id']);
                $schedule = PaymentSchedule::find($paymentData['schedule_id']);
                $user = $order->user;

                // Créer le paiement
                $payment = Payment::create([
                    'order_id' => $order->id,
                    'schedule_id' => $schedule->id,
                    'amount' => $paymentData['amount'],
                    'method' => $paymentData['method'],
                    'transaction_id' => $paymentData['transaction_id'],
                    'status' => 'completed',
                    'payment_date' => now(),
                ]);

                // Marquer comme complété
                $payment->markAsCompleted();

                // 📧 DÉCLENCHER L'ÉVÉNEMENT EMAIL
                event(new MonthlyPaymentProcessed($payment, $user, $order));

                $results[] = [
                    'order_id' => $order->id,
                    'success' => true,
                    'payment_id' => $payment->id
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'order_id' => $paymentData['order_id'],
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'message' => 'Traitement des paiements mensuels terminé',
            'results' => $results
        ], 200);
    }
}
```

---

## 3. Appel direct du Service Email

### Utilisation avancée:

```php
<?php

namespace App\Http\Controllers;

use App\Models\PaymentSchedule;
use App\Services\EmailService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Envoyer manuellement un rappel à un utilisateur
     */
    public function sendManualReminder(Request $request, EmailService $emailService)
    {
        $schedule = PaymentSchedule::find($request->schedule_id);

        if (!$schedule) {
            return response()->json(['message' => 'Échéance non trouvée'], 404);
        }

        try {
            // Envoyer le rappel manuellement
            $emailService->sendPaymentDueReminderEmails($schedule);

            return response()->json([
                'message' => 'Rappel envoyé avec succès'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de l\'envoi du rappel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Envoyer manuellement une notification de retard
     */
    public function sendManualOverdueNotification(Request $request, EmailService $emailService)
    {
        $schedule = PaymentSchedule::find($request->schedule_id);

        if (!$schedule) {
            return response()->json(['message' => 'Échéance non trouvée'], 404);
        }

        try {
            // Envoyer la notification de retard
            $emailService->sendPaymentOverdueEmails($schedule);

            return response()->json([
                'message' => 'Notification envoyée avec succès'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de l\'envoi de la notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
```

---

## 4. Routes API pour Tester

### Dans `routes/api.php`:

```php
<?php

use App\Http\Controllers\KYCController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // KYC
    Route::post('/kyc/submit', [KYCController::class, 'submit']);

    // Paiements
    Route::post('/payments/process', [PaymentController::class, 'processPayment']);
    Route::post('/payments/monthly', [PaymentController::class, 'processMonthlyPayments']);

    // Admin - Emails manuels
    Route::post('/admin/send-reminder', [AdminController::class, 'sendManualReminder']);
    Route::post('/admin/send-overdue-notification', [AdminController::class, 'sendManualOverdueNotification']);
});
```

---

## 5. Commandes Artisan Custom (Optionnel)

### Créer une commande pour tester les emails:

```php
<?php

// app/Console/Commands/TestEmailCommand.php

namespace App\Console\Commands;

use App\Models\KYC;
use App\Models\PaymentSchedule;
use App\Events\KYCSubmitted;
use App\Services\EmailService;
use Illuminate\Console\Command;

class TestEmailCommand extends Command
{
    protected $signature = 'email:test {type}';
    protected $description = 'Test email sending';

    public function handle(EmailService $emailService)
    {
        $type = $this->argument('type');

        switch ($type) {
            case 'kyc':
                $kyc = KYC::first();
                if ($kyc) {
                    event(new KYCSubmitted($kyc, $kyc->user));
                    $this->info('KYC email sent');
                }
                break;

            case 'reminder':
                $schedule = PaymentSchedule::where('status', '!=', 'paid')->first();
                if ($schedule) {
                    $emailService->sendPaymentDueReminderEmails($schedule);
                    $this->info('Reminder email sent');
                }
                break;

            case 'overdue':
                $schedule = PaymentSchedule::where('status', '!=', 'paid')
                    ->where('due_date', '<', now())
                    ->first();
                if ($schedule) {
                    $emailService->sendPaymentOverdueEmails($schedule);
                    $this->info('Overdue email sent');
                }
                break;

            default:
                $this->error('Invalid type. Use: kyc, reminder, or overdue');
        }
    }
}
```

Usage:
```bash
php artisan email:test kyc
php artisan email:test reminder
php artisan email:test overdue
```

---

## 6. Exemple Complet d'Intégration

### Scénario: Un utilisateur soumet un KYC et paie le premier versement

```php
<?php

namespace App\Http\Controllers;

use App\Events\KYCSubmitted;
use App\Events\FirstPaymentProcessed;
use App\Models\KYC;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

class CompleteOnboardingController extends Controller
{
    /**
     * Onboarding complet: KYC + Premier Versement
     */
    public function completeOnboarding(Request $request)
    {
        $validated = $request->validate([
            'kyc_data' => 'required|array',
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:MTN,ORANGE,CARD',
            'transaction_id' => 'required|string',
        ]);

        try {
            $user = auth()->user();
            $order = Order::find($validated['order_id']);

            // Étape 1: Soumettre le KYC
            $kyc = KYC::create([
                'user_id' => $user->id,
                'data' => $validated['kyc_data'],
                'status' => 'pending',
            ]);

            // 📧 ENVOYER EMAIL KYC
            event(new KYCSubmitted($kyc, $user));

            // Étape 2: Traiter le paiement
            $payment = Payment::create([
                'order_id' => $order->id,
                'amount' => $validated['amount'],
                'method' => $validated['payment_method'],
                'transaction_id' => $validated['transaction_id'],
                'status' => 'completed',
                'payment_date' => now(),
            ]);

            $payment->markAsCompleted();

            // 📧 ENVOYER EMAIL DE PREMIER VERSEMENT
            event(new FirstPaymentProcessed($payment, $user, $order));

            return response()->json([
                'message' => 'Onboarding complété avec succès',
                'kyc_id' => $kyc->id,
                'payment_id' => $payment->id
            ], 201);

        } catch (\Exception $e) {
            \Log::error("Onboarding error: {$e->getMessage()}");
            return response()->json([
                'message' => 'Erreur lors de l\'onboarding',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
```

---

## 7. Checklist d'Intégration

- [ ] Configurer les variables d'environnement (.env)
- [ ] Tester la connexion SMTP
- [ ] Créer la table queue si nécessaire: `php artisan queue:table && php artisan migrate`
- [ ] Ajouter les appels `event()` dans les contrôleurs
- [ ] Démarrer le queue worker: `php artisan queue:work`
- [ ] Configurer le cron scheduler pour les rappels automatiques
- [ ] Tester chaque type d'email
- [ ] Monitorer les logs
- [ ] Déployer en production

---

## 8. Codes de Statut HTTP

| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `/api/kyc/submit` | POST | Soumettre un KYC |
| `/api/payments/process` | POST | Traiter un paiement unique |
| `/api/payments/monthly` | POST | Traiter plusieurs paiements |
| `/api/admin/send-reminder` | POST | Envoyer un rappel manuel |
| `/api/admin/send-overdue-notification` | POST | Envoyer une notification de retard |

---

**Créé:** Février 2026
**Version:** 1.0
**Système:** SmallPay Email Integration
