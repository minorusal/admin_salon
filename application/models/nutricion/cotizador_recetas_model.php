<?php
class cotizador_recetas_model extends Base_Model{

	public function __construct(){
		parent::__construct();
	}

	public function db_get_familia_by_sucursal($id_sucursal){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "	SELECT f.id_nutricion_familia
					      ,f.familia     
					FROM $tbl[nutricion_familias] f
					LEFT JOIN $tbl[nutricion_recetas] r on r.id_nutricion_familia = f.id_nutricion_familia
					LEFT JOIN $tbl[sucursales] s on s.id_sucursal = r.id_sucursal
					WHERE r.id_sucursal = $id_sucursal 
					";
		//print_debug($query);
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}

	public function db_get_family_selected($id_sucursal){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$query = "	SELECT fs.*    
					FROM $tbl[nutricion_familias_sucursal] fs
					WHERE fs.id_sucursal = $id_sucursal AND fs.activo = 1
					";
		//print_debug($query);
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}

	public function eliminar_registro_familia_sucursal($id_sucursal){
		$tbl = $this->tbl;
		$query = "DELETE FROM $tbl[nutricion_familias_sucursal]  WHERE id_sucursal = $id_sucursal";
		$query = $this->db->query($query);
		if($query){
			return $query;
		}else{
			return false;
		}
	}


	public function db_insert_cotizacion_familia($data = array()){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$insert = $this->insert_item($tbl['nutricion_familias_sucursal'], $data);
		if($insert){
			return $insert;
		}else{
			return false;
		}
	}

