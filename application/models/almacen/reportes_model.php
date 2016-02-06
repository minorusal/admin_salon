<?php
class reportes_model extends Base_Model{
	function db_get_almacen_x_region($id_region){
		$tbl = $this->tbl;
		//Query
		$query = "SELECT a.almacenes
						,a.id_almacen_almacenes
				  FROM $tbl[almacen_almacenes] a
				  LEFT JOIN $tbl[sucursales] s on s.id_sucursal = a.id_sucursal
				  LEFT JOIN $tbl[administracion_regiones] r on r.id_administracion_region = s.id_region
				  WHERE a.activo = 1 AND s.activo = 1 AND r.activo = 1 AND s.id_region = $id_region
				  ORDER BY a.almacenes";
				  
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	function db_get_stock_general($id_almacen){
		$tbl = $this->tbl;
		$query = "SELECT 
					a.id_stock AS id_stock
         	    	,a.id_almacen AS id_almacen
      			   	,b.clave_corta AS almacen_cve
      			   	,if((a.id_pasillo = 0)
			       		,NULL
        				,a.id_pasillo) AS id_pasillo
        				,c.clave_corta AS pasillo_cve
        				,a.id_gaveta AS id_gaveta
        				,d.clave_corta AS gaveta_cve
        				,n.id_articulo_tipo AS id_articulo_tipo
        				,n.articulo_tipo AS articulo_tipo
        				,h.id_compras_linea AS id_linea
        				,h.linea AS linea
        				,a.id_compras_orden_articulo AS id_compras_orden_articulo
        				,f.upc AS upc
        				,f.sku AS sku
        				,f.id_articulo AS id_articulo
        				,g.articulo AS articulo
        				,k.id_compras_marca AS id_marca
        				,k.marca AS marca
        				,l.id_compras_presentacion AS id_presentacion
        				,l.presentacion AS presentacion
        				,m.id_compras_embalaje AS id_embalaje
        				,m.embalaje AS embalaje
        				,f.presentacion_x_embalaje AS presentacion_x_embalaje
        				,j.id_compras_proveedor AS id_proveedor
        				,j.razon_social AS proveedor_razon_social
        				,format(a.stock,0) AS stock
        				,a.stock_um AS stock_um
        				,i.unidad_minima_cve AS stock_um_cve
        				,if((a.lote = '')
							,NULL
							,a.lote) AS lote
         					,if((a.caducidad = '0000-00-00')
								,NULL,a.caducidad) AS caducidad
         						,concat('$',format((a.stock * (e.costo_x_cantidad / e.cantidad)),2)) AS stock_valor
         						,e.cantidad AS cantidad_adquirida
         						,concat('$',format((e.costo_x_cantidad / e.cantidad),2)) AS costo_unitario
         						,concat('$',format((e.subtotal + (e.costo_x_cantidad - e.subtotal)),2)) AS subtotal
         						,if((e.descuento > 0),concat('$',format((e.costo_x_cantidad - e.subtotal),2))
									,NULL) AS descuento
         	 						,concat('$',format(e.valor_impuesto,2)) AS impuesto
         	 						,concat('$',format(e.total,2)) AS total 
         	 	from ((((((((((((($tbl[almacen_stock] a 
         	 	left join $tbl[almacen_almacenes] b on((a.id_almacen = b.id_almacen_almacenes)))
            	left join $tbl[almacen_pasillos] c on((a.id_pasillo = c.id_almacen_pasillos)))
				left join $tbl[almacen_gavetas] d on((a.id_gaveta = d.id_almacen_gavetas)))
				left join $tbl[compras_ordenes_articulos] e on((a.id_compras_orden_articulo = e.id_compras_orden_articulo)))
				left join $tbl[compras_articulos_precios] f on((e.id_compras_articulo_precios = f.id_compras_articulo_precios)))
				left join $tbl[compras_articulos] g on((f.id_articulo = g.id_compras_articulo))) left join $tbl[compras_lineas] h on((g.id_compras_linea = h.id_compras_linea)))
				left join $tbl[compras_um] i on((g.id_compras_um = i.id_compras_um)))
				left join $tbl[compras_proveedores] j on((f.id_proveedor = j.id_compras_proveedor)))
				left join $tbl[compras_marcas] k on((f.id_marca = k.id_compras_marca)))
				left join $tbl[compras_presentaciones] l on((f.id_presentacion = l.id_compras_presentacion)))
				left join $tbl[compras_embalaje] m on((f.id_embalaje = m.id_compras_embalaje)))
				left join $tbl[compras_articulos_tipo] n on((a.id_articulo_tipo = n.id_articulo_tipo)))
				where b.id_almacen_almacenes = $id_almacen
				order by a.id_almacen,a.id_pasillo,a.id_gaveta,a.id_articulo_tipo,h.id_compras_linea,f.id_articulo,f.id_marca,f.id_presentacion,f.id_embalaje";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	function db_get_stock_almacen($id_almacen){
		$tbl = $this->tbl;
		$query = "SELECT a.almacenes
					    ,a.clave_corta as clave_almacen
					    ,pa.pasillos
					    ,pa.clave_corta as clave_pasillo
					    ,ag.gavetas
					    ,ag.clave_corta as clave_gabeta
					    ,er.num_factura
					    ,er.fecha_factura
					    ,er.descuento
					    ,er.subtotal
					    ,er.impuesto
					    ,er.total
					    ,er.n_orden
					    ,er.tipo_orden
				FROM $tbl[almacen_stock] s
				LEFT JOIN $tbl[almacen_almacenes] a on a.id_almacen_almacenes = s.id_almacen
				LEFT JOIN $tbl[almacen_pasillos] pa on pa.id_almacen_pasillos = s.id_pasillo
				LEFT JOIN $tbl[almacen_gavetas] ag on ag.id_almacen_gavetas = s.id_gaveta
				LEFT JOIN (SELECT
								 er.id_almacen_entradas_recepcion
								,er.num_factura
								,er.fecha_factura
								,er.descuento
								,er.subtotal
								,er.impuesto
								,er.total
								,co.orden_num as n_orden
								,ot.orden_tipo as tipo_orden
							FROM $tbl[almacen_entradas_recibir] er
							LEFT JOIN $tbl[compras_ordenes] co on co.id_compras_orden = er.id_compras_orden
							LEFT JOIN $tbl[compras_ordenes_tipo] ot on ot.id_orden_tipo = co.id_orden_tipo) er on (er.id_almacen_entradas_recepcion = s.id_almacen_entradas_recepcion)
				LEFT JOIN (SELECT o.num_orden
					       FROM $tbl[compras_ordenes_articulos] oa
					       LEFT JOIN $tbl[compras_ordenes] o on o.id_compras_orden = oa.id_compras_orden) oa on 
				WHERE s.activo = 1 AND s.id_almacen = $id_almacen
				GROUP BY s.id_almacen, pa.pasillos, ag.gavetas, er.num_factura";
		print_debug($query);
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}
}