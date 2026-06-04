<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Turno extends Model
{
    use HasFactory;

    protected $table = 'turno';
    protected $primaryKey = 'id_turno';
    public $timestamps = false;

    protected $fillable = [
        'id_empresa',
        'nombre',
        'hora_inicio',
        'hora_fin',
        'status'
    ];
}
