<?php
class aprobar_ordenes_model extends Base_Model{
	public function db_get_data($data=array()){	
		// DB Info
		$tbl = $this->tbl;
		$filtro_sucursal = $this->privileges_sucursal('e');
		$filtro = (isset($data['buscar']))?$data['buscar']:false;
		$limit 			= (isset($data['limit']))?$data['limit']:0;
		$offset 		= (isset($data['offset']))?$data['offset']:0;
		$aplicar_limit 	= (isset($data['aplicar_limit']))?true:false;

		$filtro = ($filtro!="") ? "and (a.orden_num LIKE '%$filtro%' 
							   or b.razon_social LIKE '%$filtro%'
							   or a.descripcion LIKE '%$filtro%'
							   or c.estatus LIKE '%$filtro%',
							   or e.sucursal LIKE '%$filtro%'
							   )" 
							: "";
		$limit 			= ($aplicar_limit) ? "LIMIT $offset ,$limit" : "";
		// Query
		$query="SELECT 
					a.id_compras_orden 
					,a.orden_num
					,a.orden_fecha
					,a.descripcion
					,a.entrega_direccion
					,a.entrega_fecha
					,a.observaciones
					,a.prefactura_num
					,a.timestamp
					,b.razon_social
					,c.estatus
					,d.orden_tipo
					,e.sucursal
					,f.forma_pago
					,g.credito
				from $tbl[compras_ordenes] a 
				LEFT JOIN $tbl[compras_proveedores] b on a.id_proveedor=b.id_compras_proveedor
				LEFT JOIN $tbl[compras_ordenes_estatus] c on a.estatus=c.id_estatus
				LEFT JOIN $tbl[compras_ordenes_tipo] d on a.id_orden_tipo=d.id_orden_tipo
				LEFT JOIN $tbl[sucursales] e on a.id_sucursal=e.id_sucursal
				LEFT JOIN $tbl[administracion_forma_pago] f on a.id_forma_pago=f.id_forma_pago
				LEFT JOIN $tbl[administracion_creditos] g on a.id_credito=g.id_administracion_creditos
				WHERE a.activo=1 $filtro_sucursal AND a.estatus IN(7,4) AND 1  $filtro
				ORDER BY orden_num ASC
				$limit";
				//echo $query;
      	// Execute querie

      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}
}
?>