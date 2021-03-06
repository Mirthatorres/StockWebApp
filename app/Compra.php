<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Mpdf\Mpdf;
use App\ComprasDet;
use App\Lotes_tiene_ComprasDet;
use App\Deuda;
use App\Pagos_Prov_Det;

class Compra extends Model
{
    protected $connection = 'retail';
	protected $table = 'compras';
	const CREATED_AT = 'FECALTAS';
	const UPDATED_AT = 'FECMODIF';

	public static function guardar_compra($data){

		/*  --------------------------------------------------------------------------------- */

        // OBTENER LOS DATOS DEL USUARIO LOGUEADO 

        $user = auth()->user();
        
    	/*  --------------------------------------------------------------------------------- */

    	// INIICAR VARIABLES 

    	$c = 0;
    	$dia = date('Y-m-d');
    	$hora = date("H:i:s");
    	$lote = '';

    	/*  --------------------------------------------------------------------------------- */

    	try {
    		

    		/*  --------------------------------------------------------------------------------- */

    		// OBTENER CANDEC 

    		$candec = (Parametro::candec($data->data["moneda"]))['CANDEC'];

    		/*  --------------------------------------------------------------------------------- */

    		// OBTENER ULTIMO NUMERO 

    		$codigo = Compra::ultimo_codigo();

    		/*  --------------------------------------------------------------------------------- */

    		DB::beginTransaction();

    		$compra = new Compra();

    		$compra->CODIGO = $codigo;
    		$compra->PROVEEDOR = $data->data["proveedor"];
    		$compra->NRO_FACTURA = $data->data["nro_caja"];
    		$compra->FEC_FACTURA = $data->data["fecha"];
    		
    		/*  --------------------------------------------------------------------------------- */

    		// REVISAR PLAN DE PAGO 

    		$compra->TIPO = $data->data["tipo_compra"];

    		if ($data->data["tipo_compra"] === 'CO') {
    			$compra->PLAN_PAGO = $data->data["cuotas"];
    		} else if ($data->data["tipo_compra"] === 'CR') {
    			$compra->PLAN_PAGO = 0;
    		} else if ($data->data["tipo_compra"] === 'CS') {
    			$compra->PLAN_PAGO = 0;
    		}
    		
    		/*  --------------------------------------------------------------------------------- */

    		$compra->TOTAL = Common::quitar_coma($data->data["total_compra"], $candec);
    		$compra->EXENTAS = Common::quitar_coma($data->data["exentas"], $candec);
    		$compra->MONEDA = $data->data["moneda"];
    		$compra->CANCELADO = 'NO';
    		$compra->ID_SUCURSAL = $user->id_sucursal;
    		$compra->PEDIDO = $data->data["nro_pedido"];
    		$compra->FK_USER_CR = $user->id;
    		$compra->save();
    		
    		/*  --------------------------------------------------------------------------------- */

    		// RECORRER PRODUCTOS 

    		foreach ($data->data["productos"] as $key => $value) {

    			/*  --------------------------------------------------------------------------------- */
    			
    			$compra_det = new ComprasDet();
    			
    			$c = $c + 1;
    			$compra_det->CODIGO = $codigo;
    			$compra_det->ITEM = $c;
    			$compra_det->COD_PROD = $value['CODIGO'];
    			$compra_det->DESCRIPCION = $value['DESCRIPCION'];
    			$compra_det->ETIQUETAS = 0;
    			$compra_det->CANTIDAD = $value['CANTIDAD'];
    			$compra_det->COSTO = Common::quitar_coma($value['COSTO'], $candec);
    			$compra_det->COSTO_TOTAL = Common::quitar_coma($value['COSTO_TOTAL'], $candec);

    			/*  --------------------------------------------------------------------------------- */

    			// INSERTAR LOTE 

    			$lote = Stock::insetar_lote($value['CODIGO'], $value['CANTIDAD'], Common::quitar_coma($value['COSTO'], $candec), 1, '', $value['VENCIMIENTO']);
    			$compra_det->LOTE = $lote["lote"];

    			/*  --------------------------------------------------------------------------------- */

    			$compra_det->PORCENTAJE = $value['PORCENTAJE'];
    			$compra_det->PREC_VENTA = Common::quitar_coma($value['PRECIO'], $candec);
    			$compra_det->PREMAYORISTA = Common::quitar_coma($value['MAYORISTA'], $candec);
    			$compra_det->USER = $user->name;
    			$compra_det->HORALTAS = $hora;
    			$compra_det->ID_SUCURSAL = $user->id_sucursal;

    			$compra_det->save();

    			/*  --------------------------------------------------------------------------------- */

    			// GUARDAR REFERENCIA 

    			Lotes_tiene_ComprasDet::guardar_referencia($compra_det->id, $lote["id"]);

    			/*  --------------------------------------------------------------------------------- */

    		}

    		DB::commit();

    		/*  --------------------------------------------------------------------------------- */

    		// INSERTAR DEUDA 

    		if ($data->data["tipo_compra"] === 'CR') {
    			Deuda::insertar($data->data, $candec, $compra->id);
    		}

    		/*  --------------------------------------------------------------------------------- */

    		// RETORNAR VALOR 

    		return ["response" => true, "codigo" => $codigo];

    		/*  --------------------------------------------------------------------------------- */

    	} catch (Exception $e) {

    		DB::rollBack();

    		/*  --------------------------------------------------------------------------------- */

    		// RETORNAR VALOR 
    		
    		return ["response" => false];

    		/*  --------------------------------------------------------------------------------- */
    	}

    	/*  --------------------------------------------------------------------------------- */

	}

