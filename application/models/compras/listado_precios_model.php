<?php
class listado_precios_model extends Base_Model{

	public function db_get_data($data=array()){
		// DB Info
		$tbl = $this->tbl;
		
		// Filtro
		$filtro 		= (isset($data['buscar']))?$data['buscar']:false;
		$limit 			= (isset($data['limit']))?$data['limit']:0;
		$offset 		= (isset($data['offset']))?$data['offset']:0;
		$aplicar_limit 	= (isset($data['aplicar_limit']))?true:false;
		$filtro = ($filtro!="") ? "AND (a.presentacion_x_embalaje LIKE '%$filtro%' OR
										p.um_x_presentacion 	  LIKE '%$filtro%' OR
										p.upc  	   				  LIKE '%$filtro%' OR
										p.sku  	   				  LIKE '%$filtro%' OR
										b.articulo  	   		  LIKE '%$filtro%' OR
										c.nombre_comercial 		  LIKE '%$filtro%' OR
										d.marca 		   		  LIKE '%$filtro%' OR
										e.presentacion 	   		  LIKE '%$filtro%' OR
										f.embalaje 	       		  LIKE '%$filtro%')" : "";
		$limit 			= ($aplicar_limit) ? "LIMIT $offset ,$limit" : "";
		// Query
		$query="SELECT 
					 a.id_compras_articulo_precio_proveedor as id_compras_articulo_precios
					,a.id_compras_articulo_presentacion 
					,p.upc
					,p.sku
					,a.id_articulo
					,a.id_proveedor
					,p.id_marca
					,p.id_presentacion
					,a.id_embalaje
					,a.presentacion_x_embalaje
					,a.costo_sin_impuesto
					,a.um_x_embalaje
					,p.um_x_presentacion
					,p.peso_unitario
					,a.costo_unitario
					,a.costo_x_um
					,a.timestamp
					,b.articulo
					,c.nombre_comercial
					,d.marca
					,e.presentacion
					,e.clave_corta as cl_presentacion
					,f.embalaje
					,f.clave_corta as cl_embalaje
					,g.valor as impuesto
					,h.clave_corta as cl_um
					,i.clave_corta as cl_region
					,IF(e.presentacion IS NOT NULL, CONVERT(CONCAT(IFNULL(e.presentacion,''), ' DE ', IFNULL(p.um_x_presentacion,''), ' ', IFNULL(h.clave_corta,'')) USING utf8),null) as presentacion_detalle
				FROM $tbl[compras_articulos_precios_proveedores] a 
				LEFT JOIN $tbl[compras_articulos_presentaciones] p on a.id_compras_articulo_presentacion = p.id_compras_articulo_presentacion
				LEFT JOIN $tbl[compras_articulos] b on a.id_articulo = b.id_compras_articulo
				LEFT JOIN $tbl[compras_proveedores] c on a.id_proveedor	= c.id_compras_proveedor
				LEFT JOIN $tbl[compras_marcas] d on p.id_marca = d.id_compras_marca
				LEFT JOIN $tbl[compras_presentaciones] e on p.id_presentacion = e.id_compras_presentacion
				LEFT JOIN $tbl[compras_embalaje] f on a.id_embalaje = f.id_compras_embalaje
				LEFT JOIN $tbl[administracion_impuestos] g on a.id_impuesto	= g.id_administracion_impuestos
				LEFT JOIN $tbl[compras_um] h on b.id_compras_um = h.id_compras_um
				WHERE a.activo = 1 AND 1  $filtro
				ORDER BY a.id_compras_articulo_precio_proveedor ASC
				$limit";
      	// Execute querie
				//echo $query;
		//print_debug($query);
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}
	public function db_get_data_x_proveedor($id_proveedor=false){
		$condicion =($id_proveedor)?"AND a.id_proveedor= '$id_proveedor'":"";
		// DB Info
		$tbl = $this->tbl;
		// Query
		$query="SELECT 
					 a.id_compras_articulo_precio_proveedor as id_compras_articulo_precios
					,a.id_compras_articulo_presentacion
					,p.upc
					,p.sku
					,a.id_articulo
					,a.id_proveedor
					,p.id_marca
					,p.id_presentacion
					,a.id_embalaje
					,a.id_impuesto
					,a.presentacion_x_embalaje
					,a.costo_sin_impuesto
					,a.um_x_embalaje
					,p.um_x_presentacion
					,p.peso_unitario
					,a.costo_unitario
					,a.costo_x_um
					,a.timestamp
					,b.articulo
					,c.nombre_comercial
					,d.marca
					,e.presentacion
					,e.clave_corta as cl_presentacion
					,f.embalaje
					,f.clave_corta as cl_embalaje
					,g.valor as impuesto
					,h.clave_corta as cl_um
					,IF(e.presentacion IS NOT NULL, CONVERT(CONCAT(IFNULL(e.presentacion,''), ' DE ', IFNULL(p.um_x_presentacion,''), ' ', IFNULL(h.clave_corta,'')) USING utf8),null) as presentacion_detalle
				FROM $tbl[compras_articulos_precios_proveedores] a 
				LEFT JOIN $tbl[compras_articulos_presentaciones] p on a.id_compras_articulo_presentacion = p.id_compras_articulo_presentacion
				LEFT JOIN $tbl[compras_articulos] b on a.id_articulo = b.id_compras_articulo
				LEFT JOIN $tbl[compras_proveedores] c on a.id_proveedor	= c.id_compras_proveedor
				LEFT JOIN $tbl[compras_marcas] d on p.id_marca = d.id_compras_marca
				LEFT JOIN $tbl[compras_presentaciones] e on p.id_presentacion = e.id_compras_presentacion
				LEFT JOIN $tbl[compras_embalaje] f on a.id_embalaje = f.id_compras_embalaje
				LEFT JOIN $tbl[administracion_impuestos] g on a.id_impuesto	= g.id_administracion_impuestos
				LEFT JOIN $tbl[compras_um] h on b.id_compras_um = h.id_compras_um
				WHERE a.activo = 1 AND 1  $condicion
				ORDER BY a.id_compras_articulo_precio_proveedor ASC";
      	// Execute querie
				//echo $query;
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}
	public function db_get_data_x_articulos($id_compras_articulo_precio_proveedor=false){
		$condicion =($id_compras_articulo_precio_proveedor)?"AND a.id_compras_articulo_precio_proveedor= '$id_compras_articulo_precio_proveedor'":"";
		// DB Info
		$tbl = $this->tbl;
		// Query
		$query="SELECT 
					 a.id_compras_articulo_precio_proveedor as id_compras_articulo_precios
					,a.id_compras_articulo_presentacion
					,p.upc
					,p.sku
					,a.id_articulo
					,a.id_proveedor
					,p.id_marca
					,p.id_presentacion
					,a.id_embalaje
					,a.id_impuesto
					,a.presentacion_x_embalaje
					,a.costo_sin_impuesto
					,a.um_x_embalaje
					,p.um_x_presentacion
					,p.peso_unitario
					,a.costo_unitario
					,a.costo_x_um
					,a.timestamp
					,b.articulo
					,c.nombre_comercial
					,d.marca
					,e.presentacion
					,e.clave_corta as cl_presentacion
					,f.embalaje
					,f.clave_corta as cl_embalaje
					,g.valor as impuesto
					,h.clave_corta as cl_um
					,IF(e.presentacion IS NOT NULL, CONVERT(CONCAT(IFNULL(e.presentacion,''), ' DE ', IFNULL(p.um_x_presentacion,''), ' ', IFNULL(h.clave_corta,'')) USING utf8),null) as presentacion_detalle
				FROM $tbl[compras_articulos_precios_proveedores] a 
				LEFT JOIN $tbl[compras_articulos_presentaciones] p on a.id_compras_articulo_presentacion = p.id_compras_articulo_presentacion
				LEFT JOIN $tbl[compras_articulos] b on a.id_articulo = b.id_compras_articulo
				LEFT JOIN $tbl[compras_proveedores] c on a.id_proveedor	= c.id_compras_proveedor
				LEFT JOIN $tbl[compras_marcas] d on p.id_marca = d.id_compras_marca
				LEFT JOIN $tbl[compras_presentaciones] e on p.id_presentacion = e.id_compras_presentacion
				LEFT JOIN $tbl[compras_embalaje] f on a.id_embalaje = f.id_compras_embalaje
				LEFT JOIN $tbl[administracion_impuestos] g on a.id_impuesto	= g.id_administracion_impuestos
				LEFT JOIN $tbl[compras_um] h on b.id_compras_um = h.id_compras_um
				WHERE a.activo = 1 AND 1  $condicion
				ORDER BY a.id_compras_articulo_precio_proveedor ASC";
      	// Execute querie
				//echo $query;
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}
	public function get_data_articulo_default($data=array()){
		$id_administracion_region = " AND a.id_administracion_region=".$data['id_administracion_region'];
		$id_articulo = " AND a.id_articulo=".$data['id_articulo'];
		$condicion=$id_administracion_region.$id_articulo;
		// DB Info
		$tbl = $this->tbl;
		// Query
		$query="SELECT 
					 a.id_compras_articulo_precio_proveedor as id_compras_articulo_precios
					,a.id_administracion_region
					,a.articulo_default
					
				from $tbl[compras_articulos_precios_proveedores] a 
				WHERE a.activo = 1 AND 1  $condicion;";
      	// Execute querie
				//echo $query;
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}
	public function update_listado_principal($data = array(),$id_region){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$id_articulo = " AND id_articulo = ".$data['id_articulo'];
		$condicion = "id_administracion_region = ".$id_region.$id_articulo;

		$update = $this->update_item($tbl['compras_articulos_precios_proveedores'], $data, 'articulo_default', $condicion);
		return $update;
	}
	public function db_insert_data($data = array()){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$insert = $this->insert_item($tbl['compras_articulos_precios_proveedores'], $data);
		$ultimo_id  = $this->db->insert_id();
		//ULTIMO ID
		$query="SELECT id_row FROM $tbl[administracion_movimientos] WHERE id_administracion_movimientos=$ultimo_id";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
		//return $insert;
	}
	public function get_data_unico($id_compras_articulo_precio_proveedor){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$query = "SELECT a.*, b.valor as impuesto_porcentaje FROM $tbl[compras_articulos_precios_proveedores] a
					LEFT JOIN $tbl[administracion_impuestos] b ON a.id_impuesto=b.id_administracion_impuestos
					WHERE id_compras_articulo_precio_proveedor = $id_compras_articulo_precio_proveedor";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}
	public function db_update_data($data=array()){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$condicion = "id_compras_articulo_precio_proveedor = ".$data['id_compras_articulo_precios'];
		$update = $this->update_item($tbl['compras_articulos_precios_proveedores'], $data, 'id_compras_articulo_precio_proveedor', $condicion);
		return $update;
	}
	public function db_update_sku($data=array()){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$condicion = "id_compras_articulo_precio_proveedor = ".$data['id_compras_articulo_precios'];
		$update = $this->update_item($tbl['compras_articulos_precios_proveedores'], $data, 'id_compras_articulo_precio_proveedor', $condicion);
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
}
?>