# Guide d'Intégration Frontend - Système de Paiement SmallPay

## Vue d'Ensemble des Écrans

```
Login/OTP
   ↓
Products Catalog
   ↓
Create Order
   ↓
[✨ NEW] Payment Flow:
   ├─ 1. Verify KYC Screen
   ├─ 2. Payment Details Review
   ├─ 3. Processing Payment (Loading)
   ├─ 4. Success / Failed Screen
   └─ 5. Payment Schedule View
   ↓
Order History
```

---

## 1. Vérifier le Statut KYC

**Écran:** `KYCVerificationScreen`

**Logique:**
```typescript
// Avant de montrer le bouton "Payer"
const checkKYCStatus = async () => {
  const response = await api.get('/kyc/status');
  
  if (response.data.status === 'approved') {
    // ✅ Afficher bouton "Procéder au Paiement"
    setCanProceed(true);
  } else if (response.data.status === 'pending') {
    // ⏳ "En attente d'approbation"
    setMessage('Votre KYC est en attente d\'approbation');
  } else if (response.data.status === 'rejected') {
    // ❌ "KYC Rejeté"
    setMessage('Votre KYC a été rejeté. Raison: ' + response.data.reason);
    // Bouton: "Resoummettre KYC"
  } else {
    // 🔍 "KYC non soumis"
    setMessage('Veuillez compléter votre KYC');
    // Bouton: "Soumettre KYC"
  }
};

useEffect(() => {
  checkKYCStatus();
}, []);
```

**UI Component:**
```tsx
<SafeAreaView style={styles.container}>
  <Header title="Vérification KYC" />
  
  {isLoading ? (
    <LoadingSpinner />
  ) : (
    <>
      {kycStatus === 'approved' ? (
        <>
          <SuccessIcon color="green" size={60} />
          <Text style={styles.title}>KYC Approuvé</Text>
          <Text style={styles.subtitle}>
            Vous êtes prêt à effectuer votre paiement
          </Text>
          
          <Button
            title="Procéder au Paiement"
            onPress={() => navigation.navigate('PaymentDetails')}
          />
        </>
      ) : (
        <>
          <WarningIcon color="orange" size={60} />
          <Text style={styles.title}>KYC Non Approuvé</Text>
          <Text style={styles.subtitle}>{message}</Text>
          
          <Button
            title="Soumettre / Vérifier KYC"
            onPress={() => navigation.navigate('KYCForm')}
          />
        </>
      )}
    </>
  )}
</SafeAreaView>
```

---

## 2. Écran de Revue des Détails de Paiement

**Écran:** `PaymentDetailsScreen`

**Données affichées:**
```
┌─────────────────────────────┐
│    DÉTAILS DE PAIEMENT      │
├─────────────────────────────┤
│ Montant Total:   100,000    │
│ Acompte (30%):    30,000    │
├─────────────────────────────┤
│ Durée:           6 mois     │
│ Montant/Mois:    11,666.67  │
├─────────────────────────────┤
│ Montant dû aujourd'hui:     │
│ ► 30,000 XAF               │
├─────────────────────────────┤
│ [Annuler]  [Continuer]     │
└─────────────────────────────┘
```

