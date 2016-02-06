<?php
class impuestos_model extends Base_Model{
	//Función que obtiene toda la información de la tabla sys_impuestos
	public function db_get_data($data=array())	{
		// DB Info		
		$tbl = $this->tbl;
		// Filtro
		$filtro         = (isset($data['buscar']))?$data['buscar']:false;
		$limit 			= (isset($data['limit']))?$data['limit']:0;
		$offset 		= (isset($data['offset']))?$data['offset']:0;
		$aplicar_limit 	= (isset($data['aplicar_limit']))?true:false;
		$filtro = ($filtro) ? "AND (im.valor like '%$filtro%' OR
									im.clave_corta like '%$filtro%' OR
									im.descripcion like '%$filtro%' OR
									im.impuesto like '%$filtro%')" : "";
		$limit 			= ($aplicar_limit) ? "LIMIT $offset ,$limit" : "";
		//Query
		$query = "	SELECT *
					FROM $tbl[administracion_impuestos] im
					WHERE im.activo = 1 $filtro
					ORDER BY im.id_administracion_impuestos ASC
					$limit
					";
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}

	public function db_get_impuesto($id_impuesto){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "SELECT COUNT(*) num_impuestos FROM (
			      SELECT id_impuesto FROM $tbl[compras_articulos_precios] WHERE id_impuesto = $id_impuesto UNION ALL
			      SELECT id_impuesto FROM $tbl[compras_articulos_presentaciones] WHERE id_impuesto = $id_impuesto) i";
 //print_debug($query);
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	/*Trae la información para el formulario de edición de la tabla sys_impuestos*/
	public function get_orden_unico_sucursal($id_administracion_impuestos)	{
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "SELECT * FROM $tbl[administracion_impuestos] WHERE id_administracion_impuestos = $id_administracion_impuestos";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	/*Actualiza la información en el formuladio de edición de la tabla sys_impuestos*/
	public function db_update_data($data=array()){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$condicion = array('id_administracion_impuestos !=' => $data['id_administracion_impuestos'], 'clave_corta = '=> $data['clave_corta']); 
		$existe    = $this->row_exist($tbl['administracion_impuestos'], $condicion);
		if(!$existe){
			$condicion = "id_administracion_impuestos = ".$data['id_administracion_impuestos']; 
			$update    = $this->update_item($tbl['administracion_impuestos'], $data, 'id_administracion_impuestos', $condicion);
			return $update;
		}else{
			return false;
		}
	}

	/*Inserta registro de la tabla sys_impuestos*/
	public function db_insert_data($data = array())	{
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$existe = $this->row_exist($tbl['administracion_impuestos'], array('clave_corta'=> $data['clave_corta']));
		if(!$existe){
			$insert = $this->insert_item($tbl['administracion_impuestos'], $data);
			return $insert;
		}else{
			return false;
		}
	}

	public function bd_delete_data($data = array()){
		// DB Info
		$tbl = $this->tbl;
		
		$condicion = array('id_administracion_impuestos ='=> $data['id_administracion_impuestos']);
		$update = $this->update_item($tbl['administracion_impuestos'],$data,'id_administracion_impuestos',$condicion);
		if($update){
			return $update;
		}else{
			return false;
		}
	}
}