	public static function ultimo_codigo() {

		/*  --------------------------------------------------------------------------------- */

        // OBTENER LOS DATOS DEL USUARIO LOGUEADO 

        $user = auth()->user();

		/*  --------------------------------------------------------------------------------- */

		// OBTENER ULTIMO CODIGO

		$codigo =Compra::select('CODIGO')
		->where(['ID_SUCURSAL' => $user->id_sucursal])
		->orderBy('CODIGO', 'desc')
		->first();

		/*  --------------------------------------------------------------------------------- */

		// RETORNAR VALOR 

		return $codigo['CODIGO'] + 1;

		/*  --------------------------------------------------------------------------------- */

	}

	 public static function mostrarDatatable($request)
    {

        /*  --------------------------------------------------------------------------------- */

        // OBTENER LOS DATOS DEL USUARIO LOGUEADO 

        $user = auth()->user();

        /*  --------------------------------------------------------------------------------- */

        // CREAR COLUMNA DE ARRAY 

        $columns = array( 
                            0 => 'CODIGO', 
                            1 => 'PROVEEDOR',
                            2 => 'TIPO',
                            3 => 'NRO_FACTURA',
                            4 => 'FEC_FACTURA',
                            5 => 'TOTAL',
                            6 => 'ACCION',
                        );
        
        /*  --------------------------------------------------------------------------------- */

        // CONTAR LA CANTIDAD DE TRANSFERENCIAS ENCONTRADAS 

        $totalData = Compra::where('ID_SUCURSAL','=', $user->id_sucursal)
                     ->count();  
        
        /*  --------------------------------------------------------------------------------- */

        // INICIAR VARIABLES 

        $totalFiltered = $totalData; 
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        
        /*  --------------------------------------------------------------------------------- */

        // REVISAR SI EXISTE VALOR EN VARIABLE SEARCH

        if(empty($request->input('search.value')))
        {            

            /*  ************************************************************ */

            //  CARGAR TODOS LOS PRODUCTOS ENCONTRADOS 

            $posts = Compra::select(DB::raw('COMPRAS.CODIGO, COMPRAS.MONEDA, PROVEEDORES.NOMBRE, COMPRAS.TIPO, COMPRAS.NRO_FACTURA, COMPRAS.FEC_FACTURA, COMPRAS.TOTAL'))
                         ->leftjoin('PROVEEDORES', 'PROVEEDORES.CODIGO', '=', 'COMPRAS.PROVEEDOR')
                         ->where('COMPRAS.ID_SUCURSAL','=', $user->id_sucursal)
                         ->offset($start)
                         ->limit($limit)
                         ->orderBy($order,$dir)
                         ->get();

            /*  ************************************************************ */

        } else {

            /*  ************************************************************ */

            // CARGAR EL VALOR A BUSCAR 

            $search = $request->input('search.value'); 

            /*  ************************************************************ */

            // CARGAR LOS PRODUCTOS FILTRADOS EN DATATABLE

            $posts =  Compra::select(DB::raw('COMPRAS.CODIGO, COMPRAS.MONEDA, PROVEEDORES.NOMBRE, COMPRAS.TIPO, COMPRAS.NRO_FACTURA, COMPRAS.FEC_FACTURA, COMPRAS.TOTAL'))
                         ->leftjoin('PROVEEDORES', 'PROVEEDORES.CODIGO', '=', 'COMPRAS.PROVEEDOR')
                         ->where('COMPRAS.ID_SUCURSAL','=', $user->id_sucursal)
                            ->where(function ($query) use ($search) {
                                $query->where('COMPRAS.CODIGO','LIKE',"{$search}%")
                                      ->orWhere('PROVEEDORES.NOMBRE', 'LIKE',"%{$search}%");
                            })
                            ->offset($start)
                            ->limit($limit)
                            ->orderBy($order,$dir)
                            ->get();

            /*  ************************************************************ */

            // CARGAR LA CANTIDAD DE PRODUCTOS FILTRADOS 

            $totalFiltered = Compra::where('COMPRAS.ID_SUCURSAL','=', $user->id_sucursal)
            				->leftjoin('PROVEEDORES', 'PROVEEDORES.CODIGO', '=', 'COMPRAS.PROVEEDOR')
                            ->where(function ($query) use ($search) {
                                $query->where('COMPRAS.CODIGO','LIKE',"{$search}%")
                                      ->orWhere('PROVEEDORES.NOMBRE', 'LIKE',"%{$search}%");
                            })
                            ->count();

            /*  ************************************************************ */  

        }

        $data = array();

        /*  --------------------------------------------------------------------------------- */

        // REVISAR SI LA VARIABLES POST ESTA VACIA 

        if(!empty($posts))
        {
            foreach ($posts as $post)
            {

                /*  --------------------------------------------------------------------------------- */

                // CARGAR EN LA VARIABLE 

                $nestedData['CODIGO'] = $post->CODIGO;
                $nestedData['PROVEEDOR'] = $post->NOMBRE;

                /*  --------------------------------------------------------------------------------- */

                // TIPO DE COMPRA

                if ($post->TIPO === 'CO') {
                    $nestedData['TIPO'] = 'Contado';
                } else if ($post->TIPO === 'CR') {
                    $nestedData['TIPO'] = 'Credito';
                } else if ($post->TIPO === 'CS') {
                    $nestedData['TIPO'] = 'Consignacion';
                } else {
                	$nestedData['TIPO'] = 'N/A';
                }

                /*  --------------------------------------------------------------------------------- */

                $nestedData['NRO_FACTURA'] = $post->NRO_CAJA;
                $nestedData['FEC_FACTURA'] = $post->FEC_FACTURA;
                $nestedData['TOTAL'] = Common::precio_candec($post->TOTAL, $post->MONEDA);
                
                $nestedData['ACCION'] = "&emsp;<a href='#' id='mostrar' title='Mostrar'><i class='fa fa-list'  aria-hidden='true'></i></a>&emsp;<a href='#' id='editar' title='Editar'><i class='fa fa-edit text-warning' aria-hidden='true'></i></a>&emsp;<a href='#' id='eliminar' title='Eliminar'><i class='fa fa-trash text-danger' aria-hidden='true'></i></a>
                    &emsp;<a href='#' id='reporte' title='Reporte'><i class='fa fa-file text-secondary' aria-hidden='true'></i></a>";

                $data[] = $nestedData;

                /*  --------------------------------------------------------------------------------- */

            }
        }
        
        /*  --------------------------------------------------------------------------------- */

        // PREPARAR EL ARRAY A ENVIAR 

        $json_data = array(
                    "draw"            => intval($request->input('draw')),  
                    "recordsTotal"    => intval($totalData),  
                    "recordsFiltered" => intval($totalFiltered), 
                    "data"            => $data   
                    );
        
        /*  --------------------------------------------------------------------------------- */

        // CONVERTIR EN JSON EL ARRAY Y ENVIAR 

       return $json_data; 

        /*  --------------------------------------------------------------------------------- */
    }

