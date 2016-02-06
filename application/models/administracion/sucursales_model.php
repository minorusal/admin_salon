<?php
class sucursales_model extends Base_Model{

	//Función que obtiene toda la información de la tabla sys_sucursales
	public function db_get_data($data=array()){
		// DB Info		
		$tbl = $this->tbl;

		$filtro_sucursal = ($this->sucursales_availables) ? "AND su.id_sucursal IN (".$this->sucursales_availables.")" : "";
		// Filtro
		$filtro         = (isset($data['buscar']))?$data['buscar']:false;
		$limit 			= (isset($data['limit']))?$data['limit']:0;
		$offset 		= (isset($data['offset']))?$data['offset']:0;
		$aplicar_limit 	= (isset($data['aplicar_limit']))?true:false;
		$filtro = ($filtro) ? "AND (su.clave_corta like '%$filtro%' OR
									su.direccion like '%$filtro%' OR
									su.sucursal like '%$filtro%')" : "";
		$limit 			= ($aplicar_limit) ? "LIMIT $offset ,$limit" : "";
		//Query
		$query = "	SELECT 
						 su.id_sucursal
						,su.clave_corta
						,su.rfc
						,su.direccion
						,su.sucursal
						,su.razon_social
						,r.clave_corta as region
					FROM $tbl[sucursales] su
					LEFT JOIN $tbl[administracion_regiones] r on su.id_region = r.id_administracion_region
					WHERE su.activo = 1 $filtro_sucursal $filtro
					ORDER BY su.id_sucursal ASC
					$limit
					";
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}

	/*Trae la información para el formulario de edición de sucursales*/
	public function get_orden_unico_sucursal($id_sucursal){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "SELECT * FROM $tbl[sucursales] WHERE id_sucursal = $id_sucursal";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	/*Actualliza la información en el formuladio de edición de sucursales*/
	public function db_update_data($data=array()){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$condicion = array('id_sucursal !=' => $data['id_sucursal'], 'clave_corta = '=> $data['clave_corta']); 
		$existe = $this->row_exist($tbl['sucursales'], $condicion);
		if(!$existe){
			$condicion = "id_sucursal = ".$data['id_sucursal']; 
			$update    = $this->update_item($tbl['sucursales'], $data, 'id_sucursal', $condicion);
			return $update;
		}else{
			return false;
		}
	}

	/*Inserta registro de sucursales*/
	public function db_insert_data($data = array()){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$existe = $this->row_exist($tbl['sucursales'], array('clave_corta'=> $data['clave_corta']));
		if(!$existe){
			$insert = $this->insert_item($tbl['sucursales'], $data);
			return $insert;
		}else{
			return false;
		}
	}
}