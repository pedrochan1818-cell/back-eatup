<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    use HasFactory;

    protected $table = 'permiso'; // Nombre de la tabla en la BD
    protected $primaryKey = 'idpermiso'; // Clave primaria

    public $timestamps = false; // Desactivar timestamps si no usas created_at y updated_at

    protected $fillable = ['cvpermiso','nompermiso']; // Campos permitidos para asignación masiva
}
