<?php
class operadores_model extends Base_Model{

	function get_vendedores($limit, $offset, $filtro="", $aplicar_limit = true){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$filtro = ($filtro=="") ? "" : "AND ( 	vv.nombre  LIKE '%$filtro%' OR 
												vv.paterno  LIKE '%$filtro%' OR 
												vv.materno  LIKE '%$filtro%' OR 
												vv.clave_corta  LIKE '%$filtro%' OR
												vv.rfc            LIKE '%$filtro%' OR 
												e.entidad         LIKE '%$filtro%' OR
												su.sucursal       LIKE '%$filtro%' 
											)";
		$limit = ($aplicar_limit) ? "LIMIT $offset ,$limit" : "";
		$query = "SELECT 
						vv.id_ventas_vendedores,
						vv.nombre,
						vv.paterno,
						vv.materno,
						vv.clave_corta,
						vv.rfc,
						vv.calle,
						vv.num_int,
						vv.num_ext,
						vv.colonia,
						vv.municipio,
						e.entidad,
						su.sucursal,
						vv.telefonos,
						vv.email,
						vv.timestamp
					FROM $tbl[ventas_vendedores] vv
					LEFT JOIN $tbl[administracion_entidades]  e on vv.id_entidad = e.id_administracion_entidad
					LEFT JOIN $tbl[sucursales] su on vv.id_sucursal = su.id_sucursal
					WHERE vv.activo = 1 $filtro
					ORDER BY vv.id_ventas_vendedores
				$limit;";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			 return $query->result_array();
		}	
	}
	function get_existencia_vendedor($clave_corta){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$query = "SELECT * FROM $tbl[ventas_vendedores] vv WHERE vv.clave_corta = '$clave_corta'";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}
	function get_vendedor_unico($id_vendedor){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$query = "SELECT * FROM $tbl[ventas_vendedores] vv WHERE vv.id_ventas_vendedores = $id_vendedor";

		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}
	function insert_vendedor($data){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$insert = $this->insert_item($tbl['ventas_vendedores'], $data);
		return $insert;
	}
	function update_vendedor($data, $id_vendedor){
		$tbl = $this->tbl;

		$data['id_ventas_vendedores'] = $id_vendedor;
		$condicion = "id_ventas_vendedores = $id_vendedor"; 
		$update    = $this->update_item($tbl['ventas_vendedores'], $data, 'id_ventas_vendedores', $condicion);
		return $update;
	}

	function db_get_operador_ocupado($id_operador){
		$tbl = $this->tbl;
		$query = "SELECT * 
		          FROM $tbl[ventas_vendedores] v
		          WHERE v.activo = 0
		          AND v.id_ventas_vendedores = $id_operador";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	public function bd_delete_data($data = array()){
		// DB Info
		$tbl = $this->tbl;
		
		$condicion = array('id_ventas_vendedores ='=> $data['id_ventas_vendedores']);
		$update = $this->update_item($tbl['ventas_vendedores'],$data,'id_ventas_vendedores',$condicion);
		if($update){
			return $update;
		}else{
			return false;
		}
	}
}