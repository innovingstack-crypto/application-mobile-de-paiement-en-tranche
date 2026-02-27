import { StyleSheet } from 'react-native';

export const launchScreenStyles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#ffffff',
  },
  logoContainer: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  footerText: {
    position: 'absolute',
    bottom: 40,
    alignSelf: 'center',
    fontSize: 20,
    fontWeight: '500',
    color: '#64748b',
    textAlign: 'center',
    letterSpacing: 0.5,
  },
});
