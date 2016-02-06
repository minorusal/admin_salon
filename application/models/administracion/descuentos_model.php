<?php
class descuentos_model extends Base_Model{

	//Función que obtiene toda la información de la tabla av_administracion_descuentos
	public function db_get_data($data=array())	{
		// DB Info		
		$tbl = $this->tbl;
		// Filtro
		$filtro         = (isset($data['buscar']))?$data['buscar']:false;
		$limit 			= (isset($data['limit']))?$data['limit']:0;
		$offset 		= (isset($data['offset']))?$data['offset']:0;
		$aplicar_limit 	= (isset($data['aplicar_limit']))?true:false;
		$filtro = ($filtro) ? "AND (de.valor_descuento like '%$filtro%' OR
									de.clave_corta like '%$filtro%' OR
									de.descripcion like '%$filtro%' OR
									de.descuento like '%$filtro%')" : "";
		$limit 			= ($aplicar_limit) ? "LIMIT $offset ,$limit" : "";
		//Query
		$query = "	SELECT *
					FROM $tbl[administracion_descuentos] de
					WHERE de.activo = 1 $filtro
					ORDER BY de.id_administracion_descuentos ASC
					$limit
					";
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}

	public function db_get_descuento($id_descuento){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "SELECT COUNT(*) num_descuentos FROM (
			      SELECT id_descuento FROM $tbl[sucursales_descuento_sucursal] WHERE id_descuento = $id_descuento) d";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	/*Trae la información para el formulario de edición de la tabla av_administracion_descuentos*/
	public function get_orden_unico_descuento($id_administracion_descuentos){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "SELECT * FROM $tbl[administracion_descuentos] WHERE id_administracion_descuentos = $id_administracion_descuentos";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	/*Actualiza la información en el formuladio de edición de la tabla av_administracion_descuentos*/
	public function db_update_data($data=array())	{
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$condicion = array('id_administracion_descuentos !=' => $data['id_administracion_descuentos'], 'clave_corta = '=> $data['clave_corta']); 
		$existe    = $this->row_exist($tbl['administracion_descuentos'], $condicion);
		if(!$existe){
			$condicion = "id_administracion_descuentos = ".$data['id_administracion_descuentos']; 
			$update    = $this->update_item($tbl['administracion_descuentos'], $data, 'id_administracion_descuentos', $condicion);
			return $update;
		}else{
			return false;
		}
	}

	/*Inserta registro de la tabla av_administracion_descuentos*/
	public function db_insert_data($data = array())	{
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$existe = $this->row_exist($tbl['administracion_descuentos'], array('clave_corta'=> $data['clave_corta']));
		if(!$existe){
			$insert = $this->insert_item($tbl['administracion_descuentos'], $data);
			return $insert;
		}else{
			return false;
		}
	}

	public function bd_delete_data($data = array()){
		// DB Info
		$tbl = $this->tbl;
		
		$condicion = array('id_administracion_descuentos ='=> $data['id_administracion_descuentos']);
		$update = $this->update_item($tbl['administracion_descuentos'],$data,'id_administracion_descuentos',$condicion);
		if($update){
			return $update;
		}else{
			return false;
		}
	}
}