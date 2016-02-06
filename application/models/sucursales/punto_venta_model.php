<?php
class punto_venta_model extends Base_Model{

	public function db_get_data($data = array()){
		// DB Info		
		$tbl = $this->tbl;
		$filtro_sucursal = $this->privileges_sucursal('s');
		// Query
		$filtro         = (isset($data['buscar']))?$data['buscar']:false;
		$limit 			= (isset($data['limit']))?$data['limit']:0;
		$offset 		= (isset($data['offset']))?$data['offset']:0;
		$aplicar_limit 	= (isset($data['aplicar_limit']))?true:false;
		$filtro = ($filtro) ? "AND (p.punto_venta like '%$filtro%' OR
									p.clave_corta like '%$filtro%' OR
									a.clave_corta like '%$filtro%' OR
									s.sucursal like '%$filtro%' OR
									ag.gavetas like '%$filtro%' OR
									p.descripcion like '%$filtro%')" : "";
		$limit 			= ($aplicar_limit) ? "LIMIT $offset ,$limit" : "";
		//Query
		$query = "	SELECT 
						p.*
						,a.clave_corta as cv_almacen
						,s.sucursal
						,ag.gavetas
					FROM $tbl[sucursales_punto_venta] p
					LEFT JOIN $tbl[almacen_almacenes] a on a.id_almacen_almacenes = p.id_almacen
					LEFT JOIN $tbl[sucursales] s on s.id_sucursal = p.id_sucursal
					LEFT JOIN $tbl[almacen_gavetas] ag on ag.id_almacen_gavetas = p.id_almacen_gavetas
					WHERE p.activo = 1 $filtro_sucursal $filtro
					ORDER BY p.id_sucursales_punto_venta ASC
					$limit
					";
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}


	public function get_cliente_punto_venta($id_cliente, $id_punto_venta){
		// DB Info		
		$tbl = $this->tbl;
		//Query
		$query = "SELECT * 
		          FROM $tbl[sucursales_cliente_venta] 
				  WHERE activo = 1 
				  AND id_cliente = $id_cliente
				  AND id_punto_venta = $id_punto_venta";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}
	public function get_punto_venta_x_pventa($id_punto_venta){
		// DB Info		
		$tbl = $this->tbl;
		//Query
		$query = "SELECT * FROM $tbl[sucursales_punto_venta] where activo = 1 AND id_sucursales_punto_venta = $id_punto_venta";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}
	public function get_punto_venta_x_sucursal($id_sucursal){
		// DB Info		
		$tbl = $this->tbl;
		//Query
		$query = "SELECT * FROM $tbl[sucursales_punto_venta] where activo = 1 AND id_sucursal = $id_sucursal";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	public function get_clientes_x_punto_venta($id_punto_venta){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "SELECT
						  c.*
				  FROM $tbl[ventas_clientes] c
				  LEFT JOIN $tbl[sucursales_cliente_venta] cv on cv.id_cliente = c.id_ventas_clientes
				  LEFT JOIN $tbl[sucursales_punto_venta] pv on pv.id_sucursales_punto_venta = cv.id_punto_venta
				  WHERE c.activo = 1 AND pv.id_sucursales_punto_venta = $id_punto_venta 
				  GROUP BY c.id_ventas_clientes ASC";
				  
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	public function get_punto_venta_x_venta_sucursal($id_punto_venta){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "SELECT
						  p.id_sucursales_punto_venta
						 ,p.punto_venta
						 ,p.clave_corta
				  FROM $tbl[sucursales_punto_venta] p
				  LEFT JOIN $tbl[sucursales_cliente_venta] cv on cv.id_punto_venta = p.id_sucursales_punto_venta
				  LEFT JOIN $tbl[ventas_clientes] c on c.id_ventas_clientes = cv.id_cliente
				  WHERE p.activo = 1 AND p.id_sucursales_punto_venta != $id_punto_venta
				  GROUP BY p.id_sucursales_punto_venta ASC";
				  
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	public function get_orden_unico_punto_venta($id_punto_venta){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "SELECT 
						 p.id_usuario as id_user
						,p.punto_venta
						,p.clave_corta as cv_punto_venta
						,p.id_sucursal
						,p.edit_id_usuario
						,p.edit_timestamp as edicion
						,p.timestamp
						,p.descripcion
						,a.clave_corta as cv_almacen
						,a.id_almacen_almacenes
						,s.sucursal
						,ag.id_almacen_gavetas
					FROM $tbl[sucursales_punto_venta] p
					LEFT JOIN $tbl[almacen_almacenes] a on a.id_almacen_almacenes = p.id_almacen
					LEFT JOIN $tbl[almacen_gavetas] ag on ag.id_almacen_gavetas = p.id_almacen_gavetas
					
					LEFT JOIN $tbl[sucursales] s on s.id_sucursal = p.id_sucursal
					LEFT JOIN $tbl[sucursales_cliente_venta] cv on cv.id_punto_venta = p.id_sucursales_punto_venta
					LEFT JOIN $tbl[ventas_clientes] c on c.id_ventas_clientes = cv.id_cliente
					WHERE p.activo = 1 AND p.id_sucursales_punto_venta = $id_punto_venta
					ORDER BY p.id_sucursales_punto_venta ASC";
					//print_debug($query);
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	public function db_update_data($data = array()){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$condicion = array('id_sucursales_punto_venta !=' => $data['id_sucursales_punto_venta'], 'clave_corta = '=> $data['clave_corta']); 
		$existe    = $this->row_exist($tbl['sucursales_punto_venta'], $condicion);
		if(!$existe){
			$condicion = "id_sucursales_punto_venta = ".$data['id_sucursales_punto_venta'];			
			$update = $this->update_item($tbl['sucursales_punto_venta'], $data, 'id_sucursales_punto_venta', $condicion);
			return $update;
		}else{
			return false;
		}
	}

	public function get_data_almacenes_x_sucursal($id_sucursal){
		// DB Info
		$tbl = $this->tbl;
		//Query
		$query = "SELECT * FROM $tbl[almacen_almacenes] WHERE id_sucursal = $id_sucursal AND activo = 1";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	public function get_data_gavetas_x_almacen($id_almacen_almacenes){
		// DB Info
		$tbl = $this->tbl;
		//Query
		$query = "SELECT * FROM $tbl[almacen_gavetas] WHERE id_almacen_almacenes = $id_almacen_almacenes AND activo = 1";
		// print_debug($query);
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}


	public function db_insert_data($data = array()){
		// DB Info
		$tbl = $this->tbl;
		//Query
		$existe = $this->row_exist($tbl['sucursales_punto_venta'], array('clave_corta'=> $data['clave_corta']));
		if(!$existe){
			$insert = $this->insert_item($tbl['sucursales_punto_venta'], $data);
			return $insert;
		}else{
			return false;
		}
	}

	public function insert_cliente_venta($data = array()){
		//print_debug($data);
		// DB Info
		$tbl = $this->tbl;
		// Query
		$insert = $this->insert_item($tbl['sucursales_cliente_venta'], $data,true);
		return $insert;
	}

	public function get_data_punto_venta_delete($id_punto_venta){
		// DB Info
		$tbl = $this->tbl;
		//Query
		$query = "SELECT COUNT(*) num_pventa FROM (
			      SELECT id_punto_venta FROM $tbl[sucursales_cliente_venta] WHERE id_punto_venta = $id_punto_venta) pv";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	public function bd_delete_data($data = array()){
		// DB Info
		$tbl = $this->tbl;
		
		$condicion = array('id_sucursales_punto_venta ='=> $data['id_sucursales_punto_venta']);
		$update = $this->update_item($tbl['sucursales_punto_venta'],$data,'id_sucursales_punto_venta',$condicion);
		if($update){
			return $update;
		}else{
			return false;
		}
	}
}
