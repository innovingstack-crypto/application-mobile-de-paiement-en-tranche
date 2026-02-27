<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = strtolower(trim((string) $value));
    }
}