**Code TypeScript:**
```typescript
interface PaymentDetails {
  orderId: number;
  totalAmount: number;
  depositAmount: number;
  remainingAmount: number;
  paymentDuration: number;
  monthlyPayment: number;
}

const PaymentDetailsScreen = ({ route, navigation }) => {
  const { orderId } = route.params;
  const [paymentDetails, setPaymentDetails] = useState<PaymentDetails | null>(null);

  useEffect(() => {
    fetchOrderDetails();
  }, []);

  const fetchOrderDetails = async () => {
    try {
      const response = await api.get(`/orders/${orderId}`);
      const order = response.data.data;
      
      const depositAmount = order.total_amount * 0.30;
      const remainingAmount = order.total_amount - depositAmount;
      const monthlyPayment = remainingAmount / order.payment_duration;
      
      setPaymentDetails({
        orderId: order.id,
        totalAmount: order.total_amount,
        depositAmount,
        remainingAmount,
        paymentDuration: order.payment_duration,
        monthlyPayment,
      });
    } catch (error) {
      Alert.alert('Erreur', 'Impossible de charger les détails');
      navigation.goBack();
    }
  };

  const handlePayment = async () => {
    navigation.navigate('PaymentProcessing', { 
      orderId,
      paymentType: 'deposit'
    });
  };

  if (!paymentDetails) return <LoadingSpinner />;

  return (
    <SafeAreaView style={styles.container}>
      <ScrollView>
        <Card style={styles.detailsCard}>
          <Text style={styles.sectionTitle}>Résumé de la Commande</Text>
          
          <Row label="Montant Total" value={`${paymentDetails.totalAmount.toLocaleString()} XAF`} />
          <Row label="Acompte (30%)" value={`${paymentDetails.depositAmount.toLocaleString()} XAF`} highlight />
          
          <Divider style={styles.divider} />
          
          <Text style={styles.sectionTitle}>Plan de Financement</Text>
          
          <Row label="Durée" value={`${paymentDetails.paymentDuration} mois`} />
          <Row label="Montant/Mois" value={`${paymentDetails.monthlyPayment.toLocaleString()} XAF`} />
          <Row label="Reste après acompte" value={`${paymentDetails.remainingAmount.toLocaleString()} XAF`} />
          
          <Divider style={styles.divider} />
          
          <Text style={styles.sectionTitle}>À Payer Aujourd'hui</Text>
          <Text style={styles.amountToPay}>
            {paymentDetails.depositAmount.toLocaleString()} XAF
          </Text>
          
          <Text style={styles.disclaimer}>
            Vous pourrez voir le détail de vos échéances mensuelles après le paiement du dépôt.
          </Text>
        </Card>
      </ScrollView>
      
      <BottomBar>
        <Button 
          title="Annuler" 
          variant="outline"
          onPress={() => navigation.goBack()}
        />
        <Button 
          title="Continuer vers le Paiement"
          onPress={handlePayment}
        />
      </BottomBar>
    </SafeAreaView>
  );
};
```

---

## 3. Écran de Traitement du Paiement (Polling)

**Écran:** `PaymentProcessingScreen`

**Architecture:**
```
1. Appeler POST /api/payments/deposit
2. Obtenir reference
3. Afficher "Approuvez sur votre téléphone"
4. Poll GET /api/payments/{reference}/status toutes 2s
5. Attendre succès ou timeout
6. Rediriger appropriément
```

