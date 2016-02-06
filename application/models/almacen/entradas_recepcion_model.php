<?php
class entradas_recepcion_model extends Base_Model{
	public function db_get_data($data=array()){	
		// DB Info
		$tbl = $this->tbl;
		// Filtro
		$filtro = (isset($data['buscar']))?$data['buscar']:false;
		$limit 			= (isset($data['limit']))?$data['limit']:0;
		$offset 		= (isset($data['offset']))?$data['offset']:0;
		$aplicar_limit 	= (isset($data['aplicar_limit']))?true:false;

		$filtro = ($filtro!="") ? "and (a.orden_num LIKE '%$filtro%' 
							   or b.razon_social LIKE '%$filtro%'
							   or a.descripcion LIKE '%$filtro%'
							   or c.estatus LIKE '%$filtro%'
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
				WHERE a.activo=1 AND a.estatus = 2 AND 1  $filtro
				ORDER BY orden_num ASC
				$limit";
				//echo $query;

      	// Execute querie

      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}
	public function db_get_data_orden_listado_registrado_unificado($data = array()){
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
					c.nombre_comercial,
					b.articulo,
					b.id_compras_articulo,
					b.id_articulo_tipo,
					b.id_compras_um,
					a.costo_sin_impuesto,
					a.id_compras_articulo_presentacion,
					p.upc,
					p.sku,
					a.presentacion_x_embalaje,
					a.edit_timestamp,
					p.peso_unitario,
					a.um_x_embalaje,
					p.um_x_presentacion,
					e.clave_corta as cl_presentacion,
					f.embalaje,
					h.clave_corta as cl_um,
					h.unidad_minima,
					e.presentacion
					,IF(e.presentacion IS NOT NULL, CONVERT(CONCAT(IFNULL(e.presentacion,''), ' DE ', IFNULL(p.um_x_presentacion,''), ' ', IFNULL(h.clave_corta,'')) USING utf8),null) as presentacion_detalle
					FROM $tbl[compras_ordenes_articulos] l
					LEFT JOIN $tbl[compras_articulos_precios_proveedores] a on l.id_compras_articulo_precios = a.id_compras_articulo_precio_proveedor
					LEFT JOIN $tbl[compras_articulos_presentaciones] p on a.id_compras_articulo_presentacion = p.id_compras_articulo_presentacion
					LEFT JOIN $tbl[compras_articulos] b on a.id_articulo = b.id_compras_articulo
					LEFT JOIN $tbl[compras_proveedores] c on a.id_proveedor = c.id_compras_proveedor
					LEFT JOIN $tbl[compras_presentaciones] e on p.id_presentacion = e.id_compras_presentacion
					LEFT JOIN $tbl[compras_embalaje] f on a.id_embalaje = f.id_compras_embalaje
					LEFT JOIN $tbl[compras_um] h on b.id_compras_um = h.id_compras_um
				WHERE 1 AND l.activo = 1 $condicion";
		//echo $query;
				//dump_var($query);
      	// Execute querie
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}
	public function insert($data){
		// DB Info
		$tbl = $this->tbl;
		// Query
		if($insert = $this->insert_item($tbl['almacen_entradas_recibir'], $data,true)){
			// Logs ToDo
			return $insert;
		}else{
			return false;
		}

	}
	public function insert_entradas_partidas($data){
		// DB Info
		$tbl = $this->tbl;
		// Query
		/*$existe = $this->row_exist($tbl['almacen_entradas_recibir']);
		if(!$existe){*/
			$insert = $this->insert_item($tbl['almacen_entradas_partidas'], $data);
			return $insert;
			//return $last_id = $this->last_id();
		/*}else{
			return false;
		}*/
	}	
	public function insert_entradas_stock($data){
		// DB Info
		$tbl = $this->tbl;
		// Query
		/*$existe = $this->row_exist($tbl['almacen_entradas_recibir']);
		if(!$existe){*/
			$insert = $this->insert_item($tbl['almacen_stock'], $data);
			return $insert;
			//return $last_id = $this->last_id();
		/*}else{
			return false;
		}*/
	}	
}
?>