<?php
class sign_up_model extends Base_Model{
	public function search_user_token($token){
		// DB Info
		$tbl    = $this->tbl;
		$query  = "	SELECT 
						U.id_usuario
						,P.id_personal
						,P.nombre
						,P.paterno 
						,P.materno
						,P.telefono
						,P.mail
						,P.avatar as avatar_user
						,Pa.id_pais
						,Pa.pais
						,Pa.dominio
						,Pa.avatar as avatar_pais
						,Pa.moneda
						,E.id_empresa
						,E.empresa
						,S.id_sucursal
						,S.sucursal
						,N.id_perfil
						,N.perfil
						,U.id_sucursales as user_id_sucursales
						,N.id_menu_n1
						,N.id_menu_n2
						,N.id_menu_n3
						,U.id_menu_n1 as user_id_menu_n1
						,U.id_menu_n2 as user_id_menu_n2
						,U.id_menu_n3 as user_id_menu_n3
						,C.timestamp
						,U.activo
						,C.user
						,C.id_clave
						,C.edit_timestamp
					FROM $tbl[usuarios] U
					left join $tbl[personales] P on U.id_personal = P.id_personal
					left join $tbl[claves]     C on U.id_clave    = C.id_clave
					left join $tbl[perfiles]   N on U.id_perfil  = N.id_perfil
					left join $tbl[paises]     Pa on U.id_pais    = Pa.id_pais
					left join $tbl[empresas]   E on U.id_empresa  = E.id_empresa
					left join $tbl[sucursales] S on U.id_sucursal = S.id_sucursal
					WHERE C.token = '$token' AND U.activo = 1 AND C.activo = 1 AND P.activo = 1
					ORDER BY 
						N.id_perfil;
				";

		//print_debug($query);
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}	
	}

	public function user_available($user){
		$tbl    = $this->tbl;
		$query = "SELECT * FROM $tbl[claves] where user = '$user';";
		$query = $this->db->query($query);
		if($return_data){
			return $query->result_array();
		}
		if($query->num_rows >= 1){
			return false;
		}else{
			return true;
		}
	}

	public function user_available_profile($user, $id_usuario){
		$tbl    = $this->tbl;
		$query = "SELECT * FROM $tbl[claves] where user = '$user' AND id_clave NOT IN ( SELECT id_clave FROM $tbl[usuarios] WHERE id_usuario = $id_usuario);";
		//print_debug($query);
		$query = $this->db->query($query);
		
		if($query->num_rows >= 1){
			return false;
		}else{
			return true;
		}
	}

	public function user_register($id_clave, $user, $pwd){
		$tbl = $this->tbl;
		$query  = " UPDATE $tbl[claves] 
					SET 
						user = '$user' , 
						pwd = '$pwd' , 
						token = null 
					WHERE 
						id_clave = $id_clave";

		$query = $this->db->query($query);
	}

	public function set_claves($id_usuario, $user, $pwd){
		$tbl = $this->tbl;
		if($pwd){
			$add = "pwd = '$pwd' ,";
		}else{
			$add = '';
		}
		$query  = " UPDATE $tbl[claves] 
					SET 
						user = '$user' , 
						$add
						token = null 
					WHERE 
						id_clave = (SELECT id_clave FROM $tbl[usuarios] WHERE id_usuario = $id_usuario)";

		$query = $this->db->query($query);
	}

	public function search_email_user($email){
		$tbl    = $this->tbl;
		$query = "SELECT 
					U.id_usuario,
					P.id_personal,
					concat_ws(' ', P.nombre, P.paterno , P.materno) as nombre,
					P.mail
				  FROM $tbl[personales] P 
				  LEFT JOIN $tbl[usuarios]  U on U.id_personal = P.id_personal
				  WHERE P.mail = '$email'
				  AND P.activo = 1
				  GROUP BY U.id_personal;";

		//print_debug($query);
		$query = $this->db->query($query);
		if($query->num_rows >= 1){
			return $query->result_array();
		}else{
			return false;
		}
	}

	public function db_update_personal_profile($data=array()){
		// DB Info
		$tbl = $this->tbl;
	
		$condicion = "id_personal = ".$data['id_personal'];			
		$update = $this->update_item($tbl['personales'], $data, 'id_personal', $condicion);
		return $update;
		
	}
}