    public static function verificar_eliminacion($codigo) {

    	/*  --------------------------------------------------------------------------------- */

        // OBTENER LOS DATOS DEL USUARIO LOGUEADO 

        $user = auth()->user();

        /*  --------------------------------------------------------------------------------- */

        // INICIAR VARIABLES 

        $codigo = $codigo["data"];
        $modificado = false;

        /*  --------------------------------------------------------------------------------- */
        
        // OBTENER ID COMPRA 

        $id_compra = Compra::select('ID')
        ->where('ID_SUCURSAL','=', $user->id_sucursal)
        ->where('CODIGO','=', $codigo)
        ->get();

        /*  --------------------------------------------------------------------------------- */

        // OBTENER LOS LOTES A ELIMINAR

		$compradet = ComprasDet::select(DB::raw(
                        'COMPRASDET.ID AS COMPRA_ID, LOTES.ID AS LOTE_ID, LOTES.COD_PROD, LOTES.USERM, FK_USER_MD'
                    ))
        ->rightJoin('LOTE_TIENE_COMPRASDET', 'LOTE_TIENE_COMPRASDET.ID_COMPRAS_DET', '=', 'COMPRASDET.ID')
        ->leftJoin('LOTES', 'LOTES.ID', '=', 'LOTE_TIENE_COMPRASDET.ID_LOTE')
        ->where('COMPRASDET.ID_SUCURSAL','=', $user->id_sucursal)
        ->where('COMPRASDET.CODIGO','=', $codigo)
        ->get();      	  

        /*  --------------------------------------------------------------------------------- */

        // SI NO ENCUENTRA REGISTROS EN LAS CLAVES FORANEAS PROHIBIR ELIMINAR 

        if(count($compradet) <= 0) {
        	return ["response" => false, "statusText" => "Esta compra no se podra eliminar o modificar a través de esta plataforma !"];
        }

        /*  --------------------------------------------------------------------------------- */

        foreach ($compradet as $key => $value) {
        	if($value->FK_USER_MD !== 0 or $value->USERM !== null) {
        		$modificado = true;
        		break;
        	}
        }

        /*  --------------------------------------------------------------------------------- */

        // SI ES TRUE MODIFICADO SE RECHAZA LA ELIMINACION
        
        if($modificado === true) {
        	return ["response" => false, "statusText" => "Esta compra tiene lotes ya modificados"];
        }

        /*  --------------------------------------------------------------------------------- */

        // VERIFICAR PAGOS

        /*  --------------------------------------------------------------------------------- */

        // COMPROBAR SI YA HAY PAGO
        
        $pagos = Pagos_Prov_Det::leftjoin('DEUDAS', 'DEUDAS.ID', '=', 'PAGOS_PROV_DET.FK_DEUDA')
        ->where('DEUDAS.FK_COMPRA','=', $id_compra[0]["ID"])
        ->count();

        if ($pagos > 0) {
            return ["response" => false, "statusText" => "Hay pagos a proveedores hechos con esta compra !"];
        }

        /*  --------------------------------------------------------------------------------- */

        // RETORNAR TRUE SI NO SE PUEDE ELIMINAR 

        return ["response" => true, "statusText" => "Se puede eliminar", "data" => $compradet];

        /*  --------------------------------------------------------------------------------- */

    }

