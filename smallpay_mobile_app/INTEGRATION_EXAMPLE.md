# 🔗 Exemple d'intégration - ConsentGate dans l'app

## Intégration dans app/_layout.tsx (ou votre layout principal)

```typescript
// app/_layout.tsx

import React from 'react';
import { GestureHandlerRootView } from 'react-native-gesture-handler';
import { Provider } from 'react-redux';
import { store } from '@/store';
import { Stack } from 'expo-router';
import ConsentGate from '@/components/ConsentGate';

export default function RootLayout() {
  return (
    <GestureHandlerRootView style={{ flex: 1 }}>
      <Provider store={store}>
        <ConsentGate
          onConsentAccepted={() => {
            console.log('✅ Consentements acceptés');
            // Vous pouvez déclencher des actions ici si nécessaire
          }}
        >
          {/* Votre navigation habituelle */}
          <Stack
            screenOptions={{
              headerShown: false,
            }}
          >
            <Stack.Screen name="(tabs)" />
            <Stack.Screen name="product/[id]" />
            <Stack.Screen name="kyc-form" />
            {/* ... autres écrans */}
          </Stack>
        </ConsentGate>
      </Provider>
    </GestureHandlerRootView>
  );
}
```

## Alternative: Intégration à l'inscription

```typescript
// app/register.tsx

import React, { useState } from 'react';
import { View, Alert } from 'react-native';
import ConsentModal from '@/components/ConsentModal';
import { useConsentManager } from '@/hooks/useConsentManager';

export default function RegisterScreen() {
  const { saveConsent } = useConsentManager();
  const [showConsent, setShowConsent] = useState(false);
  const [loading, setLoading] = useState(false);

  const handleRegister = async (userData: any) => {
    // Avant de compléter l'inscription, demander le consentement
    setShowConsent(true);
  };

  const handleConsentAccepted = async () => {
    try {
      setLoading(true);
      
      // Sauvegarder les consentements
      await saveConsent(true, true, userData.id);
      
      // Compléter l'inscription
      await submitRegistration(userData);
      
      // Naviguer vers le KYC
      navigation.replace('kyc-form');
      
    } catch (error) {
      Alert.alert('Erreur', 'Une erreur s\'est produite');
    } finally {
      setLoading(false);
    }
  };

  return (
    <View style={{ flex: 1 }}>
      {/* Votre formulaire d'inscription ici */}
      
      <ConsentModal 
        visible={showConsent} 
        onAccept={handleConsentAccepted}
        loading={loading}
      />
    </View>
  );
}
```

## Utilisation dans les composants

```typescript
// Anywhere in your app

import { useConsentManager } from '@/hooks/useConsentManager';
import LegalDocumentViewer from '@/components/LegalDocumentViewer';
import { useState } from 'react';
import { TouchableOpacity, Text } from 'react-native';

export default function MyComponent() {
  const { hasAcceptedConsents, getConsent } = useConsentManager();
  const [showTerms, setShowTerms] = useState(false);

  const checkConsents = async () => {
    const accepted = await hasAcceptedConsents();
    const consent = await getConsent();
    
    if (accepted) {
      console.log('Consentements acceptés le', consent?.acceptedAt);
    } else {
      console.log('Consentements non acceptés');
    }
  };

  return (
    <>
      <TouchableOpacity onPress={checkConsents}>
        <Text>Vérifier les consentements</Text>
      </TouchableOpacity>

      <TouchableOpacity onPress={() => setShowTerms(true)}>
        <Text>Lire les conditions d'utilisation</Text>
      </TouchableOpacity>

      <LegalDocumentViewer
        visible={showTerms}
        documentType="terms"
        onClose={() => setShowTerms(false)}
      />
    </>
  );
}
```

## Intégration avec les formulaires KYC

```typescript
// app/kyc-form.tsx - Exemple d'intégration complète

import React, { useState, useEffect } from 'react';
import { useConsentManager } from '@/hooks/useConsentManager';
import { useImagePicker } from '@/hooks/useImagePicker';
import { useDocumentPicker } from '@/hooks/useDocumentPicker';

export default function KycFormScreen() {
  const { getConsentForBackend } = useConsentManager();
  const { pickImage, pickImageOrTakePhoto } = useImagePicker();
  const { pickDocument } = useDocumentPicker();
  const [consentData, setConsentData] = useState(null);

  useEffect(() => {
    loadConsentData();
  }, []);

  const loadConsentData = async () => {
    const data = await getConsentForBackend();
    setConsentData(data);
  };

  const handleSubmitKyc = async () => {
    // Envoyer les données au backend avec les consentements
    const payload = {
      // Données du formulaire
      fullName: '...',
      phoneNumber: '...',
      // ...
      
      // Ajouter les consentements
      ...consentData,
    };

    // POST /api/kyc/submit
    await api.post('/kyc/submit', payload);
  };

  return (
    // Votre formulaire KYC ici
    // Les permissions seront demandées automatiquement quand l'utilisateur
    // appuiera sur les boutons de sélection d'images/documents
  );
}
```

## Envoi des consentements au backend

