import { productsStyles as styles } from '@/constants/products.styles';
import { useState } from 'react';
import { ArrowLeft, Filter, SlidersHorizontal, Search } from 'lucide-react-native';
import ProductCard from '@/components/ProductCard';
import { View, Text, TouchableOpacity, ScrollView, FlatList, ActivityIndicator, TextInput } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useRouter, useLocalSearchParams } from 'expo-router';

import { useProductsData } from '@/hooks/useProductsData';
import { Product } from '@/types';
import { CategoryChip } from '@/components/CategoryChip';

interface ProductsListScreenProps {
    onBack?: () => void;
    onProductClick?: (productId: string) => void;
}

export default function ProductsListScreen({ onBack, onProductClick }: ProductsListScreenProps) {
    const router = useRouter();
    const params = useLocalSearchParams<{ searchQuery?: string }>();
    const { categories, products, selectedCategory, setSelectedCategory, loading, error } = useProductsData();
    const [sortBy, setSortBy] = useState<string>('popular');
    const [searchQuery, setSearchQuery] = useState<string>(
        Array.isArray(params.searchQuery) ? params.searchQuery[0] : (params.searchQuery || '')
    );

    const handleBack = () => {
        if (onBack) {
            onBack();
        } else {
            router.back();
        }
    };

    const handleProductClick = (product: Product) => {
        if (onProductClick) {
            onProductClick(product.id);
        } else {
            // Passer le produit complet avec toutes les données
            router.push({
                pathname: '/product/[id]',
                params: {
                    id: product.id,
                    productData: JSON.stringify(product),
                },
            });
        }
    };

    // Filtrer les produits par recherche
    const filteredProducts = products.filter((product) =>
        product.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
        product.description.toLowerCase().includes(searchQuery.toLowerCase())
    );

    return (
        <SafeAreaView style={styles.container}>
            <ScrollView showsVerticalScrollIndicator={false}>
            {/* Header */}
            <View style={styles.header}>
                <TouchableOpacity onPress={handleBack}>
                    <ArrowLeft size={24} color="#111827" />
                </TouchableOpacity>
                <Text style={styles.headerTitle}>Produits</Text>
                <TouchableOpacity style={styles.filterButton}>
                    <SlidersHorizontal size={24} color="#111827" />
                </TouchableOpacity>
            </View>

            {/* Search Bar */}
            <View style={{ paddingHorizontal: 16, paddingVertical: 12 }}>
                <View style={{ flexDirection: 'row', alignItems: 'center', backgroundColor: '#f3f4f6', borderRadius: 8, paddingHorizontal: 12 }}>
                    <Search size={20} color="#9ca3af" />
                    <TextInput
                        style={{
                            flex: 1,
                            marginLeft: 8,
                            paddingVertical: 10,
                            fontSize: 14,
                            color: '#111827',
                        }}
                        placeholder="Rechercher un produit..."
                        placeholderTextColor="#9ca3af"
                        value={searchQuery}
                        onChangeText={setSearchQuery}
                    />
                </View>
            </View>

            {/* Categories */}
            <ScrollView
                horizontal
                showsHorizontalScrollIndicator={false}
                style={styles.categoriesScroll}
                contentContainerStyle={styles.categoriesContainer}
                scrollEventThrottle={16}
            >
                {categories.map((category) => (
                    <CategoryChip
                        key={category.id}
                        category={category}
                        selectedCategory={selectedCategory}
                        onPress={setSelectedCategory}
                    />
                ))}
            </ScrollView>

            {/* Filter Bar */}
            <View style={styles.filterBar}>
                <Text style={styles.productCount}>{filteredProducts.length} produits trouvés</Text>
                <TouchableOpacity style={styles.filterButtonBar}>
                    <Filter size={16} color="#111827" />
                    <Text style={styles.filterText}>Filtrer</Text>
                </TouchableOpacity>
            </View>

            {/* Error Message */}
            {error && (
                <View style={{ padding: 16, backgroundColor: '#fee2e2', marginHorizontal: 16, borderRadius: 8, marginBottom: 12 }}>
                    <Text style={{ color: '#dc2626', fontSize: 14 }}>{error}</Text>
                </View>
            )}

            {/* Loading State */}
            {loading && products.length === 0 ? (
                <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center' }}>
                    <ActivityIndicator size="large" color="#3b82f6" />
                    <Text style={{ marginTop: 12, color: '#666', fontSize: 14 }}>Chargement des produits...</Text>
                </View>
            ) : filteredProducts.length > 0 ? (
                <FlatList
                    data={filteredProducts}
                    keyExtractor={(item) => item.id}
                    numColumns={2}
                    columnWrapperStyle={styles.gridRow}
                    contentContainerStyle={styles.gridContainer}
                    renderItem={({ item }) => (
                        <View style={styles.productCardWrapper}>
                            <ProductCard
                                product={item as Product}
                                onPress={() => handleProductClick(item as Product)}
                            />
                        </View>
                    )}
                    scrollEnabled={false}
                    nestedScrollEnabled={false}
                />
            ) : (
                <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center', paddingHorizontal: 16 }}>
                    <Text style={{ color: '#9ca3af', fontSize: 16, marginTop: 20 }}>Aucun produit trouvé</Text>
                </View>
            )}
            </ScrollView>
        </SafeAreaView>
    );
}
