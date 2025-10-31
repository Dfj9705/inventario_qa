<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Existencia extends Model
{
    use HasFactory;

    protected $fillable = [
        'producto_id',
        'almacen_id',
        'stock',
    ];

    protected $primaryKey = null;

    public $incrementing = false;

    public $timestamps = false;

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }

    protected function setKeysForSaveQuery($query)
    {
        return $query
            ->where('producto_id', $this->getAttribute('producto_id'))
            ->where('almacen_id', $this->getAttribute('almacen_id'));
    }
}
