<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'producto'; // Nombre de la tabla en la BD
    protected $primaryKey = 'idproducto'; // Clave primaria

    public $timestamps = false; // Desactivar timestamps si no usas created_at y updated_at

    protected $fillable = ['nombre', 'precio', 'foto', 'descripcion','categoria']; // Campos permitidos para asignación masiva
}
