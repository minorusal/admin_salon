<?php
class stock_model extends Base_Model{
/**
* Descripción:	Manejo de stock
* @author 		Oscar Maldonado
* Creación:		2015-07-02
* Modificación:	
*/
	/*STOCK*/
	public function insert_data_stock($data=array()){
	/**
    * Descripción:	Inserta linea en tabla de stock
    * @author 		Oscar Maldonado
    * Creación:		2015-07-09
    * Modificación:	
    */
		// DB Info
		$tbl = $this->tbl;
		// Query
		if($insert = $this->insert_item($tbl['almacen_stock'], $data, true)){
			return $insert;
		}else{
			return false;
		}
	}

	public function update_data_stock($data=array()){
	/**
    * Descripción:	Actualiza linea en tabla de stock
    * @author 		Oscar Maldonado
    * Creación:		2015-07-09
    * Modificación:	
    */
		// DB Info
		$tbl = $this->tbl;
		// Query
		$id_stock = (isset($data['id_stock']))?$data['id_stock']:false;
		$condicion = ($id_stock)?"id_stock='$id_stock'":'';
		if($update    = $this->update_item($tbl['almacen_stock'], $data, 'id_stock', $condicion)){
			return $update;
		}else{
			return false;
		}
	}

	public function convert_embalaje_a_piezas($data=array()){
	/**
    * Descripción:	Calcula la equivalencia de lo comprado y devuelve las piezas y um para insertarlas en el stock
    * @author 		Oscar Maldonado
    * Creación:		2015-11-12
    * Modificación:	
    */
		// DB Info
		$tbl = $this->tbl;
		// Filtros
		$id_compras_orden_articulo 	= (isset($data['id_compras_orden_articulo']))?$data['id_compras_orden_articulo']:false;
		$filtro 					= ($id_compras_orden_articulo)?("AND l.id_compras_orden_articulo = '$id_compras_orden_articulo'"):false;
		// Query
		$query = "SELECT 
					l.id_compras_orden_articulo,
					l.id_compras_articulo_precios,
					b.id_compras_articulo,
					l.cantidad,
					/*a.presentacion_x_embalaje,			
					h.clave_corta as cl_um,
					b.articulo,
					IF(e.presentacion IS NOT NULL, CONVERT(CONCAT(IF(f.embalaje IS NOT NULL,CONCAT(f.embalaje,' CON '),''), IFNULL(a.presentacion_x_embalaje,''), ' ', IFNULL(e.presentacion,''), ' DE ', IFNULL(p.um_x_presentacion,''), ' ', IFNULL(h.clave_corta,'')) USING utf8),null) as presentacion_detalle,
					p.upc,
					p.sku,
					a.um_x_embalaje,
					p.um_x_presentacion,*/
					(l.cantidad * a.presentacion_x_embalaje) as stock_in_pz,
					(l.cantidad * a.um_x_embalaje) as stock_in_um					
					FROM $tbl[compras_ordenes_articulos] l
					LEFT JOIN $tbl[compras_articulos_precios_proveedores] a on l.id_compras_articulo_precios = a.id_compras_articulo_precio_proveedor
					LEFT JOIN $tbl[compras_articulos_presentaciones] p on a.id_compras_articulo_presentacion = p.id_compras_articulo_presentacion
					LEFT JOIN $tbl[compras_articulos] b on a.id_articulo = b.id_compras_articulo
					LEFT JOIN $tbl[compras_proveedores] c on a.id_proveedor = c.id_compras_proveedor
					LEFT JOIN $tbl[compras_presentaciones] e on p.id_presentacion = e.id_compras_presentacion
					LEFT JOIN $tbl[compras_embalaje] f on a.id_embalaje = f.id_compras_embalaje
					LEFT JOIN $tbl[compras_um] h on b.id_compras_um = h.id_compras_um
				WHERE 1 AND l.activo = 1 $filtro";
				// dump_var($query);
      	// Execute querie
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	/*STOCK_LOGS*/
	public function insert_stock_log($data=array()){
	/**
    * Descripción:	Inserta linea en tabla de stock_logs
    * @author 		Oscar Maldonado
    * Creación:		2015-07-02
    * Modificación:	
    */
		// DB Info
		$tbl = $this->tbl;
		// Query
		if($insert = $this->insert_item($tbl['almacen_stock_logs'], $data,true)){
			return $insert;
		}else{
			return false;
		}
	}

	/*STOCK DELETED*/
	public function copy_stock_to_deleted($data=array()){
	/**
    * Descripción:	Copia los registos de la tabla de stock a la tabla de stock_deleted
    * @author 		Oscar Maldonado
    * Creación:		2015-07-15
    * Modificación:	
    */
		// DB Info
		$tbl = $this->tbl;
		// Filtro
		$id_stock 	= (isset($data['id_stock']))?$data['id_stock']:false;
		$filtro 	= ($id_stock)?"AND id_stock='$id_stock'":'';
		// Query
		$sql = "INSERT INTO $tbl[almacen_stock_deleted] (SELECT * FROM $tbl[almacen_stock] WHERE activo=0 AND stock=0 $filtro);";
		$resultado = ($this->db->query($sql))?true:false;
		return $resultado;
	}

	public function delete_stock_en_cero($data=array()){
	/**
    * Descripción:	Borrado físico de los registros con stock = 0
    * @author 		Oscar Maldonado
    * Creación:		2015-07-15
    * Modificación:	
    */
		// DB Info
		$tbl = $this->tbl;
		// Filtro
		$id_stock 	= (isset($data['id_stock']))?$data['id_stock']:false;
		$filtro 	= ($id_stock)?"AND id_stock='$id_stock'":'';
		// Query
		$sql = "DELETE FROM $tbl[almacen_stock] WHERE activo=0 AND stock=0 $filtro;";
		$query = ($this->db->query($sql))?true:false;
		return $query;
	}

	public function delete_stock_en_cero_sync($id_stock){
	/**
    * Descripción:	Borrado físico de los registros con stock = 0
    * @author 		Oscar Maldonado
    * Creación:		2015-07-15
    * Modificación:	
    */
		// DB Info
		$tbl = $this->tbl;

		$sql = "DELETE FROM $tbl[almacen_stock] WHERE  id_stock = $id_stock;";
		$query = ($this->db->query($sql))?true:false;
		return $query;
	}	
}
?>