**Code complet:**
```typescript
const PaymentProcessingScreen = ({ route, navigation }) => {
  const { orderId, paymentType = 'deposit' } = route.params;
  const [reference, setReference] = useState<string | null>(null);
  const [status, setStatus] = useState<'waiting' | 'success' | 'failed'>('waiting');
  const [error, setError] = useState<string | null>(null);
  const [pollCount, setPollCount] = useState(0);
  const MAX_POLLS = 30; // 1 minute (2s × 30)

  useEffect(() => {
    initiatePayment();
  }, []);

  const initiatePayment = async () => {
    try {
      const endpoint = paymentType === 'deposit' 
        ? '/payments/deposit'
        : '/payments/monthly';
      
      const response = await api.post(endpoint, { order_id: orderId });
      
      if (response.data.success) {
        setReference(response.data.reference);
        startPolling(response.data.reference);
      } else {
        setStatus('failed');
        setError(response.data.message || 'Erreur lors de l\'initiation du paiement');
      }
    } catch (error) {
      setStatus('failed');
      setError(error.message || 'Erreur serveur');
    }
  };

  const startPolling = (ref: string) => {
    const pollInterval = setInterval(async () => {
      try {
        const response = await api.get(`/payments/${ref}/status`);
        
        setPollCount(prev => prev + 1);

        if (response.data.status === 'success') {
          setStatus('success');
          clearInterval(pollInterval);
          
          // Rediriger après 2 secondes
          setTimeout(() => {
            navigation.navigate('PaymentSuccess', { reference: ref, orderId });
          }, 2000);
        } else if (response.data.status === 'failed') {
          setStatus('failed');
          setError(response.data.error_reason || 'Le paiement a échoué');
          clearInterval(pollInterval);
        } else if (pollCount >= MAX_POLLS) {
          setStatus('failed');
          setError('Délai d\'attente dépassé. Veuillez vérifier votre paiement.');
          clearInterval(pollInterval);
        }
      } catch (error) {
        console.log('Poll error:', error);
        // Continuer à poller même en cas d'erreur
      }
    }, 2000); // Poll toutes les 2 secondes
  };

  return (
    <SafeAreaView style={styles.container}>
      <ScrollView contentContainerStyle={styles.centerContent}>
        {status === 'waiting' && (
          <>
            <Animated.View style={styles.spinnerContainer}>
              <Spinner color="primary" size="large" />
            </Animated.View>
            
            <Text style={styles.title}>Traitement du Paiement</Text>
            <Text style={styles.subtitle}>
              Veuillez approuver le paiement sur votre téléphone
            </Text>
            
            {reference && (
              <Card style={styles.refCard}>
                <Text style={styles.label}>Référence:</Text>
                <Text style={styles.reference}>{reference.substring(0, 8)}...</Text>
                <Text style={styles.refDescription}>
                  Conservez cette référence pour vos dossiers
                </Text>
              </Card>
            )}
            
            <Text style={styles.hint}>
              Vérification en cours... ({pollCount}/{MAX_POLLS})
            </Text>
            
            <Button 
              title="Annuler"
              variant="outline"
              onPress={() => navigation.goBack()}
              style={styles.cancelButton}
            />
          </>
        )}

        {status === 'success' && (
          <>
            <LottieView
              source={require('./animations/success.json')}
              autoPlay
              loop={false}
              style={styles.successAnimation}
            />
            <Text style={styles.successTitle}>Paiement Réussi!</Text>
            <Text style={styles.subtitle}>
              Redirection en cours...
            </Text>
          </>
        )}

        {status === 'failed' && (
          <>
            <ErrorIcon color="red" size={80} />
            <Text style={styles.errorTitle}>Paiement Échoué</Text>
            <Text style={styles.errorMessage}>{error}</Text>
            
            <Card style={styles.errorCard}>
              <Text style={styles.errorLabel}>Que faire?</Text>
              <Text style={styles.errorText}>
                • Vérifiez que vous avez assez de crédit sur votre compte
              </Text>
              <Text style={styles.errorText}>
                • Assurez-vous que le numéro est correct
              </Text>
              <Text style={styles.errorText}>
                • Réessayez dans quelques instants
              </Text>
            </Card>
            
            <Button 
              title="Réessayer"
              onPress={() => {
                setStatus('waiting');
                setError(null);
                setPollCount(0);
                setReference(null);
                initiatePayment();
              }}
              style={styles.retryButton}
            />
            
            <Button 
              title="Contacter le Support"
              variant="outline"
              onPress={() => {
                // Ouvrir WhatsApp ou formulaire de support
              }}
            />
          </>
        )}
      </ScrollView>
    </SafeAreaView>
  );
};
```

---

## 4. Écran de Succès

**Écran:** `PaymentSuccessScreen`

