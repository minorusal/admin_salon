<?php
class ordenes_model extends Base_Model{

	public function insert($data=array()){
		//print_debug($data);
		// DB Info
		$tbl = $this->tbl;
		// Query
		$existe = $this->row_exist($tbl['compras_ordenes'],array('orden_num ='=> $data['orden_num']));
		if(!$existe){
			$insert = $this->insert_item($tbl['compras_ordenes'], $data);
			return $insert;
		}else{
			return false;
		}
	}
	public function db_update_data($data=array()){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$id_compras_orden   = (isset($data['id_compras_orden']))?$data['id_compras_orden']:false;
		$filtro 			= ($id_compras_orden)?"id_compras_orden='$id_compras_orden'":'';
		$update    			= $this->update_item($tbl['compras_ordenes'], $data, 'id_compras_orden', $filtro);
		return $update;
	}
	public function db_get_data($data=array()){	
		// DB Info
		$tbl = $this->tbl;
		
		$filtro_sucursal = $this->privileges_sucursal('e');

		$filtro = (isset($data['buscar']))?$data['buscar']:false;
		$limit 			= (isset($data['limit']))?$data['limit']:0;
		$offset 		= (isset($data['offset']))?$data['offset']:0;
		$aplicar_limit 	= (isset($data['aplicar_limit']))?true:false;

		$filtro = ($filtro!="") ? "and (a.orden_num LIKE '%$filtro%' 
							   or b.razon_social LIKE '%$filtro%'
							   or a.descripcion LIKE '%$filtro%'
							   or c.estatus LIKE '%$filtro%'
							   or e.sucursal LIKE '%$filtro%'
							   )" 
							: "";
		$limit 			= ($aplicar_limit) ? "LIMIT $offset ,$limit" : "";
		// Query
		$query="SELECT 
					a.id_compras_orden 
					,a.orden_num
					,a.orden_fecha
					,a.descripcion
					,a.entrega_direccion
					,a.entrega_fecha
					,a.observaciones
					,a.prefactura_num
					,a.timestamp
					,b.razon_social
					,c.estatus
					,d.orden_tipo
					,e.sucursal
					,f.forma_pago
					,g.credito
				from $tbl[compras_ordenes] a 
				LEFT JOIN $tbl[compras_proveedores] b on a.id_proveedor=b.id_compras_proveedor
				LEFT JOIN $tbl[compras_ordenes_estatus] c on a.estatus=c.id_estatus
				LEFT JOIN $tbl[compras_ordenes_tipo] d on a.id_orden_tipo=d.id_orden_tipo
				LEFT JOIN $tbl[sucursales] e on a.id_sucursal=e.id_sucursal
				LEFT JOIN $tbl[administracion_forma_pago] f on a.id_forma_pago=f.id_forma_pago
				LEFT JOIN $tbl[administracion_creditos] g on a.id_credito=g.id_administracion_creditos
				WHERE a.activo=1 $filtro_sucursal AND a.estatus IN (1,3) AND 1  $filtro
				ORDER BY orden_num ASC
				$limit";
				//echo $query;

