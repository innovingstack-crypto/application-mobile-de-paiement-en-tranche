import { User, Phone, Mail, Lock, Heart, List, Gift, Zap, Lightbulb, Settings, HelpCircle, LogOut, ChevronRight, Moon, Sun } from 'lucide-react-native';
import { MenuSection } from '@/types';

export const menuSections: MenuSection[] = [
  {
    title: 'Compte',
    items: [
      { icon: User, label: 'Informations personnelles', action: 'personal-info', value: '' },
      { icon: Phone, label: 'Numéro de téléphone', action: 'phone', value: '' },
      { icon: Mail, label: 'Email', action: 'email', value: '' },
      { icon: Lock, label: 'Mot de passe', action: 'password', value: '' },
    ],
  },
  {
    title: 'Mes découvertes',
    items: [
      { icon: Heart, label: 'Produits favoris', action: 'favorites', value: '' },
      { icon: List, label: 'Wishlists', action: 'wishlists', value: '' },
      { icon: Gift, label: 'Bonus', action: 'bonus', value: '' },
      { icon: Zap, label: 'Points de fidélité', action: 'loyalty-points', value: '' },
      { icon: Lightbulb, label: 'Suggestions', action: 'suggestions', value: '' },
    ],
  },
  {
    title: 'Paramètres',
    items: [
      { icon: Moon, label: 'Thème', action: 'theme', value: '' },
      { icon: HelpCircle, label: 'Aide & Support', action: 'support', value: '' },
    ],
  },
];

export const logoutMenuItem = {
  icon: LogOut,
  label: 'Se déconnecter',
  action: () => {},
};

export const appVersion = "SmallPay v1.0.0";
