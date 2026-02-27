import React from 'react';
import { View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { VirtualAssistantFAB } from './VirtualAssistantFAB';
import { useTheme } from '@/context/ThemeContext';

interface ThemedScreenWrapperProps {
  children: React.ReactNode;
  style?: any;
}

export function ThemedScreenWrapper({ children, style }: ThemedScreenWrapperProps) {
  const { colors } = useTheme();

  return (
    <View style={{ flex: 1, backgroundColor: colors.background }}>
      <SafeAreaView
        style={{
          flex: 1,
          backgroundColor: colors.background,
          ...style,
        }}
      >
        {children}
      </SafeAreaView>
      <VirtualAssistantFAB />
    </View>
  );
}
