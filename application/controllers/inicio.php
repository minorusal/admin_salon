<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class inicio extends Base_Controller {

	private $modulo;
	private $submodulo;
	private $seccion;
	private $view_content;
	private $path;
	private $icon;

	private $offset, $limit_max;
	private $tab, $tab1;

	private $titulo, $mensaje, $template;
	
	public function __construct(){
		parent::__construct();
		$this->modulo 			= 'inicio';
		$this->icon 			= 'iconfa-laptop'; 
		$this->template 		= 'content';
		$this->path 			= $this->modulo.'/'.$this->seccion.'/'; 
		$this->view_content 	= 'content';
		$this->limit_max		= 5;
		$this->offset			= 0;
		$this->titulo 			= 'Inicio';
		$this->mensaje 			= '<h4>Bienvenido al sistema AdminControl.</h4> <br/>Por favor seleccione una opción del menú lateral.';
		// Tabs
		$this->tab1 			= 'inicio';

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

		$tabl_inicial 			  = 1;
		$contenidos_tab           = $this->mensaje;
		$data['titulo_seccion']   = $this->titulo;
		$data['titulo_submodulo'] = 'Bienvenido';
		$data['contenido']        = $this->mensaje;
		$data['icon']             = $this->icon;
		$data['tabs']             = tabbed_tpl($this->config_tabs(),base_url(),$tabl_inicial,$contenidos_tab);	
		
		$this->load_view($this->template, $data);	
	}
}