	public function db_get_recetas_from_familias($data = array()){
		$tbl = $this->tbl;
		$query = "SELECT f.familia
						       ,r.*
						       ,ra.id_compras_articulo
						       ,ra.porciones AS porciones_articulo
							   ,ca.articulo
						       ,ap.costo_x_um
						       ,cu.um
						       ,s.id_sucursal
						       ,s.sucursal
				FROM $tbl[nutricion_familias] f
				LEFT JOIN $tbl[nutricion_recetas] r on r.id_nutricion_familia = f.id_nutricion_familia
				LEFT JOIN $tbl[nutricion_recetas_articulos] ra on ra.id_nutricion_receta = r.id_nutricion_receta
				LEFT JOIN $tbl[compras_articulos] ca on ca.id_compras_articulo = ra.id_compras_articulo
				LEFT JOIN $tbl[compras_um] cu on cu.id_compras_um = ca.id_compras_um
				LEFT JOIN $tbl[sucursales] s on s.id_sucursal = r.id_sucursal
				LEFT JOIN (SELECT
				                ap.id_articulo
				               ,ap.costo_x_um
				               ,ap.id_administracion_region
				           FROM
								$tbl[compras_articulos_precios_proveedores] ap
				           WHERE
								ap.articulo_default = 1) ap
				ON ap.id_articulo = ca.id_compras_articulo
				WHERE f.id_nutricion_familia IN ($data[lista_familias])
				AND r.id_sucursal = $data[id_sucursal]";
				//print_debug($query);
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	public function db_get_grupo_by_sucursal($id_sucursal){
		$tbl = $this->tbl;
		$query = "SELECT *
		          FROM $tbl[nutricion_grupos] 
		          WHERE id_sucursales = $id_sucursal
		          AND activo = 1";
				//print_debug($query);
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	public function db_get_receta_by_sucursal($id_sucursal){
		$tbl = $this->tbl;
		$query = "SELECT *
		          FROM $tbl[nutricion_recetas] 
		          WHERE id_sucursal = $id_sucursal
		          AND activo = 1";
				//print_debug($query);
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}


	public function db_get_receta_selected($id_grupo){
		$tbl = $this->tbl;
		$query = "SELECT *
		          FROM $tbl[nutricion_grupo_receta] 
		          WHERE id_grupo = $id_grupo
		          AND activo = 1";
				//print_debug($query);
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	public function eliminar_registro_grupo_receta($id_grupo){
		$tbl = $this->tbl;
		$query = "DELETE FROM $tbl[nutricion_grupo_receta]  WHERE id_grupo = $id_grupo";
		$query = $this->db->query($query);
		if($query){
			return $query;
		}else{
			return false;
		}
	}

	public function db_insert_cotizacion_receta($data = array()){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$insert = $this->insert_item($tbl['nutricion_grupo_receta'], $data);
		if($insert){
			return $insert;
		}else{
			return false;
		}
	}

	public function db_get_recetas_from_grupos($data = array()){
		$tbl = $this->tbl;
		$query = "SELECT r.*
						       ,ra.id_compras_articulo
						       ,ra.porciones AS porciones_articulo
							   ,ca.articulo
						       ,ap.costo_x_um
						       ,cu.um
						       ,s.id_sucursal
						       ,s.sucursal
						       ,g.grupo
				FROM $tbl[nutricion_recetas] r
				LEFT JOIN $tbl[nutricion_grupo_receta] gr ON gr.id_receta = r.id_nutricion_receta
				LEFT JOIN $tbl[nutricion_grupos] g on g.id_nutricion_grupos = gr.id_grupo
				LEFT JOIN $tbl[nutricion_recetas_articulos] ra on ra.id_nutricion_receta = r.id_nutricion_receta
				LEFT JOIN $tbl[compras_articulos] ca on ca.id_compras_articulo = ra.id_compras_articulo
				LEFT JOIN $tbl[compras_um] cu on cu.id_compras_um = ca.id_compras_um
				LEFT JOIN $tbl[sucursales] s on s.id_sucursal = r.id_sucursal
				LEFT JOIN (SELECT
				                ap.id_articulo
				               ,ap.costo_x_um
				               ,ap.id_administracion_region
				           FROM
								$tbl[compras_articulos_precios_proveedores] ap
				           WHERE
								ap.articulo_default = 1) ap
				ON ap.id_articulo = ca.id_compras_articulo
				WHERE r.id_nutricion_receta IN ($data[lista_recetas])
				AND gr.id_grupo = $data[id_grupo]";
				//print_debug($query);
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	public function db_get_insumos_by_sucursal($id_sucursal){
		$tbl = $this->tbl;
		$query = "SELECT r.*
						       ,ra.id_compras_articulo
						       ,ra.porciones AS porciones_articulo
							   ,ca.articulo
							   ,CONCAT_WS(' ',ra.porciones,cu.um,ca.articulo) as articulo_nombre
						       ,ap.costo_x_um
						       ,cu.um
						       ,s.id_sucursal
						       ,s.sucursal
						       ,ap.id_articulo
				FROM $tbl[nutricion_recetas] r
				LEFT JOIN $tbl[nutricion_recetas_articulos] ra on ra.id_nutricion_receta = r.id_nutricion_receta
				LEFT JOIN $tbl[compras_articulos] ca on ca.id_compras_articulo = ra.id_compras_articulo
				LEFT JOIN $tbl[compras_um] cu on cu.id_compras_um = ca.id_compras_um
				LEFT JOIN $tbl[sucursales] s on s.id_sucursal = r.id_sucursal
				LEFT JOIN (SELECT
				                ap.id_articulo
				               ,ap.costo_x_um
				               ,ap.id_administracion_region
				           FROM
								$tbl[compras_articulos_precios_proveedores] ap
				           WHERE
								ap.articulo_default = 1) ap
				ON ap.id_articulo = ca.id_compras_articulo
				WHERE r.id_sucursal = $id_sucursal AND ap.id_articulo is not NULL
				GROUP BY ca.articulo";
				//print_debug($query);
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	public function eliminar_registro_insumo_sucursal($id_sucursal){
		$tbl = $this->tbl;
		$query = "DELETE FROM $tbl[nutricion_sucursal_insumo]  WHERE id_sucursal = $id_sucursal";
		$query = $this->db->query($query);
		if($query){
			return $query;
		}else{
			return false;
		}
	}

	public function db_insert_cotizacion_insumo($data = array()){
		// DB Info
		$tbl = $this->tbl;
		// Query
		$insert = $this->insert_item($tbl['nutricion_sucursal_insumo'], $data);
		if($insert){
			return $insert;
		}else{
			return false;
		}
	}

	public function db_get_insumos_from_sucursales($data = array()){
		$tbl = $this->tbl;
		$query = "SELECT r.*
						       ,ra.id_compras_articulo
						       ,ra.porciones AS porciones_articulo
							   ,ca.articulo
							   ,CONCAT_WS(' ',ra.porciones,cu.um,ca.articulo) as articulo_nombre
						       ,ap.costo_x_um
						       ,cu.um
						       ,s.id_sucursal
						       ,s.sucursal
				FROM $tbl[nutricion_recetas] r
				LEFT JOIN $tbl[nutricion_recetas_articulos] ra on ra.id_nutricion_receta = r.id_nutricion_receta
				LEFT JOIN $tbl[compras_articulos] ca on ca.id_compras_articulo = ra.id_compras_articulo
				LEFT JOIN $tbl[compras_um] cu on cu.id_compras_um = ca.id_compras_um
				LEFT JOIN $tbl[sucursales] s on s.id_sucursal = r.id_sucursal
				LEFT JOIN (SELECT
				                ap.id_articulo
				               ,ap.costo_x_um
				               ,ap.id_administracion_region
				           FROM
								$tbl[compras_articulos_precios_proveedores] ap
				           WHERE
								ap.articulo_default = 1) ap
				ON ap.id_articulo = ca.id_compras_articulo
				WHERE ap.id_articulo IN ($data[lts_insumos])
				AND r.id_sucursal = $data[lts_sucursales]
				GROUP BY ca.id_compras_articulo";
				//print_debug($query);
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}

	public function db_get_insumo_selected($id_sucursal){
		$tbl = $this->tbl;
		$query = "SELECT *
		          FROM $tbl[nutricion_sucursal_insumo] 
		          WHERE id_sucursal = $id_sucursal
		          AND activo = 1";
				//print_debug($query);
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}
	}
}