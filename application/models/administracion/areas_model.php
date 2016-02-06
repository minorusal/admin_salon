<?php
class areas_model extends Base_Model{

	public function __construct(){
		parent::__construct();
	}

	//Función que obtiene toda la información de la tabla av_administracion_areas
	public function db_get_data($data=array())	{		
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$filtro         = (isset($data['buscar']))?$data['buscar']:false;
		$limit 			= (isset($data['limit']))?$data['limit']:0;
		$offset 		= (isset($data['offset']))?$data['offset']:0;
		$aplicar_limit 	= (isset($data['aplicar_limit']))?true:false;
		$filtro = ($filtro) ? "AND (ar.area like '%$filtro%' OR
									ar.clave_corta like '%$filtro%' OR
									ar.descripcion like '%$filtro%')" : "";
		$limit 			= ($aplicar_limit) ? "LIMIT $offset ,$limit" : "";
		//Query
		$query = "	SELECT *
					FROM $tbl[administracion_areas] ar
					WHERE ar.activo = 1 $filtro
					ORDER BY ar.id_administracion_areas ASC
					$limit
					";
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}

	public function db_get_area_user($id_area){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "SELECT u.*
				  FROM $tbl[administracion_areas] a
				  LEFT JOIN $tbl[usuarios] u on a.id_administracion_areas = u.id_area
				  WHERE a.activo = 1 AND u.id_area = $id_area ";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	/*Trae la información para el formulario de edición de la tabla av_administracion_areas*/
	public function get_orden_unico_area($id_administracion_areas)	{
		// DB Info
		$tbl = $this->tbl;
		// Query
		$query = "SELECT * FROM $tbl[administracion_areas] WHERE id_administracion_areas = $id_administracion_areas";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	/*Actualiza la información en el formuladio de edición de la tabla av_administracion_areas*/
	public function db_update_data($data=array()){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$condicion = array('id_administracion_areas !=' => $data['id_administracion_areas'], 'clave_corta = '=> $data['clave_corta']); 
		$existe    = $this->row_exist($tbl['administracion_areas'], $condicion);
		if(!$existe){
			$condicion = "id_administracion_areas = ".$data['id_administracion_areas'];			
			$update = $this->update_item($tbl['administracion_areas'], $data, 'id_administracion_areas', $condicion);
			return $update;
		}else{
			return false;
		}
	}

	/*Inserta registro de la tabla ac_administracion_areas*/
	public function db_insert_data($data = array()){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$existe = $this->row_exist($tbl['administracion_areas'], array('clave_corta'=> $data['clave_corta']));
		if(!$existe){
			$insert = $this->insert_item($tbl['administracion_areas'], $data);
			return $insert;
		}else{
			return false;
		}
	}

	public function bd_delete_data($data = array()){
		// DB Info
		$tbl = $this->tbl;
		
		$condicion = array('id_administracion_areas ='=> $data['id_administracion_areas']);
		$update = $this->update_item($tbl['administracion_areas'],$data,'id_administracion_areas',$condicion);
		if($update){
			return $update;
		}else{
			return false;
		}
	}
}