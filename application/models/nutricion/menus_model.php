<?php
class menus_model extends Base_Model{

	public function db_get_data($data=array())	{		
		// DB Info		
		$tbl = $this->tbl;
		$filtro_sucursal = $this->privileges_sucursal('s');
		
		$filtro         = (isset($data['buscar']))?$data['buscar']:false;
		$limit 			= (isset($data['limit']))?$data['limit']:0;
		$offset 		= (isset($data['offset']))?$data['offset']:0;
		$aplicar_limit 	= (isset($data['aplicar_limit']))?true:false;
		$filtro = ($filtro) ? "AND (m.menu like '%$filtro%' OR
									m.id_nutricion_menu like '%$filtro%' OR
									r.id_nutricion_receta like '%$filtro%' OR
									m.clave_corta like '%$filtro%')" : "";
		$limit 			= ($aplicar_limit) ? "LIMIT $offset ,$limit" : "";
		//Query
		$query = "	SELECT
						 m.*
						,s.sucursal
						,s.id_sucursal
						,r.id_nutricion_receta
						,rm.id_menu
						,a.id_compras_articulo_precios
					FROM $tbl[nutricion_menu] m
					LEFT JOIN $tbl[nutricion_programacion_articulo_menu] am on am.id_menu = m.id_nutricion_menu
					LEFT JOIN $tbl[nutricion_programacion_receta_menu] rm on rm.id_menu = m.id_nutricion_menu
					LEFT JOIN $tbl[sucursales] s on s.id_sucursal = am.id_sucursal
					LEFT JOIN $tbl[nutricion_recetas] r on r.id_nutricion_receta = rm.id_receta
					LEFT JOIN $tbl[compras_articulos_precios] a on a.id_compras_articulo_precios = am.id_articulo
					WHERE m.activo = 1 $filtro_sucursal $filtro
					GROUP BY m.id_nutricion_menu ASC
					$limit
					";
					//print_debug($query);
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}

	public function get_lts_articulos_x_menu($data=array()){
		$tbl = $this->tbl;
		$query = "  SELECT 
						am.*		
					FROM 
						$tbl[nutricion_programacion_articulo_menu] am
					WHERE am.id_menu = $data[id_menu] AND am.id_sucursal = $data[id_sucursal]";
		/*$query = "SELECT
						s.sucursal
						,s.id_region
						,a.id_articulo
						,a.id_compras_articulo_precios
						,a.upc
						,CONCAT_WS(' ', ar.articulo, p.presentacion, a.um_x_presentacion, u.um) as articulo
					FROM
						$tbl[sucursales] s
					LEFT JOIN $tbl[compras_articulos_precios] a on a.id_administracion_region = s.id_region
					LEFT JOIN $tbl[compras_articulos] ar on ar.id_compras_articulo = a.id_articulo
					LEFT JOIN $tbl[compras_presentaciones] p on p.id_compras_presentacion = a.id_presentacion
					LEFT JOIN $tbl[compras_um]  u on u.id_compras_um = ar.id_compras_um
					WHERE
						ar.id_articulo_tipo = 3
					AND	s.id_sucursal = $data[id_sucursal]";*/
					//print_debug($query);
		$query = $this->db->query($query);
		if($query->num_rows >=1){
			return $query->result_array();
		}else{
			return false;
		}
	}
	public function get_lts_recetas_x_menu($data=array()){
		//print_debug($data);
		$tbl = $this->tbl;
		$query = "  SELECT 
						r.id_nutricion_receta
						,r.receta
						,r.clave_corta		
					FROM 
						$tbl[nutricion_recetas] r
					LEFT JOIN $tbl[nutricion_programacion_receta_menu] rm on rm.id_receta = r.id_nutricion_receta
					WHERE r.id_sucursal = $data[id_sucursal] 
					AND rm.id_menu = $data[id_menu]";

		$query = $this->db->query($query);
		if($query->num_rows >=1){
			return $query->result_array();
		}else{
			return false;
		}
	}

	public function get_lts_recetas( $id_sucursal ){	
		$tbl = $this->tbl;
		$query = "  SELECT 
						r.id_nutricion_receta
						,r.receta
						,r.clave_corta		
					FROM 
						$tbl[nutricion_recetas] r
					WHERE r.id_sucursal = $id_sucursal ";

		$query = $this->db->query($query);
		if($query->num_rows >=1){
			return $query->result_array();
		}else{
			return false;
		}
	}
	public function get_lts_articulos( $id_sucursal ){
		$tbl =$this->tbl;

		$query = "  SELECT
						 s.sucursal
						,s.id_region
						,a.id_articulo
						,a.id_compras_articulo_precios
						,CONCAT_WS(' ', ar.articulo, p.presentacion, a.um_x_presentacion, u.um) as articulo
					FROM
						$tbl[sucursales] s
					LEFT JOIN $tbl[compras_articulos_precios] a on a.id_administracion_region = s.id_region
					LEFT JOIN $tbl[compras_articulos] ar on ar.id_compras_articulo = a.id_articulo
					LEFT JOIN $tbl[compras_presentaciones] p on p.id_compras_presentacion = a.id_presentacion
					LEFT JOIN $tbl[compras_um] u on u.id_compras_um = ar.id_compras_um
					WHERE
						ar.id_articulo_tipo = 3
					AND	s.id_sucursal = $id_sucursal";
		//print_debug($query);
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}else{
			return false;
		}
	}

	/*Inserta registro de menus*/
	public function db_insert_data($data = array()){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$existe = $this->row_exist($tbl['nutricion_menu'], array('clave_corta'=> $data['clave_corta']));
		if(!$existe){
			$insert = $this->insert_item($tbl['nutricion_menu'], $data,true);
			return $insert;
		}else{
			return false;
		}
	}

	public function db_insert_receta($data = array()){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$insert = $this->insert_item($tbl['nutricion_programacion_receta_menu'], $data);
		if($insert){
			return $insert;
		}else{
			return false;
		}
	}

	public function db_insert_articulo($data = array()){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$insert = $this->insert_item($tbl['nutricion_programacion_articulo_menu'], $data);
		if($insert){
			return $insert;
		}else{
			return false;
		}
	}

	public function db_update_data($data=array()){
		//print_debug($data);
		// DB Info		
		$tbl = $this->tbl;
		// Query
			$condicion = "id_nutricion_menu = ".$data['id_nutricion_menu']; 
			$update    = $this->update_item($tbl['nutricion_menu'], $data, 'id_nutricion_menu', $condicion);
		if($update){
			return $update;
		}else{
			//print_debug($update);
			return false;
		}
	}

	public function delete_receta($id_sucursal){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "DELETE 
				  FROM $tbl[nutricion_programacion_receta_menu]
				  WHERE id_sucursal =".$id_sucursal;
		$query = $this->db->query($query);
		if($query){
			return $query;
		}
	}

	public function db_update_data_receta($data = array()){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$insert = $this->insert_item($tbl['nutricion_programacion_receta_menu'], $data);
		if($insert){
			return $insert;
		}
	}

	public function delete_articulo($id_sucursal){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "DELETE 
				  FROM $tbl[nutricion_programacion_articulo_menu]
				  WHERE id_sucursal =".$id_sucursal;
		$query = $this->db->query($query);
		if($query){
			return $query;
		}
	}

	public function db_update_data_articulo($data = array()){
		//print_debug($data);
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$insert = $this->insert_item($tbl['nutricion_programacion_articulo_menu'], $data);
		if($insert){
			return $insert;
		}
	}

}
//55531521 diana alva
?>