      	// Execute querie

      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}
	public function get_orden_unico($id_compras_orden){
		// DB Info
		$tbl = $this->tbl;
		// Query
		//$query = "SELECT * FROM $tbl[compras_ordenes] WHERE id_compras_orden = $id_compras_orden";
		$query="SELECT a.*, b.estatus
				from $tbl[compras_ordenes] a 
				LEFT JOIN $tbl[compras_ordenes_estatus] b ON a.estatus=b.id_estatus
				WHERE a.activo=1 AND id_compras_orden = $id_compras_orden;";

		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}
	public function db_get_proveedores($data=array(),$id_compras_proveedor=false){
		$condicion =($id_compras_proveedor)?"AND id_compras_proveedor= '$id_compras_proveedor'":"";
		// DB Info
		$tbl = $this->tbl;
		// Filtro
		$buscar = (isset($data['buscar']))?$data['buscar']:false;
		$filtro = ($buscar) ? "" : "";
		// Limit
		$limit 			= (isset($data['limit']))?$data['limit']:0;
		$offset 		= (isset($data['offset']))?$data['offset']:0;
		$aplicar_limit 	= (isset($data['aplicar_limit']))?true:false;
		$limit 			= ($aplicar_limit) ? "LIMIT $offset ,$limit" : "";
		// Query
		$query = "	SELECT 
						 id_compras_proveedor
						,razon_social
						,nombre_comercial
						,clave_corta
					FROM $tbl[compras_proveedores]
					WHERE 1 AND activo=1 $filtro $condicion
					ORDER BY clave_corta ASC
					$limit
					";
		// dump_var($query);
      	// Execute querie
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}
	public function db_get_total_rows($data=array()){
		// DB Info
		$tbl = $this->tbl;
		// Filtro
		$buscar = (isset($data['buscar']))?$data['buscar']:false;
		$filtro = ($buscar) ? "and (orden_num LIKE '$buscar%' 
							   or razon_social LIKE '$buscar%'
							   or descripcion LIKE '$buscar%'
							   )" 
							: "";
		// Query
		$query = "	SELECT count(*)
					FROM $tbl[vw_compras_orden_proveedores]
					WHERE 1 AND activo=1 $filtro
					ORDER BY orden_num ASC
					";
		 dump_var($query);
      	// Execute querie
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}
	public function db_get_tipo_orden($id_orden_tipo=false){
		$condicion =($id_orden_tipo)?"AND id_orden_tipo= '$id_orden_tipo'":"";
		// DB Info
		$tbl = $this->tbl;
		// Query
		$query = "SELECT * FROM $tbl[compras_ordenes_tipo] WHERE activo= 1 $condicion";
      	// Execute querie
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}
	public function db_get_data_orde_listado_precio($data = array()){
		$id_compras_orden 			 = (isset($data['id_compras_orden']))?$data['id_compras_orden']:false;
		$id_compras_articulo_precios = (isset($data['id_compras_articulo_precios']))?$data['id_compras_articulo_precios']:false;
		$condicion = ($id_compras_orden)?("AND id_compras_orden = '$id_compras_orden'"):false;
		$condicion2 = ($id_compras_articulo_precios)?("AND id_compras_articulo_precios = '$id_compras_articulo_precios'"):false;
		// DB Info
		$tbl = $this->tbl;
		// Query
		$query = "SELECT * FROM $tbl[compras_ordenes_articulos] WHERE activo= 1 $condicion $condicion2";
		//echo $query;

      	// Execute querie
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}
	public function db_insert_orde_listado_articulos($data=array()){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$insert = $this->insert_item($tbl['compras_ordenes_articulos'], $data);
		return $insert;
	}
	public function db_update_orden_listado_articulos($data=array()){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$id_compras_orden   		  = (isset($data['id_compras_orden']))?$data['id_compras_orden']:false;
		$id_compras_articulo_precios  = (isset($data['id_compras_articulo_precios']))?$data['id_compras_articulo_precios']:false;

		$filtro 					  = ($id_compras_articulo_precios)?"id_compras_articulo_precios='$id_compras_articulo_precios' AND id_compras_orden ='$id_compras_orden'":'';
		$update    					  = $this->update_item($tbl['compras_ordenes_articulos'], $data, 'id_compras_orden', $filtro);
		return $update;
	}
	public function db_get_data_orden_listado_registrado($data = array()){
		$id_compras_orden 			 = (isset($data['id_compras_orden']))?$data['id_compras_orden']:false;
		$condicion = ($id_compras_orden)?("AND id_compras_orden = '$id_compras_orden'"):false;
		$tbl = $this->tbl;
		// Query
		$query = "SELECT 
					l.id_compras_orden_articulo,
					l.id_compras_articulo_precios,
					l.cantidad,
					l.costo_x_cantidad,
					l.descuento,
					l.impuesto_porcentaje,
					l.subtotal,
					l.valor_impuesto,
					l.total,
					a.costo_sin_impuesto,
					c.nombre_comercial,
					b.articulo,
					b.id_compras_articulo,
					b.id_articulo_tipo,
					b.id_compras_um,
					p.upc,
					p.sku,
					e.clave_corta as cl_presentacion
					,f.embalaje
					,h.clave_corta as cl_um
					,p.peso_unitario
					,e.presentacion
					,a.presentacion_x_embalaje
					,a.edit_timestamp
					,IF(e.presentacion IS NOT NULL, CONVERT(CONCAT(IFNULL(e.presentacion,''), ' DE ', IFNULL(p.um_x_presentacion,''), ' ', IFNULL(h.clave_corta,'')) USING utf8),null) as presentacion_detalle
					FROM $tbl[compras_ordenes_articulos] l
					LEFT JOIN $tbl[compras_articulos_precios_proveedores] a on l.id_compras_articulo_precios = a.id_compras_articulo_precio_proveedor
					LEFT JOIN $tbl[compras_articulos_presentaciones] p on a.id_compras_articulo_presentacion = p.id_compras_articulo_presentacion
					LEFT JOIN $tbl[compras_articulos] b on a.id_articulo  	= b.id_compras_articulo
					LEFT JOIN $tbl[compras_proveedores] c on a.id_proveedor 	= c.id_compras_proveedor
					LEFT JOIN $tbl[compras_presentaciones] e on p.id_presentacion	= e.id_compras_presentacion
					LEFT JOIN $tbl[compras_embalaje] f on a.id_embalaje    	= f.id_compras_embalaje
					LEFT JOIN $tbl[compras_um] h on b.id_compras_um    	= h.id_compras_um
				WHERE 1 AND l.activo = 1 $condicion";
		//echo $query;
      	// Execute querie
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}
	public function db_update_activo_orden_listado($data=array()){
		$id_compras_orden  			  = (isset($data['id_compras_orden']))?$data['id_compras_orden']:false;
		$id_compras_articulo_precios  = (isset($data['id_compras_articulo_precios']))?$data['id_compras_articulo_precios']:false;
		$filtro 					  = ($id_compras_articulo_precios)?"id_compras_articulo_precios='$id_compras_articulo_precios' AND id_compras_orden ='$id_compras_orden'":'';
		
		$tbl = $this->tbl;

		 $update    = $this->update_item($tbl['compras_ordenes_articulos'], $data, 'id_compras_orden', $filtro);
		return $update;
	}
	public function db_get_data_historial($data=array()){	
		// DB Info
		$tbl = $this->tbl;
		$filtro_sucursal = $this->privileges_sucursal('e');
		$filtro = (isset($data['buscar']))?$data['buscar']:false;
		$limit 			= (isset($data['limit']))?$data['limit']:0;
		$offset 		= (isset($data['offset']))?$data['offset']:0;
		$aplicar_limit 	= (isset($data['aplicar_limit']))?true:false;

		$filtro = ($filtro!="") ? "and (a.orden_num LIKE '%$filtro%' 
							   or b.razon_social LIKE '%$filtro%'
							   or a.descripcion LIKE '%$filtro%'
							   or c.estatus LIKE '%$filtro%'
							    or e.sucursal LIKE '%$filtro%'
							   )" 
							: "";
		$limit 			= ($aplicar_limit) ? "LIMIT $offset ,$limit" : "";
		// Query
		$query="SELECT 
					a.id_compras_orden 
					,a.orden_num
					,a.orden_fecha
					,a.descripcion
					,a.entrega_direccion
					,a.entrega_fecha
					,a.observaciones
					,a.prefactura_num
					,a.timestamp
					,b.razon_social
					,c.estatus
					,d.orden_tipo
					,e.sucursal
					,f.forma_pago
					,g.credito
				from $tbl[compras_ordenes] a 
				LEFT JOIN $tbl[compras_proveedores] b on a.id_proveedor=b.id_compras_proveedor
				LEFT JOIN $tbl[compras_ordenes_estatus] c on a.estatus=c.id_estatus
				LEFT JOIN $tbl[compras_ordenes_tipo] d on a.id_orden_tipo=d.id_orden_tipo
				LEFT JOIN $tbl[sucursales] e on a.id_sucursal=e.id_sucursal
				LEFT JOIN $tbl[administracion_forma_pago] f on a.id_forma_pago=f.id_forma_pago
				LEFT JOIN $tbl[administracion_creditos] g on a.id_credito=g.id_administracion_creditos
				WHERE a.activo=1 $filtro_sucursal AND 1  $filtro
				ORDER BY orden_num ASC
				$limit";
				//echo $query;

      	// Execute querie

      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}
}
?>