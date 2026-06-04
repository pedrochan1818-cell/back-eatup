<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'rol'; // Nombre de la tabla en la BD
    protected $primaryKey = 'idrol'; // Clave primaria

    public $timestamps = false; // Desactivar timestamps si no usas created_at y updated_at

    protected $fillable = ['nomrol']; // Campos permitidos para asignación masiva
}
