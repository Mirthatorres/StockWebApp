<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes(['register' => true]);
/* Auth::routes(); */
 	

/* -------------------------------------------------------------------------- */

// HOME 

Route::get('/home', 'HomeController@index')->name('home');
//Route::apiResource('categorias', 'CategoriaController');

/* -------------------------------------------------------------------------- */

// UTILES 

Route::apiResource('charts', 'ChartController');
Route::apiResource('busquedas', 'BusquedaController');

/* -------------------------------------------------------------------------- */

// VENTAS 

Route::post('ventas', 'VentaController@mostrar');
Route::get('ventas/periodos', 'VentaController@periodos_superados');
// Route::apiResource('ventas', 'VentaController');

/* -------------------------------------------------------------------------- */

// TRANSFERENCIA

Route::get('transferencias', 'TransferenciaControler@mostrarDataTable');
Route::post('transferencias', 'TransferenciaControler@mostrar');
Route::post('transferenciaG', 'TransferenciaControler@guardarTransferencia');
Route::post('transferenciaModificar', 'TransferenciaControler@modificarTransferencia');
Route::post('transferenciaEliminar', 'TransferenciaControler@eliminarTransferencia');
Route::post('transferenciaEnviar', 'TransferenciaControler@enviarTransferencia');
Route::post('transferenciaCabecera', 'TransferenciaControler@mostrarCabecera');
Route::post('transferenciaCuerpo', 'TransferenciaControler@mostrarCuerpo');
Route::get('transferenciaMostrarImportar', 'TransferenciaControler@mostrarImportar');
Route::get('transferenciasMostrarProductos', 'TransferenciaControler@mostrarProductos');
Route::post('transferenciaRechazar', 'TransferenciaControler@rechazarTransferencia');
Route::post('transferenciaImportar', 'TransferenciaControler@importarTransferencia');
Route::post('transferenciaDetalle', 'TransferenciaControler@detalleTransferencia');
Route::post('pdf-generar-factura','TransferenciaControler@getGenerar');
Route::post('pdf-generar-transferencia','TransferenciaControler@getRptTransferencia');

/* -------------------------------------------------------------------------- */

// INVENTARIO

Route::post('inventarioGuardar', 'InventarioController@guardarInventario');
Route::post('inventarioAgregarEditarProducto', 'InventarioController@agregarEditarProducto');
Route::get('inventarioProductos', 'InventarioController@productosDataTable');
Route::get('inventarioMostrar', 'InventarioController@inventarioDataTable');

/* -------------------------------------------------------------------------- */

// CATEGORIA

Route::get('categoria', 'CategoriaController@obtenerCategorias');
Route::post('categoriaDatatable', 'CategoriaController@categoriasDatatable');
Route::post('CategoriasPorSubCategoriasDatatable', 'CategoriaController@categoriaDatatable');
Route::post('categoriaFiltrar', 'CategoriaController@filtrarCategoria');
Route::post('CategoriaEliminar', 'CategoriaController@CategoriaEliminar');
Route::get('nuevaCategoria','CategoriaController@obtenerCodigo');
Route::post('categoriaGuardar', 'CategoriaController@categoriaGuardar');

/* -------------------------------------------------------------------------- */

// SUB CATEGORIA

Route::get('subCategoria', 'SubCategoriaController@obtenerSubCategorias');
Route::post('subCategoriaFiltrar', 'SubCategoriaController@filtrarSubCategoria');
Route::get('nuevaSubCategoria','SubCategoriaController@obtenerCodigo');
Route::post('subCategoriaEliminar', 'SubCategoriaController@subCategoriaEliminar');
Route::post('subCategoriaGuardar', 'SubCategoriaController@subCategoriaGuardar');
Route::post('subCategoriaDatatable', 'SubCategoriaController@subCategoriasDatatable');
Route::post('subCategoriasDatatable', 'SubCategoriaController@subCategoriaDatatable');

/* -------------------------------------------------------------------------- */

// COLOR

Route::get('nuevoColor','ColorController@obtenerCodigo');
Route::get('color', 'ColorController@obtenerColores');
Route::post('coloresDatatable', 'ColorController@ColoresDatatable');
Route::post('colorFiltrar', 'ColorController@filtrarColor');
Route::post('colorGuardar', 'ColorController@colorGuardar');
Route::post('colorEliminar', 'ColorController@colorEliminar');

/* -------------------------------------------------------------------------- */

// TELAS

Route::get('tela', 'TelaController@obtenerTelas');
Route::post('telasDatatable', 'TelaController@telasDatatable');
Route::post('telaFiltrar', 'TelaController@filtrarTela');
Route::get('nuevaTela','TelaController@obtenerCodigo');
Route::post('telaGuardar', 'TelaController@telaGuardar');
Route::post('telaEliminar', 'TelaController@telaEliminar');

