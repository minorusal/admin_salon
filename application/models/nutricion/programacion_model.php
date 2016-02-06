<?php
class programacion_model extends Base_Model{
	public function get_params_ciclos($id_sucursal){
		$tbl = $this->tbl;
		$query = "SELECT 
					 np.id_nutricion_programacion
					,date_format(np.fecha_inicio , '%d/%m/%Y') as fecha_inicio
					,date_format(np.fecha_termino , '%d/%m/%Y') as fecha_termino
					,np.id_sucursal
					,np.id_usuario
				  FROM $tbl[nutricion_programacion] np WHERE np.id_sucursal = $id_sucursal";
				//print_debug($query);
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}
	public function get_dias_festivos($id_sucursal){
		$tbl = $this->tbl;
		$query = "SELECT 
					date_format(f.fecha, '%d/%m/%Y') as fecha
				  FROM $tbl[nutricion_programacion_dias_festivos] f
				  WHERE id_sucursal = $id_sucursal
				  ORDER BY fecha";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}else{
			return null;
		}
	}
	public function get_dias_especiales($id_sucursal){
		$tbl = $this->tbl;
		$query = "	SELECT 
						s.id_nutricion_ciclos,
						date_format(s.fecha, '%d/%m/%Y') as fecha, 
						c.clave_corta, 
						c.ciclo 
					FROM $tbl[nutricion_programacion_dias_especiales] s
					LEFT JOIN $tbl[nutricion_ciclos] c ON c.id_nutricion_ciclos = s.id_nutricion_ciclos
					WHERE s.id_sucursal = $id_sucursal
					ORDER BY fecha";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}else{
			return null;
		}
	}
	public function get_dias_especiales_contenido_ciclo($id_sucursal){
		$tbl = $this->tbl;
		$query = "	SELECT 			 
						cl.ciclo
						,ncr.id_nutricion_ciclo_receta
						,ncr.porciones
						,s.servicio
						,tm.tiempo
						,fm.familia
						,nr.receta
						,concat_ws('-', s.inicio, s.final) as horario
						,e.*
					FROM 
						$tbl[nutricion_programacion_dias_especiales] e
					LEFT JOIN $tbl[nutricion_ciclos] cl  on cl.id_nutricion_ciclos = e.id_nutricion_ciclos
					LEFT JOIN $tbl[nutricion_ciclo_receta] ncr on cl.id_nutricion_ciclos = ncr.id_ciclo
					LEFT JOIN $tbl[nutricion_recetas] nr on nr.id_nutricion_receta = ncr.id_receta
					LEFT JOIN $tbl[nutricion_tiempos] tm on tm.id_nutricion_tiempo = ncr.id_tiempo
					LEFT JOIN $tbl[nutricion_familias] fm on fm.id_nutricion_familia = ncr.id_familia
					LEFT JOIN $tbl[administracion_servicios] s on s.id_administracion_servicio = ncr.id_servicio
					WHERE e.id_sucursal= $id_sucursal
					ORDER BY e.fecha";
		//print_debug($query);
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}else{
			return null;
		}
	}
	public function get_dias_descartados($id_sucursal){
		$tbl   = $this->tbl;
		$query = "SELECT * FROM $tbl[nutricion_programacion_dias_descartados] WHERE id_sucursal = $id_sucursal";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}
	public function get_ciclos($id_sucursal, $id_ciclo = false){
		$filtro = ($id_ciclo) ? 'AND nc.id_nutricion_ciclos = '.$id_ciclo : '';
		$tbl   = $this->tbl;
		$query = "  SELECT * 
					FROM 
						$tbl[nutricion_ciclos] nc
					WHERE nc.id_nutricion_ciclos IN (
							SELECT 
								ncr.id_ciclo
							FROM
								$tbl[nutricion_ciclo_receta] ncr
						) AND nc.activo = 1 AND nc.id_sucursal = $id_sucursal $filtro";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}else{
			return false;
		}
	}
	public function get_ciclos_programados($id_sucursal){
		$tbl   = $this->tbl;
		$query = "	SELECT   
						pc.id_nutricion_programacion_ciclo
						,pc.id_nutricion_ciclos
						,pc.orden
						,nc.ciclo
					FROM 
						$tbl[nutricion_programacion_ciclos] pc
					LEFT JOIN $tbl[nutricion_ciclos] nc ON pc.id_nutricion_ciclos = nc.id_nutricion_ciclos
			 		WHERE pc.id_sucursal = $id_sucursal  
					ORDER BY pc.orden;";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}
	public function get_contenido_ciclo($id_ciclo){
		$tbl = $this->tbl;
		$query = "	SELECT 
						 cl.id_nutricion_ciclos
						,cl.ciclo
						,ncr.id_familia
						,ncr.id_nutricion_ciclo_receta
						,ncr.id_receta
						,ncr.id_servicio
						,ncr.id_tiempo
						,ncr.porciones
						,fm.familia
						,nr.id_nutricion_receta
						,nr.receta
						,s.servicio
						,concat_ws('-', s.inicio, s.final) as horario
						,tm.tiempo
					FROM 
						$tbl[nutricion_ciclo_receta] ncr
					LEFT JOIN $tbl[nutricion_ciclos] cl on cl.id_nutricion_ciclos = ncr.id_ciclo
					LEFT JOIN $tbl[nutricion_recetas] nr on nr.id_nutricion_receta = ncr.id_receta
					LEFT JOIN $tbl[nutricion_tiempos] tm on tm.id_nutricion_tiempo = ncr.id_tiempo
					LEFT JOIN $tbl[nutricion_familias] fm on fm.id_nutricion_familia = ncr.id_familia
					LEFT JOIN $tbl[administracion_servicios] s on s.id_administracion_servicio = ncr.id_servicio
					WHERE cl.id_nutricion_ciclos= $id_ciclo AND ncr.activo = 1
					ORDER BY ncr.id_servicio, ncr.id_tiempo ,ncr.id_familia";
		//print_debug($query);	
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}
	public function get_programacion_contenido_ciclo($id_sucursal){
		$tbl   = $this->tbl;
		$query = "	SELECT 
						npc.orden,
						c.*
					FROM 
						$tbl[nutricion_programacion_ciclos] npc
					LEFT JOIN (
						SELECT 
							 cl.id_nutricion_ciclos
							,cl.ciclo
							,ncr.id_familia
							,ncr.id_nutricion_ciclo_receta
							,ncr.id_receta
							,ncr.id_servicio
							,ncr.id_tiempo
							,ncr.porciones
							,fm.familia
							,nr.id_nutricion_receta
							,nr.receta
							,s.servicio
							,concat_ws('-', s.inicio, s.final) as horario
							,tm.tiempo
						FROM 
							$tbl[nutricion_ciclos] cl 
						LEFT JOIN $tbl[nutricion_ciclo_receta] ncr on cl.id_nutricion_ciclos = ncr.id_ciclo
						LEFT JOIN $tbl[nutricion_recetas] nr on nr.id_nutricion_receta = ncr.id_receta
						LEFT JOIN $tbl[nutricion_tiempos] tm on tm.id_nutricion_tiempo = ncr.id_tiempo
						LEFT JOIN $tbl[nutricion_familias] fm on fm.id_nutricion_familia = ncr.id_familia
						LEFT JOIN $tbl[administracion_servicios] s on s.id_administracion_servicio = ncr.id_servicio
						WHERE cl.id_sucursal= $id_sucursal 
						ORDER BY s.servicio, tm.tiempo ,fm.familia, nr.receta
					) c on npc.id_nutricion_ciclos = c.id_nutricion_ciclos
					WHERE npc.id_sucursal = $id_sucursal 
					ORDER BY npc.orden,c.servicio, c.tiempo ,c.familia, c.receta";
		//print_debug($query);	
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}
	public function get_programacion_contenido_ciclo_insumos($id_sucursal){
		$tbl   = $this->tbl;
		// $query = "		SELECT 
		// 					npc.orden,
		// 					c.*
		// 				FROM 
		// 					$tbl[nutricion_programacion_ciclos] npc
		// 				LEFT JOIN (
		// 							SELECT 
		// 								 cl.id_nutricion_ciclos
		// 								,cl.ciclo
		// 								,ncr.id_familia
		// 								,ncr.id_nutricion_ciclo_receta
		// 								,ncr.id_receta
		// 								,ncr.id_servicio
		// 								,ncr.id_tiempo
		// 								,ncr.porciones as porciones_recetas_ciclos
		// 								,fm.familia
		// 								,nr.*
		// 								,s.servicio
		// 								,concat_ws('-', s.inicio, s.final) as horario
		// 								,tm.tiempo
		// 							FROM 
		// 								$tbl[nutricion_ciclo_receta] ncr
		// 							LEFT JOIN $tbl[nutricion_ciclos] cl on cl.id_nutricion_ciclos = ncr.id_ciclo
		// 							LEFT JOIN 
		// 								(
		// 								SELECT 
		// 									r.id_nutricion_receta
		// 									,r.receta
		// 									,r.clave_corta as clave_receta
		// 									,r.porciones as porciones_receta_preparacion
		// 									,ri.id_compras_articulo
		// 									,ca.articulo
		// 									,ri.porciones as porciones_articulo
		// 									,ap.*
		// 									,cu.um
		// 									,li.linea
		// 									,s.sucursal
		// 								FROM $tbl[nutricion_recetas] r
		// 								LEFT JOIN  $tbl[nutricion_familias] f            ON f.id_nutricion_familia  = r.id_nutricion_familia
		// 								LEFT JOIN  $tbl[nutricion_recetas_articulos] ri  ON r.id_nutricion_receta   = ri.id_nutricion_receta
		// 								LEFT JOIN  $tbl[compras_articulos] ca            ON ca.id_compras_articulo  = ri.id_compras_articulo
		// 								LEFT JOIN  $tbl[compras_lineas] li               ON ca.id_compras_linea     = li.id_compras_linea
		// 								LEFT JOIN  $tbl[compras_um] cu                   ON cu.id_compras_um        = ca.id_compras_um
		// 								LEFT JOIN  $tbl[sucursales] s                    ON s.id_sucursal           = r.id_sucursal
		// 								LEFT JOIN  (
		// 											SELECT 
		// 												a.upc
		// 												,a.sku
		// 												,a.id_articulo
		// 												,a.presentacion_x_embalaje
		// 												,a.costo_sin_impuesto
		// 												,a.um_x_embalaje
		// 												,a.um_x_presentacion
		// 												,a.peso_unitario
		// 												,a.costo_unitario
		// 												,a.costo_x_um
		// 												,a.rendimiento
		// 												,a.id_administracion_region
		// 												,c.nombre_comercial as proveedor
		// 												,d.marca
		// 												,e.presentacion
		// 												,e.clave_corta as cl_presentacion
		// 												,f.embalaje
		// 												,f.clave_corta as cl_embalaje
		// 												,g.valor as impuesto
		// 												,h.clave_corta as cl_um
		// 												,i.clave_corta as cl_region
		// 											FROM (	SELECT 
		// 														ap.upc
		// 														,ap.sku
		// 														,ap.um_x_presentacion
		// 														,ap.peso_unitario
		// 														,ap.rendimiento
		// 														,ap.id_marca
		// 														,ap.id_presentacion
		// 														,app.*
		// 													FROM 
		// 														$tbl[compras_articulos_presentaciones] ap
		// 													LEFT JOIN
		// 														$tbl[compras_articulos_precios_proveedores] app on ap.id_compras_articulo_presentacion = app.id_compras_articulo_presentacion) a 
		// 											LEFT JOIN $tbl[compras_articulos] b        on a.id_articulo  	           = b.id_compras_articulo
		// 											LEFT JOIN $tbl[compras_proveedores] c      on a.id_proveedor 	           = c.id_compras_proveedor
		// 											LEFT JOIN $tbl[compras_marcas] d           on a.id_marca			       = d.id_compras_marca
		// 											LEFT JOIN $tbl[compras_presentaciones] e   on a.id_presentacion	           = e.id_compras_presentacion
		// 											LEFT JOIN $tbl[compras_embalaje] f         on a.id_embalaje    	           = f.id_compras_embalaje
		// 											LEFT JOIN $tbl[administracion_impuestos] g on a.id_impuesto    	           = g.id_administracion_impuestos
		// 											LEFT JOIN $tbl[compras_um] h               on b.id_compras_um    	       = h.id_compras_um
		// 											LEFT JOIN $tbl[administracion_regiones] i  on a.id_administracion_region   = i.id_administracion_region
		// 											WHERE a.activo = 1 AND a.articulo_default = 1
		// 										) ap ON (ap.id_articulo = ca.id_compras_articulo AND ap.id_administracion_region = s.id_region)
		// 								WHERE r.activo = 1 AND r.id_sucursal = $id_sucursal
		// 								)nr ON nr.id_nutricion_receta = ncr.id_receta
		// 							LEFT JOIN $tbl[nutricion_tiempos] tm       ON tm.id_nutricion_tiempo       = ncr.id_tiempo
		// 							LEFT JOIN $tbl[nutricion_familias] fm      ON fm.id_nutricion_familia      = ncr.id_familia
		// 							LEFT JOIN $tbl[administracion_servicios] s ON s.id_administracion_servicio = ncr.id_servicio
		// 							WHERE cl.id_sucursal = $id_sucursal AND ncr.activo = 1
		// 							ORDER BY cl.ciclo, ncr.id_servicio, ncr.id_tiempo ,ncr.id_familia
		// 				) c on npc.id_nutricion_ciclos = c.id_nutricion_ciclos
		// 				WHERE npc.id_sucursal = $id_sucursal
		// 				ORDER BY npc.orden,c.servicio, c.tiempo ,c.familia, c.receta";
		//print_debug($query);	

		$query = "		SELECT 
							s.sucursal
							,s.clave_corta as clave_sucursal
							,s.id_region
							,concat_ws('-', s.inicio, s.final) as horario
							,pc.orden
							,pc.id_nutricion_programacion_ciclo
							,nc.*
							,nra.id_compras_articulo
							,nra.porciones as porciones_articulo
							,na.*
							,nap.*
						FROM 
							$tbl[nutricion_programacion_ciclos] pc
						LEFT JOIN $tbl[sucursales] s ON pc.id_sucursal = s.id_sucursal
						LEFT JOIN (
													SELECT 
													ncr.id_nutricion_ciclo_receta
													,nc.id_nutricion_ciclos
													,nc.ciclo
													,ncr.id_servicio
													,ncr.id_tiempo
													,ase.servicio
													,nf.id_nutricion_familia as id_familia
													,nf.familia
													,nt.tiempo
													,nr.id_nutricion_receta
													,nr.id_nutricion_receta as id_receta
													,nr.receta
													,nr.clave_corta as clave_receta
													,nr.porciones as porciones_receta_preparacion
													,ncr.porciones as porciones_recetas_ciclos
												FROM 
													$tbl[nutricion_ciclos] nc
												LEFT JOIN $tbl[nutricion_ciclo_receta] ncr ON ncr.id_ciclo = nc.id_nutricion_ciclos
												LEFT JOIN $tbl[administracion_servicios] ase ON ase.id_administracion_servicio = ncr.id_servicio
												LEFT JOIN $tbl[nutricion_tiempos] nt ON nt.id_nutricion_tiempo = ncr.id_tiempo
												LEFT JOIN $tbl[nutricion_familias] nf ON nf.id_nutricion_familia = ncr.id_familia
												LEFT JOIN $tbl[nutricion_recetas] nr ON nr.id_nutricion_receta = ncr.id_receta
										) nc ON nc.id_nutricion_ciclos = pc.id_nutricion_ciclos
						LEFT JOIN $tbl[nutricion_recetas_articulos] nra ON nra.id_nutricion_receta = nc.id_nutricion_receta
						LEFT JOIN (
								SELECT 
									 ca.id_compras_articulo as id_articulo 
									,ca.articulo
									,cl.linea
									,cu.um
									,cu.clave_corta as cl_um
								FROM
									$tbl[compras_articulos] ca
								LEFT JOIN 
									$tbl[compras_lineas] cl ON cl.id_compras_linea = ca.id_compras_linea
								LEFT JOIN
									$tbl[compras_um] cu ON cu.id_compras_um = ca.id_compras_um
						) na ON na.id_articulo = nra.id_compras_articulo
						LEFT JOIN(
							SELECT 
								a.upc
								,a.sku
								,a.id_articulo  as id_articulo_secundario
								,a.presentacion_x_embalaje
								,a.costo_sin_impuesto
								,a.um_x_embalaje
								,a.um_x_presentacion
								,a.peso_unitario
								,a.costo_unitario
								,a.costo_x_um
								,a.rendimiento
								,a.id_administracion_region
								,c.nombre_comercial as proveedor
								,d.marca
								,e.presentacion
								,e.clave_corta as cl_presentacion
								,f.embalaje
								,f.clave_corta as cl_embalaje
								,g.valor as impuesto
								,i.clave_corta as cl_region
							FROM (	SELECT 
										ap.upc
										,ap.sku
										,ap.um_x_presentacion
										,ap.peso_unitario
										,ap.rendimiento
										,ap.id_marca
										,ap.id_presentacion
										,app.*
									FROM 
										$tbl[compras_articulos_presentaciones] ap
									LEFT JOIN
										$tbl[compras_articulos_precios_proveedores] app on ap.id_compras_articulo_presentacion = app.id_compras_articulo_presentacion) a 

							LEFT JOIN $tbl[compras_proveedores] c      on a.id_proveedor 	             = c.id_compras_proveedor
							LEFT JOIN $tbl[compras_marcas] d           on a.id_marca			             = d.id_compras_marca
							LEFT JOIN $tbl[compras_presentaciones] e   on a.id_presentacion	           = e.id_compras_presentacion
							LEFT JOIN $tbl[compras_embalaje] f         on a.id_embalaje    	           = f.id_compras_embalaje
							LEFT JOIN $tbl[administracion_impuestos] g on a.id_impuesto    	           = g.id_administracion_impuestos
							LEFT JOIN $tbl[administracion_regiones] i  on a.id_administracion_region   = i.id_administracion_region
							WHERE a.activo = 1 AND a.articulo_default = 1
						) nap ON (nap.id_articulo_secundario = nra.id_compras_articulo AND nap.id_administracion_region = s.id_region )
						WHERE s.id_sucursal = $id_sucursal
						ORDER BY pc.orden,nc.servicio, nc.tiempo ,nc.familia, nc.receta";
		//print_debug($query);
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}
	public function get_dias_especiales_contenido_ciclo_insumos($id_sucursal){
		$tbl = $this->tbl;
		$query = "	SELECT 
							s.sucursal
							,s.clave_corta as clave_sucursal
							,s.id_region
							,concat_ws('-', s.inicio, s.final) as horario
							,pc.fecha 
							
							,pc.id_nutricion_programacion_dias_especiales
							,nc.*
							,nra.id_compras_articulo
							,nra.porciones as porciones_articulo
							,na.*
							,nap.*
						FROM 
							$tbl[nutricion_programacion_dias_especiales] pc
						LEFT JOIN $tbl[sucursales] s ON pc.id_sucursal = s.id_sucursal
						LEFT JOIN 
						(
							SELECT 
								ncr.id_nutricion_ciclo_receta
								,nc.id_nutricion_ciclos
								,nc.ciclo
								,ncr.id_servicio
								,ncr.id_tiempo
								,ase.servicio
								,nf.id_nutricion_familia as id_familia
								,nf.familia
								,nt.tiempo
								,nr.id_nutricion_receta
								,nr.id_nutricion_receta as id_receta
								,nr.receta
								,nr.clave_corta as clave_receta
								,nr.porciones as porciones_receta_preparacion
								,ncr.porciones as porciones_recetas_ciclos
							FROM 
								$tbl[nutricion_ciclos] nc
							LEFT JOIN $tbl[nutricion_ciclo_receta] ncr ON ncr.id_ciclo = nc.id_nutricion_ciclos
							LEFT JOIN $tbl[administracion_servicios] ase ON ase.id_administracion_servicio = ncr.id_servicio
							LEFT JOIN $tbl[nutricion_tiempos] nt ON nt.id_nutricion_tiempo = ncr.id_tiempo
							LEFT JOIN $tbl[nutricion_familias] nf ON nf.id_nutricion_familia = ncr.id_familia
							LEFT JOIN $tbl[nutricion_recetas] nr ON nr.id_nutricion_receta = ncr.id_receta
						) nc ON nc.id_nutricion_ciclos = pc.id_nutricion_ciclos
						LEFT JOIN $tbl[nutricion_recetas_articulos] nra ON nra.id_nutricion_receta = nc.id_nutricion_receta
						LEFT JOIN 
						(
							SELECT 
								 ca.id_compras_articulo as id_articulo 
								,ca.articulo
								,cl.linea
								,cu.um
								,cu.clave_corta as cl_um
							FROM
								$tbl[compras_articulos] ca
							LEFT JOIN 
								$tbl[compras_lineas] cl ON cl.id_compras_linea = ca.id_compras_linea
							LEFT JOIN
								$tbl[compras_um] cu ON cu.id_compras_um = ca.id_compras_um
						) na ON na.id_articulo = nra.id_compras_articulo
						LEFT JOIN(
							SELECT 
								a.upc
								,a.sku
								,a.id_articulo  as id_articulo_secundario
								,a.presentacion_x_embalaje
								,a.costo_sin_impuesto
								,a.um_x_embalaje
								,a.um_x_presentacion
								,a.peso_unitario
								,a.costo_unitario
								,a.costo_x_um
								,a.rendimiento
								,a.id_administracion_region
								,c.nombre_comercial as proveedor
								,d.marca
								,e.presentacion
								,e.clave_corta as cl_presentacion
								,f.embalaje
								,f.clave_corta as cl_embalaje
								,g.valor as impuesto
								,i.clave_corta as cl_region
							FROM (	SELECT 
										ap.upc
										,ap.sku
										,ap.um_x_presentacion
										,ap.peso_unitario
										,ap.rendimiento
										,ap.id_marca
										,ap.id_presentacion
										,app.*
									FROM 
										$tbl[compras_articulos_presentaciones] ap
									LEFT JOIN
										$tbl[compras_articulos_precios_proveedores] app on ap.id_compras_articulo_presentacion = app.id_compras_articulo_presentacion) a 
							LEFT JOIN $tbl[compras_proveedores] c      on a.id_proveedor 	             = c.id_compras_proveedor
							LEFT JOIN $tbl[compras_marcas] d           on a.id_marca			             = d.id_compras_marca
							LEFT JOIN $tbl[compras_presentaciones] e   on a.id_presentacion	           = e.id_compras_presentacion
							LEFT JOIN $tbl[compras_embalaje] f         on a.id_embalaje    	           = f.id_compras_embalaje
							LEFT JOIN $tbl[administracion_impuestos] g on a.id_impuesto    	           = g.id_administracion_impuestos
							LEFT JOIN $tbl[administracion_regiones] i  on a.id_administracion_region   = i.id_administracion_region
							WHERE a.activo = 1 AND a.articulo_default = 1
						) nap ON (nap.id_articulo_secundario = nra.id_compras_articulo AND nap.id_administracion_region = s.id_region )
						WHERE s.id_sucursal = $id_sucursal
						ORDER BY nc.servicio, nc.tiempo ,nc.familia, nc.receta
					";
		//print_debug($query);
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}else{
			return null;
		}
	}
	public function delete_paramas_programacion($id_sucursal){
		$tbl = $this->tbl;
		$this->db->delete($tbl['nutricion_programacion'], array('id_sucursal' => $id_sucursal)); 
		$this->db->delete($tbl['nutricion_programacion_ciclos'], array('id_sucursal' => $id_sucursal)); 
		$this->db->delete($tbl['nutricion_programacion_dias_festivos'], array('id_sucursal' => $id_sucursal));
		$this->db->delete($tbl['nutricion_programacion_dias_especiales'], array('id_sucursal' => $id_sucursal)); 
		$this->db->delete($tbl['nutricion_programacion_dias_descartados'], array('id_sucursal' => $id_sucursal)); 
	}
	public function insert_params_programacion($data){
		$tbl    = $this->tbl;
		$insert = $this->insert_item($tbl['nutricion_programacion'], $data);
		return $insert;
	}
	public function insert_dias_festivos($data){
		$tbl    = $this->tbl;
		$insert = $this->db->insert_batch($tbl['nutricion_programacion_dias_festivos'], $data);
		return $insert;
	}
	public function insert_dias_especiales($data){
		$tbl    = $this->tbl;
		$insert = $this->db->insert_batch($tbl['nutricion_programacion_dias_especiales'], $data);
		return $insert;
	}
	public function insert_dias_descartados($data){
		$tbl    = $this->tbl;
		$insert = $this->db->insert_batch($tbl['nutricion_programacion_dias_descartados'], $data);
		return $insert;
	}
	public function insert_programacion_ciclos($data){
		$tbl = $this->tbl;
		$insert = $this->db->insert_batch($tbl['nutricion_programacion_ciclos'], $data);
		return $insert;
	}
	public function update_cantidad_ciclo_receta($data){
		$tbl       = $this->tbl;
		$condicion = "id_nutricion_ciclo_receta = ".$data['id_nutricion_ciclo_receta']; 
		$update    = $this->update_item($tbl['nutricion_ciclo_receta'], $data, 'id_nutricion_ciclo_receta', $condicion);

		return $update;
	}

	public function insert_ciclo_hoy($data = array()){
		// print_debug($data);
		$tbl    = $this->tbl;
		$insert = $this->db->insert_batch($tbl['nutricion_programacion_historial'], $data);
		return $insert;
	}

	public function db_get_sucursales(){
		$tbl    = $this->tbl;
		$query  = "SELECT id_sucursal FROM $tbl[sucursales] WHERE activo = 1";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	public function get_programacion_contenido_ciclos_insumos($sucursales){
		$tbl    = $this->tbl;
		$query = "		SELECT 
							s.sucursal
							,s.clave_corta as clave_sucursal
							,s.id_region
							,concat_ws('-', s.inicio, s.final) as horario
							,pc.orden
							,pc.id_nutricion_programacion_ciclo
							,nc.*
							,nra.id_compras_articulo
							,nra.porciones as porciones_articulo
							,na.*
							,nap.*
						FROM 
							$tbl[nutricion_programacion_ciclos] pc
						LEFT JOIN $tbl[sucursales] s ON pc.id_sucursal = s.id_sucursal
						LEFT JOIN (
													SELECT 
													ncr.id_nutricion_ciclo_receta
													,nc.id_nutricion_ciclos
													,nc.ciclo
													,ncr.id_servicio
													,ncr.id_tiempo
													,ase.servicio
													,nf.id_nutricion_familia as id_familia
													,nf.familia
													,nt.tiempo
													,nr.id_nutricion_receta
													,nr.id_nutricion_receta as id_receta
													,nr.receta
													,nr.clave_corta as clave_receta
													,nr.porciones as porciones_receta_preparacion
													,ncr.porciones as porciones_recetas_ciclos
												FROM 
													$tbl[nutricion_ciclos] nc
												LEFT JOIN $tbl[nutricion_ciclo_receta] ncr ON ncr.id_ciclo = nc.id_nutricion_ciclos
												LEFT JOIN $tbl[administracion_servicios] ase ON ase.id_administracion_servicio = ncr.id_servicio
												LEFT JOIN $tbl[nutricion_tiempos] nt ON nt.id_nutricion_tiempo = ncr.id_tiempo
												LEFT JOIN $tbl[nutricion_familias] nf ON nf.id_nutricion_familia = ncr.id_familia
												LEFT JOIN $tbl[nutricion_recetas] nr ON nr.id_nutricion_receta = ncr.id_receta
										) nc ON nc.id_nutricion_ciclos = pc.id_nutricion_ciclos
						LEFT JOIN $tbl[nutricion_recetas_articulos] nra ON nra.id_nutricion_receta = nc.id_nutricion_receta
						LEFT JOIN (
								SELECT 
									 ca.id_compras_articulo as id_articulo 
									,ca.articulo
									,cl.linea
									,cu.um
									,cu.clave_corta as cl_um
								FROM
									$tbl[compras_articulos] ca
								LEFT JOIN 
									$tbl[compras_lineas] cl ON cl.id_compras_linea = ca.id_compras_linea
								LEFT JOIN
									$tbl[compras_um] cu ON cu.id_compras_um = ca.id_compras_um
						) na ON na.id_articulo = nra.id_compras_articulo
						LEFT JOIN(
							SELECT 
								a.upc
								,a.sku
								,a.id_articulo  as id_articulo_secundario
								,a.presentacion_x_embalaje
								,a.costo_sin_impuesto
								,a.um_x_embalaje
								,a.um_x_presentacion
								,a.peso_unitario
								,a.costo_unitario
								,a.costo_x_um
								,a.rendimiento
								,a.id_administracion_region
								,c.nombre_comercial as proveedor
								,d.marca
								,e.presentacion
								,e.clave_corta as cl_presentacion
								,f.embalaje
								,f.clave_corta as cl_embalaje
								,g.valor as impuesto
								,i.clave_corta as cl_region
							FROM (	SELECT 
										ap.upc
										,ap.sku
										,ap.um_x_presentacion
										,ap.peso_unitario
										,ap.rendimiento
										,ap.id_marca
										,ap.id_presentacion
										,app.*
									FROM 
										$tbl[compras_articulos_presentaciones] ap
									LEFT JOIN
										$tbl[compras_articulos_precios_proveedores] app on ap.id_compras_articulo_presentacion = app.id_compras_articulo_presentacion) a 

							LEFT JOIN $tbl[compras_proveedores] c      on a.id_proveedor 	             = c.id_compras_proveedor
							LEFT JOIN $tbl[compras_marcas] d           on a.id_marca			             = d.id_compras_marca
							LEFT JOIN $tbl[compras_presentaciones] e   on a.id_presentacion	           = e.id_compras_presentacion
							LEFT JOIN $tbl[compras_embalaje] f         on a.id_embalaje    	           = f.id_compras_embalaje
							LEFT JOIN $tbl[administracion_impuestos] g on a.id_impuesto    	           = g.id_administracion_impuestos
							LEFT JOIN $tbl[administracion_regiones] i  on a.id_administracion_region   = i.id_administracion_region
							WHERE a.activo = 1 AND a.articulo_default = 1
						) nap ON (nap.id_articulo_secundario = nra.id_compras_articulo AND nap.id_administracion_region = s.id_region )
						WHERE s.id_sucursal IN ($sucursales)
						ORDER BY pc.orden,nc.servicio, nc.tiempo ,nc.familia, nc.receta";
		// print_debug($query);
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}else{
			return null;
		}

	}

	public function get_programacion_contenido_ciclo_historial($id_sucursal,$fecha_form){
		// print_debug($fecha_form);
		$tbl   = $this->tbl;
		$query = "SELECT *
						,date_format(`timestamp`,'%Y/%m/%d') as fecha
		          FROM $tbl[nutricion_programacion_historial] 
		          WHERE id_sucursal = $id_sucursal
		          -- AND  date_format(`timestamp`,'%d/%m/%Y') < date('d/m/Y')
		          AND date_format(`timestamp`,'%d/%m/%Y %H:%i:%s') >= $fecha_form ";
		// print_debug($query);
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}else{
			return null;
		}
	}

	public function get_programacion_contenido_ciclo_historial_aux($id_sucursal,$fecha_form){
		// print_debug($fecha_form);
		$tbl   = $this->tbl;
		$query = "SELECT *
						,date_format(`timestamp`,'%Y/%m/%d') as fecha
		          FROM $tbl[nutricion_programacion_historial] 
		          WHERE id_sucursal = $id_sucursal
		          AND date_format(`timestamp`,'%Y/%m/%d') <= '$fecha_form' ";
		// print_debug($query);
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}else{
			return null;
		}
	}

}
?>