   	public static function eliminar($codigo) 
    {
    	
        /*  --------------------------------------------------------------------------------- */

        // OBTENER LOS DATOS DEL USUARIO LOGUEADO 

        $user = auth()->user();

        /*  --------------------------------------------------------------------------------- */

        $verificacion = Compra::verificar_eliminacion($codigo);
        
        if ($verificacion["response"] === false) {
        	return $verificacion;
        } else {
        	$verificacion = $verificacion["data"];
        }

        /*  --------------------------------------------------------------------------------- */

        // ELIMINAR TODA LAS REFERENCIAS

        foreach ($verificacion as $key => $value) {

        	/*  --------------------------------------------------------------------------------- */

        	// ELIMINAR REFERENCIA 

        	Lotes_tiene_ComprasDet::where('ID_COMPRAS_DET','=', $value->COMPRA_ID)
        	->delete();

        	/*  --------------------------------------------------------------------------------- */

        	// ELIMINAR LOTE 

        	Stock::eliminar_lote_por_id($value->LOTE_ID);

        	/*  --------------------------------------------------------------------------------- */

        }
        
        /*  --------------------------------------------------------------------------------- */

        // ELIMINAR COMPRAS DET 

        ComprasDet::where('ID_SUCURSAL','=', $user->id_sucursal)
        ->where('CODIGO','=', $codigo["data"])
        ->delete();

        /*  --------------------------------------------------------------------------------- */

        // ELIMINAR TODA LA COMPRA
        // SI LA OPCION ES 1 ELIMINAR LA COMPRA, SINO SOLO ELIMINAR COMPRASDET

        if ($codigo["opcion"] === 1) {

            /*  --------------------------------------------------------------------------------- */

            // REVISAR SI HAY DEUDA

        	Compra::where('ID_SUCURSAL','=', $user->id_sucursal)
        	->where('CODIGO','=', $codigo["data"])
        	->delete();

            /*  --------------------------------------------------------------------------------- */
        }
        
        /*  --------------------------------------------------------------------------------- */

        // RETORNAR VALOR 

        return ["response" => true];

        /*  --------------------------------------------------------------------------------- */
    }

