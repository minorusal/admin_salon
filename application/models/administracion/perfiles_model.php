<?php
class perfiles_model extends Base_Model{	
	//Función que obtiene toda la información de la tabla sys_perfiles
	public function db_get_data($data=array())	{
		// DB Info		
		$tbl = $this->tbl;
		// Filtro
		$filtro         = (isset($data['buscar']))?$data['buscar']:false;
		$limit 			= (isset($data['limit']))?$data['limit']:0;
		$offset 		= (isset($data['offset']))?$data['offset']:0;
		$aplicar_limit 	= (isset($data['aplicar_limit']))?true:false;
		$filtro = ($filtro) ? "AND (pr.perfil like '%$filtro%' OR
									pr.clave_corta like '%$filtro%' OR
									pr.descripcion like '%$filtro%')" : "";
		$limit 			= ($aplicar_limit) ? "LIMIT $offset ,$limit" : "";
		//Query
		$query = "	SELECT *
					FROM $tbl[perfiles] pr
					WHERE pr.activo = 1 $filtro
					AND pr.perfil NOT LIKE 'ROOT'
					ORDER BY pr.id_perfil ASC
					$limit
					";
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}
	public function get_perfil_ocupado($id_perfil){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "SELECT p.* 
				  FROM $tbl[perfiles] p
				  LEFT JOIN $tbl[usuarios] u on u.id_perfil = p.id_perfil
				  WHERE p.activo = 1
				  AND (u.id_perfil = $id_perfil)";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}
	public function get_perfil_user($id_perfil){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "SELECT 
						* 
				  FROM $tbl[usuarios] u
				  WHERE u.id_perfil = $id_perfil";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}
	/*Trae la información para el formulario de edición de la tabla sys_perfiles*/
	public function get_orden_unico_perfil($id_perfil)	{
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "SELECT * FROM $tbl[perfiles] WHERE id_perfil = $id_perfil";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

		/**
	* Consulta la info de un perfil en especifico
	* y de acuerdo a permisos especiales (tabla usuarios)
	* @param string $id_perfil
	* @return array
	*/

	public function search_data_perfil($id_perfil){
		// DB Info
		$tbl = $this->tbl;
		$query = "SELECT * FROM $tbl[perfiles] WHERE id_perfil = $id_perfil";
		$query = $this->db->query($query);
		return $query->result_array();
	}

	/*Actualiza la información en el formuladio de edición de la tabla sys_perfiles*/
	public function db_update_data($data=array()){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$condicion = array('id_perfil !=' => $data['id_perfil'], 'clave_corta = '=> $data['clave_corta']); 
		$existe    = $this->row_exist($tbl['perfiles'], $condicion);
		if(!$existe){
			$condicion = "id_perfil = ".$data['id_perfil']; 
			$update    = $this->update_item($tbl['perfiles'], $data, 'id_perfil', $condicion);
			return $update;
		}else{
			return false;
		}
	}

	/*Inserta informacion en la tabla sys_perfiles*/
	public function db_insert_data($data = array())	{
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$existe = $this->row_exist($tbl['perfiles'], array('clave_corta'=> $data['clave_corta']));
		if(!$existe){
			$insert = $this->insert_item($tbl['perfiles'], $data);
			return $insert;
		}else{
			return false;
		}
	}

	public function bd_delete_data($data = array()){
		// DB Info
		$tbl = $this->tbl;
		
		$condicion = array('id_perfil ='=> $data['id_perfil']);
		$update = $this->update_item($tbl['perfiles'],$data,'id_perfil',$condicion);
		if($update){
			return $update;
		}else{
			return false;
		}
	}
}
