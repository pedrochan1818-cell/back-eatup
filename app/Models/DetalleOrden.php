<?php
// app/Models/Carrito.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleOrden extends Model
{
    use HasFactory;

    protected $table = 'detalle_orden';
    public $timestamps = false;
    protected $fillable = ['id', 'iduser','idproducto', 'cantidad', 'precio_unitario','subtotal', 'id_orden', 'numero_pedido', 'status_pagado',  'metodo_pago'];

    // Añade esta relación
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'idproducto');
    }
}