    public static function mostrar_productos($request) {

        /*  --------------------------------------------------------------------------------- */

        // INICIAR VARIABLES

        $codigo = $request->input('codigoCompra');

        /*  --------------------------------------------------------------------------------- */

        // OBTENER LOS DATOS DEL USUARIO LOGUEADO 

        $user = auth()->user();

        /*  --------------------------------------------------------------------------------- */

        // CREAR COLUMNA DE ARRAY 

        $columns = array( 
                            0 => 'ITEM', 
                            1 => 'COD_PROD',
                            2 => 'DESCRIPCION',
                            3 => 'CANTIDAD',
                            4 => 'COSTO',
                            5 => 'COSTO_TOTAL',
                            6 => 'LOTE',
                            7 => 'PRECIO'
                        );
        
        /*  --------------------------------------------------------------------------------- */

        // CONTAR LA CANTIDAD DE PRODUCTOS ENCONTRADOS 

        $totalData = Comprasdet::leftjoin('PRODUCTOS', 'PRODUCTOS.CODIGO', '=', 'COMPRASDET.COD_PROD')
                    ->where('COMPRASDET.CODIGO','=', $codigo)
                    ->where('COMPRASDET.ID_SUCURSAL','=', $user->id_sucursal)
                    ->count();  
        
        /*  --------------------------------------------------------------------------------- */

        // INICIAR VARIABLES 

        $totalFiltered = $totalData; 
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        
        /*  --------------------------------------------------------------------------------- */

        // REVISAR SI EXISTE VALOR EN VARIABLE SEARCH

        if(empty($request->input('search.value')))
        {            

            /*  ************************************************************ */

            //  CARGAR TODOS LOS PRODUCTOS ENCONTRADOS 

            $posts = ComprasDet::select(DB::raw('COMPRASDET.ITEM, COMPRASDET.COD_PROD, PRODUCTOS.DESCRIPCION, COMPRASDET.CANTIDAD, COMPRASDET.PREC_VENTA AS PRECIO, COMPRASDET.COSTO, COMPRASDET.COSTO_TOTAL, COMPRASDET.LOTE, COMPRAS.MONEDA'))
            ->leftjoin('PRODUCTOS', 'PRODUCTOS.CODIGO', '=', 'COMPRASDET.COD_PROD')
            ->leftJoin('COMPRAS', function($join){
			    $join->on('COMPRAS.CODIGO', '=', 'COMPRASDET.CODIGO')
			         ->on('COMPRAS.ID_SUCURSAL', '=', 'COMPRASDET.ID_SUCURSAL');
			})
            ->where('COMPRASDET.ID_SUCURSAL','=', $user->id_sucursal)
            ->where('COMPRASDET.CODIGO','=', $codigo)
            ->offset($start)
            ->limit($limit)
            ->orderBy($order,$dir)
            ->get();

            /*  ************************************************************ */

        } else {

            /*  ************************************************************ */

            // CARGAR EL VALOR A BUSCAR 

            $search = $request->input('search.value'); 

            /*  ************************************************************ */

            // CARGAR LOS PRODUCTOS FILTRADOS EN DATATABLE

            $posts =  ComprasDet::select(DB::raw('COMPRASDET.ITEM, COMPRASDET.COD_PROD, PRODUCTOS.DESCRIPCION, COMPRASDET.CANTIDAD, COMPRASDET.PREC_VENTA AS PRECIO, COMPRASDET.COSTO, COMPRASDET.COSTO_TOTAL, COMPRASDET.LOTE, COMPRAS.CODIGO'))
            ->leftjoin('PRODUCTOS', 'PRODUCTOS.CODIGO', '=', 'COMPRASDET.COD_PROD')
            ->leftJoin('COMPRAS', function($join){
			    $join->on('COMPRAS.CODIGO', '=', 'COMPRASDET.CODIGO')
			         ->on('COMPRAS.ID_SUCURSAL', '=', 'COMPRASDET.ID_SUCURSAL');
			})
            ->where('COMPRASDET.ID_SUCURSAL','=', $user->id_sucursal)
            ->where('COMPRASDET.CODIGO','=', $codigo)
            ->where(function ($query) use ($search) {
                $query->where('COMPRASDET.COD_PROD','LIKE',"{$search}%")
                ->orWhere('PRODUCTOS.DESCRIPCION', 'LIKE',"%{$search}%");
            })
            ->offset($start)
            ->limit($limit)
            ->orderBy($order,$dir)
            ->get();

            /*  ************************************************************ */

            // CARGAR LA CANTIDAD DE PRODUCTOS FILTRADOS 

            $totalFiltered = ComprasDet::leftjoin('PRODUCTOS', 'PRODUCTOS.CODIGO', '=', 'COMPRASDET.COD_PROD')
            ->where('COMPRASDET.ID_SUCURSAL','=', $user->id_sucursal)
            ->where('COMPRASDET.CODIGO','=', $codigo)
            ->where(function ($query) use ($search) {
                $query->where('COMPRASDET.COD_PROD','LIKE',"{$search}%")
                ->orWhere('PRODUCTOS.DESCRIPCION', 'LIKE',"%{$search}%");
            })
            ->count();

            /*  ************************************************************ */  

        }

        $data = array();

        /*  --------------------------------------------------------------------------------- */

        // REVISAR SI LA VARIABLES POST ESTA VACIA 

        if(!empty($posts))
        {
            foreach ($posts as $post)
            {

                /*  --------------------------------------------------------------------------------- */

                // CARGAR EN LA VARIABLE 

                $nestedData['ITEM'] = $post->ITEM;
                $nestedData['COD_PROD'] = $post->COD_PROD;
                $nestedData['DESCRIPCION'] = $post->DESCRIPCION;
                $nestedData['CANTIDAD'] = $post->CANTIDAD;
                $nestedData['COSTO'] = Common::precio_candec($post->COSTO, $post->MONEDA);
                $nestedData['COSTO_TOTAL'] = Common::precio_candec($post->COSTO_TOTAL, $post->MONEDA);
                $nestedData['LOTE'] = $post->LOTE;
                $nestedData['PRECIO'] = Common::precio_candec($post->PRECIO, $post->MONEDA);

                $data[] = $nestedData;

                /*  --------------------------------------------------------------------------------- */

            }
        }
        
        /*  --------------------------------------------------------------------------------- */

        // PREPARAR EL ARRAY A ENVIAR 

        $json_data = array(
                    "draw"            => intval($request->input('draw')),  
                    "recordsTotal"    => intval($totalData),  
                    "recordsFiltered" => intval($totalFiltered), 
                    "data"            => $data   
                    );
        
        /*  --------------------------------------------------------------------------------- */

        // CONVERTIR EN JSON EL ARRAY Y ENVIAR 

       return $json_data; 

        /*  --------------------------------------------------------------------------------- */
    }

