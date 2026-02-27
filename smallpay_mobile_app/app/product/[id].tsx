import Button from '@/components/Button';
import { AppDispatch } from '@/store';
import { setCurrentOrder } from '@/store/ordersSlice';
import { Product } from '@/types';
import { calculatePaymentOptions } from '@/utils/paymentCalculations';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { ArrowLeft, Heart, Share2, ChevronRight } from 'lucide-react-native';
import React, { useEffect, useMemo, useState } from 'react';
import {
    Image,
    ScrollView,
    Text,
    TouchableOpacity,
    View,
    Alert, // Ajout de Alert pour l'option "Acheter maintenant"
} from 'react-native';
import { productDetailStyles as styles } from '@/constants/productDetail.styles';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useDispatch, useSelector } from 'react-redux';
import { BnplDetails } from '@/components/BnplDetails';


// Données statiques de fallback
const STATIC_PRODUCTS: Product[] = [
    {
        id: '1',
        name: 'iPhone 15 Pro',
        description: "Écran 6,7'' Super Retina XDR, A17 Pro, 256 Go",
        price: 950000,
        stock: 10,
        image_url: 'https://images.unsplash.com/photo-1678652197831-2d180705e625?w=600&h=600&fit=crop',
        images: [
            'https://images.unsplash.com/photo-1678652197831-2d180705e625?w=600&h=600&fit=crop',
            'https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=600&h=600&fit=crop',
            'https://images.unsplash.com/photo-1565849904461-04a58ad377e0?w=600&h=600&fit=crop',
            'https://images.unsplash.com/photo-1605236453806-6ff36851218e?w=600&h=600&fit=crop',
        ],
        category: 'Smartphones',
        is_active: true,
        created_at: new Date().toISOString(),
    },
    {
        id: '2',
        name: 'Samsung Galaxy S23',
        description: "Écran 6,6'' AMOLED, Snapdragon, 256 Go",
        price: 650000,
        stock: 5,
        image_url: 'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=600&h=600&fit=crop',
        images: [
            'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=600&h=600&fit=crop',
            'https://images.unsplash.com/photo-1605236453806-6ff36851218e?w=600&h=600&fit=crop',
            'https://images.unsplash.com/photo-1589492477829-5e65395b66cc?w=600&h=600&fit=crop',
            'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=600&h=600&fit=crop',
        ],
        category: 'Smartphones',
        is_active: true,
        created_at: new Date().toISOString(),
    },
];

