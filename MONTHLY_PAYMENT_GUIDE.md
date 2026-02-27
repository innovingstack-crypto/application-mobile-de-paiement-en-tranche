# 📅 Guide des Paiements Mensuels

## Vue d'ensemble

Après que l'utilisateur ait payé l'acompte (dépôt), le système crée automatiquement un calendrier de paiement mensuel. Ce guide explique comment les paiements mensuels fonctionnent et comment les intégrer dans l'app.

---

## 🔄 Processus automatique

### 1. Dépôt payé avec succès
```
POST /api/payments/deposit
→ CampayPayment status = 'success'
→ Event: PaymentSuccessful déclenché
```

### 2. Création automatique du calendrier
```
Listener: CreatePaymentSchedule
→ PaymentFlowService::generatePaymentSchedule()
→ Crée N PaymentSchedule (N = payment_duration)
```

### 3. Structure du calendrier

```php
// Exemple: 100000 FCFA sur 6 mois
// Dépôt: 30000 (30%)
// Restant: 70000

PaymentSchedule {
  installment 1: due_date = today,     amount = 30000  (dépôt)
  installment 2: due_date = today+1m,  amount = 11667
  installment 3: due_date = today+2m,  amount = 11667
  installment 4: due_date = today+3m,  amount = 11667
  installment 5: due_date = today+4m,  amount = 11667
  installment 6: due_date = today+5m,  amount = 11667
  ---------------------------------------------------
  Total:                               = 100000
}
```

---

## 💳 Paiements mensuels dans l'app

### Écran: Profil utilisateur + Calendrier

À ajouter dans `/profile`:

```typescript
<View>
  <Text style={styles.sectionTitle}>Mes paiements</Text>
  
  {/* Paiements en attente */}
  {pendingSchedules.map(schedule => (
    <View style={styles.scheduleCard} key={schedule.id}>
      <View style={styles.scheduleHeader}>
        <Text style={styles.dueDate}>
          Dû le: {new Date(schedule.due_date).toLocaleDateString('fr-CM')}
        </Text>
        <Text style={[
          styles.status,
          { color: schedule.status === 'overdue' ? '#DC2626' : '#F59E0B' }
        ]}>
          {schedule.status}
        </Text>
      </View>
      
      <View style={styles.scheduleDetails}>
        <Text style={styles.amount}>
          {schedule.amount.toLocaleString()} FCFA
        </Text>
        <Button
          title="Payer maintenant"
          onPress={() => handlePayMonthly(schedule)}
        />
      </View>
    </View>
  ))}
</View>
```

### Endpoint: Initier un paiement mensuel

**POST /api/payments/monthly**

```json
Request:
{
  "order_id": 1,
  "schedule_id": 2
}

Response:
{
  "success": true,
  "reference": "uuid",
  "amount": 11667,
  "currency": "XAF",
  "message": "Veuillez approuver le paiement sur votre téléphone",
  "redirect_to": "campay_payment_screen"
}
```

### Ajouter au PaymentService

```typescript
// services/paymentService.ts (déjà créé)

async initiateMonthlyPayment(orderId: number): Promise<PaymentInitiationResponse> {
  try {
    const response = await api.post<PaymentInitiationResponse>('/payments/monthly', {
      order_id: orderId,
    });

    return response.data;
  } catch (error: any) {
    throw {
      success: false,
      error: error.response?.data?.message || 'Erreur lors de l\'initiation du paiement',
      reference: null,
    };
  }
}
```

---

## 🔔 Notifications et rappels

### Système de rappels (Cron Job)

À ajouter dans le backend:

```php
// app/Console/Commands/CheckOverduePayments.php

class CheckOverduePayments extends Command {
    public function handle() {
        $today = Carbon::now();
        
        // Trouver tous les paiements en retard
        $overdue = PaymentSchedule::where('due_date', '<', $today)
            ->where('status', 'pending')
            ->get();
            
        foreach ($overdue as $schedule) {
            // Marquer comme en retard
            $schedule->status = 'overdue';
            $schedule->save();
            
            // Envoyer notification
            $user = $schedule->order->user;
            $user->notify(new OverduePaymentNotification($schedule));
        }
    }
}

// Ajouter dans kernel.php
$schedule->command('payments:check-overdue')->daily();
```

### Messages de notification

