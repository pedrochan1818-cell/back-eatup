<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $table = 'users'; // Define la tabla en la BD
    protected $primaryKey = 'iduser'; // Clave primaria personalizada
    public $timestamps = false; // Si no usas `created_at` y `updated_at`

    // Campos que se pueden llenar con asignación masiva
    protected $fillable = ['nombre', 'email', 'password', 'idrol', 'genero', 'edad', 'foto'];

    // Campos ocultos en respuestas JSON
    protected $hidden = ['password'];

    // Cast automático de atributos
    protected $casts = [
        'password' => 'hashed', // Laravel 10 permite `hashed` en lugar de `bcrypt`
        'edad' => 'integer', // Asegúrate de que `edad` se trate como un número entero
    ];
}