```typescript
// lib/authService.ts

import { api } from './api';
import { useConsentManager } from '@/hooks/useConsentManager';

export const registerUser = async (userData: any) => {
  const { getConsentForBackend } = useConsentManager();
  
  // Récupérer les consentements sauvegardés
  const consentData = await getConsentForBackend();

  if (!consentData) {
    throw new Error('Consentements requis');
  }

  const payload = {
    email: userData.email,
    password: userData.password,
    fullName: userData.fullName,
    // ...autres données...
    
    // Consentements
    terms_accepted: consentData.terms_accepted,
    privacy_accepted: consentData.privacy_accepted,
    accepted_at: consentData.accepted_at,
    terms_version: consentData.terms_version,
    privacy_version: consentData.privacy_version,
  };

  const response = await api.post('/auth/register', payload);
  return response.data;
};
```

## Backend: Stocker les consentements

```php
// Laravel: UserConsentController.php

namespace App\Http\Controllers;

use App\Models\UserConsent;

class UserConsentController extends Controller {
    public function store(Request $request) {
        UserConsent::create([
            'user_id' => $request->user()->id,
            'terms_accepted' => $request->boolean('terms_accepted'),
            'privacy_accepted' => $request->boolean('privacy_accepted'),
            'accepted_at' => $request->input('accepted_at'),
            'terms_version' => $request->input('terms_version'),
            'privacy_version' => $request->input('privacy_version'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['success' => true]);
    }
}
```

```sql
-- Migration: create_user_consents_table.php

Schema::create('user_consents', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();
    $table->boolean('terms_accepted');
    $table->boolean('privacy_accepted');
    $table->timestamp('accepted_at');
    $table->string('terms_version');
    $table->string('privacy_version');
    $table->string('ip_address')->nullable();
    $table->text('user_agent')->nullable();
    $table->timestamps();
    
    // Index pour requêtes rapides
    $table->index(['user_id', 'created_at']);
});
```

## Gestion des mises à jour de documents

```typescript
// Quand vous mettez à jour les documents légaux:

// 1. Mettez à jour constants/legal.ts
const TERMS_OF_SERVICE = `... nouveau contenu ...`

// 2. Augmentez la version dans useConsentManager.ts
const TERMS_VERSION = '1.1.0';

// 3. La prochaine fois que l'utilisateur ouvre l'app,
//    hasNewVersions() retournera true et ConsentGate
//    affichera à nouveau le modal

// 4. L'utilisateur devra accepter les nouvelles versions

// ✅ C'est automatique!
```

## Tester l'intégration

```typescript
// test/consent.test.ts

import { renderHook, act } from '@testing-library/react-native';
import { useConsentManager } from '@/hooks/useConsentManager';

describe('useConsentManager', () => {
  it('should save and retrieve consent', async () => {
    const { result } = renderHook(() => useConsentManager());

    // Sauvegarder le consentement
    await act(async () => {
      await result.current.saveConsent(true, true, 'user123');
    });

    // Récupérer le consentement
    const consent = await result.current.getConsent();
    expect(consent.termsOfServiceAccepted).toBe(true);
    expect(consent.privacyPolicyAccepted).toBe(true);
  });

  it('should detect new versions', async () => {
    const { result } = renderHook(() => useConsentManager());

    // Sauvegarder une ancienne version
    await act(async () => {
      // Sauvegarder manuellement une version antérieure
      // ...
    });

    // Vérifier les nouvelles versions
    const hasNew = await result.current.hasNewVersions();
    expect(hasNew).toBe(true);
  });
});
```

## Meilleure pratique: Afficher le statut des consentements

```typescript
// components/ConsentStatus.tsx

import React, { useEffect, useState } from 'react';
import { View, Text } from 'react-native';
import { useConsentManager } from '@/hooks/useConsentManager';

export default function ConsentStatus() {
  const { getConsent } = useConsentManager();
  const [consent, setConsent] = useState(null);

  useEffect(() => {
    loadConsent();
  }, []);

  const loadConsent = async () => {
    const data = await getConsent();
    setConsent(data);
  };

  if (!consent) return null;

  return (
    <View style={{ padding: 16, backgroundColor: '#f3f4f6', borderRadius: 8 }}>
      <Text style={{ fontWeight: '600', marginBottom: 8 }}>
        ✅ Consentements acceptés
      </Text>
      <Text style={{ fontSize: 12, color: '#64748b' }}>
        {new Date(consent.acceptedAt).toLocaleDateString('fr-FR')}
      </Text>
      <Text style={{ fontSize: 10, color: '#94a3b8', marginTop: 4 }}>
        Version {consent.termsOfServiceVersion}
      </Text>
    </View>
  );
}
```

---

## Résumé de l'intégration

| Étape | Où | Quand |
|-------|-----|-------|
| 1. Wrapper ConsentGate | app/_layout.tsx | Démarrage de l'app |
| 2. Demande consentement | ConsentModal | Automatique si non accepté |
| 3. Sauvegarde locally | AsyncStorage | Quand accepté |
| 4. Envoie au backend | /api/auth/register | À l'inscription |
| 5. Stocke en DB | user_consents table | Persistance légale |

C'est tout! ✅
