<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class sign_up extends Base_Controller {
	public function __construct(){
		parent::__construct();
		$this->load->model('sign_up_model','db_sign_up');
	}
		
	public function index(){
		$token = ( $this->ajax_get('token') ) ? $this->ajax_get('token') : false;
		if($this->session->userdata('is_logged')){
			$this->logout(false);
		}

		if($token){
			$user_data  = $this->db_sign_up->search_user_token($token);
			if(is_array($user_data)){
				$set_cambio = (is_null($user_data[0]['edit_timestamp'])) ? $this->timestamp() : $user_data[0]['edit_timestamp'];
				$antiguedad = $this->time_diff( $set_cambio , $this->timestamp());
				if($antiguedad>=5){
					redirect(base_url('login'));
				}

				$user = array(
	                            'class'       => 'requerido input-block-level animate3 bounceIn',
	                            'placeholder' => $this->lang_item('username'),
	                            'id'          => 'username'
	                        );  
				$pwd = array(
	                            'class'       => 'requerido input-block-level animate4 bounceIn',
	                            'placeholder' => $this->lang_item('password_new'),
	                            'id'          => 'password_new',
	                            'type'        => 'password'
	                        );
				$pwd_repeat = array(
	                            'class'       => 'requerido input-block-level animate5 bounceIn',
	                            'placeholder' => $this->lang_item('password_repeat'),
	                            'id'          => 'password_repeat',
	                            'type'        => 'password'
	                        );

				$btn_registro = array(
	                            'class'   => 'btn btn-primary animate6 bounceIn',
	                            'id'      => 'sign_registro',
	                            'content' => $this->lang_item('sign_resgitro')
	                        );

				$username              = '<p>'.form_input($user).'</p>';
				$password_new          = '<p>'.form_input($pwd).'</p>';
				$pasw_repeart          = '<p>'.form_input($pwd_repeat).'</p>';
				$data['form']          = $username.$password_new.$pasw_repeart;
				$data['idduser']       = $user_data[0]['id_clave'];
				$data['sign_tittle']   = $this->lang_item('sys_tittle');
				$data['sign_type']     = $this->lang_item('sign_new_user');
				$data['sign_up']       = $this->lang_item('sign_up');
				$data['sign_info']     = $this->lang_item('sign_info_new_user');
				$data['btn_accion']    = form_button($btn_registro);
				$data['classanime']    = 'animate7 bounceIn';
				$js['js'][]            = array('name' => 'sign_up', 'dirname' => '');

				return $this->load_view_unique('sign_up', $data, false, $js);
			}else{
				redirect(base_url('login'));
			}
		}else{
			redirect(base_url('login'));
		}
	}

	public function register(){
		$complete   = ($this->ajax_post('requeridos')>0)? false : true;
		$user       = ($this->ajax_post('user')) ? $this->ajax_post('user') : false;
		$iduser     = ($this->ajax_post('iduser')) ? $this->ajax_post('iduser') : false;
		$pwd_new    = ($this->ajax_post('pwd_new')) ? $this->ajax_post('pwd_new') : false;
		$pwd_repeat = ($this->ajax_post('pwd_repeat')) ? $this->ajax_post('pwd_repeat') : false;;
		$success    = false;
		$msg        = '';
		if($complete){
			if(strcmp(md5($pwd_new) , md5($pwd_repeat) )!==0 ){
				$msg = alertas_tpl('error', $this->lang_item("pwd_diferentes",false) ,false) ;
			}else{
				if($this->validate_string($pwd_new)){
					if( preg_match("/^[a-zA-Z0-9@.-_]+$/", $user) ){
						if($this->db_sign_up->user_available($user)){
							$this->db_sign_up->user_register($iduser , $user, md5($pwd_new));
							$success = true;
						}else{
							$msg = alertas_tpl('', $this->lang_item("user_available",false) ,false);
						}
					}else{
						$msg = alertas_tpl('', $this->lang_item("user_err",false) ,false);
					}
				}else{
					$msg = alertas_tpl('', $this->lang_item("pwd_longitud",false) ,false);
				}
			}
		}else{
			$msg = alertas_tpl('error', $this->lang_item("form_incompleto",false) ,false);
		}
		echo json_encode(array(  'success'=> $success, 'err' => $msg));
	}

	public function forgot_pwd(){
		$user = array(
                        'class'       => 'requerido input-block-level animate3 bounceIn',
                        'placeholder' => $this->lang_item('introduce_mail'),
                        'id'          => 'user_mail'
                    ); 
		$username     = '<p>'.form_input($user).'</p>';
		$btn_registro = array(
                            'class'   => 'btn btn-primary animate4 bounceIn',
                            'id'      => 'sign_forgot_pwd',
                            'content' => $this->lang_item('password_reset')
                        );
		$data['form']          = $username;
		$data['idduser']       = '';
		$data['sign_tittle']   = $this->lang_item('sys_tittle');
		$data['sign_type']     = $this->lang_item('sign_forgot');
		$data['sign_up']       = $this->lang_item('sign_up');
		$data['sign_info']     = $this->lang_item('sign_info_forgot');
		$data['btn_accion']    = form_button($btn_registro);
		$data['classanime']    = 'animate5 bounceIn';
		$js['js'][]            = array('name' => 'sign_up', 'dirname' => '');
		return $this->load_view_unique('sign_up', $data, false, $js);
	}

	public function reset_pwd(){
		$complete   = ($this->ajax_post('requeridos')>0) ? false : true;
		$user_mail  = ($this->ajax_post('user_mail')) ? $this->ajax_post('user_mail') : false;
		$success    = false;
		$data_user  = '';
		$smtp       = '';
		if($complete){
			if($this->validate_email($user_mail)){
				$email_user = $this->db_sign_up->search_email_user(trim($user_mail));
				if(is_array($email_user)){
					if($email_user[0]['id_usuario']!=''){
						$id_usuario  = $email_user[0]['id_usuario'];
						$id_personal = $email_user[0]['id_personal'];
						$mail        = $email_user[0]['mail'];
						$name        = $email_user[0]['nombre'];
						$smtp        = $this->enviar_mail($id_usuario, $id_personal, $mail, $name);
						if($smtp){
							$success = true;
							$msg     = alertas_tpl('success', $this->lang_item("reset_pwd_success",false) ,false);
						}else{
							$msg     = alertas_tpl('error', $this->lang_item("mail_failed",false) ,false);
						}
					}else{
						$msg     = alertas_tpl('', $this->lang_item("email_err",false) ,false);
					}
				}else{
					$msg = alertas_tpl('', $this->lang_item("email_err",false) ,false);
				}
			}else{
				$msg = alertas_tpl('', $this->lang_item("email_err",false) ,false);
			}
		}else{
			$msg = alertas_tpl('error', $this->lang_item("form_incompleto",false) ,false);
		}
		echo json_encode(array(  'success'=> $success, 'msg' => $msg));
	}

	public function validate_string($string){
		if(strlen($string)>=6){
			if (preg_match("/^[a-zA-Z0-9@.-_]+$/", $string)) { 
		    	return true; 
		    }else{ 
		    	return false; 
		    } 
		}else{
			return false;
		}
	}

	public function enviar_mail($id_usuario='', $id_personal='', $mail = '', $name = ''){
		$this->load->model('users_model');
		$token   = $this->generate_token();
		$sqlData = array(
			 'id_usuario'      => $id_usuario
			,'user'            => ''
			,'pwd'             => ''
			,'token'           => $token
			,'edit_id_usuario' => $id_usuario
			,'edit_timestamp'  => $this->timestamp());

		$insert_claves     = $this->users_model->db_update_claves($sqlData);
		$url_image         = base_url().'assets/images/';
		$destinatarios[]   = array(
					 'email'	=> $mail
					,'nombre'	=> $name
				);
		$destinatariosCC[] = array(
					 'email'	=> $mail
					,'nombre'	=> $name
				);
		$destinatariosBCC[] = array(
					 'email'	=> $mail
					,'nombre'	=> $name
				);
		$adjuntos[]   = false;
		$htmlData     = array(
							 'titulo'    => $this->lang_item('sign_forgot')
							,'nombre'    => $name
							,'url_image' => $url_image
							,'token'     => base_url().'sign_up?token='.$token
						);
		$htmlTPL = $this->load_view_unique('mail/mailing' , $htmlData, true);
		$tplData = array(
			 'body' 				=> $htmlTPL
			,'tipo' 				=> 'html' 
			,'destinatarios' 		=> $destinatarios
			,'destinatariosCC' 		=> $destinatariosCC
			,'destinatariosBCC' 	=> $destinatariosBCC
			,'asunto' 				=> utf8_decode($this->lang_item("sign_forgot"))
			,'adjuntos' 			=> $adjuntos
			,'imagenes' 			=> array(
												array('ruta' => 'assets/images', 'alias' => 'logo', 'file' => 'logo.png', 'encode' => 'base64', 'mime' => 'image/png' ),
												array('ruta' => 'assets/images', 'alias' => 'banner', 'file' => 'banner_azul.png', 'encode' => 'base64', 'mime' => 'image/png' ),
											    array('ruta' => 'assets/images', 'alias' => 'footer', 'file' => 'mail_footer.png', 'encode' => 'base64', 'mime' => 'image/png' )
										)											
		);
		$resultado = $this->mailsmtp->send($tplData);
		if(is_array($resultado)){
			if($resultado['success']){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

}