```typescript
const PaymentSuccessScreen = ({ route, navigation }) => {
  const { reference, orderId } = route.params;
  const [schedule, setSchedule] = useState(null);

  useEffect(() => {
    fetchPaymentSchedule();
  }, []);

  const fetchPaymentSchedule = async () => {
    try {
      const response = await api.get(`/orders/${orderId}/schedule`);
      setSchedule(response.data);
    } catch (error) {
      console.log('Error fetching schedule:', error);
    }
  };

  return (
    <SafeAreaView style={styles.container}>
      <ScrollView>
        <SuccessHeader 
          amount={schedule?.summary.total_paid}
          reference={reference}
        />
        
        <Card style={styles.receiptCard}>
          <Text style={styles.receiptTitle}>Reçu de Paiement</Text>
          
          <Row label="Montant Payé" value={`${schedule?.summary.total_paid.toLocaleString()} XAF`} />
          <Row label="Date" value={new Date().toLocaleDateString('fr-FR')} />
          <Row label="Référence" value={reference} />
          <Row label="Méthode" value="Mobile Money (Campay)" />
          
          <Divider />
          
          <Text style={styles.subtitle}>Solde Restant</Text>
          <Text style={styles.remainingBalance}>
            {schedule?.summary.balance_due.toLocaleString()} XAF
          </Text>
        </Card>
        
        {/* Afficher le planning si disponible */}
        {schedule && (
          <Card style={styles.scheduleCard}>
            <Text style={styles.scheduleTitle}>Votre Plan de Paiement</Text>
            <Text style={styles.scheduleDuration}>
              {schedule.payment_duration} paiements mensuels
            </Text>
            
            {schedule.schedules.map((s, index) => (
              <ScheduleRow
                key={s.id}
                number={s.installment_number}
                amount={s.amount}
                dueDate={new Date(s.due_date).toLocaleDateString('fr-FR')}
                status={s.status}
                isOverdue={s.is_overdue}
              />
            ))}
          </Card>
        )}
        
        <InfoBox>
          <InfoIcon />
          <Text style={styles.infoText}>
            Vous recevrez une notification avant chaque paiement mensuel.
          </Text>
        </InfoBox>
      </ScrollView>
      
      <BottomBar>
        <Button 
          title="Voir ma Commande"
          onPress={() => navigation.navigate('OrderDetails', { orderId })}
        />
      </BottomBar>
    </SafeAreaView>
  );
};
```

---

## 5. Écran d'Échec

**Écran:** `PaymentFailedScreen`

```typescript
const PaymentFailedScreen = ({ route, navigation }) => {
  const { errorCode, errorReason, orderId } = route.params;

  const getErrorAdvice = (code: string) => {
    switch (code) {
      case 'ER301':
        return {
          title: 'Solde Insuffisant',
          advice: 'Votre compte mobile money n\'a pas assez de crédit.',
          action: 'Recharger mon compte',
        };
      case 'ER302':
        return {
          title: 'Numéro Invalide',
          advice: 'Le numéro de téléphone n\'est pas reconnu.',
          action: 'Vérifier mon numéro',
        };
      default:
        return {
          title: 'Erreur de Paiement',
          advice: errorReason || 'Une erreur est survenue lors du paiement.',
          action: 'Réessayer',
        };
    }
  };

  const errorInfo = getErrorAdvice(errorCode);

  return (
    <SafeAreaView style={styles.container}>
      <ScrollView contentContainerStyle={styles.centerContent}>
        <ErrorAnimated />
        
        <Text style={styles.title}>{errorInfo.title}</Text>
        <Text style={styles.subtitle}>{errorInfo.advice}</Text>
        
        {errorCode && (
          <Card style={styles.errorCard}>
            <Text style={styles.errorCode}>Code: {errorCode}</Text>
          </Card>
        )}
        
        <AdviceBox>
          <Text style={styles.adviceTitle}>Que faire?</Text>
          
          {errorCode === 'ER301' ? (
            <>
              <Text style={styles.adviceText}>
                • Rechargez votre compte mobile money
              </Text>
              <Text style={styles.adviceText}>
                • Attendez quelques instants
              </Text>
              <Text style={styles.adviceText}>
                • Réessayez le paiement
              </Text>
            </>
          ) : (
            <>
              <Text style={styles.adviceText}>
                • Vérifiez votre connexion internet
              </Text>
              <Text style={styles.adviceText}>
                • Assurez-vous que votre numéro est correct
              </Text>
              <Text style={styles.adviceText}>
                • Réessayez dans quelques instants
              </Text>
            </>
          )}
        </AdviceBox>
      </ScrollView>
      
      <BottomBar>
        <Button 
          title="Réessayer"
          onPress={() => navigation.navigate('PaymentDetails', { orderId })}
        />
        
        <Button 
          title="Contacter le Support"
          variant="outline"
          onPress={() => openWhatsApp('Besoin d\'aide pour mon paiement')}
        />
      </BottomBar>
    </SafeAreaView>
  );
};
```

