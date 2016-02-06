<?php
if (!defined( 'BASEPATH')) exit('No direct script access allowed'); 
class check_session extends Base_Controller
{
	private $ci;
	private $reload;
	public function __construct(){
		$this->ci =& get_instance();
	}	

	public function check_activity(){
		$uri_string         = $this->query_uri_string();
		$sites_availables   = $this->ci->sites_availables;
		$sites_availables[] = 'sign_up';
		$sites_availables[] = 'sign_up/register';
		$sites_availables[] = 'sign_up/reset_pwd';
		$sites_availables[] = 'forgot_pwd';
		$sites_availables[] = 'sync/SyncCatalogs';
		$sites_availables[] = 'sync/SyncSales';
		$sites_availables[] = 'cropper/avatar_user';
		$sites_availables[] = 'historial';
		if(in_array($uri_string, $sites_availables)){
			return false;
		}
		$interseccion = null;
		$ajax = $this->ci->ajax_post(false);
		if( $this->ci->uri->segment(1) != 'login' ){
			if(!$this->ci->session->userdata('is_logged')){
				if($ajax){
					$msg     = $this->ci->lang_item("msg_seesion_destroy",false);
					$reload  = "<script>";
					$reload .= "location.reload();";
					$reload .= "</script>";
					echo json_encode( $reload );
					exit;
				}else{	
					redirect(base_url('login'));					
				}
			}
		}else{
			$uri_login    = array('authentication','valindando' );
			$uri_segment  = $this->ci->uri->segment_array();
			$interseccion = array_intersect($uri_login,$uri_segment);
			if(!empty($interseccion)){
				if(!$this->ci->session->userdata('is_logged') && $ajax == false){
					redirect(base_url('login'));
				}
			}
		}
		$dominio = $this->ci->session->userdata('dominio');
		$this->ci->load_database($dominio);
	}

	public function check_sites_availables(){
		$uri = "";
		if($this->ci->session->userdata('is_logged')){
			$ajax               = $this->ci->ajax_post(false);
			$uri_string         = $this->query_uri_string();
			$sites_availables   = $this->ci->sites_availables;
			$sites_availables[] = 'default_controller';
			$sites_availables[] = 'inicio';
			$sites_availables[] = 'logout';
			$sites_availables[] = 'login';
			$sites_availables[] = 'sign_up';
			$sites_availables[] = 'forgot_pwd';
			$sites_availables[] = 'edit_profile';
			$sites_availables[] = 'test/imprimir_ticket'; #tmp
			$sites_availables[] = 'test/barcode'; #tmp
			$sites_availables[] = 'test/codeqr'; #tmp
			$sites_availables[] = 'test/load_vars'; #tmp
			$sites_availables[] = 'test/directorio'; #tmp
			$sites_availables[] = 'test/mailing'; #tmp
			$sites_availables[] = 'test/mpdf'; #tmp
			$sites_availables[] = 'test/webservice'; #tmp
			$sites_availables[] = '404_override'; 
			$sites_availables[] = 'sync/SyncCatalogs';
			$sites_availables[] = 'sync/SyncSales';
			$sites_availables[] = 'cropper/avatar_user';
			$sites_availables[] = 'cropper/avatar_user';
			$sites_availables[] = 'historial';
			if(!in_array($uri_string, $sites_availables)){
				if($uri_string==''){
					redirect(base_url('inicio'));
				}else{
					if(!$ajax){
						if(!mb_strstr($this->ci->uri_segment_end(),'export_')){
							if(!mb_strstr($this->ci->uri_segment_end(),'import_')){
								redirect(base_url('404_override'));	
							}
						}
					}
				}
			}
		}
	}

	private function query_uri_string(){
		$new_uri     = "";
		$uri_string  = $this->ci->uri->uri_string();
		$uri_string  = explode('/', $uri_string);
		foreach($uri_string as $value){
			if(!is_numeric($value)){
				$new_uri .= $value.'/';
			}
		}
		return trim($new_uri, '/');
	}
	
}