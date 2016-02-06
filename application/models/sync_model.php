<?php
class sync_model extends Base_Model{

	public function get_data_news($table, $columns , $last_id = array(), $limit = 100)
	{
		if(!empty($last_id)){
			$condicion = ' '.$last_id[0].' > '.$last_id[1];
		}else{
			$condicion = '';
		}

		$tbl     = $this->tbl;
		$columns = implode(',', $columns);
		$query   = "SELECT $columns FROM $tbl[$table] WHERE  $condicion LIMIT $limit";
		$query   = $this->db->query($query);

		if($query->num_rows >= 1){
			return $query->result_array();
		}else{
			return false;
		}
	}

	public function get_data_updates($table , $columns, $filter_id = array() ,$filter_timestamp =  array(),  $limit = 100)
	{
		if(!empty($filter_timestamp)){
			$filtro_1 = ' '.$filter_timestamp[0].' > "'.$filter_timestamp[1].'"';
		}else{
			$filtro_1 = '';
		}
		if($filter_id){
			$filtro_2 = 'AND '.$filter_id[0].' > "'.$filter_id[1].'"';
		}else{
			$filtro_2 = '';
		}
		$tbl     = $this->tbl;
		$columns = implode(',', $columns);
		$query   = "SELECT $columns FROM $tbl[$table] WHERE  $filtro_1 $filtro_2  LIMIT $limit";
		$query   = $this->db->query($query);

		if($query->num_rows >= 1){
			return $query->result_array();
		}else{
			return false;
		}
	}

	public function get_maxId($table, $primary_key, $condition = array() ){
		$tbl       = $this->tbl;
		$condition = ( !empty($condition) ) ? 'WHERE '.implode(' ', $condition) : '';
		$query     = "SELECT IF(max($primary_key) is NULL , 0, max($primary_key) ) as last_id FROM $tbl[$table] $condition";
		$query     = $this->db->query($query);
		$row       = $query->row();
		return $row->last_id;
	}

	public function insert_packages($table, $data){
		$tbl = $this->tbl;
		$this->db->insert_batch($tbl[$table], $data);
	}

	public function get_info_pv($id_pv){
		$tbl = $this->tbl;

		$query = "SELECT id_almacen_gavetas as id_gaveta, id_almacen FROM $tbl[sucursales_punto_venta] WHERE id_sucursales_punto_venta = $id_pv";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}else{
			return false;
		}
	}
	public function get_stock($id_almacen, $id_gaveta, $group = false, $limit = ''){
		if($group){
			$GroupBy = ' Group By id_compras_articulo_presentacion ';
			$stock   = " SUM(ae.stock) " ;
		}else{
			$GroupBy = "";
			$stock   = " ae.stock ";
		}
		$tbl     = $this->tbl;
		$query   = "SELECT 
						 ap.id_compras_articulo_presentacion
						,ap.upc
						,ap.sku
						,UPPER(CONCAT_WS(' ',ca.articulo,cp.presentacion, ap.peso_unitario, cu.clave_corta)) as articulos
						,cast($stock AS UNSIGNED ) as stock_almacen
						,ap.id_marca
						,ca.id_compras_linea as id_linea
						, 0 as descuento
						,ap.precio_publico
						,ap.precio_publico_con_impuesto as precio_publico_grabado
						,ai.valor as impuesto_porcentaje
						,ai.impuesto as impuesto_descripcion
						,ap.impuesto_aplica
						, 1 as activo
						,ae.caducidad
					FROM
						$tbl[almacen_stock]  ae
					LEFT JOIN 
						$tbl[compras_articulos_presentaciones] ap ON ap.id_compras_articulo_presentacion = ae.id_compras_articulo_presentacion
					LEFT JOIN
						$tbl[compras_presentaciones] cp ON cp.id_compras_presentacion = ap.id_presentacion
					LEFT JOIN 
						$tbl[compras_articulos] ca ON ca.id_compras_articulo = ap.id_articulo
					LEFT JOIN
						$tbl[compras_um]  cu ON cu.id_compras_um = ca.id_compras_um
					LEFT JOIN 
						$tbl[administracion_impuestos] ai ON ai.id_administracion_impuestos = ap.id_impuesto 
					WHERE
						ae.id_almacen = $id_almacen AND ae.id_gaveta = $id_gaveta 
					$GroupBy 
					ORDER BY id_compras_articulo_presentacion, caducidad";

		$query  = $this->db->query($query);					
		if($query->num_rows >= 1){
			return $query->result_array();
		}else{
			return false;
		}
	}

	public function get_stock_filter($id){

		$tbl = $this->tbl;

		$query = "	SELECT 
						s.*,
						MAX(s.caducidad) as caducidad
					FROM 
						$tbl[almacen_stock]  s
					WHERE s.id_compras_articulo_presentacion = $id
					GROUP BY s.id_compras_articulo_presentacion ";
		$query     = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}else{
			return false;
		}
	}
}