<?php
class listado_sucursales_model extends Base_Model{

	//Función que obtiene toda la información de la tabla sys_sucursales
	public function db_get_data($data=array()){
		$tbl = $this->tbl;
		$filtro_sucursal = $this->privileges_sucursal('su');
					
		$filtro         = (isset($data['buscar']))?$data['buscar']:false;
		$limit 			= (isset($data['limit']))?$data['limit']:0;
		$offset 		= (isset($data['offset']))?$data['offset']:0;
		$aplicar_limit 	= (isset($data['aplicar_limit']))?true:false;
		$filtro = ($filtro) ? "AND (su.clave_corta like '%$filtro%' OR
									su.direccion like '%$filtro%' OR
									su.inicio like '%$filtro%' OR
									su.final like '%$filtro%' OR
									e.entidad like '%$filtro%' OR
									r.clave_corta like '%$filtro%' OR
									su.sucursal like '%$filtro%')" : "";
		$limit 			= ($aplicar_limit) ? "LIMIT $offset ,$limit" : "";
		//Query
		$query = "	SELECT 
						 su.id_sucursal
						,su.clave_corta
						,su.clave_corta as cv_sucursal
						,su.inicio
						,su.final
						,su.direccion
						,su.sucursal
						,su.razon_social
						,e.entidad
						,r.clave_corta as region
					FROM $tbl[sucursales] su
					LEFT JOIN $tbl[administracion_regiones] r on su.id_region = r.id_administracion_region
					LEFT JOIN $tbl[administracion_entidades] e on e.id_administracion_entidad = su.id_entidad
					WHERE su.activo = 1 $filtro_sucursal $filtro
					ORDER BY su.id_sucursal ASC
					$limit
					";
					//print_debug($query);
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}

	public function get_sucursales_usuarios(){
		$tbl = $this->tbl;
		//Query
		$query = "	SELECT 
						 su.id_sucursal
						,su.clave_corta
						,su.clave_corta as cv_sucursal
						,su.inicio
						,su.final
						,su.direccion
						,su.sucursal
						,su.razon_social
						,e.entidad
						,r.clave_corta as region
					FROM $tbl[sucursales] su
					LEFT JOIN $tbl[administracion_regiones] r on su.id_region = r.id_administracion_region
					LEFT JOIN $tbl[administracion_entidades] e on e.id_administracion_entidad = su.id_entidad
					WHERE su.activo = 1 
					ORDER BY su.id_sucursal ASC
					";

      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}

	public function get_esquema_pago(){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "SELECT * FROM $tbl[sucursales_esquema_pago] ";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}
	public function get_esquema_venta(){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "SELECT * FROM $tbl[sucursales_esquema_venta] ";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	public function delete_pago($id_sucursal){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "DELETE 
				  FROM $tbl[sucursales_pago]
				  WHERE id_sucursal =".$id_sucursal;
		$query = $this->db->query($query);
		if($query){
			return $query;
		}
	}
	public function db_update_data_pago($data = array()){
		//print_debug($data);
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$insert = $this->insert_item($tbl['sucursales_pago'], $data);
		if($insert){
			return $insert;
		}
	}

	public function delete_venta($id_sucursal){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "DELETE 
				  FROM $tbl[sucursales_venta]
				  WHERE id_sucursal =".$id_sucursal;
		$query = $this->db->query($query);
		if($query){
			return $query;
		}
	}
	public function db_update_data_venta($data = array()){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$insert = $this->insert_item($tbl['sucursales_venta'], $data);
		if($insert){
			return $insert;
		}
	}
	public function delete_fpago($id_sucursal){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "DELETE 
				  FROM $tbl[sucursales_forma_pago]
				  WHERE id_sucursal =".$id_sucursal;
		$query = $this->db->query($query);
		if($query){
			return $query;
		}
	}
	public function db_update_data_fpago($data = array()){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$insert = $this->insert_item($tbl['sucursales_forma_pago'], $data);
		if($insert){
			return $insert;
		}
	}
	public function delete_descuento($id_sucursal){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "DELETE 
				  FROM $tbl[sucursales_descuento_sucursal]
				  WHERE id_sucursal =".$id_sucursal;
		$query = $this->db->query($query);
		if($query){
			return $query;
		}
	}
	public function db_update_data_descuento($data = array()){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$insert = $this->insert_item($tbl['sucursales_descuento_sucursal'], $data);
		if($insert){
			return $insert;
		}
	}
	/*Trae la información para el formulario de edición de sucursales*/
	public function get_orden_unico_sucursal($id_sucursal){
		// DB Info		
		$tbl = $this->tbl;
		$filtro_sucursal = $this->privileges_sucursal('s');
		// Query
		$query = "SELECT 
					 s.*
					 ,ep.esquema_pago
					 ,ev.esquema_venta
					 ,fp.forma_pago
					 ,ep.id_sucursales_esquema_pago
					 ,ev.id_sucursales_esquema_venta
					 ,fp.id_forma_pago
					 ,r.region
					 ,e.entidad
					 ,ds.id_descuento

				  FROM $tbl[sucursales] s
				 
				  LEFT JOIN $tbl[sucursales_pago] p on p.id_sucursal = s.id_sucursal
				  LEFT JOIN $tbl[sucursales_venta] v on v.id_sucursal = s.id_sucursal
				  LEFT JOIN $tbl[sucursales_forma_pago] f on f.id_sucursal = s.id_sucursal
				  LEFT JOIN $tbl[sucursales_esquema_pago] ep on ep.id_sucursales_esquema_pago = p.id_esquema_pago
				  LEFT JOIN $tbl[sucursales_esquema_venta] ev on ev.id_sucursales_esquema_venta = v.id_esquema_venta				  
				  LEFT JOIN $tbl[administracion_forma_pago] fp on fp.id_forma_pago =  f.id_forma_pago
				  LEFT JOIN $tbl[administracion_regiones] r on r.id_administracion_region = s.id_region
				  LEFT JOIN $tbl[administracion_entidades] e on e.id_administracion_entidad = s.id_entidad
				  LEFT JOIN $tbl[sucursales_descuento_sucursal] ds on ds.id_sucursal = s.id_sucursal 
				  WHERE s.id_sucursal = $id_sucursal AND s.activo = 1 $filtro_sucursal ";

		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	public function get_id_sucursal(){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "SELECT COUNT(*) AS cuantos FROM $tbl[sucursales] WHERE activo = 1";
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
			$insert = $this->insert_item($tbl['sucursales'], $data,true);
			return $insert;
		}else{
			return false;
		}
	}

	public function get_forma_pago($id_sucursal){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "SELECT fp.forma_pago
				  FROM $tbl[sucursales_forma_pago] p
				  LEFT JOIN $tbl[sucursales] s on s.id_sucursal = p.id_sucursal
				  LEFT JOIN $tbl[administracion_forma_pago] fp on fp.id_forma_pago = p.id_forma_pago
		          WHERE p.id_sucursal = $id_sucursal
		          ";
		          //print_debug($query);
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}
	public function get_esquemas_pago($id_sucursal){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "SELECT ep.esquema_pago
				  FROM $tbl[sucursales_pago] p
				  LEFT JOIN $tbl[sucursales] s on s.id_sucursal = p.id_sucursal
				  LEFT JOIN $tbl[sucursales_esquema_pago] ep on ep.id_sucursales_esquema_pago = p.id_esquema_pago
		          WHERE p.id_sucursal = $id_sucursal
		          ";
		          //print_debug($query);
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	public function get_esquemas_venta($id_sucursal){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "SELECT ev.esquema_venta
				  FROM $tbl[sucursales_venta] v
				  LEFT JOIN $tbl[sucursales] s on s.id_sucursal = v.id_sucursal
				  LEFT JOIN $tbl[sucursales_esquema_venta] ev on ev.id_sucursales_esquema_venta = v.id_esquema_venta
		          WHERE v.id_sucursal = $id_sucursal
		          ";
		          //print_debug($query);
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	public function db_get_sucursales_ocupadas($id_sucursal){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "SELECT count(*) as num_sucursales from (
				  SELECT id_sucursal FROM 00_ac_mx.av_nutricion_programacion_dias_festivos where id_sucursal = $id_sucursal
				  UNION ALL 
				  SELECT id_sucursal FROM 00_ac_mx.av_nutricion_programacion_dias_especiales where id_sucursal = $id_sucursal
				  UNION ALL 
				  SELECT id_sucursal FROM 00_ac_mx.av_nutricion_programacion_dias_descartados where id_sucursal = $id_sucursal
				  UNION ALL 
				  SELECT id_sucursal FROM 00_ac_mx.av_nutricion_programacion where id_sucursal = $id_sucursal
				  UNION ALL 
				  SELECT id_sucursal FROM 00_ac_mx.av_almacen_almacenes where id_sucursal = $id_sucursal
				  UNION ALL 
				  SELECT id_sucursal FROM 00_ac_mx.av_sucursales_punto_venta where id_sucursal = $id_sucursal
				  UNION ALL 
				  SELECT id_sucursal FROM 00_ac_mx.av_ventas_clientes where id_sucursal = $id_sucursal
				  UNION ALL 
				  SELECT id_sucursal FROM 00_ac_mx.av_nutricion_programacion_ciclos where id_sucursal = $id_sucursal
				  UNION ALL 
				  SELECT id_sucursal FROM 00_ac_mx.av_nutricion_ciclos where id_sucursal = $id_sucursal
				  UNION ALL 
				  SELECT id_sucursal FROM 00_ac_mx.av_administracion_servicios where id_sucursal = $id_sucursal
				  UNION ALL 
				  SELECT id_sucursal FROM 00_ac_mx.av_nutricion_recetas where id_sucursal = $id_sucursal
				  UNION ALL 
				  SELECT id_sucursal FROM 00_ac_mx.av_nutricion_programacion_receta_menu where id_sucursal = $id_sucursal
				  UNION ALL 
				  SELECT id_sucursal FROM 00_ac_mx.av_nutricion_menu where id_sucursal = $id_sucursal
				  UNION ALL 
				  SELECT id_sucursal FROM 00_ac_mx.av_nutricion_programacion_articulo_menu where id_sucursal = $id_sucursal
				  UNION ALL 
				  SELECT id_sucursal FROM 00_ac_mx.av_ventas_vendedores where id_sucursal = $id_sucursal
				  UNION ALL 
				  SELECT id_sucursal FROM 00_ac_mx.av_compras_ordenes where id_sucursal = $id_sucursal
				  UNION ALL 
				  SELECT id_sucursal FROM 00_ac_system.sys_usuarios where id_sucursal = $id_sucursal
				  ) a";
		         // print_debug($query);
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	public function bd_delete_data($data = array()){
		// DB Info
		$tbl = $this->tbl;
		
		$condicion = array('id_sucursal ='=> $data['id_sucursal']);
		$update = $this->update_item($tbl['sucursales'],$data,'id_sucursal',$condicion);
		if($update){
			return $update;
		}else{
			return false;
		}
	}

	public function db_get_sucursal_usada($id_sucursal){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query="SELECT *
		        FROM $tbl[sucursales]
		        WHERE id_sucursal = $id_sucursal";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	public function db_update_sucursales_user($privileges, $id_usuario){
		$this->privileges_sucursal_update_user($privileges, $id_usuario);
	}
}