import { Stack } from 'expo-router';

export default function ProfileLayout() {
  return (
    <Stack
      screenOptions={{
        headerShown: false,
      }}
    >
      <Stack.Screen name="personal-info" />
      <Stack.Screen name="phone" />
      <Stack.Screen name="email" />
      <Stack.Screen name="password" />
      <Stack.Screen name="favorites" />
      <Stack.Screen name="wishlists" />
      <Stack.Screen name="bonus" />
      <Stack.Screen name="loyalty-points" />
      <Stack.Screen name="suggestions" />
      <Stack.Screen name="support" />
    </Stack>
  );
}
