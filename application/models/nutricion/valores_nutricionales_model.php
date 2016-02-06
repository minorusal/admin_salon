<?php
class valores_nutricionales_model extends Base_Model{

	public function get_valores_nutricionales_default($data = array()){
		// DB Info		
		$tbl = $this->tbl;
		// Filtro
		$buscar = (array_key_exists('buscar',$data))?$data['buscar']:false;
		$filtro = ($buscar) ? "AND ( 	ar.articulo  LIKE '%$buscar%')" : "";
		
		$limit 			= (array_key_exists('limit',$data)) ?$data['limit']:0;
		$offset 		= (array_key_exists('offset',$data))?$data['offset']:0;
		$aplicar_limit 	= (array_key_exists('aplicar_limit',$data)) ? $data['aplicar_limit'] : false;
		$limit 			= ($aplicar_limit) ? "LIMIT $offset ,$limit" : "";
		
		// Query
		$query = "	SELECT *
					FROM $tbl[nutricion_valores_nutricionales] va
					LEFT JOIN $tbl[compras_articulos] ar on ar.id_compras_articulo = va.id_compras_articulos
					WHERE va.activo = 1 $filtro
					ORDER BY va.id_compras_articulos ASC
					$limit
					";
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}

	public function db_get_compras_articulos_valores($id_articulo){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "SELECT *
				  FROM $tbl[nutricion_valores_nutricionales] 
				  WHERE activo = 1 AND id_compras_articulos = $id_articulo ";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	public function get_valores_nutricionales_unico($id_articulo){
		// DB Info
		$tbl = $this->tbl;
		
		$condicion = array('id_compras_articulos =' => $id_articulo); 
		$existe    = $this->row_exist($tbl['nutricion_valores_nutricionales'], $condicion);
		if($existe){
			$query = "SELECT 
							 ca.articulo
							,vn.cantidad_sugerida
							,cu.um
							,vn.peso_bruto
							,vn.peso_neto
							,vn.energia
							,vn.proteina
							,vn.lipidos
							,vn.hidratos_carbono
							,vn.fibra
							,vn.vitamina_a
							,vn.acido_ascorbico
							,vn.acido_folico
							,vn.hierro_nohem 
							,vn.potasio
							,vn.azucar
							,vn.indice_glicemico
							,vn.carga_glicemica
							,vn.calcio
							,vn.sodio
							,vn.selenio
							,vn.fosforo
							,vn.colesterol
							,vn.ag_saturados
							,vn.ag_mono
							,vn.ag_poli
							,vn.timestamp
							,vn.edit_id_usuario
							,vn.edit_timestamp
							,ca.avatar
					  FROM $tbl[nutricion_valores_nutricionales] vn
					  LEFT JOIN $tbl[compras_articulos] ca on ca.id_compras_articulo = vn.id_compras_articulos
					  LEFT JOIN $tbl[compras_um] cu on cu.id_compras_um = ca.id_compras_um
					  WHERE vn.id_compras_articulos = $id_articulo
					  ";
			$query = $this->db->query($query);
			if($query->num_rows >= 1){
				return $query->result_array();
			}
		}
		else
		{
			return false;
		}
	}

	/*Actualiza la información en el formuladio de edición de la tabla nutricion_valores_nutricionales*/
	public function db_update_data($data=array()){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$condicion = array('id_compras_articulos =' => $data['id_compras_articulos']); 
		$existe    = $this->row_exist($tbl['nutricion_valores_nutricionales'], $condicion);
		if(!$existe){		
			$insert = $this->insert_item($tbl['nutricion_valores_nutricionales'], $data);
			return $insert;
		}else{
			//print_debug($data);
			$insert = $this->update_item($tbl['nutricion_valores_nutricionales'], $data, 'id_compras_articulos', $condicion);
			return $insert;
		}
	}

	public function bd_delete_data($data = array()){
		// DB Info
		$tbl = $this->tbl;
		
		$condicion = array('id_compras_articulo ='=> $data['id_compras_articulo']);
		$update = $this->update_item($tbl['compras_articulos'],$data,'id_compras_articulo',$condicion);
		if($update){
			return $update;
		}else{
			return false;
		}
	}
}