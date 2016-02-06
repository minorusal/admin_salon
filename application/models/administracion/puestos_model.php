<?php
class puestos_model extends Base_Model{

	//Función que obtiene toda la información de la tabla av_administracion_puestos
	public function db_get_data($data=array())	{
		// DB Info		
		$tbl = $this->tbl;
		// Filtro
		$filtro         = (isset($data['buscar']))?$data['buscar']:false;
		$limit 			= (isset($data['limit']))?$data['limit']:0;
		$offset 		= (isset($data['offset']))?$data['offset']:0;
		$aplicar_limit 	= (isset($data['aplicar_limit']))?true:false;
		$filtro = ($filtro) ? "AND (ps.puesto like '%$filtro%' OR
									ps.clave_corta like '%$filtro%' OR
									ps.descripcion like '%$filtro%')" : "";
		$limit 			= ($aplicar_limit) ? "LIMIT $offset ,$limit" : "";
		//Query
		$query = "	SELECT *
					FROM $tbl[administracion_puestos] ps
					WHERE ps.activo = 1 $filtro
					ORDER BY ps.id_administracion_puestos ASC
					$limit
					";
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}

	public function db_get_puesto_user($id_puesto){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "SELECT p.*
				  FROM $tbl[administracion_puestos] p
				  LEFT JOIN $tbl[usuarios] u on p.id_administracion_puestos = u.id_puesto
				  WHERE p.activo = 1 AND u.id_puesto = $id_puesto ";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	/*Trae la información para el formulario de edición de la tabla av_administracion_puestos*/
	public function get_orden_unico_puesto($id_administracion_puestos){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "SELECT * FROM $tbl[administracion_puestos] WHERE id_administracion_puestos = $id_administracion_puestos";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	/*Actualiza la información en el formuladio de edición de la tabla av_administracion_puestos*/
	public function db_update_data($data=array()){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$condicion = array('id_administracion_puestos !=' => $data['id_administracion_puestos'], 'clave_corta = '=> $data['clave_corta']); 
		$existe    = $this->row_exist($tbl['administracion_puestos'], $condicion);
		if(!$existe){
			$condicion = "id_administracion_puestos = ".$data['id_administracion_puestos']; 
			$update    = $this->update_item($tbl['administracion_puestos'], $data, 'id_administracion_puestos', $condicion);
			return $update;
		}else{
			return false;
		}
	}

	/*Inserta registro de la tabla ac_administracion_puestos*/
	public function db_insert_data($data = array())	{
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$existe = $this->row_exist($tbl['administracion_puestos'], array('clave_corta'=> $data['clave_corta']));
		if(!$existe){
			$insert = $this->insert_item($tbl['administracion_puestos'], $data);
			return $insert;
		}else{
			return false;
		}
	}

	public function bd_delete_data($data = array()){
		// DB Info
		$tbl = $this->tbl;
		
		$condicion = array('id_administracion_puestos ='=> $data['id_administracion_puestos']);
		$update = $this->update_item($tbl['administracion_puestos'],$data,'id_administracion_puestos',$condicion);
		if($update){
			return $update;
		}else{
			return false;
		}
	}
}