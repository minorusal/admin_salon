<?php
class familias_model extends Base_Model{

	//Función que obtiene toda la información de la tabla av_nutricion_familias
	public function db_get_data($data=array())	{		
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$filtro         = (isset($data['buscar']))?$data['buscar']:false;
		$limit 			= (isset($data['limit']))?$data['limit']:0;
		$offset 		= (isset($data['offset']))?$data['offset']:0;
		$aplicar_limit 	= (isset($data['aplicar_limit']))?true:false;
		$filtro = ($filtro) ? "AND (fa.familia like '%$filtro%' OR
									fa.clave_corta like '%$filtro%' OR
									fa.descripcion like '%$filtro%')" : "";
		$limit 			= ($aplicar_limit) ? "LIMIT $offset ,$limit" : "";
		//Query
		$query = "	SELECT *
					FROM $tbl[nutricion_familias] fa
					WHERE fa.activo = 1 $filtro
					ORDER BY fa.id_nutricion_familia ASC
					$limit
					";
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}
	 
	 public function db_get_familia_ciclo($id_familia){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "SELECT *
				  FROM $tbl[nutricion_ciclo_receta]
				  WHERE activo = 1 AND id_familia = $id_familia";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	/*Trae la información para el formulario de edición de la tabla av_nutricion_familias*/
	public function get_orden_unico_familia($id_nutricion_familia){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "SELECT * FROM $tbl[nutricion_familias] WHERE id_nutricion_familia = $id_nutricion_familia";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	/*Actualiza la información en el formuladio de edición de la tabla av_nutricion_familias*/
	public function db_update_data($data=array()){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$condicion = array('id_nutricion_familia !=' => $data['id_nutricion_familia'], 'clave_corta = '=> $data['clave_corta']); 
		$existe    = $this->row_exist($tbl['nutricion_familias'], $condicion);
		if(!$existe){
			$condicion = "id_nutricion_familia = ".$data['id_nutricion_familia']; 
			$update    = $this->update_item($tbl['nutricion_familias'], $data, 'id_nutricion_familia', $condicion);
			return $update;
		}else{
			return false;
		}
	}

	/*Inserta registro de la tabla av_nutricion_familias*/
	public function db_insert_data($data = array())	{
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$existe = $this->row_exist($tbl['nutricion_familias'], array('clave_corta'=> $data['clave_corta']));
		if(!$existe){
			$insert = $this->insert_item($tbl['nutricion_familias'], $data);
			return $insert;
		}else{
			return false;
		}
	}

	public function bd_delete_data($data = array()){
		// DB Info
		$tbl = $this->tbl;
		
		$condicion = array('id_nutricion_familia ='=> $data['id_nutricion_familia']);
		$update = $this->update_item($tbl['nutricion_familias'],$data,'id_nutricion_familia',$condicion);
		if($update){
			return $update;
		}else{
			return false;
		}
	}

	public function db_get_familia_usada($id_familia){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "SELECT COUNT(*) num_familias FROM (
			      SELECT id_familia FROM $tbl[nutricion_ciclo_receta] WHERE id_familia = $id_familia UNION ALL
			      SELECT id_nutricion_familia FROM $tbl[nutricion_recetas] WHERE id_nutricion_familia = $id_familia) f";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}
}