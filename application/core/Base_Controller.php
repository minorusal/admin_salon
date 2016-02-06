<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Base_Controller extends CI_Controller {
 	
 	public $sites;
 	public $sites_availables;
 	public $sites_panel;
 	public $sucursales_availables;

    public function __construct(){
        parent::__construct();
        $this->removeCache();
        $this->lang_load("system","es_ES");
        $this->lang_load("navigate");
        if($this->session->userdata('is_logged')){
        	$this->sites                 = $this->sites_privilege_navigate();
        	$this->sites_availables      = $this->sites['sites'];
			$this->sites_panel           = $this->sites['modules'];
			$this->sucursales_availables = $this->sites['sucursales'];
        }
    }
   
    
    /**
    * Carga la base de datos de acuerdo al pais de origen
    * del usuario (mx,cr,etc)
    * @param string $db
    * @return void
    */
    public function load_database($bd){
    	if($bd!=""){
    		$load = $this->load->database($bd,TRUE);
	    	if(!$load){
	    		return true;
	    	}
    	}
    }

    /**
	* Finalizar la sesion activa
	* @return void
	*/
	public function logout($redirect = true){
		$this->load_database('global_system');
		$this->session->sess_destroy();
		if($redirect){
			redirect('login');	
		}
	}

	/**
	* Evalua si el perfil usuario corresponde al root
	* del sistema
	* @return void
	*/

	public function root_available(){
		
		return (md5(strtolower($this->session->userdata('perfil')))=='63a9f0ea7bb98050796b649e85481845') ? true : false;
	}

    /**
    * Verfica los modulos que seran visibles en el panel de navegacion
    * y a los cuales se podra acceder
    * @return array
    */
    private function sites_privilege_navigate(){
    	$uri                    = $this->uri->segment_array();
		$nivel_1                = $this->session->userdata('id_menu_n1');
		$nivel_2                = $this->session->userdata('id_menu_n2');
		$nivel_3                = $this->session->userdata('id_menu_n3');
		$sucursal_origin        = $this->session->userdata('id_sucursal');
		$sites_privileges       = array();
		$this->load->database('global_system',TRUE);
		$this->load->model('users_model');

		$data_modulos = $this->users_model->search_modules_for_user($nivel_1, $nivel_2, $nivel_3,$this->root_available());

		if((is_array($data_modulos))){
			$data_modulos     = $this->build_array_navigator($data_modulos);
			$navigate_items   = $data_modulos[1];
			$sites_privileges = $data_modulos[0];
			$panel_navigate   = $this->build_panel_navigate($navigate_items,$uri);
		}else{
			$data_modulos   = "";
			$navigate_items = "";
			$panel_navigate = "";
		}

		if($this->root_available()){
			$sucursales_privileges = false;
		}else{
			$sucursales_privileges = trim($sucursal_origin.','.trim($this->session->userdata('user_sucursales'), ','), ',');
		}

		$data_sites = array(
								'sites'      => $sites_privileges, 
								'modules'    => $panel_navigate, 
								'sucursales' => $sucursales_privileges
							);

		return $data_sites;
	}

    /**
    * unifica las vistas header & footer con las vistas parseadas
    * de la seccion seleccionada
    * @param string $view
    * @param array $data
    * @param array $data_includes
    * @param string $ext
    * @return void
    */
    public function load_view($view, $data = array(), $data_includes = array() ,$ext = '.html'){
		$this->vars = new config_vars();
        $this->vars->load_vars();
		$ext          = ($ext!='.html') ? '': $ext;
		$uri          = $this->uri->segment_array();
		$includes     = $this->load_scripts($data_includes);
		$data_modulos = $this->sites_panel;
		$img_path     = './assets/avatar/users/';
		$img_path_    = base_url().'assets/avatar/users/';
		$avatar_image = ($this->session->userdata('avatar_user') =='' ) ? 'sin_foto.png' : $this->session->userdata('avatar_user');
		$icon_root    = ($this->root_available()) ? 'fa fa-user-secret' : '';

		$dataheader['site_title'] 	  = $this->vars->cfg['site_title'];
		$dataheader['data_js']        = (!empty($includes)) ? $includes['js']  : '';
		$dataheader['data_css']       = (!empty($includes)) ? $includes['css'] : '';
		$dataheader['base_url']       = base_url();
		$dataheader['panel_navigate'] = $this->sites_panel;
		$dataheader['avatar_user']    = (file_exists($img_path.$avatar_image))? $img_path_.$avatar_image : $img_path_.'sin_foto.png';
		$dataheader['avatar_pais']    = $this->session->userdata('avatar_pais');
		$dataheader['user_mail']      = $this->session->userdata('mail');
		$dataheader['user_name']      = $this->session->userdata('name');
		$dataheader['user_perfil']    = $this->session->userdata('perfil');
		$dataheader['close_session']  = $this->lang_item('close_session');
		$dataheader['date']           = $this->lang_item('edit_perfil');
		$dataheader['icon_root']      = $icon_root;
		$dataheader['date']           = date('d/m/Y');
		$dataheader['fecha_hoy']	  = $this->timestamp_complete();
		$uri_nav                      = $this->array2string_lang(explode('/', $this->uri->uri_string()),array("navigate","es_ES"),' » ');
		$dataheader['uri_string']     = $uri_nav;
		
		$datafooter                   = array('anio' => date('Y'));
		
		$data = (empty($data)) ? array() : $data;
		$data['base_url']=$dataheader['base_url'];
		$this->parser->parse('dashboard/header.html', $dataheader);
		$this->parser->parse($view.$ext, $data);
		$this->parser->parse('dashboard/footer.html', $datafooter); 
	}

	/**
    * Carga una vista unica sin integrar el header 
    * ni el footer, puede servir para la carga de 
    * paginas de error
    * @param string $view
    * @param array $data
    * @param boolean $autoload
    * @param array $data_includes
    * @param string $ext
    * @return void
    */
	public function load_view_unique($view, $data = array(), $autoload = false ,$data_includes = array() ,$ext = '.html'){
		$this->vars = new config_vars();
        $this->vars->load_vars();
		$ext      = ($ext!='.html') ? '': $ext;
		$includes = $this->load_scripts($data_includes);

		$data['data_js']  = (!empty($includes)) ? $includes['js']  : '';
		$data['data_css'] = (!empty($includes)) ? $includes['css'] : '';
		$data['base_url'] = base_url();
		$data['site_title'] = $this->vars->cfg['site_title'];
		
		return $this->parser->parse($view.$ext, $data, $autoload);
	}

	/**
    * Carga la vista de login
    * @return void
    */
	public function load_view_login(){
		$this->vars = new config_vars();
        $this->vars->load_vars();
		$att_fopen = array('id' => 'login');
		$att_hiden = array(
                            'name'    => 'id_user',
                            'id'      => 'id_user',
                            'type'    => 'hidden'
                        );  
		$att_user = array(
                            'name'        => 'user',
                            'id'          => 'user',
                        );  
		$att_pwd = array(
                            'name'        => 'pwd',
                            'id'          => 'pwd'
                        ); 
		$att_btn = array(
                            'name'    => 'button',
                            'id'      => 'button_login',
                            'value'   => 'true',
                            'content' => $this->lang_item('lang_btn_ingresar')
                        );

		$data['base_url']          = base_url();
		$data['forgot_pwd']        = $this->lang_item('forgot_pwd');
		$data['form_open']         = form_open('', $att_fopen);
		$data['form_input_hidden'] = form_input($att_hiden);
		$data['form_input_user']   = form_input($att_user, '', 'placeholder="'.$this->lang_item('lang_ph_user').'"');
		$data['form_input_pwd']    = form_password($att_pwd, '', 'placeholder="'.$this->lang_item('lang_ph_pwd').'"');
		$data['form_button']       = form_button($att_btn);
		$data['form_close']        = form_close();
		$data['site_title']        = $this->vars->cfg['site_title'];
		
		$this->parser->parse('login.html', $data);
	}

	/**
    * Carga archivos js & css en el header
    * @param array $data
    * @return array
    */
	public function load_scripts($data){

		$vars_js = '';
		$vars_js.= "var days_timepicker   = ".json_encode($this->days(false, 1)).";";
		$vars_js.= "var months_timepicker = ".json_encode($this->months(false, 1)).";";
		$vars_js.= "var calendar_month    = '".$this->lang_item('calendar_month',false)."';";
		$vars_js.= "var calendar_week     = '".$this->lang_item('calendar_week',false)."';";
		$vars_js.= "var calendar_day      = '".$this->lang_item('calendar_day',false)."';";
		$vars_js.= "var calendar_today    = '".$this->lang_item('calendar_today',false)."';";
		$vars_js.= "var msg_eliminar      = '".$this->lang_item('msg_eliminar',false)."';";
		$vars_js.= "var msg_atencion      = '".$this->lang_item('msg_atencion',false)."';";

		$files_js  		   = "<script type='text/javascript'>".$vars_js." </script>";
		$files_css 		   = '';
		$url_js    		   = base_url().'assets/js/system';
		$url_css  		   = base_url().'assets/css';

		/*if(empty($data)){
			if($this->root_available()){
				$files_css .= '<link rel="stylesheet" href="'.$url_css.'/root.css" type="text/css" />';
			}

			$data_load['css'] = $files_css;
			$data_load['js']  = '';
			return $data_load;
		}*/
		if(!empty($data)){
			if (array_key_exists('js', $data)) { 
				foreach ($data['js'] as $key => $value) {
					$js_name  = $value['name'];
					$js_dir   = $value['dirname'];
					$files_js.= "<script type='text/javascript' src='$url_js/$js_dir/$js_name.js'></script>";
				}
			}
			if (array_key_exists('css', $data)) {
				foreach ($data['css'] as $key => $value) {
					$css_name  = $value['name'];
					$css_dir   = $value['dirname'];
					$files_css.= '<link rel="stylesheet" href="'.$url_css.'/'.$css_name.'.css" type="text/css" />';
				}
			}

			$data_load['js']  = $files_js;
		}

		if($this->root_available()){
			$files_css .= '<link rel="stylesheet" href="'.$url_css.'/root.css" type="text/css" />';		
		}




		$data_load['css'] = $files_css;
		return $data_load;
	}

	/**
	* Prepara un array para la construccion
	* del panel de navegacion 
	* @param array $array_navigator
	* @return array
	*/
	public function build_array_navigator($navigator){
		foreach ($navigator as $key => $value) {
			$route = "";
			if(!is_null($value['menu_n2'])){
				if(!is_null($value['menu_n3'])){
					$route = $value['menu_n3_routes'];
					$data_navigator[$value['menu_n1']]['content'][$value['menu_n2']]['content'][$value['menu_n3']] = array( 'menu_n3'=> $value['menu_n3'] , 'icon' => $value['menu_n3_icon'],'routes'=> $route);
					$data_navigator[$value['menu_n1']]['content'][$value['menu_n2']]['icon'] = $value['menu_n2_icon'];
					$data_navigator[$value['menu_n1']]['icon'] = $value['menu_n1_icon'];
				}else{
					$route = $value['menu_n2_routes'];
					$data_navigator[$value['menu_n1']]['content'][$value['menu_n2']] = array('icon' => $value['menu_n2_icon'] , 'routes' => $route);
					$data_navigator[$value['menu_n1']]['icon'] = $value['menu_n1_icon'];
				}
			}else{
				$route = $value['menu_n1_routes'];
				$data_navigator[$value['menu_n1']] = array('icon'=>$value['menu_n1_icon'], 'routes' => $route);
			}
			$sites_availables[] = $route;
		}
		$data = array($sites_availables, $data_navigator);
		//print_debug($data);
		return $data;
	}

	/**
	* Prepara un array para la construccion
	* del treeview 
	* @param array $array_treeview
	* @return array
	*/
	public function build_array_treeview($navigator){
		foreach ($navigator as $key => $value) {
			if(!is_null($value['menu_n2'])){
				if(!is_null($value['menu_n3'])){
					$id = $value['id_menu_n3'];
					$data_navigator[$value['id_menu_n1'].'-'.$value['menu_n1']]['content'][$value['id_menu_n2'].'-'.$value['menu_n2']]['content'][$value['id_menu_n3'].'-'.$value['menu_n3']] = array( 'menu_n3'=> $value['id_menu_n3'].'-'.$value['menu_n3'] , 'icon' => $value['menu_n3_icon'], 'nivel' => 3);
					$data_navigator[$value['id_menu_n1'].'-'.$value['menu_n1']]['content'][$value['id_menu_n2'].'-'.$value['menu_n2']]['icon']  = $value['menu_n2_icon'];
					$data_navigator[$value['id_menu_n1'].'-'.$value['menu_n1']]['content'][$value['id_menu_n2'].'-'.$value['menu_n2']]['nivel'] = 2;
				}else{
					$data_navigator[$value['id_menu_n1'].'-'.$value['menu_n1']]['content'][$value['id_menu_n2'].'-'.$value['menu_n2']] = array('icon' => $value['menu_n2_icon'],  'nivel' => 2);
				}
				$data_navigator[$value['id_menu_n1'].'-'.$value['menu_n1']]['icon'] = $value['menu_n1_icon'];
				$data_navigator[$value['id_menu_n1'].'-'.$value['menu_n1']]['nivel'] = 1;
			}else{
				$data_navigator[$value['id_menu_n1'].'-'.$value['menu_n1']] = array('icon'=>$value['menu_n1_icon'], 'nivel' => 1);
			}
		}
		return $data_navigator;
	}

	/**
    * Contruye el treeview de navegacion
    * @param array $items
    * @param array $uri
    * @param bolean $sub
    * @return string
    */
	public function list_tree_view($items, $id_niveles = array(), $sub = false, $checado = false, $locked = false){

	    $panel    = "";
	    $style_ul = "";
	    $style    = "treeview-gray";
	    if($sub){ 
	    	$panel .= "<ul>";
		}else{
			$panel .= "<ul id = 'treeview-modules' class='treeview-gray '>";
	    }
	    
	    foreach ($items as $item => $subitems) {
	    	$item         = explode('-', $item);
	    	$itemId       = $item[0]; 
	    	$itemName     = $item[1];
	    	$content      = "";	
			$sub_nivel    = "";
			$checked      = "";
			$lock         = "";
	        if(array_key_exists('content', $subitems)){
	        	$content .= $this->list_tree_view($subitems['content'],$id_niveles, $sub = true, $checado, $locked);
	        }
	        
	        $icon      = $subitems['icon'];
	        $nivel     = $subitems['nivel'];
	        $lang_item = $this->lang_item(str_replace(' ','_', $itemName));
	        if(!$checado)
	        {

	        	$panel    .= "<li>&nbsp;<input name = 'nivel_$nivel' $checked  type ='checkbox' value='$itemId' />&nbsp;<span class='$icon'></span>&nbsp;<span>".text_format_tpl($lang_item).'</span>';
	        	$panel    .= $content;
	       		$panel    .= "</li>";
	        }
	    	else
	    	{
		        switch ($nivel) {
		        	case 1:
		        		if(in_array($itemId, $id_niveles['id_menu_n1'])){
		        			$checked = "checked='checked'";
		        			$lock = ($locked)  ? 'disabled = disabled' : '';
		        		}else{
		        			$checked = '';
		        		}
		        		break;
		        	case 2:
		        		if(in_array($itemId, $id_niveles['id_menu_n2'])){
		        			$checked = "checked='checked'";
		        			$lock = ($locked)  ? 'disabled = disabled' : '';
		        		}else{
		        			$checked = '';
		        		}
		        		break;
		        	case 3:
		        		if(in_array($itemId, $id_niveles['id_menu_n3'])){
		        			$checked = "checked='checked'";
		        			$lock = ($locked) ? 'disabled = disabled' : '';
		        		}else{
		        			$checked = '';
		        			$lock = '';
		        		}
		        		break;
		        	default:
		        		break;
		        }
		    	$panel    .= "<li>&nbsp;<input $lock name = 'nivel_$nivel' $checked type ='checkbox' value='$itemId' />&nbsp;<span class='$icon'></span>&nbsp;<span>".text_format_tpl($lang_item).'</span>';
	        	$panel    .= $content;
	       		$panel    .= "</li>";    
		    }
	    }
	    if($sub){$panel .= "</ul>";}
	    return $panel;
	}

	/**
    * Contruye el treeview de navegacion de acuerdo al perfil
    * @param array $id_perfil
    * @return array
    */
	public function treeview_perfiles($id_perfil=false, $locked = false){
		$this->load_database('global_system');
		$this->load->model('administracion/perfiles_model','perfiles_model');
		$this->load->model('users_model');
		if($id_perfil)
		{
			$info_perfil  = $this->perfiles_model->search_data_perfil($id_perfil);
			$id_menu_n1   = $info_perfil[0]['id_menu_n1'];
			$id_menu_n2   = $info_perfil[0]['id_menu_n2'];
			$id_menu_n3   = $info_perfil[0]['id_menu_n3'];

			$id_niveles   = array(	
									'id_menu_n1' => explode(',', $info_perfil[0]['id_menu_n1']),
									'id_menu_n2' => explode(',', $info_perfil[0]['id_menu_n2']),
									'id_menu_n3' => explode(',', $info_perfil[0]['id_menu_n3']),
							);
			$checked = true;
		}
		else
		{
			$id_niveles = "";
			$checked = false;
		}
		
		$data_modulos = $this->users_model->search_modules_for_user('', '' , '' , true);
		$data_modulos = $this->build_array_treeview($data_modulos);
		$controls     = '<div id="sidetreecontrol"><a href="?#">'.$this->lang_item('collapse', false).'</a> | <a href="?#">'.$this->lang_item('expand', false).'</a></div>';
		return $controls.$this->list_tree_view($data_modulos, $id_niveles,false,$checked,$locked);
	}

	/**
    * Contruye el treeview de navegacion de acuerdo a los perfiles en usuarios y en perfiles
    * @param array $id_usuario
    * @param array $id_perfil
    * @return array
    */
	public function treeview_perfiles_usuarios($id_personal=false, $id_perfil=false, $locked = false){
		$this->load_database('global_system');
		$this->load->model('administracion/perfiles_model','perfiles_model');
		$this->load->model('administracion/users_model','users_model');
		if($id_personal && $id_perfil){	
			$info_perfil  = $this->perfiles_model->search_data_perfil($id_perfil);
			
			$id_menu_n1   = $info_perfil[0]['id_menu_n1'];
			$id_menu_n2   = $info_perfil[0]['id_menu_n2'];
			$id_menu_n3   = $info_perfil[0]['id_menu_n3']; 

			$info_usuario  = $this->users_model->search_data_perfil_usuario($id_personal,$id_perfil);
			if($info_usuario[0]['id_menu_n1'] == '' || $info_usuario[0]['id_menu_n2'] == '' || $info_usuario[0]['id_menu_n3'] == ''){
				$id_niveles   = array(	
						'id_menu_n1' => explode(',', $info_usuario[0]['id_menu_n1']),
						'id_menu_n2' => explode(',', $info_usuario[0]['id_menu_n2']),
						'id_menu_n3' => explode(',', $info_usuario[0]['id_menu_n3']),
						);
			}else{
				$id_niveles   = array(	
						'id_menu_n1' => explode(',', ($info_perfil[0]['id_menu_n1'].','.$info_usuario[0]['id_menu_n1'])),
						'id_menu_n2' => explode(',', ($info_perfil[0]['id_menu_n2'].','.$info_usuario[0]['id_menu_n2'])),
						'id_menu_n3' => explode(',', ($info_perfil[0]['id_menu_n3'].','.$info_usuario[0]['id_menu_n3'])),
						);	
			}
			$checked = true;
		}else{
			$id_niveles = "";
			$checked    = false;
		}
		
		$data_modulos = $this->users_model->search_modules_for_user('', '' , '' , true);
		$data_modulos = $this->build_array_treeview($data_modulos);
		$controls     = '<div id="sidetreecontrol"><a href="?#">'.$this->lang_item('collapse', false).'</a> | <a href="?#">'.$this->lang_item('expand', false).'</a></div>';

		return $controls.$this->list_tree_view($data_modulos, $id_niveles,false,$checked,$locked);
	}
	

	/**
    * Contruye el Panel de navegacion
    * @param array $items
    * @param array $uri
    * @param bolean $sub
    * @return string
    */
	public function build_panel_navigate($items, $uri, $sub = false, $bool = false) {
		
	    $panel    = "";
	    $style_ul = "";
	    if($sub){ if($bool){ $style_ul = "style='display: block;'";} $panel .= "<ul class='' $style_ul>";}
	    foreach ($items as $item => $subitems) {
	        $mod_dropdown = "";
	       	$content      = "";	
	       	$routes       = "";
	       	$active       = "";
	       	$icon         = "";
	       	$sub_nivel    = "";
	       	$bool         = false;
	       	$lang_item    = "";
	       	$class_clik   = "";
	       	if(in_array(strtolower(str_replace(' ','_', $item)), $uri)){
	        	$active  = "active";
	        	$bool    = true;
	        } 
	        if(array_key_exists('content', $subitems)){
	        	$mod_dropdown = "dropdown";
	        	$content     .= $this->build_panel_navigate($subitems['content'],$uri,$sub = true, $bool);
	        	$routes       = base_url();
	        	$icon         = $subitems['icon'];
	        	$class_clik   = "";
	        	$sub_nivel    = "<span class='iconfa-circle-arrow-down' style='float:right;'></span>";
	        }else{
	        	$routes       = base_url().$subitems['routes'];
	        	$icon         = $subitems['icon'] ;
	        	$class_clik   = "load_controller";
	        }
	        $lang_item = $this->lang_item(str_replace(' ','_', $item));//<--Si se activa esta funcion se alentiza la carga de la vista "OPTIMIZAR!!!!!!!";
    		$panel .= "<li class='$mod_dropdown $active $class_clik'><a href='$routes'><span class='$icon'></span>".text_format_tpl($lang_item)." $sub_nivel </a>";
	        $panel .= $content;
	       	$panel .= "</li>";
	    }
	    if($sub){$panel .= "</ul>";}
	    return $panel;
	}         		

	/**
	* convierte un objeto a un arreglo
	* @param object $obj
	* @return array
	*/
	public function object_to_array($obj){
		$reaged = (array)$obj;
		foreach($reaged as $key => &$field){
			if(is_object($field))
				$field = $this->object_to_array($field);
		}
		return $reaged;
	}

	/**
	* elimina el cache almacenado
	* @return void
	*/
	public function removeCache(){
        $this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
        $this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
        $this->output->set_header('Pragma: no-cache');
    }

    /**
    * Devuleve la cantidad de segmentos contenidos en la URL
    * contabilizando a partir del dominio
    * Ejemplo http://domain.com/sitio/pagina/3
    * el valor devuleto sera 4
    * @return int
    */
    public function uri_segment(){
    	return $this->uri->total_segments();
    }

    /**
    * Devuleve el ultimo segmentos contenido en la URL
    * @return int
    */
    public function uri_segment_end($prev = 0){
    	return $this->uri->segment($this->uri->total_segments()-$prev);
    }

    /**
    * Devuleve la URL actual
    * @return string
    */
    public function uri_string(){
    	return $this->uri->uri_string();
    }

    /**
    * Si $post es false devuleve un arreglo con el total de items 
    * recibidos por el metodo POST[]
    * de lo contrario devolvera el item con respecto al index $post
    * @param int $post
    * @return array
    */
    public function ajax_post($post){
    	if($post===false){
    		return $this->input->post();
    	}
    	return $this->input->post($post);
    }


    /**
    * Si $get es false devuleve un arreglo con el total de items 
    * recibidos por el metodo GET[]
    * de lo contrario devolvera el item con respecto al index $get
    * @param int $get
    * @return array
    */
    public function ajax_get($get){
    	if($get===false){
    		return $this->input->get();
    	}
    	return $this->input->get($get);
    }

    /**
    * devuleve el tiempo unix actual
    * @return string
    */
    public function timestamp(){
    	return date('Y-m-d H:i:s');
    }
    /**
    * Devuelve el item del dia con respecto al indice $index,
    * si el $index no se define devolvera un array con todos los dias
    * @param int $index
    * @return array
    */
    public function days($index = false, $sub = false){
		if($sub){
			$days[0]= substr($this->lang_item('domingo',false), 0, $sub);
			$days[1]= substr($this->lang_item('lunes',false), 0, $sub);
			$days[2]= substr($this->lang_item('martes',false), 0, $sub);
			$days[3]= substr($this->lang_item('miercoles',false), 0, $sub);
			$days[4]= substr($this->lang_item('jueves',false), 0, $sub);
			$days[5]= substr($this->lang_item('viernes',false), 0, $sub);
			$days[6]= substr($this->lang_item('sabado',false), 0, $sub);
			
		}else{
			$days[0]= $this->lang_item('lunes',false);
			$days[1]= $this->lang_item('martes',false);
			$days[2]= $this->lang_item('miercoles',false);
			$days[3]= $this->lang_item('jueves',false);
			$days[4]= $this->lang_item('viernes',false);
			$days[5]= $this->lang_item('sabado',false);
			$days[6]= $this->lang_item('domingo',false);
		}
		if($index){
			return $days[ltrim($index,'0')];
		}
		return $days;
    }
    /**
    * Devuelve el item del dia con respecto al indice $index
    * @param int $index
    */
    public function days_item($index){
    	$day = $this->days_all();
		return $day[$index];
    }
    /**
    * Devuelve un array con los dias de la semana
    * @param array
    */
    public function days_all(){
		$days[0]= $this->lang_item('domingo',false);
		$days[1]= $this->lang_item('lunes',false);
		$days[2]= $this->lang_item('martes',false);
		$days[3]= $this->lang_item('miercoles',false);
		$days[4]= $this->lang_item('jueves',false);
		$days[5]= $this->lang_item('viernes',false);
		$days[6]= $this->lang_item('sabado',false);
		
		return $days;
    }
    /**
    * Devuelve el item del mes con respecto al indice $index,
    * si el $index no se define devolvera un array con todos los meses
    * @param int $index
    * @return array
    */
    public function months($index = false){
		$months[0]  = $this->lang_item('enero', false);
		$months[1]  = $this->lang_item('febrero', false);
		$months[2]  = $this->lang_item('marzo', false);
		$months[3]  = $this->lang_item('abril', false);
		$months[4]  = $this->lang_item('mayo', false);
		$months[5]  = $this->lang_item('junio', false);
		$months[6]  = $this->lang_item('julio', false);
		$months[7]  = $this->lang_item('agosto', false);
		$months[8]  = $this->lang_item('septiembre', false);
		$months[9]  = $this->lang_item('octubre', false);
		$months[10] = $this->lang_item('noviembre', false);
		$months[11] = $this->lang_item('diciembre', false);
		if ($index === 0) {
			return $months[$index];
		}
		if($index){
			return $months[ltrim($index,'0')];
		}
		return $months;
    }
    /**
    * Devuelve la fecha larga de un timestamp determinado
    * @param string $timestamp
    * @param bool $time
    * @return string
    */
    public function timestamp_complete($timestamp = "" , $time = false){
		// Crea fecha larga i.e: Miércoles 06 de Mayo del 2015
    	if($timestamp==""){
			$dia=date("l");
			if ($dia=="Monday") $dia=$this->lang_item('lunes', false);
			if ($dia=="Tuesday") $dia=$this->lang_item('martes', false);
			if ($dia=="Wednesday") $dia=$this->lang_item('miercoles', false);
			if ($dia=="Thursday") $dia=$this->lang_item('jueves', false);
			if ($dia=="Friday") $dia=$this->lang_item('viernes', false);
			if ($dia=="Saturday") $dia=$this->lang_item('sabado', false);
			if ($dia=="Sunday") $dia=$this->lang_item('domingo', false);
			$dia2=date("d");
			$mes=date("F");
			if ($mes=="January") $mes=$this->lang_item('enero', false);
			if ($mes=="February") $mes=$this->lang_item('febrero', false);
			if ($mes=="March") $mes=$this->lang_item('marzo', false);
			if ($mes=="April") $mes=$this->lang_item('abril', false);
			if ($mes=="May") $mes=$this->lang_item('mayo', false);
			if ($mes=="June") $mes=$this->lang_item('junio', false);
			if ($mes=="July") $mes=$this->lang_item('julio', false);
			if ($mes=="August") $mes=$this->lang_item('agosto', false);
			if ($mes=="September")$mes=$this->lang_item('septiembre', false);
			if ($mes=="October") $mes=$this->lang_item('octubre', false);
			if ($mes=="November") $mes=$this->lang_item('noviembre', false);
			if ($mes=="December") $mes=$this->lang_item('diciembre', false);
			$anio=date("Y");

			if($time){
				$time = date('H:i:s');
				return "$dia $dia2 ". sprintf($this->lang_item('timestamp_string', false),$mes, $anio, $time);
			}
			return "$dia $dia2 ". sprintf($this->lang_item('fecha_actual', false),$mes, $anio);
		}else{
			
			$days      = $this->days();
			$timestamp = explode(' ', $timestamp);
			$time      = $timestamp[1];
			$date      = explode('-', $timestamp[0]);
			$day       = $days[intval(  (date("w",mktime(0,0,0,$date[1],$date[2],$date[0])))-1)];
			$month     = $this->months($date[1]-1);
			//$time      = date('H:m:s');
			$fecha     =  "$day ".$date[2]." ". sprintf($this->lang_item('timestamp_string', false),$month, $date[0], $time);
			
			return $fecha;
		}
	}

    /**
    * Devuelve el item de idioma con respecto al indice $index
    * @param int $index
    * @return string
    */

    public function lang_item($index, $format = true){
    	$index = strtolower(str_replace('lang_', '', trim($index)));
    	$lang_item = ($this->lang->line($index)) ? $this->lang->line($index) : 'lang_'.$index;
    	
    	if($format==true){
    		$lang_item = text_format_tpl($lang_item);
    	}
    	return $lang_item;
    }

    /**
    * carga el archivo Lang de idioma
    * @param string $name
    * @param string $lang
    * @return void
    */

    public function lang_load($name, $lang = "es_ES"){
    	$this->lang->load(trim($name,'/'),$lang);
    }
    
    /**
    * convierte un arreglo a su respectivo item lang
    * @param array $input
    * @param string $lang_
    * @param string $separator
    * @return string
    */

	public function array2string_lang($input = array(), $lang_ =array(), $separator = " "){
		$string = "";
		$land_load = (empty($lang_)) ? false : $this->lang_load($lang_[0],$lang_[1]);
		foreach ($input as $item) {
			$lang_item = $this->lang_item($item);
			$string    =  $string . $separator . $lang_item;
		}
		return trim($string, $separator);
	}

	/**
    * Devuelve la variable de session con respecto al indice $index
    * @param int $index
    * @return string
    */
	public function item_session($index){
		return $this->session->userdata($index);
	}

	/**
	* Devuelve un arreglo con mensaje y respuesta true o false
	*@param string $inicio, string $termino
	*@param array 
	*/
	public function check_time_longer($inicio, $termino){
		//print_debug($inicio);
		$response = '';
		$msg = '';
		$mk_inicio       =  explode(':', $inicio);
		$mk_termino      =  explode(':', $termino);
		$mk_inicio       =  mktime($mk_inicio[0],  $mk_inicio[1],  0,0,0,0);
		$mk_termino      =  mktime($mk_termino[0], $mk_termino[1],0,0,0,0);
		if($mk_inicio==$mk_termino){
			$msg = 'msg_hora_igual';
			$response = false;
		}else if($mk_inicio>$mk_termino){
			$msg = 'msg_horainicio_mayor';
			$response = false;
		}else{
			$msg = 'msg_insert_success';
			$response = true;
		}
		//print_debug(array('response'=> $response, 'msg' =>  $msg));
		return array('response'=> $response, 'msg' =>  $msg);
	}

	/**
    * Devuelve un arreglo con mensaje y respuesta true o false
    * @param string $inicio, string $termino, array $times
    * @return array
    */
	public function check_times_ranges($incio, $termino, $times = array()){
		$mk_inicio       =  explode(':', $incio);
		$mk_termino      =  explode(':', $termino);
		$mk_inicio       =  mktime($mk_inicio[0],  $mk_inicio[1],  0,0,0,0);
		$mk_termino      =  mktime($mk_termino[0], $mk_termino[1],0,0,0,0);

		if($mk_inicio==$mk_termino){
				$msg = 'msg_hora_igual';
				$response = false;
		}else{
			if($mk_inicio>$mk_termino){
				$msg = 'msg_horainicio_mayor';
				$response = false;
			}else{
				$contador = 0;
				if(is_array($times)){
					foreach ($times as $item) {
						if($this->validar_rango( $item['inicio'] , $item['final'], $incio)){
							$contador++;
						}
						if($this->validar_rango( $item['inicio'] , $item['final'], $termino)){
							$contador++;
						}
					}
					if($contador>0){
						$msg = 'msg_horario_empalmado';
						$response = false;
					}else{
						foreach ($times as $item) {
							if($this->validar_rango( $incio , $termino, $item['inicio'])){
								$contador++;
							}
							if($this->validar_rango( $incio , $termino, $item['final'])){
								$contador++;
							}
						}
						if($contador>0){
							$msg = 'msg_horario_empalmado';
							$response = false;
						}else{
							$msg = 'msg_insert_success';
							$response = true;
						}
					}
				}else{
					$msg = 'msg_insert_success';
					$response = true;
				}
			}
		}
		return array('response'=> $response, 'msg' =>  $msg);
	}

	/**
    * Devuelve respuesta true o false
    * @param time $inicio, time $termino, time $values
    * @return boolean
    */
	public function validar_rango($inicio, $fin, $value){
	    $inicio  = strtotime($inicio);
	    $fin     = strtotime($fin);
	    $value   = strtotime($value);
	    return (($value >= $inicio) && ($value <= $fin));
	}

	/**
	* Devuelve el valo buscado aplicando una regla de tres
	* @param double $valorA, double $valorB, double $cantidadBuscada
	* @return double
	*/
	public function regla_de_tres($valorA, $valorB, $cantidadBuscada=1){
		$resultado = ($cantidadBuscada*$valorB)/$valorA;
		return $resultado;
	}

	/**
	* Devuelve la cadena encryptada
	* @param string $string
	* @param bool $key
	* @return string
	*/
	public function encrypt_encode($string = '', $key = false){
		if($key){
			return base64_encode( $this->encrypt->encode($string, $key) );
		}else{
			return base64_encode( $this->encrypt->encode($string) );
		}
		
	}

	/**
	* Devuelve la cadena desencryptada
	* @param string $string
	* @param bool $key
	* @return string
	*/
	public function encrypt_decode($string = '', $key = false){
		if($key){
			return base64_decode( $this->encrypt->decode($string, $key) );
		}else{
			return base64_decode( $this->encrypt->decode($string) );
		}
	}

	/**
	* Genera un token 
	* @param int $length
	* @param bool $encrypt
	* @param string $key
	* @return string
	*/
	public function generate_token($length = 30, $encrypt = true , $key = ''){
		$key_encrypt = ($key) ? $key : '';

		$settings = array(
							'upercase' => true ,
							'lowercase' => true ,
							'int' => true, 
							'symbol' => true
						);

		$token =  ($encrypt) ? $this->encrypt_encode($this->random_string($length, $settings, $key_encrypt)) : $this->random_string($length,$settings, $key_encrypt);
		
		return $token;
	}

	/**
	* Genera un string con caracteres aleatorios dependiendo de la configuraciion 
	* @param int $length
	* @param array $settings
	* @param string $key
	* @return string
	*/
	public function random_string($length = 10, $settings = array(),  $key = ''){
		if(empty($settings)){
			return false;
		}
	    $characters  = '';
	    $characters .= ($key!='') ? $key : '';
	    $characters .= (array_key_exists('upercase', $settings))  ?  ($settings['upercase'])  ? 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' : '' : '';
	    $characters .= (array_key_exists('lowercase', $settings)) ?  ($settings['lowercase']) ? 'abcdefghijklmnopqrstuvwxyz' : '' : '';
	    $characters .= (array_key_exists('int', $settings))       ?  ($settings['int'])       ? '1234567890'                 : '' : '';
	    $characters .= (array_key_exists('symbol', $settings))    ?  ($settings['symbol'])    ? '|@#~$%()=^*+[]{}-_'  : '' : '';
	    
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}

	/**
	* Devuelve la diferencia de tiempo entre dos fecha u horas
	* @param string $inicio
	* @param string $termino
	* @param string $type
	* @return string
	*/
	public function time_diff($inicio, $termino, $type = 'd'){
		switch ($type) {
			case 'd':
				$sub = 86400; //strtotime("+1 day", $i)
				break;
			case 'm':
				$sub = 60;
				break;
			default:
				$sub = false;
				break;
		}

		if($sub){
			$time_diff = (strtotime($inicio)-strtotime($termino))/$sub;
			$time_diff = abs($time_diff); $time_diff = floor($time_diff);
			return $time_diff;
		}else{
			return null;
		}
		
	}

	/**
	* Valida una direccion de correo electronico
	* @param string $mail
	* @return boolean
	*/
	public function validate_email($mail){
	   	$exp ='#^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,6}$#';
	   	if(preg_match($exp, $mail)){
	     	return true;
	   	}
	   	else{
	    	return false;
	   	}
	}


	public function multisort($arr, $orderby = ''){
		$sortArray = array(); 

		foreach($arr as $item){ 
		    foreach($item as $key=> $value){ 
		        if(!isset($sortArray[$key])){ 
		            $sortArray[$key] = array(); 
		        } 
		        $sortArray[$key][] = $value; 
		    } 
		} 

		array_multisort($sortArray[$orderby],SORT_ASC,$arr);

		return $arr;
	}

	public function cadena_aleatoria($num_caracteres = 10){
		$caracteres = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
		$cadena = ""; 
		for($i=0;$i<$num_caracteres;$i++){
		    $cadena .= substr($caracteres,rand(0,strlen($caracteres)),1);  
		}
		return $cadena;
	}	

	
}
?>