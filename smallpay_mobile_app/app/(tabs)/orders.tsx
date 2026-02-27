import React, { useState } from 'react';
import { FlatList, Text, TextInput, TouchableOpacity, View, ScrollView, ActivityIndicator } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Search, Filter } from 'lucide-react-native';
import OrderCard from '@/components/OrderCard';
import { ordersStyles as styles } from '@/constants/orders.styles';
import { useOrdersData } from '@/hooks/useOrdersData';
import { FilterStatus, Order } from '@/types';
import { useRouter } from 'expo-router';
import { getFilterColor } from '@/utils/orderUtils';

export default function OrdersScreen() {
  const router = useRouter();
  const { filterStatus, setFilterStatus, searchText, setSearchText, filteredAndSearched, statusCounts, loading, error } = useOrdersData();

  return (
    <SafeAreaView style={styles.container}>
      {/* Header */}
      <View style={styles.headerGradient}>
        <Text style={styles.headerTitle}>Mes commandes</Text>
        
        {/* Search Bar */}
        <View style={styles.searchContainer}>
          <Search size={20} color="#9ca3af" />
          <TextInput
            style={styles.searchInput}
            placeholder="Rechercher une commande..."
            placeholderTextColor="#9ca3af"
            value={searchText}
            onChangeText={setSearchText}
          />
          <TouchableOpacity style={styles.filterButton}>
            <Filter size={20} color="#4b5563" />
          </TouchableOpacity>
        </View>
      </View>

      {/* Filter Tabs */}
      <View style={styles.filterTabsContainer}>
        <View style={styles.filterGrid}>
          <TouchableOpacity
            onPress={() => setFilterStatus('all')}
            style={[
              styles.filterTabButton,
              filterStatus === 'all' && styles.filterTabButtonActive,
              filterStatus === 'all' && { backgroundColor: getFilterColor('all') },
            ]}
          >
            <Text
              style={[
                styles.filterTabText,
                filterStatus === 'all' && styles.filterTabTextActive,
              ]}
            >
              Tout ({statusCounts.all})
            </Text>
          </TouchableOpacity>
          <TouchableOpacity
            onPress={() => setFilterStatus('en_cours')}
            style={[
              styles.filterTabButton,
              filterStatus === 'en_cours' && styles.filterTabButtonActive,
              filterStatus === 'en_cours' && { backgroundColor: getFilterColor('en_cours') },
            ]}
          >
            <Text
              style={[
                styles.filterTabText,
                filterStatus === 'en_cours' && styles.filterTabTextActive,
              ]}
            >
              En cours ({statusCounts.en_cours})
            </Text>
          </TouchableOpacity>
          <TouchableOpacity
            onPress={() => setFilterStatus('retard')}
            style={[
              styles.filterTabButton,
              filterStatus === 'retard' && styles.filterTabButtonActive,
              filterStatus === 'retard' && { backgroundColor: getFilterColor('retard') },
            ]}
          >
            <Text
              style={[
                styles.filterTabText,
                filterStatus === 'retard' && styles.filterTabTextActive,
              ]}
            >
              Retard ({statusCounts.retard})
            </Text>
          </TouchableOpacity>
          <TouchableOpacity
            onPress={() => setFilterStatus('paye')}
            style={[
              styles.filterTabButton,
              filterStatus === 'paye' && styles.filterTabButtonActive,
              filterStatus === 'paye' && { backgroundColor: getFilterColor('paye') },
            ]}
          >
            <Text
              style={[
                styles.filterTabText,
                filterStatus === 'paye' && styles.filterTabTextActive,
              ]}
            >
              Payé ({statusCounts.paye})
            </Text>
          </TouchableOpacity>
        </View>
      </View>

      {/* Error Message */}
      {error && (
        <View style={{ padding: 16, backgroundColor: '#fee2e2', marginHorizontal: 16, borderRadius: 8, marginBottom: 12 }}>
          <Text style={{ color: '#dc2626', fontSize: 14 }}>{error}</Text>
        </View>
      )}

      {/* Orders List */}
      <ScrollView contentContainerStyle={styles.ordersListContainer}>
        {loading && filteredAndSearched.length === 0 ? (
          <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center', paddingTop: 40 }}>
            <ActivityIndicator size="large" color="#3b82f6" />
            <Text style={{ marginTop: 12, color: '#666', fontSize: 14 }}>Chargement des commandes...</Text>
          </View>
        ) : filteredAndSearched.length > 0 ? (
          filteredAndSearched.map((order) => (
            <OrderCard
              key={order.id}
              order={order}
              onPress={() => {
                router.push(`/order/${order.id}?orderData=${encodeURIComponent(JSON.stringify(order))}`);
              }}
            />
          ))
        ) : (
          <View style={styles.emptyStateContainer}>
            <View style={styles.emptyIconBackground}>
              <Search size={40} color="#9ca3af" />
            </View>
            <Text style={styles.emptyText}>Aucune commande trouvée</Text>
          </View>
        )}
      </ScrollView>
    </SafeAreaView>
  );
}
