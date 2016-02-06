<?php
class subrogacion_model extends Base_Model{
	//Función que obtiene toda la información de la tabla av_administracion_areas
	public function db_get_data($data=array())	{		
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$filtro         = (isset($data['buscar']))?$data['buscar']:false;
		$limit 			= (isset($data['limit']))?$data['limit']:0;
		$offset 		= (isset($data['offset']))?$data['offset']:0;
		$aplicar_limit 	= (isset($data['aplicar_limit']))?true:false;
		$filtro = ($filtro) ? "AND (s.subrogacion like '%$filtro%' OR
									s.clave_corta like '%$filtro%' OR
									s.descripcion like '%$filtro%')" : "";
		$limit 			= ($aplicar_limit) ? "LIMIT $offset ,$limit" : "";
		//Query
		$query = "	SELECT *
					FROM $tbl[administracion_subrogacion] s
					WHERE s.activo = 1 $filtro
					ORDER BY s.id_administracion_subrogacion ASC
					$limit
					";
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}

	public function db_get_subrogaciones_user($id_subrogacion){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "SELECT * FROM $tbl[administracion_subrogacion] WHERE id_administracion_subrogacion = $id_subrogacion";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}


	public function get_orden_unico_subrogacion($id_subrogacion)	{
		// DB Info
		$tbl = $this->tbl;
		// Query
		$query = "SELECT * FROM $tbl[administracion_subrogacion] WHERE id_administracion_subrogacion = $id_subrogacion";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	public function db_update_data($data=array()){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$condicion = array('id_administracion_subrogacion !=' => $data['id_administracion_subrogacion'], 'clave_corta = '=> $data['clave_corta']); 
		$existe    = $this->row_exist($tbl['administracion_subrogacion'], $condicion);
		if(!$existe){
			$condicion = "id_administracion_subrogacion = ".$data['id_administracion_subrogacion'];			
			$update = $this->update_item($tbl['administracion_subrogacion'], $data, 'id_administracion_subrogacion', $condicion);
			return $update;
		}else{
			return false;
		}
	}

	public function db_insert_data($data = array()){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$existe = $this->row_exist($tbl['administracion_subrogacion'], array('clave_corta'=> $data['clave_corta']));
		if(!$existe){
			$insert = $this->insert_item($tbl['administracion_subrogacion'], $data);
			return $insert;
		}else{
			return false;
		}
	}

	public function bd_delete_data($data = array()){
		// DB Info
		$tbl = $this->tbl;
		
		$condicion = array('id_administracion_subrogacion ='=> $data['id_administracion_subrogacion']);
		$update = $this->update_item($tbl['administracion_subrogacion'],$data,'id_administracion_subrogacion',$condicion);
		if($update){
			return $update;
		}else{
			return false;
		}
	}
}