---

## 6. Affichage du Planning Mensuel

**Écran:** `PaymentScheduleScreen`

**API Call:**
```typescript
GET /api/orders/{orderId}/schedule

Response:
{
  payment_duration: 6,
  total_amount: 100000,
  remaining_amount: 70000,
  schedules: [
    {
      id: 1,
      installment_number: 1,
      amount: 11666.67,
      due_date: "2026-03-14",
      status: "pending",
      is_overdue: false,
      days_until_due: 28
    },
    ...
  ],
  summary: {
    total_paid: 30000,
    balance_due: 70000,
    next_due_date: "2026-03-14",
    next_due_amount: 11666.67,
    has_overdue: false
  }
}
```

**Code:**
```typescript
const PaymentScheduleScreen = ({ route }) => {
  const { orderId } = route.params;
  const [schedule, setSchedule] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchSchedule();
  }, []);

  const fetchSchedule = async () => {
    try {
      const response = await api.get(`/orders/${orderId}/schedule`);
      setSchedule(response.data);
    } catch (error) {
      Alert.alert('Erreur', 'Impossible de charger le planning');
    } finally {
      setLoading(false);
    }
  };

  if (loading) return <LoadingSpinner />;
  if (!schedule) return <ErrorPlaceholder />;

  const { summary, schedules } = schedule;

  return (
    <SafeAreaView style={styles.container}>
      <ScrollView>
        {/* Résumé */}
        <Card style={styles.summaryCard}>
          <Row 
            label="Montant Total" 
            value={`${summary.total_amount.toLocaleString()} XAF`}
          />
          <Row 
            label="Payé" 
            value={`${summary.total_paid.toLocaleString()} XAF`}
            valueColor="green"
          />
          <Row 
            label="Solde Dû" 
            value={`${summary.balance_due.toLocaleString()} XAF`}
            valueColor={summary.has_overdue ? 'red' : 'black'}
          />
          
          <ProgressBar 
            value={summary.total_paid / summary.total_amount}
            style={styles.progressBar}
          />
        </Card>

        {/* Alerte en retard */}
        {summary.has_overdue && (
          <AlertBox type="error">
            <Text style={styles.overdueAlert}>
              ⚠️ Vous avez des paiements en retard
            </Text>
          </AlertBox>
        )}

        {/* Prochain paiement dû */}
        {summary.next_due_date && (
          <Card style={styles.nextPaymentCard}>
            <Text style={styles.nextPaymentTitle}>Prochain Paiement</Text>
            <Text style={styles.nextPaymentDate}>
              {new Date(summary.next_due_date).toLocaleDateString('fr-FR')}
            </Text>
            <Text style={styles.nextPaymentAmount}>
              {summary.next_due_amount.toLocaleString()} XAF
            </Text>
            
            {/* Calcul des jours */}
            <Text style={styles.daysLeft}>
              {Math.max(0, Math.ceil(
                (new Date(summary.next_due_date).getTime() - Date.now()) / (1000 * 60 * 60 * 24)
              ))} jours restants
            </Text>
            
            <Button 
              title="Payer Maintenant"
              onPress={() => navigation.navigate('PaymentProcessing', { 
                orderId,
                paymentType: 'monthly'
              })}
            />
          </Card>
        )}

        {/* Détail du planning */}
        <Text style={styles.scheduleTitle}>
          Planning Complet ({schedules.length} paiements)
        </Text>

        {schedules.map((schedule) => (
          <ScheduleCard
            key={schedule.id}
            installment={schedule.installment_number}
            amount={schedule.amount}
            dueDate={schedule.due_date}
            status={schedule.status}
            isOverdue={schedule.is_overdue}
            daysUntilDue={schedule.days_until_due}
          />
        ))}
      </ScrollView>
    </SafeAreaView>
  );
};

// Component réutilisable
const ScheduleCard = ({ 
  installment, 
  amount, 
  dueDate, 
  status, 
  isOverdue, 
  daysUntilDue 
}) => {
  const statusColor = 
    status === 'paid' ? '#10b981' :
    isOverdue ? '#ef4444' :
    '#f59e0b';

  const statusLabel =
    status === 'paid' ? '✓ Payé' :
    isOverdue ? '! En retard' :
    'Paiement Dû';

  return (
    <Card style={[styles.scheduleItem, { borderLeftColor: statusColor, borderLeftWidth: 4 }]}>
      <View style={styles.scheduleHeader}>
        <Text style={styles.installmentNumber}>
          Paiement {installment}
        </Text>
        <Badge color={statusColor} label={statusLabel} />
      </View>
      
      <View style={styles.scheduleBody}>
        <Text style={styles.amount}>
          {amount.toLocaleString()} XAF
        </Text>
        <Text style={styles.dueDate}>
          À payer avant le {new Date(dueDate).toLocaleDateString('fr-FR')}
        </Text>
        {daysUntilDue > 0 && (
          <Text style={styles.daysLeft}>
            {daysUntilDue} jours restants
          </Text>
        )}
      </View>
    </Card>
  );
};
```

