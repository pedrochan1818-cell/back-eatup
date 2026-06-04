<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $table = 'planes';
    protected $primaryKey = 'id_plan';
    public $timestamps = false; 
    
    protected $fillable = [
        'nombre',
        'precio',
        'descripcion',
        'beneficios',
    ];
    
}
