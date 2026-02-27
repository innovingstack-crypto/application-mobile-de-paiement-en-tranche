import { AppDispatch, RootState } from '@/store';
import { logout } from '@/store/authSlice';
import { useRouter } from 'expo-router';
import { ProfileMenuItem } from '@/components/ProfileMenuItem';
import { MenuSection } from '@/types';
import { getInitials } from '@/utils/profileUtils';
import { menuSections, logoutMenuItem, appVersion } from '@/constants/profileMenuData';
import React, { useEffect, useState } from 'react';
import { Alert, ScrollView, Text, TouchableOpacity, View, ActivityIndicator } from 'react-native';
import { profileStyles as styles } from '@/constants/profile.styles';
import { useDispatch, useSelector } from 'react-redux';
import { useUserStats } from '@/hooks/useUserStats';
import { useKYC } from '@/hooks/useKYC';
import { useTheme } from '@/context/ThemeContext';
import { ThemedScreenWrapper } from '@/components/ThemedScreenWrapper';

/**
 * Screen Profil
 * Récupère les données utilisateur et statistiques depuis le backend (smallpay_backend)
 * @component
 */
export default function ProfileScreen() {
  const router = useRouter();
  const dispatch = useDispatch<AppDispatch>();
  const { user } = useSelector((state: RootState) => state.auth);
  const { stats, loading: statsLoading } = useUserStats();
  const { getKYCStatus } = useKYC();
  const { isDarkMode, toggleTheme, colors } = useTheme();
  const [kycLabel, setKycLabel] = useState('Pas encore soumis');
  const [kycBadgeStyle, setKycBadgeStyle] = useState(styles.kycBadgeNotSubmitted);

  useEffect(() => {
    const loadKycStatus = async () => {
      try {
        const response = await getKYCStatus();

        if (!response.has_kyc || !response.kyc?.status) {
          setKycLabel('Pas encore soumis');
          setKycBadgeStyle(styles.kycBadgeNotSubmitted);
          return;
        }

        const status = response.kyc.status.toLowerCase();

        if (status === 'approved' || status === 'verified') {
          setKycLabel('Verifie');
          setKycBadgeStyle(styles.kycBadgeVerified);
          return;
        }

        if (status === 'pending') {
          setKycLabel('En attente');
          setKycBadgeStyle(styles.kycBadgePending);
          return;
        }

        if (status === 'rejected') {
          setKycLabel('Rejected');
          setKycBadgeStyle(styles.kycBadgeRejected);
          return;
        }

        setKycLabel('Pas encore soumis');
        setKycBadgeStyle(styles.kycBadgeNotSubmitted);
      } catch (error) {
        setKycLabel('Pas encore soumis');
        setKycBadgeStyle(styles.kycBadgeNotSubmitted);
      }
    };

    loadKycStatus();
  }, [getKYCStatus]);

  const handleMenuItemPress = (action: any) => {
    if (typeof action === 'string') {
      switch (action) {
        case 'personal-info':
          router.push('/profile/personal-info');
          break;
        case 'phone':
          router.push('/profile/phone');
          break;
        case 'email':
          router.push('/profile/email');
          break;
        case 'password':
          router.push('/profile/password');
          break;
        case 'favorites':
          router.push('/profile/favorites');
          break;
        case 'wishlists':
          router.push('/profile/wishlists');
          break;
        case 'bonus':
          router.push('/profile/bonus');
          break;
        case 'loyalty-points':
          router.push('/profile/loyalty-points');
          break;
        case 'suggestions':
          router.push('/profile/suggestions');
          break;
        case 'theme':
          toggleTheme();
          break;
        case 'support':
          router.push('/profile/support');
          break;
        default:
          break;
      }
    }
  };

  const handleLogout = () => {
    Alert.alert('Déconnexion', 'Êtes-vous sûr?', [
      { text: 'Annuler', style: 'cancel' },
      {
        text: 'Déconnexion',
        style: 'destructive',
        onPress: async () => {
          await dispatch(logout());
          router.replace('/auth/login');
        },
      },
    ]);
  };



  return (
    <ThemedScreenWrapper style={styles.container}>
      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Header */}
        <View style={styles.header}>
          <Text style={styles.headerTitle}>Mon profil</Text>

          {/* Profile Card */}
          <View style={styles.profileCard}>
            <View style={styles.profileTop}>
              <View style={styles.avatarLarge}>
                <Text style={styles.avatarText}>{getInitials(user?.name)}</Text>
              </View>
              <View style={styles.profileInfo}>
                <Text style={styles.profileName}>{user?.name || 'Utilisateur'}</Text>
                <Text style={styles.profilePhone}>{user?.phone}</Text>
                <View style={styles.kycRow}>
                  <Text style={styles.kycLabel}>Statut KYC</Text>
                  <View style={[styles.kycBadge, kycBadgeStyle]}>
                    <Text style={styles.kycBadgeText}>{kycLabel}</Text>
                  </View>
                </View>
              </View>
            </View>

            {/* Stats */}
            <View style={styles.statsContainer}>
              {statsLoading ? (
                <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center', paddingVertical: 12 }}>
                  <ActivityIndicator size="small" color="#3b82f6" />
                </View>
              ) : stats ? (
                <>
                  <View style={styles.statItem}>
                    <Text style={styles.statNumber}>{stats.totalOrders}</Text>
                    <Text style={styles.statLabel}>Commandes</Text>
                  </View>
                  <View style={[styles.statItem, styles.statItemBorder]}>
                    <Text style={styles.statNumberGreen}>{stats.paidOrders}</Text>
                    <Text style={styles.statLabel}>Payées</Text>
                  </View>
                  <View style={styles.statItem}>
                    <Text style={styles.statNumberOrange}>{stats.activeOrders}</Text>
                    <Text style={styles.statLabel}>En cours</Text>
                  </View>
                </>
              ) : (
                <>
                  <View style={styles.statItem}>
                    <Text style={styles.statNumber}>0</Text>
                    <Text style={styles.statLabel}>Commandes</Text>
                  </View>
                  <View style={[styles.statItem, styles.statItemBorder]}>
                    <Text style={styles.statNumberGreen}>0</Text>
                    <Text style={styles.statLabel}>Payées</Text>
                  </View>
                  <View style={styles.statItem}>
                    <Text style={styles.statNumberOrange}>0</Text>
                    <Text style={styles.statLabel}>En cours</Text>
                  </View>
                </>
              )}
            </View>
          </View>
        </View>

        {/* Menu Sections */}
        <View style={styles.menuContainer}>
          {menuSections.map((section, sectionIndex) => (
            <View key={sectionIndex} style={styles.section}>
              <Text style={styles.sectionTitle}>{section.title}</Text>
              <View style={styles.menuCard}>
                {section.items.map((item, itemIndex) => {
                  let displayValue = item.value;
                  if (item.label === 'Numéro de téléphone') {
                    displayValue = user?.phone || '';
                  } else if (item.label === 'Email') {
                    displayValue = user?.email || '';
                  } else if (item.label === 'Thème') {
                    displayValue = isDarkMode ? 'Sombre' : 'Clair';
                  }
                  
                  return (
                    <ProfileMenuItem
                      key={itemIndex}
                      icon={item.icon}
                      label={item.label}
                      value={displayValue}
                      action={() => handleMenuItemPress(item.action)}
                      isLastItem={itemIndex === section.items.length - 1}
                    />
                  );
                })}
              </View>
            </View>
          ))}

          {/* Logout Button */}
          <TouchableOpacity style={styles.logoutButton} onPress={handleLogout}>
            <View style={styles.logoutIconContainer}>
              <logoutMenuItem.icon size={20} color="#ef4444" />
            </View>
            <Text style={styles.logoutText}>{logoutMenuItem.label}</Text>
          </TouchableOpacity>

          {/* App Version */}
          <View style={styles.versionContainer}>
            <Text style={styles.versionText}>{appVersion}</Text>
          </View>
        </View>
      </ScrollView>
    </ThemedScreenWrapper>
  );
}
