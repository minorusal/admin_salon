<?php
class creditos_model extends Base_Model{

	//Función que obtiene toda la información de la tabla av_administracion_creditos
	public function db_get_data($data=array())	{
		// DB Info		
		$tbl = $this->tbl;
		// Filtro
		$filtro         = (isset($data['buscar']))?$data['buscar']:false;
		$limit 			= (isset($data['limit']))?$data['limit']:0;
		$offset 		= (isset($data['offset']))?$data['offset']:0;
		$aplicar_limit 	= (isset($data['aplicar_limit']))?true:false;
		$filtro = ($filtro) ? "AND (cr.valor_credito like '%$filtro%' OR
									cr.clave_corta like '%$filtro%' OR
									cr.descripcion like '%$filtro%' OR
									cr.credito like '%$filtro%')" : "";
		$limit 			= ($aplicar_limit) ? "LIMIT $offset ,$limit" : "";
		//Query
		$query = "	SELECT *
					FROM $tbl[administracion_creditos] cr
					WHERE cr.activo = 1 $filtro
					ORDER BY cr.id_administracion_creditos ASC
					$limit
					";
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}

	public function db_get_credito($id_credito){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "SELECT c.*
				  FROM $tbl[administracion_creditos] c
				  LEFT JOIN $tbl[compras_ordenes] o on o.id_credito = c.id_administracion_creditos
				  WHERE c.activo = 1 AND o.id_credito = $id_credito ";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	/*Trae la información para el formulario de edición de la tabla av_administracion_creditos*/
	public function get_orden_unico_credito($id_administracion_creditos)	{
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "SELECT * FROM $tbl[administracion_creditos] WHERE id_administracion_creditos = $id_administracion_creditos";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	/*Actualiza la información en el formuladio de edición de la tabla av_administracion_creditos*/
	public function db_update_data($data=array()){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$condicion = array('id_administracion_creditos !=' => $data['id_administracion_creditos'], 'clave_corta = '=> $data['clave_corta']); 
		$existe    = $this->row_exist($tbl['administracion_creditos'], $condicion);
		if(!$existe){
			$condicion = "id_administracion_creditos = ".$data['id_administracion_creditos']; 
			$update = $this->update_item($tbl['administracion_creditos'], $data, 'id_administracion_creditos', $condicion);
			return $update;
		}else{
			return false;
		}
	}

	/*Inserta registro de la tabla ac_administracion_creditos*/
	public function db_insert_data($data = array()){
		$tbl = $this->tbl;
		$existe = $this->row_exist($tbl['administracion_creditos'], array('clave_corta'=> $data['clave_corta']));
		if(!$existe){
			$insert = $this->insert_item($tbl['administracion_creditos'], $data);
			return $insert;
		}else{
			return false;
		}
	}

	public function bd_delete_data($data = array()){
		// DB Info
		$tbl = $this->tbl;
		
		$condicion = array('id_administracion_creditos ='=> $data['id_administracion_creditos']);
		$update = $this->update_item($tbl['administracion_creditos'],$data,'id_administracion_creditos',$condicion);
		if($update){
			return $update;
		}else{
			return false;
		}
	}
}