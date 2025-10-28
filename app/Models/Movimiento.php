<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class Movimiento extends Model
{
    use HasFactory;

    public const TIPO_ENTRADA = 'entrada';
    public const TIPO_SALIDA = 'salida';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'producto_id',
        'almacen_id',
        'tipo',
        'cantidad',
        'descripcion',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cantidad' => 'integer',
    ];

    /**
     * Get the product associated with the movement.
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    /**
     * Get the warehouse associated with the movement.
     */
    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class);
    }

    /**
     * Calculate the stock for a product in a warehouse.
     */
    public static function stockPara(int $productoId, int $almacenId): int
    {
        return (int) static::query()
            ->where('producto_id', $productoId)
            ->where('almacen_id', $almacenId)
            ->selectRaw("COALESCE(SUM(CASE WHEN tipo = '" . self::TIPO_ENTRADA . "' THEN cantidad ELSE -cantidad END), 0) as total")
            ->value('total');
    }

    /**
     * Build a collection with the stock grouped by product and warehouse.
     */
    public static function stockAgrupado(): Collection
    {
        return static::query()
            ->select('producto_id', 'almacen_id')
            ->selectRaw("COALESCE(SUM(CASE WHEN tipo = '" . self::TIPO_ENTRADA . "' THEN cantidad ELSE -cantidad END), 0) as total")
            ->groupBy('producto_id', 'almacen_id')
            ->orderBy('producto_id')
            ->orderBy('almacen_id')
            ->with(['producto', 'almacen'])
            ->get()
            ->map(function (self $movimiento) {
                return [
                    'producto_id' => $movimiento->producto_id,
                    'producto' => $movimiento->producto?->nombre,
                    'almacen_id' => $movimiento->almacen_id,
                    'almacen' => $movimiento->almacen?->nombre,
                    'stock' => (int) $movimiento->total,
                ];
            })
            ->values();
    }
}