/* -------------------------------------------------------------------------- */

// TALLE

Route::get('talle', 'TalleController@obtenerTalles');
Route::post('tallesDatatable', 'TalleController@tallesDatatable');
Route::post('talleFiltrar', 'TalleController@filtrarTalle');
Route::post('talleEliminar', 'TalleController@talleEliminar');
Route::get('nuevoTalle','TalleController@obtenerCodigo');
Route::post('talleGuardar', 'TalleController@talleGuardar');

/* -------------------------------------------------------------------------- */

// GENERO

Route::get('genero', 'GeneroController@obtenerGeneros');

/* -------------------------------------------------------------------------- */

// MARCA

Route::get('marca','MarcaController@obtenerMarcas');
Route::get('nuevaMarca','MarcaController@obtenerCodigo');
Route::post('marcaFiltrar', 'MarcaController@filtrarMarca');
Route::post('marcaGuardar', 'MarcaController@marcaGuardar');
Route::post('marcaEliminar', 'MarcaController@marcaEliminar');
Route::post('marcasDatatable', 'MarcaController@MarcasDatatable');
Route::post('MarcasPorCategoriaDatatable', 'MarcaController@MarcasPorCategoriaDatatable');

/* -------------------------------------------------------------------------- */

// GONDOLA

Route::get('gondola', 'GondolaController@obtenerGondolas');
Route::post('gondolasDatatable', 'GondolaController@GondolasDatatable');
Route::post('gondolasFiltrar', 'GondolaController@filtrarGondola');
Route::post('gondolaGuardar', 'GondolaController@gondolaGuardar');
Route::post('gondolaEliminar', 'GondolaController@gondolaEliminar');
Route::get('nuevaGondola','GondolaController@obtenerCodigo');
Route::post('gondola/producto', 'GondolaController@obtenerGondolasProducto');

/* -------------------------------------------------------------------------- */

// PROVEEDOR

Route::get('proveedor', 'ProveedorController@obtenerProveedores');
Route::post('proveedores', 'ProveedorController@mostrar');
Route::post('proveedor/pago', 'ProveedorController@pago');
Route::get('proveedor/datatable', 'ProveedorController@datatable');
Route::post('proveedor/lote', 'ProveedorController@loteProducto');
Route::post('proveedor/devolucion', 'ProveedorController@devolucion');
Route::get('proveedor/devolucion/mostrar', 'ProveedorController@devolucionMostrar');
Route::get('proveedor/devolucion/detalle', 'ProveedorController@devolucionDetalle');

/* -------------------------------------------------------------------------- */

// PAGOS PROVEEDOR

Route::post('pagos_prov/datatable', 'Pagos_ProvController@datatable');
Route::post('pagos_prov/pagoUnico', 'Pagos_ProvController@pagoUnico');

/* -------------------------------------------------------------------------- */

// MONEDA

Route::get('moneda', 'MonedaController@obtenerMonedas');

/* -------------------------------------------------------------------------- */

// TARJETA

Route::get('tarjeta/datatable', 'TarjetaController@datatable');

/* -------------------------------------------------------------------------- */

// BANCO

Route::get('banco/datatable', 'BancoController@datatable');

/* -------------------------------------------------------------------------- */

// VENDEDORES 

Route::post('devendedores', 'DevolucionController@mostrar');
Route::post('vendedores', 'VendedorController@mostrar');

/* -------------------------------------------------------------------------- */

/* LARAVEL EXCEL */

// use App\Exports\VentasMarca;
// use Maatwebsite\Excel\Facades\Excel;

// Route::get('/download', function(){
// 	return Excel::download(new VentasMarca, 'ventasMarca.xlsx');
// });

// Route::post('/downloadVentaMarca', function(){
// 	return Excel::download(new VentasMarca(), 'ventasMarca.xlsx');
// });

/* -------------------------------------------------------------------------- */

Route::post('export', 'ExportController@mostrar');

/* -------------------------------------------------------------------------- */

// SUCURSAL

Route::get('sucursal', 'SucursalController@mostrar');
Route::post('sucursal', 'SucursalController@encontrar');

/* -------------------------------------------------------------------------- */

//	EMPLEADOS

Route::get('empleado', 'EmpleadoController@mostrar');
Route::post('empleado', 'EmpleadoController@encontrar');

/* -------------------------------------------------------------------------- */

//	PRODUCTOS

