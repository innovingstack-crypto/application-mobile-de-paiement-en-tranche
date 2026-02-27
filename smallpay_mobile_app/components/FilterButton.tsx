import { Text, TouchableOpacity } from 'react-native';
import { ordersStyles as styles } from '@/constants/orders.styles';
import { FilterStatus } from '@/types';
import { getFilterColor } from '@/utils/orderUtils';

interface FilterButtonProps {
  status: FilterStatus;
  label: string;
  count: number;
  filterStatus: FilterStatus;
  onPress: (status: FilterStatus) => void;
}

export function FilterButton({ status, label, count, filterStatus, onPress }: FilterButtonProps) {
  return (
    <TouchableOpacity
      style={[
        styles.filterButton,
        filterStatus === status && { backgroundColor: getFilterColor(status) }
      ]}
      onPress={() => onPress(status)}
    >
      <Text style={[styles.filterButtonText, filterStatus === status && styles.filterButtonTextActive]}>
        {label} ({count})
      </Text>
    </TouchableOpacity>
  );
}
