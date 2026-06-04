<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrito extends Model
{
    use HasFactory;

    protected $table = 'carrito';
    protected $primaryKey = 'id'; 
    public $timestamps = false;
    protected $fillable = [ 'id_user', 'fecha', 'status','hora_comida', 'id_turno'];

}