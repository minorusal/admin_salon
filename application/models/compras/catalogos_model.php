<?php
class catalogos_model extends Base_Model{
	
	/*PRESENTACIONES*/
	public function get_presentacion_unico($id_presentacion){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$query = "SELECT * FROM $tbl[compras_presentaciones] cp WHERE cp.id_compras_presentacion = $id_presentacion";

		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}
	public function get_presentaciones($limit, $offset, $filtro="", $aplicar_limit = true){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$filtro = ($filtro=="") ? "" : "AND (
												cp.presentacion like '%$filtro%'
											OR 
												cp.clave_corta like '%$filtro%'
											OR
												cp.descripcion like '%$filtro%'
										) ";
		$limit = ($aplicar_limit) ?  "LIMIT $offset ,$limit " : "";
		$query = "	SELECT 
						cp.id_compras_presentacion
						,cp.presentacion
						,cp.clave_corta
						,cp.descripcion
					FROM $tbl[compras_presentaciones] cp
					WHERE cp.activo = 1 $filtro
					ORDER BY cp.id_compras_presentacion
					$limit";
      	
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}
	public function insert_presentacion($data){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$existe = $this->row_exist($tbl['compras_presentaciones'], array('clave_corta'=> $data['clave_corta']));
		if(!$existe){
			$insert = $this->insert_item($tbl['compras_presentaciones'], $data);
			return $insert;
		}else{
			return false;
		}
	}
	public function update_presentaciones($data, $id_presentacion){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$condicion = array('id_compras_presentacion !=' => $id_presentacion, 'clave_corta = '=> $data['clave_corta']); 
		$existe = $this->row_exist($tbl['compras_presentaciones'], $condicion);
		if(!$existe){
			$condicion = "id_compras_presentacion = $id_presentacion"; 
			$data['id_compras_presentacion'] =  $id_presentacion;
			$update    = $this->update_item($tbl['compras_presentaciones'], $data, 'id_compras_presentacion', $condicion);
			return $update;
		}else{
			return false;
		}
	}

	/*LINEAS*/
	public function get_linea_unico($id_linea){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$query = "SELECT * FROM $tbl[compras_lineas] cl WHERE cl.id_compras_linea = $id_linea";

		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}
	public function get_lineas($limit, $offset, $filtro="", $aplicar_limit = true){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$filtro = ($filtro=="") ? "" : "AND (
												cl.linea like '%$filtro%'
											OR 
												cl.clave_corta like '%$filtro%'
											OR
												cl.descripcion like '%$filtro%'
										) ";
		$limit = ($aplicar_limit) ?  "LIMIT $offset ,$limit " : "";
		$query = "	SELECT 
						cl.id_compras_linea
						,cl.linea
						,cl.clave_corta
						,cl.descripcion
					FROM $tbl[compras_lineas] cl
					WHERE cl.activo = 1 $filtro
					ORDER BY cl.id_compras_linea
					$limit";
      	
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}
	public function insert_linea($data){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$existe = $this->row_exist($tbl['compras_lineas'], array('clave_corta'=> $data['clave_corta']));
		if(!$existe){
			$insert = $this->insert_item($tbl['compras_lineas'], $data);
			return $insert;
		}else{
			return false;
		}
	}
	public function update_linea($data, $id_linea){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$condicion = array('id_compras_linea !=' => $id_linea, 'clave_corta = '=> $data['clave_corta']); 
		$existe = $this->row_exist($tbl['compras_lineas'], $condicion);
		if(!$existe){
			$condicion = "id_compras_linea = $id_linea"; 
			$data['id_compras_linea'] =  $id_linea;
			$update    = $this->update_item($tbl['compras_lineas'], $data, 'id_compras_linea', $condicion);
			return $update;
		}else{
			return false;
		}
	}

	/*MARCAS*/

	public function get_marca_unico($id_marca){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$query = "SELECT * FROM $tbl[compras_marcas] cm WHERE cm.id_compras_marca = $id_marca";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}
	public function get_marcas($limit, $offset, $filtro="", $aplicar_limit = true){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$filtro = ($filtro=="") ? "" : "AND (
												cm.marca like '%$filtro%'
											OR 
												cm.clave_corta like '%$filtro%'
											OR
												cm.descripcion like '%$filtro%'
										) ";
		$limit = ($aplicar_limit) ?  "LIMIT $offset ,$limit " : "";
		$query = "	SELECT 
						cm.id_compras_marca
						,cm.marca
						,cm.clave_corta
						,cm.descripcion
					FROM $tbl[compras_marcas] cm
					WHERE cm.activo = 1 $filtro
					ORDER BY cm.id_compras_marca
					$limit";
      	
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}
	public function insert_marca($data){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$existe = $this->row_exist($tbl['compras_marcas'], array('clave_corta'=> $data['clave_corta']));
		if(!$existe){
			$insert = $this->insert_item($tbl['compras_marcas'], $data);
			return $insert;
		}else{
			return false;
		}
	}
	public function update_marca($data, $id_marca){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$condicion = array('id_compras_marca !=' => $id_marca, 'clave_corta = '=> $data['clave_corta']); 
		$existe = $this->row_exist($tbl['compras_marcas'], $condicion);
		if(!$existe){
			$condicion = "id_compras_marca = $id_marca"; 
			$data['id_compras_marca'] =  $id_marca;
			$update    = $this->update_item($tbl['compras_marcas'], $data, 'id_compras_marca', $condicion);
			return $update;
		}else{
			return false;
		}
	}

	/*U.M.*/
	
	public function get_um_unico($id_um){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$query = "SELECT * FROM $tbl[compras_um] cu WHERE cu.id_compras_um = $id_um";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}
	public function get_um($limit, $offset, $filtro="", $aplicar_limit = true){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$filtro = ($filtro=="") ? "" : "AND (
												cu.um like '%$filtro%'
											OR 
												cu.clave_corta like '%$filtro%'
											OR
												cu.descripcion like '%$filtro%'
										) ";
		$limit = ($aplicar_limit) ?  "LIMIT $offset ,$limit " : "";
		$query = "	SELECT 
						cu.id_compras_um
						,cu.um
						,cu.clave_corta
						,cu.descripcion
					FROM $tbl[compras_um] cu
					WHERE cu.activo = 1 $filtro
					ORDER BY cu.id_compras_um
					$limit";
      	
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}
	public function insert_um($data){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$existe = $this->row_exist($tbl['compras_um'], array('clave_corta'=> $data['clave_corta']));
		if(!$existe){
			$insert = $this->insert_item($tbl['compras_um'], $data);
			return $insert;
		}else{
			return false;
		}
	}
	public function update_um($data, $id_um){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$condicion = array('id_compras_um !=' => $id_um, 'clave_corta = '=> $data['clave_corta']); 
		$existe = $this->row_exist($tbl['compras_um'], $condicion);
		if(!$existe){
			$condicion = "id_compras_um = $id_um"; 
			$data['id_compras_um'] =  $id_um;
			$update    = $this->update_item($tbl['compras_um'], $data, 'id_compras_um', $condicion);
			return $update;
		}else{
			return false;
		}
	}

	/*EMBALAJE*/
	public function get_embalaje($data=array()){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$filtro         = (isset($data['buscar']))?$data['buscar']:false;
		$limit 			= (isset($data['limit']))?$data['limit']:0;
		$offset 		= (isset($data['offset']))?$data['offset']:0;
		$aplicar_limit 	= (isset($data['aplicar_limit']))?true:false;
		$filtro = ($filtro=="") ? "" : "AND (ce.embalaje like '%$filtro%' OR 
											 ce.clave_corta like '%$filtro%'OR
										     ce.descripcion like '%$filtro%') ";
		$limit = ($aplicar_limit) ?  "LIMIT $offset ,$limit " : "";
		$query = "	SELECT 
						ce.id_compras_embalaje
						,ce.embalaje
						,ce.clave_corta
						,ce.descripcion
					FROM $tbl[compras_embalaje] ce
					WHERE ce.activo = 1 $filtro
					ORDER BY ce.id_compras_embalaje
					$limit";
      	
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}
	public function get_embalaje_unico($id_embalaje){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$query = "SELECT * FROM $tbl[compras_embalaje] ce WHERE ce.id_compras_embalaje = $id_embalaje";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}
	public function insert_embalaje($data=array()){
		// DB Info
		$tbl = $this->tbl;
		// Query		
		$existe = $this->row_exist($tbl['compras_embalaje'], array('clave_corta'=> $data['clave_corta']));
		if(!$existe){
			$query = $this->db->insert_string($tbl['compras_embalaje'], $data);
			$query = $this->db->query($query);
			return $query;
		}else{
			return false;
		}
	}
	public function update_embalaje($data, $id_embalaje){	
		// DB Info
		$tbl = $this->tbl;
		// Query
		$condicion = array('id_compras_embalaje !=' => $id_embalaje, 'clave_corta = '=> $data['clave_corta']); 
		$existe = $this->row_exist($tbl['compras_embalaje'], $condicion);
		if(!$existe){
			$condicion = "id_compras_embalaje = $id_embalaje"; 
			$query = $this->db->update_string($tbl['compras_embalaje'], $data, $condicion);
			$query = $this->db->query($query);
			return $query;
		}else{
			return false;
		}
	}

	/*ARTICULOS*/
	public function insert_articulo($data){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$existe = $this->row_exist($tbl['compras_articulos'], array('clave_corta'=> $data['clave_corta']));
		if(!$existe){
			$query = $this->db->insert_string($tbl['compras_articulos'], $data);
			$query = $this->db->query($query);
			return $query;
		}else{
			return false;
		}
	}
	public function update_articulo($data, $id_articulo){
		// DB Info
		$tbl = $this->tbl;
		// Query
		//$condicion = array('id_compras_articulo !=' => $id_articulo, 'clave_corta = '=> $data['clave_corta']); 
		//$existe = $this->row_exist($tbl['compras_articulos'], $condicion);
		//if(!$existe){
			$condicion = "id_compras_articulo = $id_articulo"; 
			$query = $this->db->update_string($tbl['compras_articulos'], $data, $condicion);
			$query = $this->db->query($query);
			return $query;
		//}else{
			//return false;
		//}
	}
	public function get_articulos($limit, $offset, $filtro="", $aplicar_limit = true){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$filtro = ($filtro=="") ? "" : "AND ( 	ca.articulo  LIKE '%$filtro%' OR 
												cl.linea  LIKE '%$filtro%' OR
												cu.um  LIKE '%$filtro%' OR 
												ca.clave_corta  LIKE '%$filtro%' OR 
												ca.descripcion  LIKE '%$filtro%'
											)";
		$limit = ($aplicar_limit) ? "LIMIT $offset ,$limit" : "";
		//print_debug($limit);
		//,ca.upc
		$query = "	SELECT 
						ca.id_compras_articulo
						,ca.articulo
						,at.articulo_tipo
						,cl.linea
						,cu.um
						,ca.clave_corta
						,ca.descripcion
						,ca.avatar
					FROM $tbl[compras_articulos] ca
					LEFT JOIN $tbl[compras_lineas] cl on cl.id_compras_linea = ca.id_compras_linea 
					LEFT JOIN $tbl[compras_um] cu on cu.id_compras_um = ca.id_compras_um
					LEFT JOIN $tbl[compras_articulos_tipo] at on ca.id_articulo_tipo = at.id_articulo_tipo
					WHERE ca.activo = 1 $filtro
					ORDER BY ca.id_compras_articulo
				$limit
					";
      	//print_debug($query);
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}
	public function get_articulo_unico($id_articulo){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$query = "SELECT * FROM $tbl[compras_articulos] ca WHERE ca.id_compras_articulo = $id_articulo";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	/*ARTICULOS TIPO*/
	public function get_articulo_tipo($limit, $offset, $filtro="", $aplicar_limit = true){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$filtro = ($filtro=="") ? "" : "AND (
												articulo_tipo like '%$filtro%'
											OR 
												clave_corta like '%$filtro%'
										) ";
		$limit = ($aplicar_limit) ?  "LIMIT $offset ,$limit " : "";
		$query = "	SELECT 
						id_articulo_tipo
						,articulo_tipo
						,clave_corta
					FROM $tbl[compras_articulos_tipo] 
					WHERE activo = 1 $filtro
					ORDER BY id_articulo_tipo
					$limit";
      	
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}

	public function db_get_presentaciones_user($id_presentacion){
		// DB Info
		$tbl = $this->tbl;
		$query = "SELECT COUNT(*) num_presentaciones FROM (
			      SELECT id_presentacion FROM $tbl[compras_articulos_precios] WHERE id_presentacion = $id_presentacion UNION ALL
			      SELECT id_presentacion FROM $tbl[compras_articulos_presentaciones] WHERE id_presentacion = $id_presentacion) p";
		// $query = "	SELECT p.*
		// 			FROM $tbl[compras_presentaciones] p 
		// 			LEFT JOIN $tbl[compras_articulos_precios] ap on ap.id_presentacion = p.id_compras_presentacion
		// 			LEFT JOIN $tbl[compras_articulos_presentaciones] apr on apr.id_presentacion = p.id_compras_presentacion
		// 			WHERE p.activo = 1 
		// 			AND (ap.id_presentacion = $id_presentacion
		// 			OR apr.id_presentacion = $id_presentacion)";
      	
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}

	public function bd_delete_data($data = array()){
		// DB Info
		$tbl = $this->tbl;
		
		$condicion = array('id_compras_presentacion ='=> $data['id_compras_presentacion']);
		$update = $this->update_item($tbl['compras_presentaciones'],$data,'id_compras_presentacion',$condicion);
		if($update){
			return $update;
		}else{
			return false;
		}
	}

	public function db_get_lineas_user($id_linea){
		// DB Info
		$tbl = $this->tbl;
		$query = "SELECT COUNT(*) num_lineas FROM (
				  SELECT id_compras_linea FROM $tbl[compras_articulos] WHERE id_compras_linea = $id_linea) l";
		
      	
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}

	public function bd_delete_data_linea($data = array()){
		// DB Info
		$tbl = $this->tbl;
		
		$condicion = array('id_compras_linea ='=> $data['id_compras_linea']);
		$update = $this->update_item($tbl['compras_lineas'],$data,'id_compras_linea',$condicion);
		if($update){
			return $update;
		}else{
			return false;
		}
	}

	public function db_get_marcas_user($id_marca){
		// DB Info
		$tbl = $this->tbl;
		$query = "SELECT COUNT(*) num_marcas FROM (
				  SELECT id_marca FROM $tbl[compras_articulos_presentaciones] WHERE id_marca = $id_marca UNION ALL
				  SELECT id_marca FROM $tbl[compras_articulos_precios] WHERE id_marca = $id_marca) m";
		// $query = "	SELECT m.*
		// 			FROM $tbl[compras_marcas] m 
		// 			LEFT JOIN $tbl[compras_articulos_presentaciones] ap on ap.id_marca = m.id_compras_marca
		// 			LEFT JOIN $tbl[compras_articulos_precios] apr on apr.id_marca = m.id_compras_marca
		// 			WHERE m.activo = 1 
		// 			AND (ap.id_marca = $id_marca
		// 			OR apr.id_marca = $id_marca)";
      	
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}

	public function bd_delete_marca_data($data = array()){
		// DB Info
		$tbl = $this->tbl;
		
		$condicion = array('id_compras_marca ='=> $data['id_compras_marca']);
		$update = $this->update_item($tbl['compras_marcas'],$data,'id_compras_marca',$condicion);
		if($update){
			return $update;
		}else{
			return false;
		}
	}

	public function db_get_um_user($id_um){
		// DB Info
		$tbl = $this->tbl;
		$query = "SELECT COUNT(*) num_um FROM (
			      SELECT id_compras_um FROM $tbl[compras_articulos] WHERE id_compras_um = $id_um) um";
		// $query = "	SELECT u.*
		// 			FROM $tbl[compras_um] u 
		// 			LEFT JOIN $tbl[compras_articulos] a on a.id_compras_um = u.id_compras_um
		// 			WHERE u.activo = 1 
		// 			AND (a.id_compras_um = $id_um)";
      	
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}

	public function bd_delete_um_data($data = array()){
		// DB Info
		$tbl = $this->tbl;
		
		$condicion = array('id_compras_um ='=> $data['id_compras_um']);
		$update = $this->update_item($tbl['compras_um'],$data,'id_compras_um',$condicion);
		if($update){
			return $update;
		}else{
			return false;
		}
	}

	public function db_get_embalaje_user($id_embalaje){
		// DB Info
		$tbl = $this->tbl;
		$query = "SELECT COUNT(*) num_embalaje FROM (
			      SELECT id_embalaje FROM $tbl[compras_articulos_precios_proveedores] WHERE id_embalaje = $id_embalaje UNION ALL
			      SELECT id_embalaje FROM $tbl[compras_articulos_precios] WHERE id_embalaje = $id_embalaje) e";
		// $query = "	SELECT e.*
		// 			FROM $tbl[compras_embalaje] e 
		// 			LEFT JOIN $tbl[compras_articulos_precios_proveedores] pp on pp.id_embalaje = e.id_compras_embalaje
		// 			LEFT JOIN $tbl[compras_articulos_precios] ap on ap.id_embalaje = e.id_compras_embalaje
		// 			WHERE e.activo = 1 
		// 			AND (pp.id_embalaje = $id_embalaje
		// 			OR ap.id_embalaje = $id_embalaje)";
      	
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}

	public function bd_delete_embalaje_data($data = array()){
		// DB Info
		$tbl = $this->tbl;
		
		$condicion = array('id_compras_embalaje ='=> $data['id_compras_embalaje']);
		$update = $this->update_item($tbl['compras_embalaje'],$data,'id_compras_embalaje',$condicion);
		if($update){
			return $update;
		}else{
			return false;
		}
	}
} 
?>