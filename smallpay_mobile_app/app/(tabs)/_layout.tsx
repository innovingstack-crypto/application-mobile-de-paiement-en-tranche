import { Tabs } from 'expo-router';
import type { BottomTabBarButtonProps } from '@react-navigation/bottom-tabs';
import { Home, ShoppingBag, User, Package } from 'lucide-react-native';
import { Pressable, StyleSheet } from 'react-native';
import { TAB_BAR_OPTIONS } from '@/constants/navigation';

function RoundedTabButton({ accessibilityState, style, children, onPress, onLongPress }: BottomTabBarButtonProps) {
  const isFocused = accessibilityState?.selected === true;

  return (
    <Pressable
      onPress={onPress}
      onLongPress={onLongPress}
      style={[style, styles.tabButton, isFocused && styles.tabButtonActive]}
    >
      {children}
    </Pressable>
  );
}

export default function TabLayout() {
  return (
    <Tabs
      screenOptions={{
        ...TAB_BAR_OPTIONS,
        tabBarButton: (props) => <RoundedTabButton {...props} />,
      }}
    >
      <Tabs.Screen name="index" options={{ title: 'Accueil', tabBarIcon: ({ size, color }) => <Home size={size} color={color} /> }} />
      <Tabs.Screen name="products" options={{ title: 'Produits', tabBarIcon: ({ size, color }) => <Package size={size} color={color} /> }} />
      <Tabs.Screen name="orders" options={{ title: 'Commandes', tabBarIcon: ({ size, color }) => <ShoppingBag size={size} color={color} /> }} />
      <Tabs.Screen name="profile" options={{ title: 'Profil', tabBarIcon: ({ size, color }) => <User size={size} color={color} /> }} />
    </Tabs>
  );
}

const styles = StyleSheet.create({
  tabButton: {
    flex: 1,
    marginHorizontal: 2,
    marginVertical: 2,
    borderRadius: 0,
    overflow: 'visible',
    alignItems: 'center',
    justifyContent: 'center',
  },
  tabButtonActive: {
    backgroundColor: '#ffffff',
  },
});
