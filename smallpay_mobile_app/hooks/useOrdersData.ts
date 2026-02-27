import { useEffect, useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { FilterStatus, Order } from '@/types';
import { AppDispatch, RootState } from '@/store';
import { fetchOrders } from '@/store/ordersSlice';

export const useOrdersData = () => {
  const dispatch = useDispatch<AppDispatch>();
  const { user } = useSelector((state: any) => state.auth || {});
  const { orders, loading, error } = useSelector((state: RootState) => state.orders);

  const [filterStatus, setFilterStatus] = useState<FilterStatus>('all');
  const [searchText, setSearchText] = useState('');

  // Fetch orders when component mounts or user changes
  useEffect(() => {
    if (user?.id) {
      dispatch(fetchOrders(user.id));
    }
  }, [user?.id, dispatch]);

  // Use API data only
  const displayOrders = orders;

  const filteredOrders = filterStatus === 'all'
    ? displayOrders
    : displayOrders.filter(order => {
        if (filterStatus === 'en_cours') return order.status === 'active';
        if (filterStatus === 'retard') return order.status === 'pending';
        if (filterStatus === 'paye') return order.status === 'completed';
        return false;
      });

  const filteredAndSearched = filteredOrders.filter((order) => {
    return !searchText || 
      order.product?.name?.toLowerCase().includes(searchText.toLowerCase()) ||
      order.id?.toLowerCase().includes(searchText.toLowerCase());
  });

  const statusCounts = {
    all: displayOrders.length,
    en_cours: displayOrders.filter(o => o.status === 'active').length,
    paye: displayOrders.filter(o => o.status === 'completed').length,
    retard: displayOrders.filter(o => o.status === 'pending').length,
  };

  return {
    filterStatus,
    setFilterStatus,
    searchText,
    setSearchText,
    filteredAndSearched,
    statusCounts,
    loading,
    error,
  };
};
