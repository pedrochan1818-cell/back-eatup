<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    use HasFactory;

    protected $table = 'reservas'; 
    protected $primaryKey = 'id_reserva';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'no_persona',
        'fecha',
        'hora',
        'fecha_reserva',
        'id_mesa',
        'id_user',
        'clave',
        'orden',
        'qr_image',
        'status',
        'id_turno'
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora' => 'datetime:H:i',
        'fecha_reserva' => 'datetime'
    ];
}