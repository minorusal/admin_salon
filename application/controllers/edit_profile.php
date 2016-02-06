<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class edit_profile extends Base_Controller {
	public function __construct(){
		parent::__construct();
        $this->lang_load("edit_profile");
        $this->load->model('sign_up_model','db_sign_up');
	}
	public function index(){
		$img_path     = './assets/avatar/users/';
		$img_path_    = base_url().'assets/avatar/users/';
		$avatar_image = ($this->session->userdata('avatar_user') =='' ) ? 'sin_foto.png' : $this->session->userdata('avatar_user');
		

		$data['avatar_user']    = (file_exists($img_path.$avatar_image))? $img_path_.$avatar_image : $img_path_.'sin_foto.png';
		

		$index       = 0;
		$nombre      = $this->session->userdata('nombre');
		$paterno     = $this->session->userdata('paterno');
		$materno     = $this->session->userdata('materno');
		$tel_user    = $this->session->userdata('user_telefono');
		$mail        = $this->session->userdata('mail');
		$avatar_user = $this->session->userdata('avatar_user');
		$timestamp   = $this->session->userdata('timestamp');
		$sucursal    = $this->session->userdata('sucursal');
		$area        = $this->session->userdata('area');
		$puesto      = $this->session->userdata('puesto');
		$user        = $this->session->userdata('user');
		$empresa     = $this->session->userdata('empresa');
		$razon_social= $this->lang_item('razon_social', false).$this->session->userdata('razon_social').'<br>';
		$rfc         = $this->lang_item('rfc', false).$this->session->userdata('rfc').'<br>';
		$dir         = explode(' ', $this->session->userdata('direccion'));
		$telefono    = $this->lang_item('tel_no', false).$this->session->userdata('telefono').'<br>';
		
		foreach ($dir as $key => $value) {
			$direccion .= $value.' ';
			if($index>2){
				$direccion = trim($direccion) . '<br>';
				$index = 0;
			}
			$index++;
		}
		$direccion = trim($direccion, '<br>').'<br>';

		$data['icon']                = 'iconfa-user';
		$data['titulo_modulo']       = $this->lang_item('Admincontrol', false);
		$data['titulo_seccion']      = $this->lang_item('edit_profile', false);
		$data['lbl_nombre']          = $this->lang_item('lbl_nombre', false);
		$data['lbl_paterno']         = $this->lang_item('lbl_paterno', false);
		$data['lbl_materno']         = $this->lang_item('lbl_materno', false);
		$data['lbl_telefono']        = $this->lang_item('lbl_telefono', false);
		$data['lbl_email']           = $this->lang_item('lbl_email', false);
		$data['empresa']             = $this->lang_item('empresa', false);
		$data['sucursal']            = $this->lang_item('sucursal', false);
		$data['area']                = $this->lang_item('area', false);
		$data['puesto']              = $this->lang_item('puesto', false);
		$data['user_name']           = $this->lang_item('user_name', false);
		$data['pwd']                 = $this->lang_item('pwd', false);
		$data['fecha_registro']      = $this->lang_item('fecha_registro', false);
		$data['val_nombre']          = $nombre;
		$data['val_sucursal']        = $sucursal;
		$data['val_paterno']         = $paterno;
		$data['val_materno']         = $materno;
		$data['val_mail']            = $mail;
		$data['val_telefono']        = $tel_user;
		$data['val_empresa']         = $this->session->userdata('empresa');
		$data['val_info']            = $razon_social.$rfc.$direccion.$telefono;
		$data['val_area']            = $area;
		$data['val_puesto']          = $puesto;
		$data['val_user_name']       = $user;
		$data['val_user_pwd']        = '3fc26dc90ab296e41bc5fc8f9a2a7d40';
		$data['val_fecha_registro']  = $timestamp;
		$data['set_profile']         = $this->lang_item('set_profile', false);
		$data['change_avatar']       = $this->lang_item('change_avatar', false);
		$data['local_img_upload']       = $this->lang_item('local_img_upload', false);
		$data['rotate_left']       = $this->lang_item('rotate_left', false);
		$data['rotate_right']       = $this->lang_item('rotate_right', false);
		$data['save_avatar']        = $this->lang_item('save_avatar', false);
		$js['js'][]                  = array('name' => 'edit_profile', 'dirname' => '');
		$this->load_view('edit_profile', $data, $js);
	}

	public function update_info_user(){
		$complete               = ($this->ajax_post('requeridos')>0)? false : true;
		$objData                = $this->ajax_post('objData');
		$personal['nombre']     = ($objData['nombre'] )? trim($objData['nombre']) : false;
		$personal['paterno']    = ($objData['paterno']) ? trim($objData['paterno']) : false;
		$personal['materno']    = ($objData['materno'] )? trim($objData['materno']) : false;
		$personal['mail']       = ($objData['mail']) ? trim($objData['mail']) : false;
		$personal['telefono']   = ($objData['telefono']) ? trim($objData['telefono']) : false;
		$user_name  			= ($this->ajax_post('username')) ? trim($this->ajax_post('username')) : false;
		$user_pwd   			= ($this->ajax_post('pwd')) ? trim($this->ajax_post('pwd')) : false;
		$success    			= false;
		$msg        			= '';
		if($complete){
			if($this->validate_email($personal['mail'])){
				$email_user = $this->db_sign_up->search_email_user(trim($personal['mail']));
				if(is_array($email_user)){
					$id_personal  = $email_user[0]['id_personal'];
					if($id_personal == $this->session->userdata('id_personal')){
						if($this->db_sign_up->user_available_profile($user_name,$this->session->userdata('id_usuario'))){
							if( preg_match("/^[a-zA-Z0-9@.-_]+$/", $user_name) ){
								if($user_pwd=='3fc26dc90ab296e41bc5fc8f9a2a7d40'){
									$user_pwd =false;
									$success = true;
								}else{
									if($this->validate_string($user_pwd)){
										$user_pwd = md5($user_pwd);
										$success = true;
									}else{
										$msg = alertas_tpl('', $this->lang_item("pwd_longitud",false) ,false);
									}
								}
							}else{
								$msg = alertas_tpl('', $this->lang_item("user_err",false) ,false);
							}
						}else{
							$msg = alertas_tpl('', $this->lang_item("user_available",false) ,false);
						}
					}else{
						$msg = alertas_tpl('', $this->lang_item("email_err_profile",false).'2' ,false);
					}
				}
			}else{
				$msg = alertas_tpl('', $this->lang_item("email_err_profile",false).'1' ,false);
			}
		}else{
			$msg = alertas_tpl('error', $this->lang_item("form_incompleto",false) ,false);
		}

		if($success){
			$this->db_sign_up->set_claves($this->session->userdata('id_usuario') , $user_name, $user_pwd);
			$this->set_personal_info($personal);
			$this->session->set_userdata('name', strtoupper($personal['nombre'].' '.$personal['paterno'].' '.$personal['materno']));
			$this->session->set_userdata('nombre', ucfirst(strtolower($personal['nombre'])));
			$this->session->set_userdata('paterno', ucfirst(strtolower($personal['paterno'])));
			$this->session->set_userdata('materno', ucfirst(strtolower($personal['materno'])));
			$this->session->set_userdata('user_telefono', $personal['telefono']);
			$this->session->set_userdata('mail', $personal['mail']);
			$this->session->set_userdata('user', $user_name);
			$msg = $this->lang_item("msg_update_success",false);
		}
		echo json_encode(array(  'success'=> $success, 'msg' => $msg));
	}

	public function set_personal_info($info_personal){
		
		$info_personal['id_personal']     = $this->session->userdata('id_personal');
		$info_personal['edit_timestamp']  = $this->timestamp();
		$info_personal['edit_id_usuario'] = $this->session->userdata('id_usuario');

		$update = $this->db_sign_up->db_update_personal_profile($info_personal);
	}

	public function validate_string($string){
		if($this->root_available()){
			$length = 0;
		}else{
			$length = 6;
		}
		if( strlen($string) >= $length){
			if (preg_match("/^[a-zA-Z0-9@.-_]+$/", $string)) { 
		    	return true; 
		    }else{ 
		    	return false; 
		    } 
		}else{
			return false;
		}
	}
}