    public static function mostrar_cabecera($data)
    {

        /*  --------------------------------------------------------------------------------- */

        // OBTENER LOS DATOS DEL USUARIO LOGUEADO 

        $user = auth()->user();
         
        /*  --------------------------------------------------------------------------------- */

        // INICIAR VARIABLES

        $codigo = $data['codigo'];

        /*  --------------------------------------------------------------------------------- */

        $compra = Compra::select(DB::raw(
                        'COMPRAS.CODIGO,
                        PROVEEDORES.NOMBRE,
                        SUBSTRING(COMPRAS.FEC_FACTURA, 1, 10) AS FEC_FACTURA,
                        COMPRAS.FECALTAS,
                        COMPRAS.NRO_FACTURA,
                        COMPRAS.TIPO,
                        COMPRAS.PLAN_PAGO,
                        COMPRAS.FK_USER_CR,
                        COMPRAS.MONEDA,
                        COMPRAS.PROVEEDOR,
                        COMPRAS.TOTAL,
                        COMPRAS.EXENTAS'
                    ))
        ->leftjoin('PROVEEDORES', 'PROVEEDORES.CODIGO', '=', 'COMPRAS.PROVEEDOR')
        ->where('COMPRAS.ID_SUCURSAL','=', $user->id_sucursal)
        ->where('COMPRAS.CODIGO','=', $codigo)
        ->get();

        /*  --------------------------------------------------------------------------------- */

        return $compra[0];

    }

    public static function mostrar_cuerpo($data)
    {
        
        /*  --------------------------------------------------------------------------------- */

        // OBTENER LOS DATOS DEL USUARIO LOGUEADO 

        $user = auth()->user();

        /*  --------------------------------------------------------------------------------- */

        // INICIAR VARIABLES

        $codigo = $data['codigo'];

        /*  --------------------------------------------------------------------------------- */

        $compra = ComprasDet::select(DB::raw(
                        'COMPRASDET.ITEM, 
                        COMPRASDET.COD_PROD, 
                        COMPRASDET.DESCRIPCION,
                        COMPRASDET.LOTE,
                        COMPRASDET.PORCENTAJE, 
                        COMPRASDET.CANTIDAD, 
                        COMPRASDET.COSTO,
                        COMPRASDET.COSTO_TOTAL,
                        COMPRASDET.PREC_VENTA,
                        COMPRASDET.PREMAYORISTA,
                        COMPRAS.MONEDA,
                        SUBSTRING(LOTES.FECHA_VENC, 1, 10) AS VENCIMIENTO'
                    ))
        ->leftJoin('COMPRAS', function($join){
			    $join->on('COMPRAS.CODIGO', '=', 'COMPRASDET.CODIGO')
			         ->on('COMPRAS.ID_SUCURSAL', '=', 'COMPRASDET.ID_SUCURSAL');
		})
        ->leftjoin('LOTE_TIENE_COMPRASDET', 'LOTE_TIENE_COMPRASDET.ID_COMPRAS_DET', '=', 'COMPRASDET.ID')
        ->leftjoin('LOTES', 'LOTES.ID', '=', 'LOTE_TIENE_COMPRASDET.ID_LOTE')
        ->where('COMPRASDET.ID_SUCURSAL','=', $user->id_sucursal)
        ->where('COMPRASDET.CODIGO','=', $codigo)
        ->get();

        /*  --------------------------------------------------------------------------------- */

        foreach ($compra as $key => $value) {

            /*  --------------------------------------------------------------------------------- */

            // VER PRECIOS DE PRODUCTOS 

            if ($value->VENCIMIENTO === '0000-00-00') {
                $value->VENCIMIENTO = 'N/A';
            }

            /*  --------------------------------------------------------------------------------- */

            $compra[$key]->CANTIDAD = Common::precio_candec_sin_letra($value->CANTIDAD, 1);
            $compra[$key]->PREC_VENTA = Common::precio_candec_sin_letra($value->PREC_VENTA, $value->MONEDA);
            $compra[$key]->PREMAYORISTA = Common::precio_candec_sin_letra($value->PREMAYORISTA, $value->MONEDA);
            $compra[$key]->COSTO = Common::precio_candec_sin_letra($value->COSTO, $value->MONEDA);
            $compra[$key]->COSTO_TOTAL = Common::precio_candec_sin_letra($value->COSTO_TOTAL, $value->MONEDA);

            /*  --------------------------------------------------------------------------------- */

        }

        return $compra;

        /*  --------------------------------------------------------------------------------- */

    }

