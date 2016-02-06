<?php
class listado_precios_proveedor_model extends Base_Model{

	public function db_get_data($data=array()){
		// DB Info
		$tbl = $this->tbl;
		
		// Filtro
		$filtro 		= (isset($data['buscar']))?$data['buscar']:false;
		$limit 			= (isset($data['limit']))?$data['limit']:0;
		$offset 		= (isset($data['offset']))?$data['offset']:0;
		$aplicar_limit 	= (isset($data['aplicar_limit']))?true:false;
		$filtro = ($filtro!="") ? "AND (a.presentacion_x_embalaje LIKE '%$filtro%' OR
										b.um_x_presentacion 	  LIKE '%$filtro%' OR
										b.upc  	   				  LIKE '%$filtro%' OR
										b.sku  	   				  LIKE '%$filtro%' OR
										c.articulo  	   		  LIKE '%$filtro%' OR
										j.nombre_comercial 		  LIKE '%$filtro%' OR
										d.marca 		   		  LIKE '%$filtro%' OR
										e.presentacion 	   		  LIKE '%$filtro%' OR
										f.embalaje 	       		  LIKE '%$filtro%')" : "";
		$limit 			= ($aplicar_limit) ? "LIMIT $offset ,$limit" : "";
		// Query
		$query= "SELECT 
					a.id_compras_articulo_precio_proveedor
					,a.id_compras_articulo_presentacion
					,a.id_articulo
					,a.id_proveedor
					,b.upc
					,b.sku
					,b.id_marca
					,b.id_presentacion
					,b.um_x_presentacion
					,b.peso_unitario
					,IF(e.presentacion IS NOT NULL, CONVERT(CONCAT(IFNULL(e.presentacion,''), ' DE ', IFNULL(b.um_x_presentacion,''), ' ', IFNULL(h.clave_corta,'')) USING utf8),null) as presentacion_detalle
					,a.id_embalaje
					,a.presentacion_x_embalaje
					,a.costo_sin_impuesto
					,a.um_x_embalaje
					,a.costo_unitario
					,a.costo_x_um
					,a.timestamp
					,c.articulo
					,j.nombre_comercial
					,d.marca
					,e.presentacion
					,e.clave_corta as cl_presentacion
					,f.embalaje
					,f.clave_corta as cl_embalaje
					,g.valor as impuesto
					,h.clave_corta as cl_um
					,i.clave_corta as cl_region
					,c.avatar
				FROM $tbl[compras_articulos_precios_proveedores] as a
				LEFT JOIN $tbl[compras_articulos_presentaciones] b ON a.id_compras_articulo_presentacion=b.id_compras_articulo_presentacion
				LEFT JOIN $tbl[compras_articulos] c on a.id_articulo = c.id_compras_articulo
				LEFT JOIN $tbl[compras_proveedores] j on a.id_proveedor = j.id_compras_proveedor
				LEFT JOIN $tbl[compras_marcas] d on b.id_marca = d.id_compras_marca
				LEFT JOIN $tbl[compras_presentaciones] e on b.id_presentacion = e.id_compras_presentacion
				LEFT JOIN $tbl[compras_embalaje] f on a.id_embalaje = f.id_compras_embalaje
				LEFT JOIN $tbl[administracion_impuestos] g on a.id_impuesto = g.id_administracion_impuestos
				LEFT JOIN $tbl[compras_um] h on c.id_compras_um	= h.id_compras_um
				LEFT JOIN $tbl[administracion_regiones] i on a.id_administracion_region = i.id_administracion_region
				WHERE 1 AND c.activo=1 $filtro
				ORDER BY a.id_compras_articulo_precio_proveedor ASC
				$limit";
      	// Execute querie
		// dump_var($query);
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
					 a.id_compras_articulo_precio_proveedor
					,a.id_compras_articulo_presentacion
					,a.id_articulo
					,a.id_proveedor
					,b.upc
					,b.sku
					,b.id_marca
					,b.id_presentacion
					,b.um_x_presentacion
					,b.peso_unitario
					,IF(e.presentacion IS NOT NULL, CONVERT(CONCAT(IFNULL(e.presentacion,''), ' DE ', IFNULL(p.um_x_presentacion,''), ' ', IFNULL(h.clave_corta,'')) USING utf8),null) as presentacion_detalle
					,a.id_embalaje
					,b.id_impuesto
					,a.presentacion_x_embalaje
					,a.costo_sin_impuesto
					,a.um_x_embalaje
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
				FROM $tbl[compras_articulos_precios_proveedores] a 
				LEFT JOIN $tbl[compras_articulos_presentaciones] p on a.id_compras_articulo_presentacion = p.id_compras_articulo_presentacion
				LEFT JOIN $tbl[compras_articulos] b on a.id_articulo = b.id_compras_articulo
				LEFT JOIN $tbl[compras_proveedores] c on a.id_proveedor = c.id_compras_proveedor
				LEFT JOIN $tbl[compras_marcas] d on p.id_marca = d.id_compras_marca
				LEFT JOIN $tbl[compras_presentaciones] e on p.id_presentacion = e.id_compras_presentacion
				LEFT JOIN $tbl[compras_embalaje] f on a.id_embalaje = f.id_compras_embalaje
				LEFT JOIN $tbl[administracion_impuestos] g on p.id_impuesto = g.id_administracion_impuestos
				LEFT JOIN $tbl[compras_um] h on b.id_compras_um = h.id_compras_um
				LEFT JOIN $tbl[administracion_regiones] i on a.id_administracion_region = i.id_administracion_region
				WHERE a.activo = 1 AND 1  $condicion
				ORDER BY a.id_compras_articulo_precio_proveedor ASC";
      	// Execute querie
				//echo $query;
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}
	public function db_get_data_x_articulos($id_compras_articulo_precios=false){
		$condicion =($id_compras_articulo_precios)?"AND a.id_compras_articulo_precios= '$id_compras_articulo_precios'":"";
		// DB Info
		$tbl = $this->tbl;
		// Query
		$query="SELECT 
					a.id_compras_articulo_precio_proveedor
					,a.id_compras_articulo_presentacion
					,a.id_articulo
					,a.id_proveedor
					,p.upc
					,p.sku
					,p.id_marca
				--	,b.id_presentacion
					,p.um_x_presentacion
					,p.peso_unitario
					,IF(e.presentacion IS NOT NULL, CONVERT(CONCAT(IFNULL(e.presentacion,''), ' DE ', IFNULL(p.um_x_presentacion,''), ' ', IFNULL(h.clave_corta,'')) USING utf8),null) as presentacion_detalle
					,a.id_embalaje
					,p.id_impuesto
					,a.presentacion_x_embalaje
					,a.costo_sin_impuesto
					,a.um_x_embalaje
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
				FROM $tbl[compras_articulos_precios_proveedores] a 
				LEFT JOIN $tbl[compras_articulos_presentaciones] p on a.id_compras_articulo_presentacion = p.id_compras_articulo_presentacion
				LEFT JOIN $tbl[compras_articulos] b on a.id_articulo = b.id_compras_articulo
				LEFT JOIN $tbl[compras_proveedores] c on a.id_proveedor = c.id_compras_proveedor
				LEFT JOIN $tbl[compras_marcas] d on p.id_marca = d.id_compras_marca
				LEFT JOIN $tbl[compras_presentaciones] e on p.id_presentacion = e.id_compras_presentacion
				LEFT JOIN $tbl[compras_embalaje] f on a.id_embalaje = f.id_compras_embalaje
				LEFT JOIN $tbl[administracion_impuestos] g on p.id_impuesto = g.id_administracion_impuestos
				LEFT JOIN $tbl[compras_um] h on b.id_compras_um = h.id_compras_um
				LEFT JOIN $tbl[administracion_regiones] i on a.id_administracion_region = i.id_administracion_region
				WHERE a.activo = 1 AND 1  
				ORDER BY a.id_compras_articulo_presentacion ASC";
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
					a.id_compras_articulo_presentacion
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
		$query = "SELECT a.*, 
					b.valor as impuesto_porcentaje 
					,c.avatar
					,IF(e.presentacion IS NOT NULL, CONVERT(CONCAT(IFNULL(e.presentacion,''), ' DE ', IFNULL(p.um_x_presentacion,''), ' ', IFNULL(h.clave_corta,'')) USING utf8),null) as presentacion_detalle
					,h.clave_corta as cv_um
					,f.clave_corta as cv_embalaje
					FROM $tbl[compras_articulos_precios_proveedores] a
					LEFT JOIN $tbl[administracion_impuestos] b ON a.id_impuesto=b.id_administracion_impuestos
					LEFT JOIN $tbl[compras_articulos] c ON c.id_compras_articulo = a.id_articulo

					LEFT JOIN $tbl[compras_articulos_presentaciones] p ON a.id_compras_articulo_presentacion=p.id_compras_articulo_presentacion
					LEFT JOIN $tbl[compras_presentaciones] e on p.id_presentacion = e.id_compras_presentacion
					LEFT JOIN $tbl[compras_embalaje] f on a.id_embalaje = f.id_compras_embalaje
					LEFT JOIN $tbl[compras_um] h on c.id_compras_um	= h.id_compras_um
					WHERE a.id_compras_articulo_precio_proveedor = $id_compras_articulo_precio_proveedor";
					// dump_var($query);
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}
	public function db_update_data($data=array()){
		// DB Info
		$tbl = $this->tbl;
		//dump_var($data);
		// Query
		$condicion = "id_compras_articulo_precio_proveedor = ".$data['id_compras_articulo_precio_proveedor'];
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

	public function get_listado_presentaciones($data=array()){
		// DB Info
		$tbl = $this->tbl;		
		// Filtro
		$filtro 		= (isset($data['buscar']))?$data['buscar']:false;
		$limit 			= (isset($data['limit']))?$data['limit']:0;
		$offset 		= (isset($data['offset']))?$data['offset']:0;
		$aplicar_limit 	= (isset($data['aplicar_limit']))?true:false;
		$filtro = ($filtro!="") ? "AND a.id_compras_articulo_presentacion='$filtro'" : "";
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
					,CONCAT(
						IF(e.presentacion IS NOT NULL, CONVERT(CONCAT(IFNULL(e.presentacion,''), ' DE ', IFNULL(a.um_x_presentacion,''), ' ', IFNULL(h.clave_corta,'')) USING utf8),null)
						,' - '
						,d.marca
						,' - '
						,a.upc
						,' - '
						,a.sku
						) as listado
				from $tbl[compras_articulos_presentaciones] a 
				LEFT JOIN $tbl[compras_articulos] b on a.id_articulo = b.id_compras_articulo
				LEFT JOIN $tbl[compras_marcas] d on a.id_marca = d.id_compras_marca
				LEFT JOIN $tbl[compras_presentaciones] e on a.id_presentacion = e.id_compras_presentacion
				LEFT JOIN $tbl[compras_um] h on b.id_compras_um	= h.id_compras_um
				WHERE a.activo = 1 AND 1  $filtro
				ORDER BY a.id_marca, a.id_compras_articulo_presentacion ASC
				;";
      	// Execute querie
		// dump_var($query);
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