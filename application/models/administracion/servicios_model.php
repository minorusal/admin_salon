<?php
class servicios_model extends Base_Model{
	//Función que obtiene toda la información de la tabla av_administracion_servicios
	public function db_get_data($data=array())	{		
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$filtro         = (isset($data['buscar']))?$data['buscar']:false;
		$limit 			= (isset($data['limit']))?$data['limit']:0;
		$offset 		= (isset($data['offset']))?$data['offset']:0;
		$aplicar_limit 	= (isset($data['aplicar_limit']))?true:false;
		$filtro = ($filtro) ? "AND (sr.servicio like '%$filtro%' OR
									sr.clave_corta like '%$filtro%' OR
									sr.descripcion like '%$filtro%')" : "";
		$limit 			= ($aplicar_limit) ? "LIMIT $offset ,$limit" : "";
		//Query
		$query = "	SELECT 
						 sr.id_administracion_servicio
						,sr.clave_corta as cv_servicio
						,su.clave_corta as cv_sucursal
						,sr.descripcion as descripcion
						,sr.servicio 
						,sr.inicio
						,sr.final
						,su.id_sucursal
						,su.sucursal 
						,sr.id_usuario
						,sr.timestamp
						,sr.edit_timestamp
					FROM $tbl[administracion_servicios] sr
					LEFT JOIN $tbl[sucursales] su on su.id_sucursal = sr.id_sucursal
					WHERE sr.activo = 1 $filtro
					ORDER BY sr.id_administracion_servicio ASC
					$limit
					";
					
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}

	public function db_get_data_x_sucursal($id_sucursal,$id_servicio = false){
		// DB Info		
		$tbl = $this->tbl;
		$id_servicio = ($id_servicio)?" AND sr.id_administracion_servicio <> $id_servicio ":'';
		$query = "	SELECT *
					FROM $tbl[administracion_servicios] sr
					WHERE sr.activo = 1 AND sr.id_sucursal = $id_sucursal
					$id_servicio
					ORDER BY sr.id_administracion_servicio ASC";
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}

	/*Trae la información para el formulario de edición de la tabla av_administracion_servicios*/
	public function get_orden_unico_servicio($id_administracion_servicios)	{
		// DB Info
		$tbl = $this->tbl;
		// Query
		$query = "SELECT * FROM $tbl[administracion_servicios] WHERE id_administracion_servicio = $id_administracion_servicios";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	public function db_update_data($data=array()){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$condicion = array('id_administracion_servicio !=' => $data['id_administracion_servicio'], 'clave_corta = '=> $data['clave_corta']); 
		$existe    = $this->row_exist($tbl['administracion_servicios'], $condicion);
		if(!$existe){
			$condicion = "id_administracion_servicio = ".$data['id_administracion_servicio'];			
			$update = $this->update_item($tbl['administracion_servicios'], $data, 'id_administracion_servicio', $condicion);
			return $update;
		}else{
			return false;
		}
	}

	/*Inserta registro de la tabla ac_administracion_servicios*/
	public function db_insert_data($data = array()){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$existe = $this->row_exist($tbl['administracion_servicios'], array('clave_corta'=> $data['clave_corta']));
		if(!$existe){
			$insert = $this->insert_item($tbl['administracion_servicios'], $data);
			return $insert;
		}else{
			return false;
		}
	}
	
}