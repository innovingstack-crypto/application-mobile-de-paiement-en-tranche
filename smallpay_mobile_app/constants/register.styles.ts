import { StyleSheet } from 'react-native';

export const registerStyles = StyleSheet.create({
  header: {
    backgroundColor: '#2563eb', // More transparent for status bar visibility
    paddingTop: 60,
    paddingBottom: 80,
    paddingHorizontal: 24,
    borderBottomLeftRadius: 32,
    borderBottomRightRadius: 32,
  },
  logoWrapper: {
    alignSelf: 'center',
    marginBottom: 16,
    marginTop: -8,
  },
  headerSubtitle: {
    fontSize: 14,
    color: '#e0f2fe',
    textAlign: 'center',
    marginTop: 5,
  },
  backButton: {
    marginBottom: 24,
  },
  headerTitle: {
    fontSize: 32,
    fontWeight: '800',
    color: '#fff',
    lineHeight: 38,
  },
  card: {
    flex: 1,
    marginTop: -40,
    backgroundColor: '#fff',
    borderTopLeftRadius: 32,
    borderTopRightRadius: 32,
    padding: 24,
  },
  registerButton: {
    marginTop: 16,
  },
  loginContainer: {
    flexDirection: 'row',
    justifyContent: 'center',
    marginTop: 24,
  },
  loginText: {
    color: '#64748b',
  },
  loginLink: {
    color: '#2563eb',
    fontWeight: '700',
  },
  errorText: {
    color: '#ef4444',
    fontSize: 12,
    marginTop: -8,
    marginBottom: 8,
    marginLeft: 5,
  },
  verificationMethodContainer: {
    marginVertical: 20,
    padding: 15,
    backgroundColor: '#f8fafc',
    borderRadius: 10,
    borderWidth: 1,
    borderColor: '#e2e8f0',
  },
  verificationMethodTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#1e293b',
    marginBottom: 5,
    textAlign: 'center',
  },
  verificationMethodSubtitle: {
    fontSize: 14,
    color: '#64748b',
    marginBottom: 15,
    textAlign: 'center',
  },
  methodOptions: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    gap: 10,
  },
  methodOption: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    padding: 12,
    borderRadius: 10,
    borderWidth: 1,
    borderColor: '#cbd5e1',
    backgroundColor: '#f1f5f9',
  },
  methodOptionActive: {
    backgroundColor: '#2563eb',
    borderColor: '#2563eb',
  },
  methodOptionText: {
    marginLeft: 8,
    fontSize: 14,
    fontWeight: '500',
    color: '#2563eb',
  },
  methodOptionTextActive: {
    color: '#ffffff',
  },
});
