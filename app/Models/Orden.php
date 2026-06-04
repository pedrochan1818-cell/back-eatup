<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orden extends Model
{
    use HasFactory;

    protected $table = 'orden';
    protected $primaryKey = 'id'; // Clave primaria personalizada
    public $timestamps = false;protected $fillable = [ 'id_user', 'id_reserva', 'fecha', 'status','hora_comida', 'id_turno'];

}