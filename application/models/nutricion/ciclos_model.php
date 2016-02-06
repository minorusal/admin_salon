<?php
class ciclos_model extends Base_Model{
	public function __construct(){
		parent::__construct();
	}


	public function insert_ciclo($data = array()){
		// DB Info
		$tbl = $this->tbl;
		if($data['tipo'] != "manual"){
			unset($data['tipo']);
			unset($data['nom_ciclo']);
			$query = "SELECT count(*) as id_indice 
					  FROM $tbl[nutricion_ciclos] cl
					  WHERE cl.id_sucursal = $data[id_sucursal]";
			$query = $this->db->query($query);
			$num_ciclos = $query->result_array();
			$cantidad = $num_ciclos[0]['id_indice'];
			$ciclos = $data['ciclo'];
			for($i=$cantidad+1;$i<($ciclos+$cantidad+1);$i++){				
				$data['ciclo'] = 'ciclo'.$i;
				$data['clave_corta'] = date("YmdHis").$i;
				$existe = $this->row_exist($tbl['nutricion_ciclos'], array('clave_corta'=> $data['clave_corta']));
				if(!$existe){
					$insert = $this->insert_item($tbl['nutricion_ciclos'], $data);
				}
			}
			return $insert;
		}else{
			unset($data['tipo']);
			$data['ciclo'] = $data['nom_ciclo'];
			unset($data['nom_ciclo']);
			// Query
			$existe = $this->row_exist($tbl['nutricion_ciclos'], array('clave_corta'=> $data['clave_corta']));
			if(!$existe){
				$insert = $this->insert_item($tbl['nutricion_ciclos'], $data);
				return $insert;
			}else{
				return false;
			}
		}
	}


	public function insert_ciclo_receta($data = array()){
		// DB Info
		$tbl = $this->tbl;

		$existe = $this->row_exist($tbl['nutricion_ciclo_receta'], array('id_servicio'=> $data['id_servicio'],'id_ciclo'=> $data['id_ciclo'],'id_receta'=>$data['id_receta'],'id_tiempo'=>$data['id_tiempo']));
		if($existe){
			return false;
		}else{
			$insert = $this->insert_item($tbl['nutricion_ciclo_receta'], $data);
		}
	}

	public function db_get_data($data = array()){
		// DB Info		
		$tbl = $this->tbl;
		// Query
		$filtro         = (isset($data['buscar']))?$data['buscar']:false;
		$limit 			= (isset($data['limit']))?$data['limit']:0;
		$offset 		= (isset($data['offset']))?$data['offset']:0;
		$aplicar_limit 	= (isset($data['aplicar_limit']))?true:false;
		$filtro 		= ($filtro) ? "AND (cl.id_sucursal like '%$filtro%' OR')" : "";
		$limit 			= ($aplicar_limit) ? "LIMIT $offset ,$limit" : "";
		//Query
		$query = "	SELECT
						 cl.id_nutricion_ciclos
						,cl.ciclo
						,cl.id_sucursal
						
					FROM $tbl[nutricion_ciclos] cl
					WHERE cl.activo = 1 AND
						  cl.id_sucursal = $data[buscar]
					ORDER BY cl.id_nutricion_ciclos ASC
					$limit
					";
      	$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}

	function get_ciclo_contenido($id_ciclo){
		$tbl = $this->tbl;
		$query = "	SELECT 
						 cl.id_nutricion_ciclos
						,cl.ciclo
						,ncr.id_ciclo
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
					ORDER BY ncr.id_servicio ,ncr.id_tiempo ,ncr.id_familia";
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}

	function eliminar_servicio($id_servicio, $id_ciclo){
		$tbl = $this->tbl;
		if($id_servicio==0){
			 $query = "DELETE FROM $tbl[nutricion_ciclo_receta] WHERE id_ciclo = $id_ciclo";
		}else{
			$query = "DELETE FROM $tbl[nutricion_ciclo_receta] WHERE id_ciclo = $id_ciclo AND id_servicio = $id_servicio";
		}
		$query = $this->db->query($query);
		if($query){
			return $query;
		}
	}

	function eliminar_tiempo($id_tiempo, $id_ciclo){
		$tbl = $this->tbl;
		if($id_tiempo==0){
			 $query = "DELETE FROM $tbl[nutricion_ciclo_receta] WHERE id_ciclo = $id_ciclo";
		}else{
			$query = "DELETE FROM $tbl[nutricion_ciclo_receta] WHERE id_ciclo = $id_ciclo AND id_tiempo = $id_tiempo";
		}
		$query = $this->db->query($query);
		if($query){
			return $query;
		}
	}

	function eliminar_familia($id_familia, $id_ciclo){
		$tbl = $this->tbl;
		if($id_familia==0){
			 $query = "DELETE FROM $tbl[nutricion_ciclo_receta] WHERE id_ciclo = $id_ciclo";
		}else{
			$query = "DELETE FROM $tbl[nutricion_ciclo_receta] WHERE id_ciclo = $id_ciclo AND id_familia = $id_familia";
		}
		$query = $this->db->query($query);
		if($query){
			return $query;
		}
	}

	function eliminar_receta($id_vinculo,$id_ciclo){
		$tbl = $this->tbl;
		$query = "DELETE FROM $tbl[nutricion_ciclo_receta] WHERE id_nutricion_ciclo_receta = $id_vinculo AND id_ciclo = $id_ciclo";
		$query = $this->db->query($query);
		if($query){
			return $query;
		}
	}

	/*function get_data_sucursales(){

	}*/

	function eliminar_ciclo($id_ciclo){
		$tbl = $this->tbl;
		$query = "SELECT *
				  FROM $tbl[nutricion_programacion_ciclos] npc
				  WHERE npc.id_nutricion_ciclos = $id_ciclo
				 ";
				 //print_debug($query);
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return false;
		}else{
			$query = "DELETE 
					  FROM $tbl[nutricion_ciclo_receta] 
					  WHERE id_ciclo = $id_ciclo";
			$query = $this->db->query($query);
			if($query){
				$query = "DELETE 
					      FROM $tbl[nutricion_ciclos] 
					      WHERE id_nutricion_ciclos = $id_ciclo";
				$query = $this->db->query($query);
				if($query){
					return $query;
				}
			}
		}
	}
	
}


//LEFT JOIN $tbl[nutricion_programacion_ciclos] npc on npc.id_nutricion_ciclos = $id_ciclo