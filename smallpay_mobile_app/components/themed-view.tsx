import { View, ViewProps, StyleSheet } from 'react-native';

interface ThemedViewProps extends ViewProps {}

export function ThemedView({ style, ...rest }: ThemedViewProps) {
  return (
    <View
      {...rest}
      style={[styles.default, style]}
    />
  );
}

const styles = StyleSheet.create({
  default: {
    backgroundColor: '#fff',
  },
});
