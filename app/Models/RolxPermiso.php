<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rolxpermiso extends Model
{
    use HasFactory;

    protected $table = 'rolxpermiso'; // Nombre de la tabla en la BD
    protected $primaryKey = 'idrolxpermiso'; // Clave primaria

    public $timestamps = false; // Desactivar timestamps si no usas created_at y updated_at

    protected $fillable = ['idrol','idpermiso']; // Campos permitidos para asignación masiva
}
