<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = ['sku', 'nombre', 'descripcion', 'precio', 'estado'];
    public function existencias()
    {
        return $this->hasMany(Existencia::class);
    }
}
