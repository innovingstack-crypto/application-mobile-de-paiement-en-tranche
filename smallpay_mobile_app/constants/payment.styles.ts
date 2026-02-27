import { StyleSheet } from 'react-native';

export const paymentStyles = StyleSheet.create({
  // Container
  container: {
    flex: 1,
    backgroundColor: '#FFFFFF',
  },
  centerContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 16,
  },

  // Header
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#E2E8F0',
  },
  backButton: {
    padding: 8,
    borderRadius: 8,
    justifyContent: 'center',
    alignItems: 'center',
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: '#1E293B',
    flex: 1,
    textAlign: 'center',
  },

  // Scroll Content
  scrollContent: {
    flexGrow: 1,
    paddingHorizontal: 16,
    paddingVertical: 16,
  },

  // Content Container
  contentContainer: {
    flex: 1,
    marginBottom: 24,
  },

  // ============================================
  // PAYMENT PROCESSING SCREEN
  // ============================================

  spinnerContainer: {
    alignItems: 'center',
    marginVertical: 40,
  },

  statusTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: '#1E293B',
    marginBottom: 12,
    textAlign: 'center',
  },

  statusSubtitle: {
    fontSize: 14,
    color: '#64748B',
    textAlign: 'center',
    marginBottom: 24,
    lineHeight: 20,
  },

  infoBox: {
    backgroundColor: '#EFF6FF',
    borderLeftWidth: 4,
    borderLeftColor: '#0EA5E9',
    borderRadius: 8,
    padding: 12,
    marginBottom: 24,
  },

  infoText: {
    fontSize: 13,
    color: '#0369A1',
    lineHeight: 18,
  },

  waitingContainer: {
    alignItems: 'center',
    paddingVertical: 20,
    backgroundColor: '#F1F5F9',
    borderRadius: 8,
    marginBottom: 24,
  },

  waitingTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: '#64748B',
  },

  waitingSubtitle: {
    fontSize: 12,
    color: '#94A3B8',
    marginTop: 4,
  },

  // ============================================
  // ERROR STYLES
  // ============================================

  errorIconContainer: {
    alignItems: 'center',
    marginVertical: 40,
  },

  errorBadge: {
    width: 120,
    height: 120,
    borderRadius: 60,
    backgroundColor: '#FEE2E2',
    justifyContent: 'center',
    alignItems: 'center',
  },

  successBadge: {
    width: 120,
    height: 120,
    borderRadius: 60,
    backgroundColor: '#D1FAE5',
    justifyContent: 'center',
    alignItems: 'center',
  },

  errorTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: '#DC2626',
    marginBottom: 8,
    textAlign: 'center',
  },

  errorCode: {
    fontSize: 13,
    color: '#64748B',
    textAlign: 'center',
    marginBottom: 20,
  },

  errorMessage: {
    fontSize: 14,
    color: '#DC2626',
    textAlign: 'center',
    lineHeight: 20,
  },

  errorReasonBox: {
    backgroundColor: '#FEE2E2',
    borderRadius: 8,
    padding: 12,
    marginBottom: 20,
  },

  errorReasonTitle: {
    fontSize: 12,
    fontWeight: '600',
    color: '#991B1B',
    marginBottom: 4,
  },

  errorReasonText: {
    fontSize: 13,
    color: '#7F1D1D',
    lineHeight: 18,
  },

  suggestionsBox: {
    backgroundColor: '#FEF3C7',
    borderLeftWidth: 4,
    borderLeftColor: '#F59E0B',
    borderRadius: 8,
    padding: 12,
    marginBottom: 20,
  },

  suggestionsTitle: {
    fontSize: 13,
    fontWeight: '600',
    color: '#92400E',
    marginBottom: 8,
  },

  suggestionItem: {
    fontSize: 13,
    color: '#78350F',
    marginBottom: 4,
    lineHeight: 18,
  },

  suggestionContainer: {
    backgroundColor: '#FFFBEB',
    borderLeftWidth: 4,
    borderLeftColor: '#F59E0B',
    borderRadius: 8,
    padding: 12,
    marginBottom: 20,
  },

  suggestionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 8,
  },

  suggestionTitle: {
    fontSize: 13,
    fontWeight: '600',
    color: '#92400E',
    marginLeft: 8,
  },

  suggestionText: {
    fontSize: 13,
    color: '#78350F',
    lineHeight: 18,
  },

  referenceBox: {
    backgroundColor: '#F0F9FF',
    borderRadius: 8,
    padding: 12,
    marginBottom: 20,
  },

  referenceLabel: {
    fontSize: 12,
    fontWeight: '600',
    color: '#0369A1',
    marginBottom: 4,
  },

  referenceValue: {
    fontSize: 13,
    color: '#0C4A6E',
    fontFamily: 'monospace',
    marginBottom: 4,
  },

  referenceNote: {
    fontSize: 11,
    color: '#64748B',
  },

  tipsContainer: {
    backgroundColor: '#F0F9FF',
    borderRadius: 8,
    padding: 12,
    marginBottom: 20,
  },

  tipsTitle: {
    fontSize: 13,
    fontWeight: '600',
    color: '#0369A1',
    marginBottom: 8,
  },

  tipItem: {
    fontSize: 13,
    color: '#0C4A6E',
    marginBottom: 4,
    lineHeight: 18,
  },

  // ============================================
  // SUCCESS STYLES
  // ============================================

  successIconContainer: {
    alignItems: 'center',
    marginVertical: 32,
  },

  successTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: '#10B981',
    marginBottom: 8,
    textAlign: 'center',
  },

  successSubtitle: {
    fontSize: 14,
    color: '#64748B',
    textAlign: 'center',
    marginBottom: 24,
    lineHeight: 20,
  },

  // Receipt
  receiptContainer: {
    backgroundColor: '#F8FAFC',
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#E2E8F0',
    marginBottom: 24,
    overflow: 'hidden',
  },

  receiptHeader: {
    backgroundColor: '#FFFFFF',
    paddingHorizontal: 12,
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#E2E8F0',
  },

  receiptTitle: {
    fontSize: 13,
    fontWeight: '600',
    color: '#1E293B',
  },

  receiptItem: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 12,
    paddingVertical: 10,
  },

  receiptLabel: {
    fontSize: 13,
    color: '#64748B',
    flex: 1,
  },

  receiptValue: {
    fontSize: 13,
    fontWeight: '500',
    color: '#1E293B',
  },

  receiptValueHighlight: {
    fontSize: 13,
    fontWeight: '600',
    color: '#10B981',
  },

  receiptDivider: {
    height: 1,
    backgroundColor: '#E2E8F0',
    marginHorizontal: 12,
  },

  // Schedule
  scheduleContainer: {
    backgroundColor: '#F8FAFC',
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#E2E8F0',
    marginBottom: 24,
    overflow: 'hidden',
  },

  scheduleHeader: {
    backgroundColor: '#FFFFFF',
    paddingHorizontal: 12,
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#E2E8F0',
  },

  scheduleTitle: {
    fontSize: 13,
    fontWeight: '600',
    color: '#1E293B',
  },

  scheduleItem: {
    flexDirection: 'row',
    paddingHorizontal: 12,
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#E2E8F0',
  },

  scheduleItemLeft: {
    flex: 1,
  },

  scheduleItemRight: {
    flex: 1,
  },

  scheduleItemLabel: {
    fontSize: 12,
    color: '#64748B',
    marginBottom: 4,
  },

  scheduleItemValue: {
    fontSize: 13,
    fontWeight: '600',
    color: '#1E293B',
  },

  scheduleNote: {
    paddingHorizontal: 12,
    paddingVertical: 10,
    backgroundColor: '#FFFBEB',
  },

  scheduleNoteText: {
    fontSize: 12,
    color: '#78350F',
    lineHeight: 16,
  },

  // ============================================
  // PAYMENT REVIEW SCREEN
  // ============================================

  summaryContainer: {
    backgroundColor: '#F8FAFC',
    borderRadius: 8,
    padding: 12,
    marginBottom: 24,
  },

  summaryTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: '#1E293B',
    marginBottom: 12,
  },

  itemsContainer: {
    marginBottom: 12,
  },

  itemRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 8,
    borderBottomWidth: 1,
    borderBottomColor: '#E2E8F0',
  },

  itemInfo: {
    flex: 1,
  },

  itemName: {
    fontSize: 13,
    fontWeight: '500',
    color: '#1E293B',
    marginBottom: 2,
  },

  itemQuantity: {
    fontSize: 12,
    color: '#64748B',
  },

  itemPrice: {
    fontSize: 13,
    fontWeight: '600',
    color: '#1E293B',
    marginLeft: 8,
  },

  totalsContainer: {
    marginTop: 12,
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: '#E2E8F0',
  },

  totalRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },

  totalLabel: {
    fontSize: 13,
    fontWeight: '600',
    color: '#1E293B',
  },

  totalValue: {
    fontSize: 14,
    fontWeight: '700',
    color: '#007AFF',
  },

  planContainer: {
    marginBottom: 24,
  },

  planTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: '#1E293B',
    marginBottom: 12,
  },

  depositBox: {
    backgroundColor: '#DBEAFE',
    borderRadius: 8,
    padding: 12,
    marginBottom: 12,
  },

  depositHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 8,
  },

  depositLabel: {
    fontSize: 13,
    fontWeight: '600',
    color: '#0C4A6E',
  },

  depositBadge: {
    backgroundColor: '#0284C7',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 4,
  },

  depositBadgeText: {
    fontSize: 11,
    fontWeight: '600',
    color: '#FFFFFF',
  },

  depositAmount: {
    fontSize: 18,
    fontWeight: '700',
    color: '#0C4A6E',
  },

  installmentBox: {
    backgroundColor: '#F0FDF4',
    borderRadius: 8,
    padding: 12,
  },

  installmentHeader: {
    marginBottom: 12,
  },

  installmentLabel: {
    fontSize: 13,
    fontWeight: '600',
    color: '#166534',
  },

  installmentDetails: {
    paddingTop: 8,
    borderTopWidth: 1,
    borderTopColor: 'rgba(22, 101, 52, 0.1)',
  },

  installmentRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 6,
  },

  installmentText: {
    fontSize: 13,
    color: '#166534',
  },

  installmentValue: {
    fontSize: 13,
    fontWeight: '600',
    color: '#166534',
  },

  infoTitle: {
    fontSize: 13,
    fontWeight: '600',
    color: '#92400E',
    marginBottom: 8,
  },

  infoItem: {
    fontSize: 12,
    color: '#78350F',
    marginBottom: 4,
    lineHeight: 16,
  },

  // ============================================
  // BUTTONS
  // ============================================

  buttonContainer: {
    marginBottom: 24,
    gap: 12,
  },

  secondaryButton: {
    paddingVertical: 12,
    paddingHorizontal: 16,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#E2E8F0',
    alignItems: 'center',
  },

  secondaryButtonText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#64748B',
  },

  tertiaryButton: {
    paddingVertical: 10,
    paddingHorizontal: 16,
    borderRadius: 8,
    alignItems: 'center',
  },

  tertiaryButtonText: {
    fontSize: 13,
    fontWeight: '500',
    color: '#64748B',
    textDecorationLine: 'underline',
  },
});
