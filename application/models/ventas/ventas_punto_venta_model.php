<?php
class Ventas_punto_venta_model extends Base_Model{
	public function db_get_punto_venta($data = array()){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$filtro         = (isset($data['buscar']))?$data['buscar']:false;
		$limit 			= (isset($data['limit']))?$data['limit']:0;
		$offset 		= (isset($data['offset']))?$data['offset']:0;
		$aplicar_limit 	= (isset($data['aplicar_limit']))?true:false;
		$filtro = ($filtro) ? "AND (pv.punto_venta like '%$filtro%' OR
									pv.clave_corta like '%$filtro%' OR
									pv.descripcion like '%$filtro% OR
									s.sucursal like '%$filtro%')" : "";
		$limit 			= ($aplicar_limit) ? "LIMIT $offset ,$limit" : "";
		//Query
		$query = "	SELECT pv.*
		                  ,s.*
					FROM $tbl[sucursales_punto_venta] pv
					LEFT JOIN $tbl[sucursales] s on s.id_sucursal = pv.id_sucursal
					WHERE pv.activo = 1 $filtro
					ORDER BY pv.id_sucursales_punto_venta ASC
					$limit
					";
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}

	public function db_get_punto_venta_by_sucursal($id_sucursal){
		// DB Info		
		$tbl = $this->tbl;
		$query = "SELECT * 
		          FROM $tbl[sucursales_punto_venta]
		          WHERE id_sucursal = $id_sucursal";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	public function db_get_all_sucursal($data = array()){
		//print_debug($data);
		// DB Info		
		$tbl = $this->tbl;
		$intervalo    = ' AND DATE_FORMAT(V.`timestamp`,"%d/%m/%Y") BETWEEN "'.$data['fecha_inicio'].'" AND "'.$data['fecha_final'].'"'; 
 		$punto_venta  = (isset($data['punto_venta']) && $data['punto_venta'] != '' && $data['punto_venta'] != 0)?'AND V.id_punto_venta = '.$data['punto_venta']:'';
 		$fecha        = ($data['rango'])?$intervalo:'AND DATE_FORMAT(V.`timestamp`,"%d/%m/%Y") = "'.$data['fecha_inicio'].'"';
 		
		$limit 			= (isset($data['limit']))?$data['limit']:0;
		$offset 		= (isset($data['offset']))?$data['offset']:0;
		$aplicar_limit 	= (isset($data['aplicar_limit']))?true:false;
		
		$limit 			= ($aplicar_limit) ? "LIMIT $offset ,$limit" : "";

		$query = "SELECT V.consecutivo
						,V.id_venta 
		                ,V.id_punto_venta
				        ,V.esquema
		    	        ,V.monto_subtotal
				        ,V.monto_descuento
				        ,V.monto_impuestos as venta_impuestos_monto
						,V.monto_subrrogacion as venta_subrrogacion_monto
				        ,V.monto_total
				        ,V.`timestamp`
            	        ,PV.punto_venta
            	        ,CONCAT_WS(' ',C.nombre,C.paterno,C.materno) AS cliente_nom
				        ,A.descripcion
            	        ,A.precio
            	        ,A.cantidad
				        ,P.cambio
				        ,P.efectivo
				        ,P.tarjeta
				        ,P.banco
				        ,DATE_FORMAT(V.`timestamp`,'%d/%m/%Y') as fecha_venta
				  FROM $tbl[ventas] V
				  LEFT JOIN $tbl[sucursales_punto_venta] PV ON PV.id_sucursales_punto_venta = V.id_punto_venta
				  LEFT JOIN $tbl[ventas_clientes] C ON C.id_ventas_clientes = V.id_cliente
				  LEFT JOIN $tbl[ventas_articulos] A ON A.id_venta_local = V.id_venta_local
				  LEFT JOIN $tbl[ventas_pagos] P ON P.id_venta_local = V.id_venta_local
				  WHERE DATE_FORMAT(V.`timestamp`,'%d/%m/%Y') <> '' $punto_venta $fecha 
				  GROUP BY V.id_venta
				  ORDER BY V.`timestamp` 
				  DESC $limit ";
				//print_debug($query);
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	public function db_get_detalle_venta($id_venta){
		// DB Info		
		$tbl = $this->tbl;
		$query="SELECT V.*
                      ,A.descripcion
                      ,A.precio
			          ,A.cantidad
			          ,A.total
                      ,CONCAT_WS(' ',C.nombre,C.paterno,C.materno) AS nombre_cliente
			          ,CO.comprobante
			          ,PV.punto_venta
			          ,PV.clave_corta as cv_punto_venta
			          ,P.cambio
				      ,P.efectivo
				      ,P.tarjeta
				      ,P.banco
                FROM $tbl[ventas] V
                LEFT JOIN $tbl[ventas_articulos] A ON A.id_venta_local = V.id_venta_local
                LEFT JOIN $tbl[ventas_clientes] C ON C.id_ventas_clientes= V.id_cliente
                LEFT JOIN $tbl[ventas_comprobantes] CO ON CO.id_venta_local = V.id_venta_local
                LEFT JOIN $tbl[sucursales_punto_venta] PV ON PV.id_sucursales_punto_venta = V.id_punto_venta
                LEFT JOIN $tbl[ventas_pagos] P ON P.id_venta_local = V.id_venta_local
                WHERE V.id_venta = $id_venta";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	public function db_get_ventas($data = array()){
		//print_debug($data);
		// DB Info		
		$tbl = $this->tbl;
		$intervalo    = ' AND DATE_FORMAT(V.`timestamp`,"%d/%m/%Y") BETWEEN "'.$data['fecha_inicio'].'" AND "'.$data['fecha_final'].'"'; 
 		$punto_venta  = (isset($data['punto_venta']) && $data['punto_venta'] != '' && $data['punto_venta'] != 0)?'AND V.id_punto_venta = '.$data['punto_venta']:'';
 		$fecha        = ($data['rango'])?$intervalo:'AND DATE_FORMAT(V.`timestamp`,"%d/%m/%Y") = "'.$data['fecha_inicio'].'"';
		$query = "SELECT V.consecutivo
						,V.id_venta 
		                ,V.id_punto_venta
				        ,V.esquema
		    	        ,V.monto_subtotal
				        ,V.monto_descuento
				        ,V.monto_impuestos as venta_impuestos_monto
						,V.monto_subrrogacion as venta_subrrogacion_monto
				        ,V.monto_total
				        ,V.`timestamp`
            	        ,PV.punto_venta
            	        ,CONCAT_WS(' ',C.nombre,C.paterno,C.materno) AS cliente_nom
				        ,A.descripcion
            	        ,A.precio
            	        ,A.cantidad
				        ,P.cambio
				        ,P.efectivo
				        ,P.tarjeta
				        ,P.banco
				        ,DATE_FORMAT(V.`timestamp`,'%d/%m/%Y') as fecha_venta
				  FROM $tbl[ventas] V
				  LEFT JOIN $tbl[sucursales_punto_venta] PV ON PV.id_sucursales_punto_venta = V.id_punto_venta
				  LEFT JOIN $tbl[ventas_clientes] C ON C.id_ventas_clientes = V.id_cliente
				  LEFT JOIN $tbl[ventas_articulos] A ON A.id_venta_local = V.id_venta_local
				  LEFT JOIN $tbl[ventas_pagos] P ON P.id_venta_local = V.id_venta_local
				  WHERE DATE_FORMAT(V.`timestamp`,'%d/%m/%Y') <> '' $punto_venta $fecha 
				  ORDER BY V.`timestamp` desc";
				//print_debug($query);
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	public function db_get_insumos_ventas($data = array()){
		$tbl = $this->tbl;
		$query = "SELECT v.`timestamp`
			,va.id_venta_local
			,va.id_inventario
			,va.descripcion
			FROM $tbl[ventas] v
			LEFT JOIN $tbl[ventas_articulos] va ON va.id_venta_local = v.id_venta_local
			WHERE DATE_FORMAT(v.`timestamp`,'%d/%m/%Y') BETWEEN $data[fecha_inicio] AND $data[fecha_final]";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}
}