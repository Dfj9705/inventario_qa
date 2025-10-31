<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Almacen extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'ubicacion',
    ];

    public function existencias()
    {
        return $this->hasMany(Existencia::class);
    }
}
