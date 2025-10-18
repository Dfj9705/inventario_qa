<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    use HasFactory;
    protected static function booted()
    {
        static::created(function ($mov) {
            $ex = Existencia::firstOrCreate([
                'producto_id' => $mov->producto_id,
                'almacen_id' => $mov->almacen_id,
            ]);
            $delta = $mov->tipo === 'IN' ? $mov->cantidad : -$mov->cantidad;
            $ex->stock = max(0, $ex->stock + $delta);
            $ex->save();
        });
    }

}
