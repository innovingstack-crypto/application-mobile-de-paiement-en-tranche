import React from 'react';
import { Text, TouchableOpacity, View } from 'react-native';
import { ChevronRight } from 'lucide-react-native';
import { profileStyles as styles } from '@/constants/profile.styles';

interface ProfileMenuItemProps {
  icon: any;
  label: string;
  value?: string;
  action: () => void;
  isLastItem: boolean;
}

export function ProfileMenuItem({ icon: Icon, label, value, action, isLastItem }: ProfileMenuItemProps) {
  return (
    <TouchableOpacity
      style={[
        styles.menuItem,
        !isLastItem && styles.menuItemBorder,
      ]}
      onPress={action}
    >
      <View style={styles.iconContainer}>
        <Icon size={20} color="#3b82f6" />
      </View>
      <View style={styles.menuItemContent}>
        <Text style={styles.menuLabel}>{label}</Text>
        {value && <Text style={styles.menuValue}>{value}</Text>}
      </View>
      <ChevronRight size={20} color="#9ca3af" />
    </TouchableOpacity>
  );
}
