import React, { useEffect, useRef } from 'react';
import { View, Animated, Easing } from 'react-native';
import { useRouter } from 'expo-router';
import { launchScreenStyles as styles } from '@/constants/launchScreen.styles';
import { SmallPayLogo } from '@/components/SmallPayLogo';

export default function LaunchScreen() {
  const router = useRouter();

  // Animations
  const fadeAnim = useRef(new Animated.Value(0)).current;
  const bounceAnim = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    // Animation d'entrée - fade in
    Animated.timing(fadeAnim, {
      toValue: 1,
      duration: 1000,
      easing: Easing.out(Easing.cubic),
      useNativeDriver: true,
    }).start();

    // Animation bounce du logo
    Animated.loop(
      Animated.sequence([
        // Saut vers le haut
        Animated.timing(bounceAnim, {
          toValue: -20,
          duration: 600,
          easing: Easing.out(Easing.cubic),
          useNativeDriver: true,
        }),
        // Retour avec effet bounce
        Animated.timing(bounceAnim, {
          toValue: 0,
          duration: 400,
          easing: Easing.bounce,
          useNativeDriver: true,
        }),
      ]),
      {
        iterations: -1,
        resetBeforeIteration: true,
      }
    ).start();

    // Redirection vers la page de login après 8 secondes
    const timer = setTimeout(() => {
      router.replace('/auth/login');
    }, 8000);

    return () => clearTimeout(timer);
  }, []);

  return (
    <View style={styles.container}>
      <Animated.View
        style={[
          styles.logoContainer,
          { 
            opacity: fadeAnim,
            transform: [{ translateY: bounceAnim }]
          }
        ]}
      >
        <SmallPayLogo size={220} />
      </Animated.View>

      <Animated.Text style={[styles.footerText, { opacity: fadeAnim }]}>
        by Godloveshop
      </Animated.Text>
    </View>
  );
}
