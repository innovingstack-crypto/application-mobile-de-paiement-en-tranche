import React, { ReactElement } from 'react';
import {
    StyleSheet,
    Text,
    TextInput,
    TextInputProps,
    View,
    ViewStyle,
    TouchableOpacity,
} from 'react-native';

interface InputProps extends TextInputProps {
  label?: string;
  error?: string;
  containerStyle?: ViewStyle;
  leftIcon?: ReactElement;
  rightIcon?: ReactElement;
  showPasswordToggle?: boolean;
  isPasswordVisible?: boolean;
  onTogglePasswordVisibility?: () => void;
}

export default function Input({
  label,
  error,
  containerStyle,
  leftIcon,
  rightIcon,
  showPasswordToggle,
  isPasswordVisible,
  onTogglePasswordVisibility,
  secureTextEntry,
  ...props
}: InputProps) {
  return (
    <View style={[styles.container, containerStyle]}>
      {label && <Text style={styles.label}>{label}</Text>}
      <View style={styles.inputWrapper}>
        {leftIcon && <View style={styles.iconContainer}>{leftIcon}</View>}
        <TextInput
          style={[styles.input, leftIcon && styles.inputWithIcon, (rightIcon || showPasswordToggle) && styles.inputWithRightIcon, error && styles.inputError]}
          placeholderTextColor="#94a3b8"
          secureTextEntry={showPasswordToggle && isPasswordVisible !== undefined ? !isPasswordVisible : secureTextEntry}
          {...props}
        />
        {showPasswordToggle && onTogglePasswordVisibility && (
          <TouchableOpacity
            onPress={onTogglePasswordVisibility}
            style={styles.rightIconContainer}
          >
            {rightIcon}
          </TouchableOpacity>
        )}
        {rightIcon && !showPasswordToggle && (
          <View style={styles.rightIconContainer}>{rightIcon}</View>
        )}
      </View>
      {error && <Text style={styles.error}>{error}</Text>}
    </View>
  );
}

const styles = StyleSheet.create({
   container: {
     marginBottom: 16,
   },
   label: {
     fontSize: 14,
     fontWeight: '600',
     color: '#1e293b',
     marginBottom: 8,
   },
   inputWrapper: {
     flexDirection: 'row',
     alignItems: 'center',
   },
   iconContainer: {
     paddingLeft: 12,
     paddingRight: 4,
     justifyContent: 'center',
     alignItems: 'center',
   },
   rightIconContainer: {
     paddingRight: 12,
     paddingLeft: 4,
     justifyContent: 'center',
     alignItems: 'center',
   },
   input: {
     flex: 1,
     borderWidth: 1,
     borderColor: '#e2e8f0',
     borderRadius: 12,
     paddingHorizontal: 16,
     paddingVertical: 14,
     fontSize: 16,
     color: '#1e293b',
     backgroundColor: '#fff',
   },
   inputWithIcon: {
     paddingLeft: 8,
   },
   inputWithRightIcon: {
     paddingRight: 8,
   },
   inputError: {
     borderColor: '#ef4444',
   },
   error: {
     color: '#ef4444',
     fontSize: 12,
     marginTop: 4,
   },
});