```php
// Rappel 1 jour avant
SMS: "Rappel: Paiement de 11667 FCFA dû demain le 16/03"

// Jour de l'échéance
SMS: "Paiement dû aujourd'hui: 11667 FCFA"

// Après 5 jours en retard
SMS: "⚠️ Votre paiement est en retard de 5 jours"

// Après 15 jours en retard
SMS: "⚠️ Compte suspendu. Contactez le support"
```

---

## 📊 Affichage du calendrier complet

### Endpoint: Récupérer le calendrier

**GET /api/orders/{id}/schedule**

```json
Response:
{
  "data": [
    {
      "id": 1,
      "order_id": 1,
      "due_date": "2026-02-16",
      "amount": 30000,
      "installment_number": 1,
      "status": "paid",
      "paid_at": "2026-02-16"
    },
    {
      "id": 2,
      "order_id": 1,
      "due_date": "2026-03-16",
      "amount": 11667,
      "installment_number": 2,
      "status": "pending",
      "paid_at": null
    },
    // ... installments 3-6
  ]
}
```

### Composant: Timeline du calendrier

```typescript
import React from 'react';
import { View, Text, FlatList } from 'react-native';

const PaymentTimeline = ({ schedules }) => {
  const getStatusColor = (status: string) => {
    switch (status) {
      case 'paid':
        return '#10B981';
      case 'pending':
        return '#F59E0B';
      case 'overdue':
        return '#DC2626';
      default:
        return '#64748B';
    }
  };

  return (
    <View style={styles.container}>
      <Text style={styles.title}>Calendrier de paiement</Text>
      
      <FlatList
        data={schedules}
        scrollEnabled={false}
        renderItem={({ item, index }) => (
          <View key={item.id} style={styles.scheduleItem}>
            {/* Ligne verticale */}
            {index < schedules.length - 1 && (
              <View style={[
                styles.line,
                { backgroundColor: getStatusColor(item.status) }
              ]} />
            )}
            
            {/* Cercle de statut */}
            <View style={[
              styles.circle,
              { borderColor: getStatusColor(item.status) }
            ]}>
              <View style={[
                styles.circleFill,
                { backgroundColor: getStatusColor(item.status) }
              ]} />
            </View>
            
            {/* Détails */}
            <View style={styles.details}>
              <Text style={styles.installmentNumber}>
                Versement {item.installment_number}
              </Text>
              <Text style={styles.dueDate}>
                Échéance: {new Date(item.due_date).toLocaleDateString('fr-CM')}
              </Text>
              <Text style={styles.amount}>
                {item.amount.toLocaleString()} FCFA
              </Text>
              <Text style={[
                styles.status,
                { color: getStatusColor(item.status) }
              ]}>
                {item.status === 'paid' && '✓ Payé'}
                {item.status === 'pending' && '⏳ En attente'}
                {item.status === 'overdue' && '⚠️ En retard'}
              </Text>
            </View>
          </View>
        )}
        keyExtractor={item => item.id.toString()}
      />
    </View>
  );
};

const styles = StyleSheet.create({
  container: { paddingHorizontal: 16 },
  title: { fontSize: 16, fontWeight: '600', marginBottom: 16 },
  scheduleItem: {
    flexDirection: 'row',
    marginBottom: 20,
    paddingLeft: 8,
  },
  line: { position: 'absolute', left: 20, top: 40, width: 2, height: 80 },
  circle: {
    width: 40,
    height: 40,
    borderRadius: 20,
    borderWidth: 2,
    justifyContent: 'center',
    alignItems: 'center',
  },
  circleFill: { width: 20, height: 20, borderRadius: 10 },
  details: { marginLeft: 16, flex: 1 },
  installmentNumber: { fontSize: 13, fontWeight: '600' },
  dueDate: { fontSize: 12, color: '#64748B', marginVertical: 4 },
  amount: { fontSize: 14, fontWeight: '600', marginVertical: 4 },
  status: { fontSize: 12, fontWeight: '500' },
});
```

---

## 🛠️ Implémentation étape par étape

### Phase 1: Backend (Déjà fait ✅)
- [x] PaymentFlowService::generatePaymentSchedule()
- [x] Endpoints /payments/monthly
- [x] Cron job pour les retards

### Phase 2: Frontend (À faire)
- [ ] Écran profil avec calendrier
- [ ] Composant timeline
- [ ] Service pour /schedule
- [ ] Bouton "Payer maintenant"

### Phase 3: Notifications (À faire)
- [ ] SMS rappels
- [ ] Push notifications
- [ ] Dashboard admin

---

## 📱 Exemple complet: Écran Profil