export default function ProductDetailScreen() {
    const router = useRouter();
    const params = useLocalSearchParams<{ id?: string; productData?: string }>();
    const idParam = Array.isArray(params.id) ? params.id[0] : params.id;
    const productDataParam = Array.isArray(params.productData)
        ? params.productData[0]
        : params.productData;

    const dispatch = useDispatch<AppDispatch>();
    const { user } = useSelector((state: any) => state.auth || {});

    const [selectedDuration, setSelectedDuration] = useState(3);
    const [liked, setLiked] = useState(false);
    const [localProduct, setLocalProduct] = useState<Product | null>(null);
    const [currentImageIndex, setCurrentImageIndex] = useState(0);

    useEffect(() => {
        if (productDataParam) {
            try {
                const parsed = JSON.parse(productDataParam) as Product;
                setLocalProduct(parsed);
            } catch (e) {
                console.warn('Produit passé en paramètre invalide:', e);
            }
        } else if (idParam) {
            const staticMatch = STATIC_PRODUCTS.find(
                (p) => String(p.id) === String(idParam)
            );
            setLocalProduct(staticMatch || null);
        } else {
            setLocalProduct(null);
        }
    }, [idParam, productDataParam]);

    const displayProduct = useMemo(() => localProduct, [localProduct]);

    if (!idParam && !displayProduct) {
        return (
            <SafeAreaView style={styles.container}>
                <View style={styles.loadingContainer}>
                    <Text style={styles.loadingText}>Paramètre id manquant</Text>
                </View>
            </SafeAreaView>
        );
    }

    if (!displayProduct) {
        return (
            <SafeAreaView style={styles.container}>
                <View style={styles.loadingContainer}>
                    <Text style={styles.loadingText}>Produit introuvable</Text>
                </View>
            </SafeAreaView>
        );
    }

    const paymentOptions = calculatePaymentOptions(displayProduct.price);
    const selectedOption =
        paymentOptions.find((opt) => opt.duration === selectedDuration) ||
        paymentOptions[0];

    // Get all product images (main + secondary)
    const allImages = displayProduct.images || (displayProduct.image_url ? [displayProduct.image_url] : []);
    const currentImage = allImages[currentImageIndex] || displayProduct.image_url;

    const handleBuyNowOnline = () => {
        Alert.alert('Acheter maintenant', 'Redirection vers la boutique en ligne...');
        // Ici, vous pouvez implémenter la redirection vers un lien de boutique en ligne
    };

    const handleBuyWithSmallPay = () => {
        const orderData = {
            user_id: user?.id ?? 'anonymous',
            product_id: displayProduct.id,
            total_amount: selectedOption.totalPrice,
            deposit_amount: selectedOption.deposit,
            remaining_amount: selectedOption.remainingAmount,
            payment_duration: selectedDuration,
            majoration_rate: selectedOption.majorationRate / 100,
        };

        dispatch(
            setCurrentOrder({
                id: '',
                ...orderData,
                status: 'pending' as const,
                created_at: new Date().toISOString(),
                product: displayProduct,
            })
        );

        router.push(`/bnpl`); // Redirige vers la nouvelle page BNPL
    };

    return (
        <SafeAreaView style={styles.container}>
            <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
                {/* Image Section with Header Buttons */}
                <View style={styles.imageContainer}>
                    <Image
                        source={{ uri: currentImage || 'https://via.placeholder.com/300' }}
                        style={styles.image}
                        resizeMode="cover"
                    />

                    {/* Image Carousel Indicators */}
                    {allImages.length > 1 && (
                        <View style={styles.imageIndicators}>
                            {allImages.map((_, index) => (
                                <TouchableOpacity
                                    key={index}
                                    style={[
                                        styles.indicator,
                                        index === currentImageIndex && styles.indicatorActive,
                                    ]}
                                    onPress={() => setCurrentImageIndex(index)}
                                />
                            ))}
                        </View>
                    )}

                    {/* Header Buttons */}
                    <View style={styles.headerButtons}>
                        <TouchableOpacity
                            style={styles.headerButton}
                            onPress={() => router.back()}
                        >
                            <ArrowLeft size={24} color="#111827" />
                        </TouchableOpacity>
                        <View style={styles.headerButtonsRight}>
                            <TouchableOpacity
                                style={styles.headerButton}
                                onPress={() => setLiked(!liked)}
                            >
                                <Heart
                                    size={24}
                                    color={liked ? '#ef4444' : '#111827'}
                                    fill={liked ? '#ef4444' : 'none'}
                                />
                            </TouchableOpacity>
                            <TouchableOpacity style={styles.headerButton}>
                                <Share2 size={24} color="#111827" />
                            </TouchableOpacity>
                        </View>
                    </View>
                </View>

                {/* Image Carousel Thumbnails */}
                {allImages.length > 1 && (
                    <View style={styles.carouselContainer}>
                        <ScrollView
                            horizontal
                            showsHorizontalScrollIndicator={false}
                            contentContainerStyle={styles.carouselScroll}
                        >
                            {allImages.map((imageUrl, index) => (
                                <TouchableOpacity
                                    key={index}
                                    style={[
                                        styles.thumbnailContainer,
                                        index === currentImageIndex && styles.thumbnailActive,
                                    ]}
                                    onPress={() => setCurrentImageIndex(index)}
                                >
                                    <Image
                                        source={{ uri: imageUrl }}
                                        style={styles.thumbnailImage}
                                        resizeMode="cover"
                                    />
                                </TouchableOpacity>
                            ))}
                        </ScrollView>
                    </View>
                )}

                {/* Product Info */}
                <View style={styles.productInfo}>
                    <Text style={styles.productName}>{displayProduct.name}</Text>
                    <View style={styles.priceContainer}>
                        <Text style={styles.price}>{displayProduct.price.toLocaleString()} FCFA</Text>
                        <Text style={styles.strikePrice}>
                            {(displayProduct.price * 1.2).toLocaleString()} FCFA
                        </Text>
                    </View>
                    <Text style={styles.description}>{displayProduct.description}</Text>
                </View>

                {/* Buttons now inside ScrollView */}
                <View style={styles.fixedButtonsContainer}>
                    <Button
                        title="Acheter maintenant"
                        onPress={handleBuyNowOnline}
                        loading={false}
                        style={styles.buyNowButton}
                    />
                    <Button
                        title="Acheter avec SmallPay"
                        onPress={handleBuyWithSmallPay}
                        loading={false}
                        style={styles.buyWithSmallPayButton}
                    />
                </View>
            </ScrollView>
        </SafeAreaView>
    );
}

