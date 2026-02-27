import { StyleSheet } from 'react-native';

export const bnplStyles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f8fafc',
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 16,
    paddingTop: 24,
    paddingBottom: 12,
    backgroundColor: 'transparent',
    borderBottomWidth: 0,
  },
  backButton: {
    backgroundColor: 'rgba(255, 255, 255, 0.8)',
    width: 44,
    height: 44,
    borderRadius: 22,
    justifyContent: 'center',
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.2,
    shadowRadius: 4,
    elevation: 4,
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: '#1e293b',
    flex: 1,
    textAlign: 'center',
    marginLeft: -24, // Compense la largeur de l'icône de retour pour centrer le titre
  },
  bnplSection: {
    backgroundColor: '#fff',
    paddingHorizontal: 24,
    paddingVertical: 24,
    marginBottom: 16,
  },
  bnplTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#111827',
    marginBottom: 20,
  },
  scrollContent: {
    flexGrow: 1,
  },
  durationGrid: {
    flexDirection: 'row',
    gap: 12,
    marginBottom: 24,
  },
  durationButton: {
    flex: 1,
    paddingVertical: 12,
    paddingHorizontal: 8,
    borderRadius: 12,
    backgroundColor: '#f3f4f6',
    alignItems: 'center',
    justifyContent: 'center',
  },
  durationButtonActive: {
    backgroundColor: '#3b82f6',
  },
  durationButtonText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#4b5563',
  },
  durationButtonTextActive: {
    color: '#fff',
  },
  breakdownCard: {
    borderRadius: 16,
    padding: 20,
  },
  breakdownRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 12,
  },
  breakdownRowBorder: {
    paddingTop: 16,
    borderTopWidth: 1,
    borderTopColor: '#e5e7eb',
  },
  breakdownLabel: {
    fontSize: 16,
    color: '#4b5563',
  },
  breakdownValue: {
    fontSize: 16,
    fontWeight: '600',
    color: '#111827',
  },
  monthlyPayment: {
    fontSize: 20,
    fontWeight: '700',
    color: '#3b82f6',
  },
  totalPayment: {
    fontSize: 18,
    fontWeight: '700',
    color: '#111827',
  },
  fixedButton: {
    paddingHorizontal: 24,
    paddingVertical: 16,
    backgroundColor: '#fff',
    marginTop: 24, // Ajout d'une marge supérieure pour séparer du contenu précédent
  },
  productImageContainer: {
    width: '100%',
    height: 300,
    borderRadius: 16,
    overflow: 'hidden',
    marginBottom: 16,
    // backgroundColor: '#f3f4f6', // Commenté si cela cause les bandes noires
  },
  productImage: {
    width: '100%',
    height: '100%',
  },
  productInfoContainer: {
    padding: 24,
    backgroundColor: '#fff',
    marginBottom: 16,
  },
  productName: {
    fontSize: 22,
    fontWeight: '700',
    color: '#111827',
    marginBottom: 8,
  },
  productPrice: {
    fontSize: 18,
    fontWeight: '600',
    color: '#2563eb',
    marginBottom: 12,
  },
  productDescription: {
    fontSize: 14,
    color: '#4b5563',
    lineHeight: 20,
  },
  specificationsSection: {
    backgroundColor: '#f8fafc',
    borderRadius: 12,
    padding: 16,
    marginBottom: 24,
  },
  specTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#111827',
    marginBottom: 12,
  },
  specsList: {
    gap: 10,
  },
  specItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 8,
  },
  specItemLast: {
    marginBottom: 0,
  },
  specText: {
    fontSize: 14,
    color: '#4b5563',
    fontWeight: '500',
  },
  cartItemDisplay: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#fff',
    marginHorizontal: 16,
    marginBottom: 12,
    borderRadius: 12,
    padding: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.08,
    shadowRadius: 4,
    elevation: 2,
  },
  cartItemImage: {
    width: 80,
    height: 80,
    borderRadius: 8,
  },
  itemTotalPrice: {
    fontSize: 14,
    fontWeight: '700',
    color: '#2563eb',
    marginLeft: 12,
  },
});
