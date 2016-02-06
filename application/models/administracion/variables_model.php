<?php
class variables_model extends Base_Model{

	//Función que obtiene toda la información de la tabla av_administracion_creditos
	public function db_get_data($data=array()){
		// DB Info		
		$tbl = $this->tbl;
		// Filtro
		$buscar = (isset($data['buscar']))?$data['buscar']:false;
		$filtro = ($buscar) ? "and id_vars = $buscar " 
							: "";
		// Query
		$query = "SELECT 
						va.nombre,
						va.valor,
						va.tabla,
						va.campo
					FROM $tbl[administracion_variables] va
					WHERE va.activo = 1 $filtro;";
					//dump_var($query);
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}
	public function update($data=array()){	
		// DB Info		
		$tbl = $this->tbl;
		// Filtro
		$resultado      = false;
		$id_vars   		= (isset($data['id_vars']))?$data['id_vars']:false;
		//$datos['valor'] = $data['valor'];
		$filtro 		= ($id_vars)?"id_vars='$id_vars'":'';
		if($id_vars){
			$update    = $this->update_item($tbl['administracion_variables'], $data, 'id_vars', $filtro);
			return $update;
		}
		return $resultado;
	}

}
?>