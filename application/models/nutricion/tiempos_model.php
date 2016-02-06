<?php
class tiempos_model extends Base_Model{

	//Función que obtiene toda la información de la tabla av_nutricion_tiempos
	public function db_get_data($data=array())	{		
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$filtro         = (isset($data['buscar']))?$data['buscar']:false;
		$limit 			= (isset($data['limit']))?$data['limit']:0;
		$offset 		= (isset($data['offset']))?$data['offset']:0;
		$aplicar_limit 	= (isset($data['aplicar_limit']))?true:false;
		$filtro = ($filtro) ? "AND (se.tiempo like '%$filtro%' OR
									se.clave_corta like '%$filtro%' OR
									se.descripcion like '%$filtro%')" : "";
		$limit 			= ($aplicar_limit) ? "LIMIT $offset ,$limit" : "";
		//Query
		$query = "	SELECT *
					FROM $tbl[nutricion_tiempos] se
					WHERE se.activo = 1 $filtro
					GROUP BY se.id_nutricion_tiempo ASC
					$limit
					";
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}

	public function db_get_tiempo_ciclo($id_tiempo){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "SELECT *
				  FROM $tbl[nutricion_ciclo_receta]
				  WHERE activo = 1 AND id_tiempo = $id_tiempo";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	/*Trae la información para el formulario de edición de la tabla av_nutricion_tiempos*/
	public function get_orden_unico_tiempo($id_nutricion_tiempo){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "SELECT * FROM $tbl[nutricion_tiempos] WHERE id_nutricion_tiempo = $id_nutricion_tiempo";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	/*Actualiza la información en el formuladio de edición de la tabla av_nutricion_tiempos*/
	public function db_update_data($data=array()){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$condicion = array('id_nutricion_tiempo !=' => $data['id_nutricion_tiempo'], 'clave_corta = '=> $data['clave_corta']); 
		$existe    = $this->row_exist($tbl['nutricion_tiempos'], $condicion);
		if(!$existe){
			$condicion = "id_nutricion_tiempo = ".$data['id_nutricion_tiempo']; 
			$update    = $this->update_item($tbl['nutricion_tiempos'], $data, 'id_nutricion_tiempo', $condicion);
			return $update;
		}else{
			return false;
		}
	}

	/*Inserta registro de la tabla av_nutricion_tiempos*/
	public function db_insert_data($data = array())	{
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$existe = $this->row_exist($tbl['nutricion_tiempos'], array('clave_corta'=> $data['clave_corta']));
		if(!$existe){
			$insert = $this->insert_item($tbl['nutricion_tiempos'], $data);
			return $insert;
		}else{
			return false;
		}
	}

	public function bd_delete_data($data = array()){
		// DB Info
		$tbl = $this->tbl;
		
		$condicion = array('id_nutricion_tiempo ='=> $data['id_nutricion_tiempo']);
		$update = $this->update_item($tbl['nutricion_tiempos'],$data,'id_nutricion_tiempo',$condicion);
		if($update){
			return $update;
		}else{
			return false;
		}
	}

	public function db_get_tiempo_usado($id_tiempo){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "SELECT COUNT(*) num_tiempos FROM (
			      SELECT id_tiempo FROM $tbl[nutricion_ciclo_receta] WHERE id_tiempo = $id_tiempo) t";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}
}