    public static function pdf_compra($dato)
    {
        
        /*  --------------------------------------------------------------------------------- */

        // OBTENER LOS DATOS DEL USUARIO LOGUEADO 

        $user = auth()->user();
         
        /*  --------------------------------------------------------------------------------- */

        // OBTENER DATOS DE CABECERA 

        $compra = Compra::mostrar_cabecera($dato);

        /*  --------------------------------------------------------------------------------- */

        // OBTENER DATOS DETALLE 

        $compra_det = Compra::mostrar_cuerpo($dato);

        /*  --------------------------------------------------------------------------------- */

        // PARAMETRO CANDEC 

        $candec = Parametro::candec($compra->MONEDA);

        /*  --------------------------------------------------------------------------------- */

        // OBTENER SUCURSAL

        $sucursal = Sucursal::encontrarSucursal(['codigoOrigen' => $user->id_sucursal]);
        
        /*  --------------------------------------------------------------------------------- */

        // INICIAR VARIABLES 

        $c = 0;
        $codigo = $compra->CODIGO;
        $proveedor = $compra->NOMBRE;
        $fec_factura = $compra->FEC_FACTURA;
        $fecaltas = $compra->FECALTAS;
        $nro_factura = $compra->NRO_FACTURA;
        $tipo = '';
        $plan_pago = $compra->PLAN_PAGO;

        /*  --------------------------------------------------------------------------------- */

        // PLAN PAGO 

        if ($compra->TIPO === 'CO') {
        	$tipo = 'CONTADO';
        } else if ($compra->TIPO === 'CR') {
        	$tipo = 'CREDITO';
        } else if ($compra->TIPO === 'CS') {
        	$tipo = 'CONSIGNACION';
        }

        /*  --------------------------------------------------------------------------------- */

        $nombre = 'Compra_'.$codigo.'_'.time().'';
        $articulos = [];
        $cantidad = 0;
        $total = 0;
        $nombre_sucursal = $sucursal['sucursal'][0]['DESCRIPCION'];
        $direccion = $sucursal['sucursal'][0]['DIRECCION'];

        /*  --------------------------------------------------------------------------------- */

        // CARGAR DETALLE DE TRANSFERENCIA DET 

        foreach ($compra_det as $key => $value) {
            $articulos[$key]["cantidad"] = $value->CANTIDAD;
            $articulos[$key]["cod_prod"] = $value->COD_PROD;
            $articulos[$key]["descripcion"] = $value->DESCRIPCION;
            $articulos[$key]["precio"] = $value->PREC_VENTA;
            $articulos[$key]["premayorista"] = $value->PREMAYORISTA;
            $articulos[$key]["lote"] = $value->LOTE;
            $articulos[$key]["costo"] = $value->COSTO;
            $articulos[$key]["costo_total"] = $value->COSTO_TOTAL;
            $cantidad = $cantidad + $value->CANTIDAD;
            $total = $total + Common::quitar_coma($value->COSTO_TOTAL, $candec['CANDEC']);
            $c = $c + 1;
        }

        /*  --------------------------------------------------------------------------------- */

        // CARGAR VARIABLES 
        
        $data['codigo'] = $codigo;
        $data['proveedor'] = $proveedor;
        $data['fec_factura'] = $fec_factura;
        $data['fecaltas'] = $fecaltas;
        $data['nro_factura'] = $nro_factura;
        $data['tipo'] = $tipo;
        $data['plan_pago'] = $plan_pago;
        $data['nombre'] = $nombre;
        $data['articulos'] = $articulos;
        $data['c'] = $c;
        $data['cantidad'] = $cantidad;
        $data['total'] = Common::precio_candec($total, $compra->MONEDA);
        $data['sucursal'] = $nombre_sucursal;
        $data['direccion'] = $direccion;

        /*  --------------------------------------------------------------------------------- */
        
        $html = view('pdf.rptCompra',$data)->render();
        
        /*  --------------------------------------------------------------------------------- */

        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];
 
        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new Mpdf([
            'fontDir' => array_merge($fontDirs, [
                public_path() . '/fonts',
            ]),
            'fontdata' => $fontData + [
                'arial' => [
                    'R' => 'arial.ttf',
                    'B' => 'arialbd.ttf',
                ],
            ],
            'default_font' => 'arial',
             "format" => "A4",
        ]);
        
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->WriteHTML($html);

        /*  --------------------------------------------------------------------------------- */

        // GENERAR ARCHIVO 

        $mpdf->Output($nombre,"I");
        