Route::post('productoDatatable', 'ProductoController@mostrar');
Route::post('producto', 'ProductoController@encontrar');
Route::get('productoCodigoInterno', 'ProductoController@generarCI');
Route::get('productoCodigo', 'ProductoController@generarCodigo');
Route::post('productoGuardar', 'ProductoController@guardar');
Route::post('productoObtener', 'ProductoController@obtener');
Route::post('productoDatatableGeneral', 'ProductoController@mostrarDataTableGeneral');
Route::post('productoDetalle', 'ProductoController@productoDetalle');
Route::post('productoConProveedor', 'ProductoController@productoProveedor');
Route::post('producto/transferencia', 'ProductoController@productoTransferencia');
Route::post('producto/eliminar', 'ProductoController@eliminar');
Route::post('productoCompra', 'ProductoController@obtenerProductoCompra');

/* -------------------------------------------------------------------------- */

//	PARAMETROS

Route::get('parametro', 'ParametroController@mostrar');

/* -------------------------------------------------------------------------- */

//	LOTES

Route::post('lotesConCantidad', 'StockController@obtenerLotesConCantidad');
Route::post('lote/vencidos', 'StockController@vencidos');

/* -------------------------------------------------------------------------- */

// COTIZACION

Route::post('cotizacion', 'CotizacionController@cotizar');
Route::get('cotizacion', 'CotizacionController@cotizacionDia');
Route::get('cotizacion/compra-dia', 'CotizacionController@cotizacionCompraDia');

/* -------------------------------------------------------------------------- */

// COMPRAS

Route::post('comprasDetProductos', 'ComprasDetController@obtenerProductosNroCaja');
Route::post('compra/guardar-modificar', 'CompraController@guardarModificarCompra');
Route::get('compra/codigo', 'CompraController@obtenerCodigo');
Route::get('compra/datatable', 'CompraController@mostrarDataTable');
Route::post('compra/eliminar', 'CompraController@eliminarCompra');
Route::get('compra/mostrar_productos', 'CompraController@mostrarProductos');
Route::post('compra/pdf', 'CompraController@getPdf');
Route::post('compra/cabecera', 'CompraController@obtenerCabecera');
Route::post('compra/cuerpo', 'CompraController@obtenerCuerpo');

/* -------------------------------------------------------------------------- */

// USUARIOS

Route::post('usuariosDatatable', 'UserController@mostrar');
Route::post('usuarioGuardar', 'UserController@guardarUsuario');


/* -------------------------------------------------------------------------- */


// ROLES

Route::get('rolTraer', 'UserController@obtenerRoles');
Route::post('rolesDatatable', 'UserController@rolesDatatable');
Route::post('rolFiltrar', 'UserController@filtrarPermisos');
Route::post('rolUsuarioTraer', 'UserController@obtenerRolesUsuario');
Route::post('rolGuardar', 'UserController@guardarRol');
Route::post('rolAsignar', 'UserController@asignarRol');

/* -------------------------------------------------------------------------- */

// PERMISOS

Route::get('permisoTraer', 'UserController@obtenerPermisos');
Route::post('permisoFiltrar', 'UserController@filtrarPermiso');
Route::post('permisosDatatable', 'UserController@permisosDatatable');
Route::post('PermisoGuardar', 'UserController@guardarPermiso');
Route::post('permisoAsignar', 'UserController@asignarPermiso');

/* -------------------------------------------------------------------------- */
// ARTICULOS 

Route::post('MontoArticulos', 'ArticulosController@PorMonto');
Route::post('CantidadArticulos', 'ArticulosController@PorCantidad');

/* -------------------------------------------------------------------------- */

// CLIENTES 

Route::post('clientes', 'ClientesController@mostrar');

/* -------------------------------------------------------------------------- */

// DESCUENTO

Route::post('descuento', 'DescuentoController@mostrar');

/* -------------------------------------------------------------------------- */

// DEUDA

Route::post('/deuda/deudaDatatable', 'DeudaController@deuda_datatable');
Route::post('/deuda/deudaCompraDatatable', 'DeudaController@deuda_compra_datatable');
Route::post('/deuda/datosNota', 'DeudaController@datos_nota');

/* -------------------------------------------------------------------------- */

// EXCEL REPORTES 

Route::post('exportProveedor', 'ProveedorController@descargar');
Route::post('exportransferencia', 'TransferenciaControler@descargar');
Route::post('export', 'ExportController@mostrar');
Route::post('stock', 'Stock@mostrar');
Route::post('exportdescuento', 'ExportDescuentoController@mostrar');
Route::post('ExportInventario', 'InventarioController@Inventario_Cerrado');
Route::post('exportCompra', 'CompraController@Descargar');

/* -------------------------------------------------------------------------- */

// PERMITE QUE SE PUEDA USAR LOS LINK DE VUE-ROUTER A LA HORA DE RECARGAR 

Route::get('{any}', function () {
    return view('home');
})->where('any','.*');

/* -------------------------------------------------------------------------- */