```typescript
// app/profile/payment-history.tsx

import React, { useEffect, useState } from 'react';
import { View, Text, FlatList, ActivityIndicator } from 'react-native';
import { api } from '@/lib/api';
import Button from '@/components/Button';
import paymentService from '@/services/paymentService';

export default function PaymentHistoryScreen() {
  const [schedules, setSchedules] = useState([]);
  const [loading, setLoading] = useState(true);
  const orderId = 1; // De params ou store

  useEffect(() => {
    const fetchSchedules = async () => {
      try {
        const schedules = await paymentService.getPaymentSchedule(orderId);
        setSchedules(schedules);
      } catch (error) {
        console.error('Erreur:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchSchedules();
  }, [orderId]);

  const handlePayNow = async (schedule) => {
    try {
      const response = await paymentService.initiateMonthlyPayment(orderId);
      // Rediriger vers /payment/processing
    } catch (error) {
      console.error('Erreur:', error);
    }
  };

  if (loading) return <ActivityIndicator />;

  return (
    <View style={styles.container}>
      <FlatList
        data={schedules}
        scrollEnabled={false}
        renderItem={({ item }) => (
          <View style={styles.card}>
            <View style={styles.header}>
              <Text style={styles.title}>
                Versement {item.installment_number}
              </Text>
              <Text style={[
                styles.badge,
                { backgroundColor: item.status === 'paid' ? '#D1FAE5' : '#FEF3C7' }
              ]}>
                {item.status.toUpperCase()}
              </Text>
            </View>
            
            <View style={styles.details}>
              <Text style={styles.amount}>
                {item.amount.toLocaleString()} FCFA
              </Text>
              <Text style={styles.dueDate}>
                Dû le: {new Date(item.due_date).toLocaleDateString('fr-CM')}
              </Text>
            </View>

            {item.status === 'pending' && (
              <Button
                title="Payer maintenant"
                onPress={() => handlePayNow(item)}
              />
            )}
          </View>
        )}
        keyExtractor={item => item.id.toString()}
      />
    </View>
  );
}
```

---

## 🎯 Cas d'usage avancés

### Paiement anticipé (Débours)
```php
POST /api/orders/{id}/pay-all-remaining
{
  "amount": 40000  // Payer plus que le minimum
}

Réaction:
- Créer un Payment de 40000
- Réduire les paiements futurs
- Ou créditer le solde
```

### Refinancement (Allonger la durée)
```php
POST /api/orders/{id}/extend-duration
{
  "new_duration": 12  // Au lieu de 6
}

Réaction:
- Recalculer les montants mensuels
- Créer/modifier PaymentSchedule
- Notifier l'utilisateur
```

### Suspension de compte (Non-paiement)
```php
// Cron job après 15 jours en retard
Order::status = 'suspended'
User::notify(new SuspensionNotification())
```

---

## 🔐 Sécurité

### Rate limiting pour paiements
```php
// Max 3 tentatives par jour par utilisateur
RateLimiter::for('payments', function (Request $request) {
    return Limit::perDay(3)->by($request->user()->id);
});
```

### Audit trail
```php
AuditLog::create([
    'action' => 'payment_initiated',
    'user_id' => $user->id,
    'order_id' => $order->id,
    'amount' => $amount,
    'reference' => $reference,
]);
```

---

## 📊 Rapports

### Pour l'admin
```
GET /api/admin/payment-stats

Response:
{
  "total_payments": 1000,
  "total_revenue": 10000000,
  "pending_schedules": 500,
  "overdue_schedules": 50,
  "completed_orders": 800,
  "suspended_orders": 20
}
```

### Pour l'utilisateur
```
GET /api/user/payment-summary

Response:
{
  "total_paid": 150000,
  "next_payment": {
    "amount": 11667,
    "due_date": "2026-03-16"
  },
  "completed_orders": 3,
  "active_loans": 2
}
```

---

## ✅ Checklist pour les paiements mensuels

- [ ] Vérifier que le calendrier se crée après le dépôt
- [ ] Tester l'endpoint /orders/{id}/schedule
- [ ] Ajouter l'écran de calendrier au profil
- [ ] Configurer les notifications SMS
- [ ] Tester le cron job des retards
- [ ] Ajouter le bouton "Payer maintenant"
- [ ] Tester le paiement mensuel via Campay
- [ ] Afficher la timeline
- [ ] Tests E2E complets

---

**À implémenter après le dépôt initial ✅**
