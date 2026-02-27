import { StyleSheet, Dimensions } from 'react-native';

const screenWidth = Dimensions.get('window').width;

export const productsStyles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f9fafb',
  },
  header: {
    backgroundColor: '#fff',
    borderBottomWidth: 1,
    borderBottomColor: '#e5e7eb',
    paddingHorizontal: 24,
    paddingTop: 24,
    paddingBottom: 16,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  headerTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: '#111827',
  },
  filterButton: {
    padding: 8,
  },
  categoriesContainer: {
    paddingHorizontal: 24,
    paddingVertical: 8,
    backgroundColor: '#fff',
    alignItems: 'center',
  },
  categoriesScroll: {
    backgroundColor: '#fff',
    flexGrow: 0,
  },
  categoryChip: {
    paddingHorizontal: 14,
    paddingVertical: 6,
    borderRadius: 20,
    marginRight: 12,
    backgroundColor: '#f3f4f6',
    justifyContent: 'center',
    alignItems: 'center',
    flexShrink: 0,
    height: 32,
  },
  categoryChipActive: {
    backgroundColor: '#3b82f6',
  },
  categoryLabel: {
    fontSize: 13,
    fontWeight: '600',
    color: '#4b5563',
    textAlign: 'center',
    textAlignVertical: 'center',
    flexShrink: 1,
  },
  categoryLabelActive: {
    color: '#fff',
  },
  filterBar: {
    paddingHorizontal: 24,
    paddingVertical: 4,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  productCount: {
    fontSize: 14,
    color: '#6b7280',
  },
  filterButtonBar: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  filterText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#111827',
  },
  gridContainer: {
    paddingHorizontal: 16,
    paddingBottom: 24,
    paddingTop: 0,
  },
  gridRow: {
    justifyContent: 'space-between',
    marginBottom: 8,
  },
  productCardWrapper: {
    flex: 1,
    marginHorizontal: 8,
  },
});
