<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Models\OrderItem;
use Carbon\Carbon;

/**
 * Product Model
 * 
 * Représente un produit du catalogue SmallPay
 * 
 * @property int $id
 * @property string $name
 * @property string $description
 * @property float $price
 * @property int $stock
 * @property string $category
 * @property string|null $image_url (Image principale)
 * @property array|null $secondary_images (Images secondaires)
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Product extends Model
{
    use HasFactory;

    /**
     * Table associée au modèle
     */
    protected $table = 'products';

    /**
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'created_by',
        'name',
        'description',
        'price',
        'stock',
        'category',
        'image_url',
        'secondary_images',
        'is_active',
        'is_featured',
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'price' => 'float',
        'stock' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'secondary_images' => 'array',
        'created_at' => 'datetime:Y-m-d\TH:i:s.000\Z',
        'updated_at' => 'datetime:Y-m-d\TH:i:s.000\Z',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Les attributs qui doivent être visibles dans les réponses JSON
     */
    protected $visible = [
        'id',
        'name',
        'description',
        'price',
        'stock',
        'category',
        'image_url',
        'secondary_images',
        'is_active',
        'is_featured',
        'created_at',
    ];

    /**
     * Relation : Éléments de commande pour ce produit
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relation : Commandes pour ce produit (via order_items)
     */
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_items');
    }

    /**
     * Scope : Produits actifs uniquement
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope : Produits mis en avant
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope : Filtrer par catégorie
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope : Recherche par nom ou description
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where('name', 'like', "%{$term}%")
                     ->orWhere('description', 'like', "%{$term}%");
    }

    /**
     * Scope : Tri par popularité (nombre de commandes)
     */
    public function scopePopular($query)
    {
        return $query->withCount('orders')
                     ->orderByDesc('orders_count');
    }

    /**
     * Scope : Tri par prix croissant
     */
    public function scopePriceAsc($query)
    {
        return $query->orderBy('price', 'asc');
    }

    /**
     * Scope : Tri par prix décroissant
     */
    public function scopePriceDesc($query)
    {
        return $query->orderBy('price', 'desc');
    }

    /**
     * Scope : Tri par date (plus récent d'abord)
     */
    public function scopeNewest($query)
    {
        return $query->orderByDesc('created_at');
    }

    /**
     * Accesseur : Formater le prix en XAF
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 2, ',', ' ') . ' XAF';
    }

    /**
     * Accesseur : Vérifier si le produit est en stock
     */
    public function getIsInStockAttribute(): bool
    {
        return $this->stock > 0;
    }

    /**
     * Accesseur : URL complète de l'image principale (image_url)
     */
    public function getImageUrlAttribute($value): ?string
    {
        if (!$value) {
            return null;
        }

        // Si c'est déjà une URL complète
        if (str_starts_with($value, 'http')) {
            return $value;
        }

        // Sinon, construire l'URL complète
        return asset('storage/' . ltrim($value, '/'));
    }

    /**
     * Accesseur : URLs complètes des images secondaires
     */
    public function getSecondaryImagesAttribute($value): array
    {
        if (!$value) {
            return [];
        }

        // Si c'est une chaîne JSON, la décoder
        if (is_string($value)) {
            $value = json_decode($value, true) ?? [];
        }

        // Transformer chaque image en URL complète
        return array_map(function ($image) {
            if (is_array($image)) {
                $image = $image['path'] ?? $image;
            }

            if (str_starts_with($image, 'http')) {
                return $image;
            }

            return asset('storage/' . ltrim($image, '/'));
        }, (array)$value);
    }

    /**
     * Mutateur : Assurer que le stock ne soit jamais négatif
     */
    public function setStockAttribute($value)
    {
        $this->attributes['stock'] = max(0, (int)$value);
    }

    /**
     * Mutateur : Normaliser le prix
     */
    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = (float)$value;
    }

    /**
     * Mutateur : Normaliser la catégorie (minuscule)
     */
    public function setCategoryAttribute($value)
    {
        $this->attributes['category'] = strtolower($value);
    }

    /**
     * Obtenir toutes les catégories uniques
     */
    public static function getCategories(): array
    {
        return self::distinct()
                   ->pluck('category')
                   ->sort()
                   ->values()
                   ->toArray();
    }

    /**
     * Obtenir les statistiques de vente du produit
     */
    public function getSalesStats()
    {
        return [
            'total_orders' => $this->orders()->count(),
            'total_revenue' => $this->orders()->sum('total_amount'),
            'active_orders' => $this->orders()->where('status', 'active')->count(),
            'completed_orders' => $this->orders()->where('status', 'completed')->count(),
        ];
    }

    /**
     * Mutateur : Assurer que secondary_images est un tableau JSON valide
     */
    public function setSecondaryImagesAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['secondary_images'] = json_encode($value);
        } else {
            $this->attributes['secondary_images'] = $value;
        }
    }

    /**
     * Convertir le modèle en tableau pour l'API
     */
    public function toArray(): array
    {
        return [
            'id' => (string)$this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'stock' => $this->stock,
            'category' => $this->category,
            'image_url' => $this->image_url,
            'secondary_images' => $this->secondary_images ?? [],
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}