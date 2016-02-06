<?php
class grupos_model extends Base_Model{

	public function __construct(){
		parent::__construct();
	}

	public function db_get_data($data=array())	{		
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$filtro         = (isset($data['buscar']))?$data['buscar']:false;
		$limit 			= (isset($data['limit']))?$data['limit']:0;
		$offset 		= (isset($data['offset']))?$data['offset']:0;
		$aplicar_limit 	= (isset($data['aplicar_limit']))?true:false;
		$filtro = ($filtro) ? "AND (g.grupo like '%$filtro%' OR
									g.clave_corta like '%$filtro%' OR
									g.descripcion like '%$filtro%')" : "";
		$limit 			= ($aplicar_limit) ? "LIMIT $offset ,$limit" : "";
		//Query
		$query = "	SELECT g.*
		                  ,s.sucursal
					FROM $tbl[nutricion_grupos] g
					LEFT JOIN $tbl[sucursales] s on s.id_sucursal = g.id_sucursales
					WHERE g.activo = 1 $filtro
					ORDER BY g.id_nutricion_grupos ASC
					$limit
					";
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}

	public function db_get_grupo_user($id_grupo){
		// DB Info		
		$tbl = $this->tbl;
		$query = "	SELECT *
					FROM $tbl[nutricion_grupos] g
					WHERE g.activo =1 AND g.id_nutricion_grupos = $id_grupo
					";
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}

	public function get_orden_unico_grupo($id_grupo){
		// DB Info		
		$tbl = $this->tbl;
		$query = "	SELECT *
					FROM $tbl[nutricion_grupos] g
					WHERE g.activo =1 AND g.id_nutricion_grupos = $id_grupo
					";
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}

	public function db_update_data($data=array()){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$condicion = array('id_nutricion_grupos !=' => $data['id_nutricion_grupos'], 'clave_corta = '=> $data['clave_corta']); 
		$existe    = $this->row_exist($tbl['nutricion_grupos'], $condicion);
		if(!$existe){
			$condicion = "id_nutricion_grupos = ".$data['id_nutricion_grupos'];			
			$update = $this->update_item($tbl['nutricion_grupos'], $data, 'id_nutricion_grupos', $condicion);
			return $update;
		}else{
			return false;
		}
	}

	public function db_insert_data($data = array()){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$existe = $this->row_exist($tbl['nutricion_grupos'], array('clave_corta'=> $data['clave_corta']));
		if(!$existe){
			$insert = $this->insert_item($tbl['nutricion_grupos'], $data);
			return $insert;
		}else{
			return false;
		}
	}

	public function bd_delete_data($data = array()){
		// DB Info
		$tbl = $this->tbl;
		
		$condicion = array('id_nutricion_grupos ='=> $data['id_nutricion_grupos']);
		$update = $this->update_item($tbl['nutricion_grupos'],$data,'id_nutricion_grupos',$condicion);
		if($update){
			return $update;
		}else{
			return false;
		}
	}
}