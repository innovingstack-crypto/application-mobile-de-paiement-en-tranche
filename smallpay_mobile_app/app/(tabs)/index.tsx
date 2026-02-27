import ProductCard from '@/components/ProductCard';
import { Product } from '@/types';
import { useRouter } from 'expo-router';
import React, { useState } from 'react';
import {
    FlatList,
    RefreshControl,
    TouchableOpacity,
    View,
} from 'react-native';
import { HomeHeader } from '@/components/HomeHeader';
import { QuickActions } from '@/components/QuickActions';
import { SectionHeader } from '@/components/SectionHeader';
import { useHomeData } from '@/hooks/useHomeData';
import { homeStyles as styles } from '@/constants/home.styles';
import { ThemedScreenWrapper } from '@/components/ThemedScreenWrapper';

export default function HomeScreen() {
    const router = useRouter();
    const { user, products, loading, refreshing, onRefresh, featuredProducts } = useHomeData();
    const [searchQuery, setSearchQuery] = useState<string>('');

    const handleViewAll = () => {
        router.push('/(tabs)/products');
    };

    const handleProductClick = (product: Product) => {
        router.push({
            pathname: '/product/[id]',
            params: {
                id: product.id,
                productData: JSON.stringify(product),
            },
        });
    };

    // Filtrer les produits par recherche
    const filteredProducts = featuredProducts.filter((product) =>
        product.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
        product.description.toLowerCase().includes(searchQuery.toLowerCase())
    );

    return (
        <ThemedScreenWrapper style={styles.container}>
            <FlatList
                data={filteredProducts}
                renderItem={({ item }) => (
                    <ProductCard product={item} onPress={() => handleProductClick(item)} />
                )}
                keyExtractor={(item: Product) => item.id}
                numColumns={2}
                columnWrapperStyle={styles.row}
                contentContainerStyle={styles.productList}
                ListHeaderComponent={
                    <View style={styles.headerContainer}>
                        <HomeHeader userName={user?.name} userAvatar={(user as any)?.avatar} onSearchChange={setSearchQuery} />
                        <QuickActions />
                        <SectionHeader title="Produits populaires" onPressViewAll={handleViewAll} />
                    </View>
                }
                refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
                scrollEnabled={true}
            />
        </ThemedScreenWrapper>
    );
}
