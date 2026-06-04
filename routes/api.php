<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AutoregistroController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PlanesController;
use App\Http\Controllers\SupersetController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [LoginController::class, 'iniciar_sesion']);
Route::post('/logout', [LoginController::class, 'logout']);
Route::post('/usuario/autoregistro', [AutoregistroController::class, 'autoregistro']);
Route::get('/usuarios/foto/{nombre_foto}', [UserController::class, 'mostrarFoto']);
Route::post('/perfil', [UserController::class, 'operacionPerfil']);


Route::get('/usuarios/{iduser}', [UserController::class, 'mostrar']);


//Route::middleware(['auth:sanctum', 'candado2:ADMIN'])
//->get('/productos', [ProductoController::class, 'listado']);

Route::get('/productos', [ProductoController::class, 'listado']);
Route::post('/productos', [ProductoController::class, 'operacion']);
Route::get('/productos/foto/{nombre_foto}', [ProductoController::class, 'mostrarFoto']);


Route::get('/usuarios', [UserController::class, 'listado']);
Route::post('/usuarios/operacion', [UserController::class, 'operacion']);
Route::middleware('auth:sanctum')->get('/usuario', function (Request $request) {
    return response()->json($request->user());
});




Route::get('/roles', [RolController::class, 'index']); 
Route::post('/roles', [RolController::class, 'operacion']); 

//roles
Route::get('/roles/{id}/permisos', [RolxPermisoController::class, 'obtenerPermisosPorRol']);
Route::post('/roles/guardar-permisos', [RolxPermisoController::class, 'guardarPermisos']);


Route::middleware('auth')->get('/inicio', [LoginController::class, 'inicio']);


Route::get('/mesas', [MesaController::class, 'obtenerMesas']); 
Route::post('/mesa/operar', [MesaController::class, 'operarMesa']);
Route::post('/reservas', [ReservaController::class, 'store']);


// routes/api.php
Route::post('/reservas/crear-orden', [ReservaController::class, 'crearOrden']);
Route::post('/save-qr', [ReservaController::class, 'saveQR']);
Route::get('/qrcodes/{filename}', [ReservaController::class, 'getQR'])->where('filename', '.*');
Route::get('reservas/mostrarQR/{nombre_foto}', [ReservaController::class, 'mostrarQR']);



Route::get('historial/{id_user}', [HistorialController::class, 'listado']);
Route::get('historial/{id_user}/{id_reserva}', [HistorialController::class, 'detalle']);
Route::post('historial/operar', [HistorialController::class, 'operacion']);

// routes/api.php
Route::post('reservas/actualizar-status', [ReservaController::class, 'actualizarStatus']);
Route::get('reservas/por-status/{status}', [ReservaController::class, 'porStatus']);

Route::get('reservas/{id_reserva}/orden', [ReservaController::class, 'verOrden']);
Route::get('/cocina/control', [CocinaController::class, 'obtenerControlCocina']);


/// posiblemente nuevas apis para la orden con id_orden
Route::post('/detalle/operar', [DetalleController::class, 'operarDetalle']);
Route::get('/detalle/{iduser}', [DetalleController::class, 'obtenerDetalles']);


// para las nuevas vistas de cotrol de reservas y ordenas
Route::get('/cocina/reservas', [CocinaController::class, 'obtenerReservas']);
Route::get('/cocina/ordenes', [CocinaController::class, 'obtenerOrdenes']);

// esta ruta sera para cambiar el status de la orden desde el dashboard
Route::put('/cocina/ordenes/{id_orden}', [CocinaController::class, 'cambiarEstado']);

// esta ruta sera para cambiar el status de la reserva desde el dashboard
Route::put('/reservas/{id_reserva}/estado', [ReservaController::class, 'cambiarEstado']);

//ruta para probar metabase
Route::get('/metabase/horarios', [MetabaseController::class, 'horarios_usados']);
Route::get('/metabase/genero', [MetabaseController::class, 'distribucion_genero']);

Route::get('/metabase/edad', [MetabaseController::class, 'distribucion_edad']);

Route::get('/metabase/orden', [MetabaseController::class, 'distribucion_orden']);

Route::get('/metabase/mesa', [MetabaseController::class, 'distribucion_mesa']);

//para la hora
Route::get('/hora-servidor', [CocinaController::class, 'obtenerHoraServidor']);

//rutas para la empresa
Route::get('/empresa', [EmpresaController::class, 'mostrar']);
Route::post('/empresa/agregar', [EmpresaController::class, 'agregar']);
Route::post('/empresa/actualizar', [EmpresaController::class, 'actualizar']);
Route::get('/empresa/logo/{nombre_logo}', [EmpresaController::class, 'mostrarLogo']);
Route::post('/empresa/actualizar-plan', [EmpresaController::class, 'actualizarPlan']);


// paras los turos
Route::get('/turno/listado', [TurnoController::class, 'listado']);
Route::post('/turno/operacion', [TurnoController::class, 'operacion']);

Route::get('/turno/status_dos', [TurnoController::class, 'status_dos']);

//para los horarios
Route::get('/horario/listado', [HorarioController::class, 'listado']);
Route::post('/horario/operacion', [HorarioController::class, 'operacion']);



Route::put('/orden/{id_orden}/pago', [ReservaController::class, 'marcarPagado']);
Route::post('/orden/pagar-efectivo', [ReservaController::class, 'pagarEfectivo']);

// metodos de pago
Route::post('/stripe/pagar', [StripeController::class, 'procesarPago']);
Route::post('/stripe/pagar-plan', [StripeController::class, 'pagarPlan']);

Route::post('/mercadopago/crear-preferencia', [MercadoPagoController::class, 'crearPreferencia']);
Route::post('/mercadopago/webhook', [MercadoPagoController::class, 'webhook']);

//catalogo de planes 
Route::get('/planes/listado', [PlanController::class, 'listado']);
Route::post('/planes/operacion', [PlanController::class, 'operacion']);

// para el carrito 
Route::post('/carrito/crear-carrito', [CarritoController::class, 'crearCarrito']);

// Operaciones con el carrito (agregar/eliminar)
Route::post('/carrito/operar', [CarritoController::class, 'operarCarrito']);
Route::post('/carrito/marcar_pagado/{iduser}', [CarritoController::class, 'marcarComoPagado']);

Route::post('/carrito/operar', [CarritoDetalleController::class, 'operarDetalle']);
Route::get('/carrito/{iduser}', [CarritoDetalleController::class, 'obtenerDetalles']);
/// marcar pagado en el carito 
Route::put('/carrito/{id_carrito}/pago', [CarritoDetalleController::class, 'marcarPagado']);

Route::post('/carrito/pagar-efectivo', [CarritoDetalleController::class, 'pagarEfectivo']);
Route::post('/mercadopago/crear-preferencia-carrito', [MercadoPagoController::class, 'crearPreferenciaCarrito']);
Route::post('/stripe/pagar-carrito', [StripeController::class, 'procesarPagoCarrito']);


//para obtener los carritos
Route::get('/cocina/carrito', [CocinaController::class, 'obtenerCarritos']);
Route::get('/cocina/carritos-usuario', [CocinaController::class, 'obtenerCarritosPorUsuario']);
Route::put('/cocina/carrito/{id_carrito}', [CocinaController::class, 'cambiarEstadoCarrito']);

// RUTA DFE SUPERSET
//Route::get('/superset/guest-token', [SupersetController::class, 'guestToken']);
Route::get('/superset/guest-token', [SupersetController::class, 'guestToken']);
