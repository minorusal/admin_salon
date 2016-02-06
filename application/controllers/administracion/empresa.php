<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class empresa extends Base_Controller {

	private $modulo;
	private $submodulo;
	private $seccion;
	//private $view_content;
	private $path;
	private $icon;

	private $offset, $limit_max;
	private $tab, $tab1;

	private $titulo, $mensaje, $template;
	
	public function __construct(){
		parent::__construct();
		$this->modulo 			= 'administracion';
		$this->submodulo 		= 'empresa';
		$this->icon 			= 'fa fa-building-o'; 
		$this->template 		= 'contentInfo';
		$this->path 			= $this->modulo.'/'.$this->submodulo.'/'; 
		$this->view_content 	= 'contentInfo';
		$this->limit_max		= 5;
		$this->offset			= 0;
		// Tabs
		$this->tab1 			= 'inicio';
		$this->load->model($this->modulo.'/'.$this->submodulo.'s_model','db_model');
		// Diccionario
		$this->lang->load($this->modulo.'/'.$this->submodulo,"es_ES");
	}

	public function config_tabs(){
		$tab_1 	= $this->tab1;
		$path  	= $this->path;
		// Nombre de Tabs
		$config_tab['names']    = array( $this->titulo ); 
		// Href de tabs
		$config_tab['links']    = array($path.$tab_1 ); 
		// Accion de tabs
		$config_tab['action']   = array('');
		// Atributos 
		$config_tab['attr']     = array('');
		return $config_tab;
	}

	public function index(){
		$sqlData = array(
			 'buscar'      	=> ''
			,'offset' 		=> 0
			,'limit'      	=> true
		);
		$this->load_database('global_system');
        $this->load->model('users_model');
		$datos = $this->db_model->db_get_data($sqlData);
		$pais = $this->users_model->search_user_for_id($datos[0]['id_usuario']);
		$button_save  = form_button(array('class'=>'btn btn-primary', 'name'=>'save_empresa', 'onclick'=>'agregar()','content'=>'Guardar'));
		$tabl_inicial 			  = 1;
		$contenidos_tab           = $this->mensaje;
		$data['titulo_seccion']   = $this->lang_item('titulo_seccion');
		$data['titulo_submodulo'] = $this->lang_item('titulo_submodulo');
		$data['icon']             = $this->icon;
		$data['Titulo']           = $this->titulo;
		$data['id_empresa']       = $datos[0]['id_empresa'];
		$data['lbl_empresa']      = $this->lang_item('titulo_seccion');
		$data['txt_empresa']      = $datos[0]['empresa'];
		$data['lbl_logotipo']     = $this->lang_item('logotipo');
		$data['imagen']           = base_url().'assets/images/empresa.jpg';
		$data['lbl_razon_social'] = $this->lang_item('razon_social');
		$data['txt_razon_social'] = $datos[0]['razon_social'];
		$data['lbl_rfc']          = $this->lang_item('r_f_c');
		$data['txt_rfc']          = $datos[0]['rfc'];
		$data['lbl_pais']         = $this->lang_item('pais');
		$data['txt_pais']         = base_url().'assets/avatar/'.$pais[0]['avatar_pais'];
		$data['lbl_moneda']       = $this->lang_item('moneda');
		$data['txt_moneda']       = $pais[0]['moneda'];
		$data['lbl_telefono']     = $this->lang_item('telefono');
		$data['txt_telefono']     = $datos[0]['telefono'];
		$data['lbl_direccion']    = $this->lang_item('direccion');
		$data['txt_direccion']    = $datos[0]['direccion'];
		$data['button_save']      = $button_save;
		$data['tabs']             = tabbed_tpl($this->config_tabs(),base_url(),$tabl_inicial,$contenidos_tab);	
		$js['js'][]  = array('name' => 'empresas', 'dirname' => 'administracion');
		$this->load_view($this->template, $data,$js);	
	}

	public function insert(){
		$objData  	= $this->ajax_post('objData');
		if($objData['incomplete']>0){
			$msg = $this->lang_item("msg_campos_obligatorios",false);
			echo json_encode( array( 'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)) );
		}else{
			$data_insert = array(
				  'id_empresa'   => $objData['id_empresa']
				 ,'empresa'      => $objData['txt_empresa']
				 ,'razon_social' => $objData['txt_razon_social']
				 ,'rfc'          => $objData['txt_rfc']
				 ,'telefono'     => $objData['txt_telefono']
				 ,'direccion'    => $objData['txt_direccion']
				 ,'id_usuario'   => $this->session->userdata('id_usuario')
				 ,'timestamp'    => $this->timestamp()
				);


			$insert = $this->db_model->db_update_data($data_insert);

			if($insert){
				$msg = $this->lang_item("msg_insert_success",false);
				echo json_encode(array(  'success'=>'true', 'mensaje' => $msg));
			}else{
				$msg = $this->lang_item("msg_err_clv",false);
				echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('', $msg ,false)));
			}
		}
	}
}