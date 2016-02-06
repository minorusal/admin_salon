<?php
class empresas_model extends Base_Model{

	//Función que obtiene toda la información de la tabla sys_sucursales
	public function db_get_data($data=array())	{
		// DB Info		
		$tbl = $this->tbl;
		// Filtro

		$filtro         = (isset($data['buscar']))?$data['buscar']:false;
		$limit 			= (isset($data['limit']))?$data['limit']:0;
		$offset 		= (isset($data['offset']))?$data['offset']:0;
		$aplicar_limit 	= (isset($data['aplicar_limit']))?true:false;
		$filtro = ($filtro) ? "AND (su.empresa like '%$filtro%' OR
									su.sarzon_social like '%$filtro%' OR
									su.rfc like '%$filtro%')" : "";
		$limit 			= ($aplicar_limit) ? "LIMIT $offset ,$limit" : "";
		//Query
		$query = "	SELECT *
					FROM $tbl[empresas] em
					WHERE em.activo = 1 $filtro
					ORDER BY em.id_empresa ASC
					$limit
					";
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}

	/*Actualiza la información en el formuladio de edición de la tabla sys_empresas*/
	public function db_update_data($data=array()){
		$tbl = $this->tbl;
		$condicion = array('id_empresa = ' => $data['id_empresa']);
		$existe    = $this->row_exist($tbl['empresas'], $condicion);
		if(!$existe){
			$insert = $this->insert_item($tbl['empresas'], $data);
			return $insert;
		}else if($existe){
			$condicion = "id_empresa =".$data['id_empresa'];
			$data['edit_timestamp']  =  $data['timestamp'];
			$data['edit_id_usuario'] = $this->session->userdata('id_usuario');
			$update = $this->update_item($tbl['empresas'], $data, 'id_empresa', $condicion);
			return $update;
		}
		else
		{
			return false;
		}
	}
}
