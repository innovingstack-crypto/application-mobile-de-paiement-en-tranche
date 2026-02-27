type FilterStatus = 'all' | 'en_cours' | 'paye' | 'retard';

export function getFilterColor(status: FilterStatus): string {
  switch (status) {
    case 'all':
      return '#3b82f6';
    case 'en_cours':
      return '#f97316';
    case 'retard':
      return '#ef4444';
    case 'paye':
      return '#22c55e';
    default:
      return '#3b82f6';
  }
}

export function getStatusColor(status: 'pending' | 'active' | 'completed' | 'cancelled'): string {
  switch (status) {
    case 'completed':
      return 'rgba(16, 185, 129, 0.7)'; // Vert, 70% d'opacité
    case 'active':
      return 'rgba(249, 115, 22, 0.7)'; // Orange, 70% d'opacité
    case 'cancelled':
      return 'rgba(239, 68, 68, 0.81)'; // Rouge, 70% d'opacité
    case 'pending':
      return 'rgba(239, 68, 68, 0.7)'; // Rouge, 70% d'opacité (pour Retard)
    default:
      return 'rgba(245, 158, 11, 0.7)'; // Jaune/Ambre, 70% d'opacité
  }
}

export function getStatusText(status: 'pending' | 'active' | 'completed' | 'cancelled'): string {
  switch (status) {
    case 'completed':
      return 'Terminée';
    case 'active':
      return 'En cours';
    case 'cancelled':
      return 'Annulée';
    case 'pending':
      return 'Retard';
    default:
      return 'En attente';
  }
}