---

## 7. Composants Réutilisables

### LoadingSpinner
```tsx
const LoadingSpinner = () => (
  <View style={styles.spinnerContainer}>
    <ActivityIndicator size="large" color="primary" />
  </View>
);
```

### Card
```tsx
const Card = ({ children, style }) => (
  <View style={[styles.card, style]}>
    {children}
  </View>
);
```

### Row
```tsx
const Row = ({ label, value, highlight = false, valueColor = 'black' }) => (
  <View style={styles.row}>
    <Text style={[styles.rowLabel, highlight && styles.rowHighlight]}>
      {label}
    </Text>
    <Text style={[styles.rowValue, { color: valueColor }]}>
      {value}
    </Text>
  </View>
);
```

### Button
```tsx
const Button = ({ 
  title, 
  onPress, 
  variant = 'primary', 
  loading = false, 
  style 
}) => (
  <TouchableOpacity
    style={[
      styles.button,
      styles[`button${variant.charAt(0).toUpperCase() + variant.slice(1)}`],
      style,
    ]}
    onPress={onPress}
    disabled={loading}
  >
    {loading ? (
      <ActivityIndicator color="white" />
    ) : (
      <Text style={styles.buttonText}>{title}</Text>
    )}
  </TouchableOpacity>
);
```

---

## 8. Intégration avec la Navigation

