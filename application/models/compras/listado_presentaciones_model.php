<?php
class listado_presentaciones_model extends Base_Model{

	public function db_get_data($data=array()){
		// DB Info
		$tbl = $this->tbl;
		
		// Filtro
		$filtro 		= (isset($data['buscar']))?$data['buscar']:false;
		$limit 			= (isset($data['limit']))?$data['limit']:0;
		$offset 		= (isset($data['offset']))?$data['offset']:0;
		$aplicar_limit 	= (isset($data['aplicar_limit']))?true:false;
		$filtro = ($filtro!="") ? "AND (
										a.um_x_presentacion 	  LIKE '%$filtro%' OR
										a.upc  	   				  LIKE '%$filtro%' OR
										a.sku  	   				  LIKE '%$filtro%' OR
										b.articulo  	   		  LIKE '%$filtro%' OR
										d.marca 		   		  LIKE '%$filtro%' OR
										e.presentacion 	   		  LIKE '%$filtro%' )" : "";
		$limit 			= ($aplicar_limit) ? "LIMIT $offset ,$limit" : "";
		// Query
		$query="SELECT 
					a.id_compras_articulo_presentacion
					,a.upc
					,a.sku
					,a.id_articulo
					,a.id_marca
					,a.id_presentacion
					,a.um_x_presentacion
					,a.peso_unitario
					,a.timestamp
					,b.articulo
					,d.marca
					,e.presentacion
					,IF(e.presentacion IS NOT NULL, CONVERT(CONCAT(IFNULL(e.presentacion,''), ' DE ', IFNULL(a.um_x_presentacion,''), ' ', IFNULL(h.clave_corta,'')) USING utf8),null) as presentacion_detalle
					,e.clave_corta as cl_presentacion
					,h.clave_corta as cl_um
					,a.precio_publico
					,a.precio_publico_con_impuesto
					,a.rendimiento
					,b.avatar
				from $tbl[compras_articulos_presentaciones] a 
				LEFT JOIN $tbl[compras_articulos] b on a.id_articulo = b.id_compras_articulo
				LEFT JOIN $tbl[compras_marcas] d on a.id_marca = d.id_compras_marca
				LEFT JOIN $tbl[compras_presentaciones] e on a.id_presentacion = e.id_compras_presentacion
				LEFT JOIN $tbl[compras_um] h on b.id_compras_um	= h.id_compras_um
				WHERE a.activo = 1 AND 1  $filtro
				ORDER BY a.id_compras_articulo_presentacion ASC
				$limit";
      	// Execute querie
		// dump_var($query);
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}

	public function db_get_data_x_articulos($id_compras_articulo_presentacion=false){
		$condicion =($id_compras_articulo_presentacion)?"AND a.id_compras_articulo_presentacion= '$id_compras_articulo_presentacion'":"";
		// DB Info
		$tbl = $this->tbl;
		// Query
		$query="SELECT 
					a.id_compras_articulo_presentacion
					,a.upc
					,a.sku
					,a.id_articulo
					,a.id_marca
					,a.id_presentacion
					,a.peso_unitario
					,a.timestamp
					,b.articulo
					,d.marca
					,e.presentacion
					,e.clave_corta as cl_presentacion
					,g.valor as impuesto
					,h.clave_corta as cl_um
				from $tbl[compras_articulos_presentaciones] a 
				LEFT JOIN $tbl[compras_articulos] b on a.id_articulo  	= b.id_compras_articulo
				LEFT JOIN $tbl[compras_marcas] d on a.id_marca			= d.id_compras_marca
				LEFT JOIN $tbl[compras_presentaciones] e on a.id_presentacion	= e.id_compras_presentacion
				LEFT JOIN $tbl[administracion_impuestos] g on a.id_impuesto    	= g.id_administracion_impuestos
				LEFT JOIN $tbl[compras_um] h on b.id_compras_um    	= h.id_compras_um
				WHERE a.activo = 1 AND 1  $condicion
				ORDER BY a.id_compras_articulo_presentacion ASC";
      	// Execute querie
				//echo $query;
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}

	public function db_insert_data($data = array()){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$insert = $this->insert_item($tbl['compras_articulos_presentaciones'], $data);
		$ultimo_id  = $this->db->insert_id();
		//ULTIMO ID
		$query="SELECT id_row FROM $tbl[administracion_movimientos] WHERE id_administracion_movimientos=$ultimo_id";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
		//return $insert;
	}
	public function get_data_unico($id_compras_articulo_presentacion){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$query = "SELECT 
					a.*, 
					b.valor as impuesto_porcentaje ,
					c.avatar,
					at.articulo_tipo,
					at.id_articulo_tipo
				FROM $tbl[compras_articulos_presentaciones] a
				LEFT JOIN $tbl[administracion_impuestos] b ON a.id_impuesto=b.id_administracion_impuestos
				LEFT JOIN $tbl[compras_articulos] c ON c.id_compras_articulo = a.id_articulo
				-- LEFT JOIN $tbl[compras_articulos_presentaciones] ap ON ap.id_articulo = c.id_compras_articulo
				LEFT JOIN $tbl[compras_articulos_tipo] at ON at.id_articulo_tipo = c.id_articulo_tipo
				WHERE id_compras_articulo_presentacion = $id_compras_articulo_presentacion";
					// dump_var($query);
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	public function get_tipo_articulo($id_articulo){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$query = "SELECT 
					a.id_compras_articulo,
					at.articulo_tipo,
					at.id_articulo_tipo
				FROM $tbl[compras_articulos] a
				LEFT JOIN $tbl[compras_articulos_tipo] at ON at.id_articulo_tipo = a.id_articulo_tipo
				WHERE at.activo = 1 AND a.activo = 1 AND a.id_compras_articulo = $id_articulo";
					// dump_var($query);
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}
	public function db_update_data($data=array()){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$condicion = "id_compras_articulo_presentacion = ".$data['id_compras_articulo_presentacion'];
		$update = $this->update_item($tbl['compras_articulos_presentaciones'], $data, 'id_compras_articulo_presentacion', $condicion);
		return $update;
	}
	public function db_update_sku($data=array()){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$condicion = "id_compras_articulo_presentacion = ".$data['id_compras_articulo_presentacion'];
		$update = $this->update_item($tbl['compras_articulos_presentaciones'], $data, 'id_compras_articulo_presentacion', $condicion);
		return $update;
	}
	public function get_articulos_um($id_compras_articulos){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$query="SELECT 
					a.id_compras_articulo,
					a.clave_corta,
					a.id_compras_um as id_unidad_medida,
					b.id_compras_um,
					b.um,
					b.clave_corta as cv_um
				FROM $tbl[compras_articulos] a
				LEFT JOIN $tbl[compras_um] b ON a.id_compras_um = b.id_compras_um
				WHERE a.id_compras_articulo= $id_compras_articulos ";
		//echo $query;
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	///CONSULTA PARA VALIDAR QUE EL ARTICULO SE ENCUENTRA EN UNA RECETA
	public function get_recetas_articulo($id_compras_articulo){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$query="SELECT 
					*
				from 
					$tbl[nutricion_recetas_articulos] 
				WHERE 
				id_compras_articulo=$id_compras_articulo";
      	// Execute querie
				//echo $query;
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}

	public function db_get_listado_presentaciones_user($id_listado_presentaciones){
		$tbl = $this->tbl;
		$query="SELECT COUNT(*) num_listado_presentaciones FROM (
			    SELECT id_compras_articulo_presentacion  FROM $tbl[compras_articulos_precios_proveedores] WHERE id_compras_articulo_presentacion = $id_listado_presentaciones) lp";
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}
}
?>