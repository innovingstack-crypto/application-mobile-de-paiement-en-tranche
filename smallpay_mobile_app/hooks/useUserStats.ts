import { useEffect, useState } from 'react';
import { useSelector } from 'react-redux';
import { RootState } from '@/store';
import { UserService, UserStats } from '@/lib/userService';

export const useUserStats = () => {
  const { user } = useSelector((state: RootState) => state.auth);
  const [stats, setStats] = useState<UserStats | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  // Récupérer les stats depuis le backend
  useEffect(() => {
    if (!user?.id) return;

    const fetchStats = async () => {
      setLoading(true);
      setError(null);
      
      try {
        const result = await UserService.getUserStats();
        
        if (result.success && result.data) {
          setStats(result.data as UserStats);
        } else {
          // Fallback: calculer les stats depuis les commandes si l'API échoue
          setStats(null);
          setError(result.error || 'Erreur lors de la récupération des statistiques');
        }
      } catch (err: any) {
        setError(err.message || 'Erreur lors de la récupération des statistiques');
      } finally {
        setLoading(false);
      }
    };

    fetchStats();
  }, [user?.id]);

  return {
    stats,
    loading,
    error,
  };
};