        /*  --------------------------------------------------------------------------------- */

    }

    public static function modificar_compra($data){

		/*  --------------------------------------------------------------------------------- */

        // OBTENER LOS DATOS DEL USUARIO LOGUEADO 

        $user = auth()->user();
        
    	/*  --------------------------------------------------------------------------------- */

    	// INIICAR VARIABLES 

    	$c = 0;
    	$dia = date('Y-m-d');
    	$hora = date("H:i:s");
    	$lote = '';
    	$plan_pago = 0;
    	$codigo = $data->data["codigo"];

    	/*  --------------------------------------------------------------------------------- */

    	try {
    		

    		/*  --------------------------------------------------------------------------------- */

    		// OBTENER CANDEC 

    		$candec = (Parametro::candec($data->data["moneda"]))['CANDEC'];

    		/*  --------------------------------------------------------------------------------- */

    		// PLAN PAGO 

    		if ($data->data["tipo_compra"] === 'CO') {
    			$plan_pago = $data->data["cuotas"];
    		} else if ($data->data["tipo_compra"] === 'CR') {
    			$plan_pago = 0;
    		} else if ($data->data["tipo_compra"] === 'CS') {
    			$plan_pago = 0;
    		}

    		/*  --------------------------------------------------------------------------------- */

    		Compra::
    		where([
    			['CODIGO', '=', $codigo],
    			['ID_SUCURSAL', '=', $user->id_sucursal]
    		])
    		->update([
    			'PROVEEDOR' => $data->data["proveedor"],
    			'NRO_FACTURA' => $data->data["nro_caja"],
    			'FEC_FACTURA' => $data->data["nro_caja"],
    			'PLAN_PAGO' => $plan_pago,
    			'TIPO' => $data->data["tipo_compra"],
    			'TOTAL' => Common::quitar_coma($data->data["total_compra"], $candec),
    			'EXENTAS' => Common::quitar_coma($data->data["exentas"], $candec),
    			'MONEDA' => $data->data["moneda"],
    			'CANCELADO' => 'NO',
    			'PEDIDO' => $data->data["nro_pedido"],
    			'FK_USER_MD' => $user->id
    		]);
    		
    		/*  --------------------------------------------------------------------------------- */

    		// ELIMINAR PRODUCTOS COMPRAS DET 
    		// ENVIAR OPCION 2 PARA EVITAR LA ELIMINACION DEL REGISTRO COMPRA 

    		$eliminar = Compra::eliminar(["data" => $codigo, "opcion" => 2]);

    		if ($eliminar["response"] === false) {
    			return $eliminar;
    		}

    		/*  --------------------------------------------------------------------------------- */

    		// RECORRER PRODUCTOS 

    		foreach ($data->data["productos"] as $key => $value) {

    			/*  --------------------------------------------------------------------------------- */
    			
    			$compra_det = new ComprasDet();
    			
    			$c = $c + 1;
    			$compra_det->CODIGO = $codigo;
    			$compra_det->ITEM = $c;
    			$compra_det->COD_PROD = $value['CODIGO'];
    			$compra_det->DESCRIPCION = $value['DESCRIPCION'];
    			$compra_det->ETIQUETAS = 0;
    			$compra_det->CANTIDAD = $value['CANTIDAD'];
    			$compra_det->COSTO = Common::quitar_coma($value['COSTO'], $candec);
    			$compra_det->COSTO_TOTAL = Common::quitar_coma($value['COSTO_TOTAL'], $candec);

    			/*  --------------------------------------------------------------------------------- */

    			// INSERTAR LOTE 

    			$lote = Stock::insetar_lote($value['CODIGO'], $value['CANTIDAD'], Common::quitar_coma($value['COSTO'], $candec), 1, '', $value['VENCIMIENTO']);
    			$compra_det->LOTE = $lote["lote"];

    			/*  --------------------------------------------------------------------------------- */

    			$compra_det->PORCENTAJE = $value['PORCENTAJE'];
    			$compra_det->PREC_VENTA = Common::quitar_coma($value['PRECIO'], $candec);
    			$compra_det->PREMAYORISTA = Common::quitar_coma($value['MAYORISTA'], $candec);
    			$compra_det->USER = $user->name;
    			$compra_det->HORALTAS = $hora;
    			$compra_det->ID_SUCURSAL = $user->id_sucursal;

    			$compra_det->save();

    			/*  --------------------------------------------------------------------------------- */

    			// GUARDAR REFERENCIA 

    			Lotes_tiene_ComprasDet::guardar_referencia($compra_det->id, $lote["id"]);

    			/*  --------------------------------------------------------------------------------- */

    		}
    		
    		DB::commit();

    		/*  --------------------------------------------------------------------------------- */
    	
    		// RETORNAR VALOR 

    		return ["response" => true, "codigo" => $codigo];


    		/*  --------------------------------------------------------------------------------- */

    	} catch (Exception $e) {

    		DB::rollBack();

    		/*  --------------------------------------------------------------------------------- */

    		// RETORNAR VALOR 
    		
    		return ["response" => false];

    		/*  --------------------------------------------------------------------------------- */
    	}

    	/*  --------------------------------------------------------------------------------- */

	}

	public static function guardar_modificar_compra($data){

		/*  --------------------------------------------------------------------------------- */

		// DE ACUERDO A LA OPCION QUE VENGA DESDE EL CLIENTE 

		if ($data->data["guardar"] === true) {
			return Compra::guardar_compra($data);
		} else {
			return Compra::modificar_compra($data);
		}

		/*  --------------------------------------------------------------------------------- */

	}  

}
