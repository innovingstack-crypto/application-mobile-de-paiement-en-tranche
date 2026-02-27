import { Animated, Easing } from 'react-native';

// Animation de fondu
export const fadeIn = (animatedValue: Animated.Value, duration: number = 300) => {
  return Animated.timing(animatedValue, {
    toValue: 1,
    duration,
    useNativeDriver: true,
  });
};

// Animation de fondu
export const fadeOut = (animatedValue: Animated.Value, duration: number = 300) => {
  return Animated.timing(animatedValue, {
    toValue: 0,
    duration,
    useNativeDriver: true,
  });
};

// Animation de glissement vers le haut
export const slideUp = (animatedValue: Animated.Value, duration: number = 300) => {
  return Animated.timing(animatedValue, {
    toValue: 0,
    duration,
    easing: Easing.out(Easing.quad),
    useNativeDriver: true,
  });
};

// Animation de glissement vers le bas
export const slideDown = (animatedValue: Animated.Value, duration: number = 300) => {
  return Animated.timing(animatedValue, {
    toValue: 100,
    duration,
    easing: Easing.in(Easing.quad),
    useNativeDriver: true,
  });
};

// Animation de scale
export const scaleIn = (animatedValue: Animated.Value, duration: number = 300) => {
  return Animated.spring(animatedValue, {
    toValue: 1,
    useNativeDriver: true,
    speed: 12,
    bounciness: 8,
  });
};

// Animation de scale
export const scaleOut = (animatedValue: Animated.Value, duration: number = 300) => {
  return Animated.spring(animatedValue, {
    toValue: 0.9,
    useNativeDriver: true,
    speed: 12,
    bounciness: 8,
  });
};

// Animation de rotation
export const rotate = (animatedValue: Animated.Value, toValue: number = 1, duration: number = 500) => {
  return Animated.timing(animatedValue, {
    toValue,
    duration,
    easing: Easing.linear,
    useNativeDriver: true,
  });
};

// Animation de pulsation
export const pulse = (animatedValue: Animated.Value, duration: number = 1000) => {
  return Animated.loop(
    Animated.sequence([
      Animated.timing(animatedValue, {
        toValue: 1.05,
        duration: duration / 2,
        easing: Easing.out(Easing.quad),
        useNativeDriver: true,
      }),
      Animated.timing(animatedValue, {
        toValue: 1,
        duration: duration / 2,
        easing: Easing.in(Easing.quad),
        useNativeDriver: true,
      }),
    ])
  );
};

// Animation de rebond
export const bounce = (animatedValue: Animated.Value, duration: number = 800) => {
  return Animated.loop(
    Animated.sequence([
      Animated.timing(animatedValue, {
        toValue: 1,
        duration: duration * 0.2,
        easing: Easing.out(Easing.quad),
        useNativeDriver: true,
      }),
      Animated.timing(animatedValue, {
        toValue: 1.2,
        duration: duration * 0.2,
        easing: Easing.out(Easing.quad),
        useNativeDriver: true,
      }),
      Animated.timing(animatedValue, {
        toValue: 1,
        duration: duration * 0.2,
        easing: Easing.in(Easing.quad),
        useNativeDriver: true,
      }),
      Animated.timing(animatedValue, {
        toValue: 1.1,
        duration: duration * 0.1,
        easing: Easing.out(Easing.quad),
        useNativeDriver: true,
      }),
      Animated.timing(animatedValue, {
        toValue: 1,
        duration: duration * 0.1,
        easing: Easing.in(Easing.quad),
        useNativeDriver: true,
      }),
    ])
  );
};

// Animation de chargement de bouton
export const buttonLoading = (animatedValue: Animated.Value, duration: number = 1500) => {
  return Animated.loop(
    Animated.sequence([
      Animated.timing(animatedValue, {
        toValue: 0,
        duration: 0,
        useNativeDriver: true,
      }),
      Animated.timing(animatedValue, {
        toValue: 1,
        duration: duration,
        easing: Easing.linear,
        useNativeDriver: true,
      }),
    ])
  );
};

export const useAnimation = (initialValue: number = 0) => {
  const animatedValue = new Animated.Value(initialValue);
  
  return {
    animatedValue,
    fadeIn: () => fadeIn(animatedValue),
    fadeOut: () => fadeOut(animatedValue),
    slideUp: () => slideUp(animatedValue),
    slideDown: () => slideDown(animatedValue),
    scaleIn: () => scaleIn(animatedValue),
    scaleOut: () => scaleOut(animatedValue),
    pulse: () => pulse(animatedValue),
    bounce: () => bounce(animatedValue),
  };
};
