<?php
class proveedores_model extends Base_Model{

	public function db_get_data($data=array()){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$buscar = (array_key_exists('buscar',$data))?$data['buscar']:false;
		$filtro = ($buscar) ? "AND ( 	p.razon_social  LIKE '%$buscar%' OR 
										p.nombre_comercial  LIKE '%$buscar%' OR
										p.clave_corta  LIKE '%$buscar%' OR
										p.rfc  LIKE '%$buscar%' OR
										e.entidad LIKE '%$buscar%'
											)" : "";		
		$limit 			= (array_key_exists('limit',$data)) ?$data['limit']:0;
		$offset 		= (array_key_exists('offset',$data))?$data['offset']:0;
		$aplicar_limit 	= (array_key_exists('aplicar_limit',$data)) ? $data['aplicar_limit'] : false;
		$limit 			= ($aplicar_limit) ? "LIMIT $offset ,$limit" : "";

		$query = "	SELECT
						 p.id_compras_proveedor
						,p.razon_social
						,p.nombre_comercial
						,p.clave_corta
						,p.rfc
						,p.calle
						,p.num_int
						,p.num_ext
						,p.colonia
						,p.municipio
						,e.entidad
						,r.clave_corta as region
						,p.cp
						,p.telefonos
						,p.email
						,p.contacto
						,p.comentarios
						,p.edit_timestamp
						,p.edit_id_usuario
						,p.timestamp
						,p.id_usuario
						,p.activo
					FROM $tbl[compras_proveedores] p 
					LEFT JOIN $tbl[administracion_entidades] e on p.id_administracion_entidad = e.id_administracion_entidad
					LEFT JOIN $tbl[administracion_regiones] r on p.id_administracion_region = r.id_administracion_region
					WHERE p.activo = 1 $filtro
					$limit
					";
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}
	public function db_get_total_rows($data=array()){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$buscar = (array_key_exists('buscar',$data))?$data['buscar']:false;
		$filtro = ($buscar) ? "AND ( 	p.razon_social  LIKE '%$buscar%' OR 
										p.nombre_comercial  LIKE '%$buscar%' OR
										p.clave_corta  LIKE '%$buscar%' OR
										p.rfc  LIKE '%$buscar%' OR
										e.entidad LIKE '%$buscar%'
											)" : "";
		$query = "SELECT
						p.id_compras_proveedor
						,p.razon_social
						,p.nombre_comercial
						,p.clave_corta
						,p.rfc
						,p.calle
						,p.num_int
						,p.num_ext
						,p.colonia
						,p.municipio
						,e.entidad
						,p.cp
						,p.telefonos
						,p.email
						,p.contacto
						,p.comentarios
						,p.edit_timestamp
						,p.edit_id_usuario
						,p.timestamp
						,p.id_usuario
						,p.activo
					FROM $tbl[compras_proveedores] p 
					LEFT JOIN $tbl[administracion_entidades] e on p.id_administracion_entidad = e.id_administracion_entidad
					WHERE p.activo = 1	$filtro";

      	$query = $this->db->query($query);
		return $query->num_rows;
	}
	public function insert($data=array()){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$existe = $this->row_exist($tbl['compras_proveedores'],array('clave_corta ='=> $data['clave_corta']));
		if(!$existe){
			$insert = $this->insert_item($tbl['compras_proveedores'], $data);
			return $insert;
		}else{
			return false;
		}
	}
	public function db_update_data($data=array()){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$condicion = array('id_compras_proveedor !=' => $data['id_compras_proveedor'], 'clave_corta'=> $data['clave_corta']); 
		$existe    = $this->row_exist($tbl['compras_proveedores'], $condicion);
		
		if(!$existe){
			$condicion = "id_compras_proveedor = ".$data['id_compras_proveedor']; 
			$update    = $this->update_item($tbl['compras_proveedores'], $data, 'id_compras_proveedor', $condicion);
			return $update;
		}else{
			return false;
		}
	}
	public function get_proveedor_unico($id_compras_proveedor){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$query = "SELECT
						 p.id_compras_proveedor
						,p.razon_social
						,p.nombre_comercial
						,p.clave_corta
						,p.rfc
						,p.calle
						,p.num_int
						,p.num_ext
						,p.colonia
						,p.municipio
						,e.id_administracion_entidad
						,r.id_administracion_region
						,p.cp
						,p.telefonos
						,p.email
						,p.contacto
						,p.comentarios
						,p.edit_timestamp
						,p.edit_id_usuario
						,p.timestamp
						,p.id_usuario
						,p.activo
					FROM $tbl[compras_proveedores] p 
					LEFT JOIN $tbl[administracion_entidades] e on p.id_administracion_entidad = e.id_administracion_entidad
					LEFT JOIN $tbl[administracion_regiones] r on p.id_administracion_region = r.id_administracion_region
					WHERE id_compras_proveedor = $id_compras_proveedor";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}
	public function get_proveedor_region($id_region){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$query = "	SELECT
						 p.id_compras_proveedor
						,p.razon_social
						,p.nombre_comercial
						,p.clave_corta
						,p.rfc
						,p.calle
						,p.num_int
						,p.num_ext
						,p.colonia
						,p.municipio
						,e.entidad
						,p.cp
						,p.telefonos
						,p.email
						,p.contacto
						,p.comentarios
						,p.edit_timestamp
						,p.edit_id_usuario
						,p.timestamp
						,p.id_usuario
						,p.activo
					FROM $tbl[compras_proveedores] p 
					LEFT JOIN $tbl[administracion_entidades] e on p.id_administracion_entidad = e.id_administracion_entidad
					WHERE p.id_administracion_region = $id_region AND p.activo=1;";
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}
}