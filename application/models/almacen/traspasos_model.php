<?php
class traspasos_model extends Base_Model{
	public function db_get_data($data=array()){	
		// DB Info
		$tbl = $this->tbl;
		// Filtro
		$filtro = (isset($data['buscar']))?$data['buscar']:false;
		$limit 			= (isset($data['limit']))?$data['limit']:0;
		$offset 		= (isset($data['offset']))?$data['offset']:0;
		$aplicar_limit 	= (isset($data['aplicar_limit']))?true:false;

		$filtro = ($filtro!="") ? "and (f.almacenes LIKE '%$filtro%' 
									   or d.articulo LIKE '%$filtro%'
									   or e.presentacion LIKE '%$filtro%'
									   or g.gavetas LIKE '%$filtro%'
							   		)" : "";
		$limit 			= ($aplicar_limit) ? "LIMIT $offset ,$limit" : "";
		// Query
		$query="SELECT 
					a.id_stock,
					a.id_almacen,
					a.id_pasillo,
					a.id_gaveta,
					a.id_almacen_entradas_recepcion,
					a.id_compras_orden_articulo,
					a.id_articulo_tipo,
					a.stock,
					a.stock_um,
					a.timestamp as fecha_recepcion,
					c.id_articulo,
					d.articulo,
					e.presentacion,
					IF(e.presentacion IS NOT NULL, CONVERT(CONCAT(IF(i.embalaje IS NOT NULL,CONCAT(i.embalaje,' CON '),''), IFNULL(c.presentacion_x_embalaje,''), ' ', IFNULL(e.presentacion,''), ' DE ', IFNULL(p.um_x_presentacion,''), ' ', IFNULL(j.clave_corta,'')) USING utf8),null) as presentacion_detalle,
					f.almacenes,
					g.gavetas,
					h.articulo_tipo
					,i.embalaje
					,j.clave_corta as cl_um
					,p.peso_unitario
					,e.presentacion
					,c.presentacion_x_embalaje
					,j.unidad_minima_cve					
				from $tbl[almacen_stock] a 
				LEFT JOIN $tbl[compras_ordenes_articulos] b on a.id_compras_orden_articulo=b.id_compras_orden_articulo
				/*LEFT JOIN $tbl[compras_articulos_precios] c on b.id_compras_articulo_precios=c.id_compras_articulo_precios*/
				LEFT JOIN $tbl[compras_articulos_precios_proveedores] c on b.id_compras_articulo_precios = c.id_compras_articulo_precio_proveedor
				LEFT JOIN $tbl[compras_articulos_presentaciones] p on c.id_compras_articulo_presentacion = p.id_compras_articulo_presentacion
				LEFT JOIN $tbl[compras_articulos] d on c.id_articulo=d.id_compras_articulo
				LEFT JOIN $tbl[compras_presentaciones] e on p.id_presentacion=e.id_compras_presentacion
				LEFT JOIN $tbl[almacen_almacenes] f on a.id_almacen=f.id_almacen_almacenes
				LEFT JOIN $tbl[almacen_gavetas] g on a.id_gaveta=g.id_almacen_gavetas
				LEFT JOIN $tbl[compras_articulos_tipo] h on a.id_articulo_tipo=h.id_articulo_tipo
				LEFT JOIN $tbl[compras_embalaje] i on c.id_embalaje = i.id_compras_embalaje
				LEFT JOIN $tbl[compras_um] j on d.id_compras_um = j.id_compras_um
			WHERE a.id_almacen != 1 $filtro
			$limit";
	  	// Execute querie

	  	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}
	public function get_data_unico($id_stock){
		$tbl = $this->tbl;
		// Query
		$query = "SELECT 
					a.id_compras_orden_articulo, 
					a.id_compras_orden, 
					a.id_compras_articulo_precios, 
					i.id_almacen_entradas_recepcion,
					p.upc, 
					p.sku,
					b.id_articulo,
					p.id_marca,
					c.marca,
					d.presentacion,
					e.lote,
					e.stock,
					e.stock_um,
					e.id_stock,
					e.caducidad,
					f.gavetas,
					g.almacenes,
					h.pasillos
					,e.id_almacen
					,e.id_pasillo
					,e.id_gaveta
					,j.unidad_minima_cve
					,k.id_articulo_tipo
				FROM 
					$tbl[compras_ordenes_articulos] a
				/*LEFT JOIN $tbl[compras_articulos_precios] b ON a.id_compras_articulo_precios=b.id_compras_articulo_precios*/
				LEFT JOIN $tbl[compras_articulos_precios_proveedores] b on a.id_compras_articulo_precios = b.id_compras_articulo_precio_proveedor
				LEFT JOIN $tbl[compras_articulos_presentaciones] p on b.id_compras_articulo_presentacion = p.id_compras_articulo_presentacion
				LEFT JOIN $tbl[compras_marcas] c ON p.id_marca=c.id_compras_marca
				LEFT JOIN $tbl[compras_presentaciones] d ON p.id_presentacion=d.id_compras_presentacion
				LEFT JOIN $tbl[almacen_stock] e ON a.id_compras_orden_articulo=e.id_compras_orden_articulo
				LEFT JOIN $tbl[almacen_gavetas] f ON e.id_gaveta=f.id_almacen_gavetas
				LEFT JOIN $tbl[almacen_almacenes] g ON e.id_almacen=g.id_almacen_almacenes
				LEFT JOIN $tbl[almacen_pasillos] h ON e.id_pasillo=h.id_almacen_pasillos
				LEFT JOIN $tbl[almacen_entradas_recibir] i ON a.id_compras_orden=i.id_compras_orden
				LEFT JOIN $tbl[compras_articulos] k on b.id_articulo=k.id_compras_articulo
				LEFT JOIN $tbl[compras_um] j on k.id_compras_um = j.id_compras_um
				WHERE e.id_stock = $id_stock";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}
	public function db_update_almacen_pasillo_gaveta($data=array()){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$id_stock = (isset($data['id_stock']))?$data['id_stock']:false;
		$condicion = ($id_stock)?"id_stock='$id_stock'":'';
		$update    = $this->update_item($tbl['almacen_stock'], $data, 'id_stock', $condicion);
		return $update;
	}
}
?>