**Navigation Stack:**
```typescript
<Stack.Navigator>
  {/* Screens existants */}
  <Stack.Screen name="Home" component={HomeScreen} />
  <Stack.Screen name="Products" component={ProductsScreen} />
  <Stack.Screen name="OrderCreate" component={OrderCreateScreen} />
  
  {/* ✨ Nouveaux Screens de Paiement */}
  <Stack.Screen 
    name="KYCVerification" 
    component={KYCVerificationScreen}
    options={{ title: 'Vérification KYC' }}
  />
  <Stack.Screen 
    name="PaymentDetails" 
    component={PaymentDetailsScreen}
    options={{ title: 'Détails du Paiement' }}
  />
  <Stack.Screen 
    name="PaymentProcessing" 
    component={PaymentProcessingScreen}
    options={{ 
      title: 'Traitement du Paiement',
      headerBackVisible: false, // Empêcher de revenir en arrière
    }}
  />
  <Stack.Screen 
    name="PaymentSuccess" 
    component={PaymentSuccessScreen}
    options={{ headerBackVisible: false }}
  />
  <Stack.Screen 
    name="PaymentFailed" 
    component={PaymentFailedScreen}
    options={{ headerBackVisible: false }}
  />
  <Stack.Screen 
    name="PaymentSchedule" 
    component={PaymentScheduleScreen}
    options={{ title: 'Planning de Paiement' }}
  />
  
  {/* Screens existants */}
  <Stack.Screen name="OrderDetails" component={OrderDetailsScreen} />
  <Stack.Screen name="OrderHistory" component={OrderHistoryScreen} />
</Stack.Navigator>
```

---

## 9. Gestion d'État et Contexte

```typescript
// PaymentContext.tsx
import React, { createContext, useState, useCallback } from 'react';

interface PaymentContextType {
  currentReference: string | null;
  paymentStatus: 'idle' | 'processing' | 'success' | 'failed';
  initiatePayment: (orderId: number, type: string) => Promise<string>;
  cancelPayment: () => void;
}

export const PaymentContext = createContext<PaymentContextType | undefined>(undefined);

export const PaymentProvider = ({ children }) => {
  const [currentReference, setCurrentReference] = useState<string | null>(null);
  const [paymentStatus, setPaymentStatus] = useState<'idle' | 'processing' | 'success' | 'failed'>('idle');

  const initiatePayment = useCallback(async (orderId: number, type: string) => {
    try {
      setPaymentStatus('processing');
      const endpoint = type === 'deposit' ? '/payments/deposit' : '/payments/monthly';
      const response = await api.post(endpoint, { order_id: orderId });
      
      if (response.data.success) {
        setCurrentReference(response.data.reference);
        return response.data.reference;
      }
    } catch (error) {
      setPaymentStatus('failed');
      throw error;
    }
  }, []);

  const cancelPayment = useCallback(() => {
    setCurrentReference(null);
    setPaymentStatus('idle');
  }, []);

  return (
    <PaymentContext.Provider value={{ currentReference, paymentStatus, initiatePayment, cancelPayment }}>
      {children}
    </PaymentContext.Provider>
  );
};

export const usePayment = () => {
  const context = useContext(PaymentContext);
  if (!context) {
    throw new Error('usePayment doit être utilisé dans PaymentProvider');
  }
  return context;
};
```

---

## 10. Erreurs Courantes et Solutions

### Problème 1: Polling ne démarre pas
```typescript
// ❌ Mauvais
const ref = response.data.reference;
startPolling(ref); // ref est toujours null

// ✅ Correct
setReference(response.data.reference);
// Puis useEffect détecte le changement et démarre le polling
useEffect(() => {
  if (reference) {
    startPolling(reference);
  }
}, [reference]);
```

### Problème 2: Perte du token
```typescript
// ✅ Utiliser Axios interceptor
api.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      // Token expiré, rediriger vers login
      navigation.navigate('Login');
    }
    return Promise.reject(error);
  }
);
```

### Problème 3: Multiple appels API simultanés
```typescript
// ✅ Utiliser AbortController
const controller = new AbortController();

useEffect(() => {
  fetchSchedule(controller.signal);
  
  return () => controller.abort(); // Cleanup
}, []);
```

---

## Checklist Frontend

- [ ] Écrans créés avec tous les components
- [ ] Navigation stackée correctement
- [ ] API calls avec erreur handling
- [ ] Polling implémenté (2s, max 30)
- [ ] Affichage succès/erreur
- [ ] Planning mensuel affiché
- [ ] Responsive sur tous les appareils
- [ ] Gestion du mode sombre
- [ ] Animations et feedback
- [ ] Tests unitaires des composants

