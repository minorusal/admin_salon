<?php
class formas_de_pago_model extends Base_Model{


	//Función que obtiene toda la información de la tabla av_administracion_forma_pago
	public function db_get_data($data=array()){
		// DB Info		
		$tbl = $this->tbl;
		// Filtro
		$filtro         = (isset($data['buscar']))?$data['buscar']:false;
		$limit 			= (isset($data['limit']))?$data['limit']:0;
		$offset 		= (isset($data['offset']))?$data['offset']:0;
		$aplicar_limit 	= (isset($data['aplicar_limit']))?true:false;
		$filtro = ($filtro) ? "AND (fp.clave_corta like '%$filtro%' OR
									fp.descripcion like '%$filtro%')" : "";
		$limit 			= ($aplicar_limit) ? "LIMIT $offset ,$limit" : "";
		//Query
		$query = "	SELECT *
					FROM $tbl[administracion_forma_pago] fp
					WHERE fp.activo = 1 $filtro
					ORDER BY fp.id_forma_pago ASC
					$limit
					";
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}

	public function db_get_forma_pago($id_forma_pago){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "SELECT COUNT(*) num_fpagos FROM (
			      SELECT id_forma_pago FROM $tbl[compras_ordenes] WHERE id_forma_pago = $id_forma_pago UNION ALL
			      SELECT id_forma_pago FROM $tbl[sucursales_forma_pago] WHERE id_forma_pago = $id_forma_pago) fp";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}
	public function db_get_forma_pago_sucursal($id_forma_pago){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "SELECT ps.id_forma_pago
				  FROM $tbl[administracion_forma_pago] p
				  LEFT JOIN $tbl[sucursales_forma_pago] ps on ps.id_forma_pago = p.id_forma_pago 
				  WHERE p.activo = 1 AND p.id_forma_pago = $id_forma_pago";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	/*Trae la información para el formulario de edición de av_administracion_forma_pago*/
	public function get_orden_unico_formapago($id_forma_pago){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "SELECT * FROM $tbl[administracion_forma_pago] WHERE id_forma_pago = $id_forma_pago";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	/*Actualiza la información en el formuladio de edición de la tabla av_administracion_forma_pago*/
	public function db_update_data($data=array()){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$condicion = array('id_forma_pago !=' => $data['id_forma_pago'], 'clave_corta = '=> $data['clave_corta']); 
		$existe    = $this->row_exist($tbl['administracion_forma_pago'], $condicion);
		if(!$existe){
			$condicion = "id_forma_pago = ".$data['id_forma_pago']; 
			$update    = $this->update_item($tbl['administracion_forma_pago'], $data, 'id_forma_pago', $condicion);
			return $update;
		}else{
			return false;
		}
	}
	/*Inserta registro de la tabla av_administracion_forma_pago*/
	public function db_insert_data($data = array()){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$existe = $this->row_exist($tbl['administracion_forma_pago'], array('clave_corta'=> $data['clave_corta']));
		if(!$existe){
			$insert = $this->insert_item($tbl['administracion_forma_pago'], $data);
			return $insert;
		}else{
			return false;
		}
	}

	public function bd_delete_data($data = array()){
		// DB Info
		$tbl = $this->tbl;
		
		$condicion = array('id_forma_pago ='=> $data['id_forma_pago']);
		$update = $this->update_item($tbl['administracion_forma_pago'],$data,'id_forma_pago',$condicion);
		if($update){
			return $update;
		}else{
			return false;
		}
	}
}