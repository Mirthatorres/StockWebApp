<?php

namespace App\Http\Controllers;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Proveedor;
use App\Exports\VentasProveedor;
class ProveedorController extends Controller
{
    public function obtenerProveedores(){
        
        /*  --------------------------------------------------------------------------------- */

        // OBTENER PROVEEDORES

        $proveedor = Proveedor::obtener_proveedores();
        return response()->json($proveedor);

        /*  --------------------------------------------------------------------------------- */
    }

    public function mostrar(Request $request)
    {
        /*  --------------------------------------------------------------------------------- */

        // MOSTRAR PROVEEDORES 

        $ventas = Proveedor::generarConsulta($request->all());
        return response()->json($ventas);

        /*  --------------------------------------------------------------------------------- */

    }

    public function descargar(Request $request)
    {

    	/*  --------------------------------------------------------------------------------- */

    	// DESCARGAR REPORTE PROVEEDORES 
    	
        return Excel::download(new VentasProveedor($request->all()), 'VentasProveedores.xlsx');

        /*  --------------------------------------------------------------------------------- */

    }

    public function pago(Request $request)
    {
        /*  --------------------------------------------------------------------------------- */

        // REALIZAR PAGO

        $proveedor = Proveedor::pago($request->all());
        return response()->json($proveedor);

        /*  --------------------------------------------------------------------------------- */

    }

    public function datatable(Request $request)
    {
        /*  --------------------------------------------------------------------------------- */

        // MOSTRAR PROVEEDORES 

        $proveedor = Proveedor::datatable($request);
        return response()->json($proveedor);

        /*  --------------------------------------------------------------------------------- */

    }

    public function loteProducto(Request $request)
    {
        /*  --------------------------------------------------------------------------------- */

        // MOSTRAR PROVEEDORES 

        $proveedor = Proveedor::loteProducto($request);
        return response()->json($proveedor);

        /*  --------------------------------------------------------------------------------- */

    }

    public function devolucion(Request $request)
    {
        /*  --------------------------------------------------------------------------------- */

        // REALIZAR PAGO

        $proveedor = Proveedor::devolucion($request->all());
        return response()->json($proveedor);

        /*  --------------------------------------------------------------------------------- */

    }

    public function devolucionMostrar(Request $request)
    {
        /*  --------------------------------------------------------------------------------- */

        // MOSTRAR PROVEEDORES 

        $proveedor = Proveedor::devolucionMostrar($request);
        return response()->json($proveedor);

        /*  --------------------------------------------------------------------------------- */

    }
    
    public function devolucionDetalle(Request $request)
    {
        /*  --------------------------------------------------------------------------------- */

        // MOSTRAR PROVEEDORES 

        $proveedor = Proveedor::devolucionDetalle($request);
        return response()->json($proveedor);

        /*  --------------------------------------------------------------------------------- */

    }
}
