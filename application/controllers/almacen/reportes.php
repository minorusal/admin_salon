<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class reportes extends Base_Controller{
	private $modulo;
	private $submodulo;
	private $seccion;
	private $view_content, $uri_view_principal;
	private $path;
	private $icon;
	private $offset, $limit_max;
	private $tab_inicial, $tab = array(), $tab_indice = array();
/*5542495381*/
	public function __construct(){
		parent::__construct();
		$this->vars = new config_vars();
        $this->vars->load_vars();
		$this->modulo 			= 'almacen';
		$this->submodulo        = 'catalogos';
		$this->seccion          = 'reportes';
		$this->template 		= 'almacen/catalogos/reportes/reportes_view';
		$this->icon 			= 'fa  fa-list-alt'; //Icono de modulo
		$this->path 			= $this->modulo.'/'.$this->seccion.'/'; //almacen/entradas_recepcion/
		$this->view_content 	= 'content';
		$this->limit_max		= 10;
		$this->offset			= 0;
		// Tabs
		$this->tab1 			= 'agregar';
		$this->tab2 			= 'listado';
		$this->tab3 			= 'detalle';
		// DB Model
		$this->load->model($this->modulo.'/'.$this->seccion.'_model','db_model');
		$this->load->model('almacen/catalogos_model','almacenes');
		$this->load->model('administracion/regiones_model','regiones');
		$this->load->model('almacen/catalogos_model','almacenes');

		// Diccionario
		$this->lang->load($this->modulo.'/'.$this->seccion,"es_ES");
		// Tabs
		$this->tab_inicial 		= 2;
		$this->tab_indice 		= array(
									 $this->tab1
									,$this->tab2
									,$this->tab3
								);
		for($i=0; $i<=count($this->tab_indice)-1; $i++){
			$this->tab[$this->tab_indice[$i]] = $this->tab_indice[$i];
		}
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
		$this->load_database('global_system');
        $this->load->model('users_model');
        $sqlData        = array(
			 'buscar'      	=> ''
			,'offset' 		=> 0
			,'limit'      	=> 0
		);	
		$region_array = array(
							'data'		=> $this->regiones->db_get_data($sqlData)
							,'value' 	=> 'id_administracion_region'
							,'text' 	=> array('region')
							,'name' 	=> "id_administracion_region"
							,'class' 	=> "requerido"
							,'event'    => array('event'       => 'onchange',
							   						 'function'    => 'carga_almacen',
							   						 'params'      => array('this.value'),
							   						 'params_type' => array(0)
			   										)
						);
		$list_regiones = dropdown_tpl($region_array);

		$almacen_array = array(
							'name' 	 => "id_almacen_almacenes"
							,'class' => "requerido"
						);
		$list_almacenes = dropdown_tpl($almacen_array);
		
		//$tabl_inicial 			  = 1;
		$contenidos_tab           = $this->mensaje;
		$data['titulo_seccion']   = $this->lang_item('titulo_seccion');
		$data['titulo_submodulo'] = $this->lang_item('titulo_submodulo');
		$data['icon']             = $this->icon;
		$data['lbl_region']		  = $this->lang_item('lbl_region');
		$data['list_region']	  = $list_regiones;
		$data['lbl_almacen']	  = $this->lang_item('lbl_almacen');
		$data['list_almacen']	  = $list_almacenes;
		//$data['almacen_btn']      = button_tpl($buttonTPL);
		$data['tabs']             = tabbed_tpl($this->config_tabs(),base_url(),$tabl_inicial,$contenidos_tab);	
		$js['js'][]  = array('name' => 'reportes', 'dirname' => 'almacen');
		$this->load_view($this->template, $data,$js);	
	}

	public function carga_almacen(){
		$id_region = $this->ajax_post('id_region');
		$almacen   = $this->db_model->db_get_almacen_x_region($id_region);
		if($almacen){
			$almacen_array = array(
								 'data'		=> $almacen
								,'text' 	=> array('almacenes')
								,'value' 	=> 'id_almacen_almacenes'
								,'name' 	=> "id_almacen_almacenes"
								,'class' 	=> "requerido"
								,'event'    => array('event'       => 'onchange',
							   						 'function'    => 'carga_btn_excel',
							   						 'params'      => array('this.value'),
							   						 'params_type' => array(0)
			   										)
						);
			$list_almacenes = dropdown_tpl($almacen_array);
			
		}else{
			$list_almacenes = $this->lang_item('lbl_alert');
		}
		echo json_encode(array('list_almacenes' => $list_almacenes, 'btn_almacen' => $btn_save));
	}

	public function carga_btn_excel(){
		$filtro = $this->ajax_post('id_almacen');
		$buttonTPL = array( 'text'       => $this->lang_item("btn_almacen"), 
							'iconsweets' => 'fa fa-file-excel-o',
							'href'       => base_url($this->path.'export_xlsx?filtro='.base64_encode($filtro))
							);

		$buttonSGTPL = array( 'text'       => $this->lang_item("stock_general"), 
							'iconsweets' => 'fa fa-file-excel-o',
							'href'       => base_url($this->path.'export_SGxlsx?filtro='.base64_encode($filtro))
							);
		$btn_excel_almacen    = button_tpl($buttonTPL);
		$btn_excel_stock_gral = button_tpl($buttonSGTPL);
		echo json_encode(array('btn_almacen' => $btn_excel_almacen, 'btn_stock_gral' => $btn_excel_stock_gral));
	}
	
	public function export_xlsx(){
		$filtro       = ($this->ajax_get('filtro')) ?  base64_decode($this->ajax_get('filtro') ): "";
		$lts_content  = $this->almacenes->get_db_almacen($filtro);
		$lts_pasillos = $this->almacenes->get_db_pasillos_x_almacen($filtro);
		$lts_gabetas  = $this->almacenes->get_db_gabetas_x_almacen($filtro);
		
		if(count($lts_content)>0){
			foreach ($lts_content as $value){
				$set_data[] = array(
									 $value['cv_almacen']
									,$value['desc_almacen']
									,$value['almacenes']
									,$value['sucursal']
									,$value['tipos']
									);
			}
			if(count($lts_pasillos) > 0){
				foreach($lts_pasillos as $value) {
					$set_data_pasillo[] = array(
										 $value['clave_corta']
										,$value['descripcion']
										,$value['pasillos']
						);
				}
				$set_heading_pasillos = array(
										$this->lang_item("lbl_clave_pasillo"),
										$this->lang_item("lbl_desc_pasillo"),
										$this->lang_item("lbl_pasillo")
										);

			}

			if(count($lts_gabetas['cuantos'] > 0)){
				foreach($lts_gabetas as $values){
					$set_data_gabeta[] = array(
												 $values['clave_corta']
												,$values['descripcion']
												,$values['gavetas']
											  );
				}

				$set_heading_gabetas = array(
												$this->lang_item('lbl_clave_gabeta')
											   ,$this->lang_item('lbl_desc_gabeta')
											   ,$this->lang_item('lbl_gabetas')
											);
			}

			$set_heading = array(
										$this->lang_item("lbl_clave_almacen"),
										$this->lang_item("lbl_desc_almacen"),
										$this->lang_item("lbl_almacen"),
										$this->lang_item("lbl_sucursal"),
										$this->lang_item("lbl_tipo")
										);
				
		}

		$params = array(	'title'            => $lts_content[0]['almacenes'],
							'items'            => $set_data,
							'headers'          => $set_heading,
							'items_pasillo'    => ($set_data_pasillo)?$set_data_pasillo:array(array($this->lang_item('lbl_sin_pasillo'))),
							'headers_pasillos' => ($set_heading_pasillos)?$set_heading_pasillos:array(array($this->lang_item("lbl_pasillo"))),
							'items_gabeta'     => ($set_data_gabeta)?$set_data_gabeta:array(array($this->lang_item('lbl_sin_gabeta'))),
							'headers_gabetas'  => ($set_heading_gabetas)?$set_heading_gabetas:array(array($this->lang_item("lbl_gabeta")))
						);
		$this->excel->almacenes_xlsx($params);
	}

	public function export_SGxlsx(){
		$filtro       = ($this->ajax_get('filtro')) ?  base64_decode($this->ajax_get('filtro') ): "";
		$lts_content = $this->db_model->db_get_stock_general($filtro);
		if(count($lts_content)>0){
			foreach ($lts_content as $value){
				$set_data[] = array(
									 $value['almacen_cve']
									,$value['pasillo_cve']
									,$value['gaveta_cve']
									,$value['articulo_tipo']
									,$value['linea']
									,$value['upc']
									,$value['sku']
									,$value['articulo']
									,$value['marca']
									,$value['presentacion']
									,$value['embalaje']
									,$value['presentacion_x_embalaje']
									,$value['proveedor_razon_social']
									,$value['stock']
									,$value['stock_um']
									,$value['stock_um_cve']
									,$value['lote']
									,$value['caducidad']
									,$value['stock_valor']
									,$value['cantidad_adquirida']
									,$value['costo_unitario']
									,$value['subtotal']
									,$value['descuento']
									,$value['impuesto']
									,$value['total']
									);
			}
			$set_heading = array(
							 $this->lang_item("lbl_clave_almacen")
							,$this->lang_item("lbl_clave_pasillo")
							,$this->lang_item("lbl_clave_gabeta")
							,$this->lang_item("lbl_tipo_articulo")
							,$this->lang_item("lbl_linea")
							,$this->lang_item("lbl_upc")
							,$this->lang_item("lbl_sku")
							,$this->lang_item("lbl_articulo")
							,$this->lang_item("lbl_marca")
							,$this->lang_item("lbl_presentación")
							,$this->lang_item("lbl_embalaje")
							,$this->lang_item("lbl_pre_x_embalaje")
							,$this->lang_item("lbl_prov_rsocial")
							,$this->lang_item("lbl_stock")
							,$this->lang_item("lbl_stock_um")
							,$this->lang_item("lbl_stock_um_cve")
							,$this->lang_item("lbl_lote")
							,$this->lang_item("lbl_caducidad")
							,$this->lang_item("lbl_stock_valor")
							,$this->lang_item("lbl_cant_adquirida")
							,$this->lang_item("lbl_costo_unitario")
							,$this->lang_item("lbl_subtotal")
							,$this->lang_item("lbl_descuento")
							,$this->lang_item("lbl_impuesto")
							,$this->lang_item("lbl_total")
							);
		}


		$params = array(	'title'    => ('Stock en '.$value['almacen_cve'])?'Stock en '.$value['almacen_cve']:$this->lang_item("lbl_excel"),
							'items'    => ($set_data)?$set_data:array($this->lang_item("lbl_sin")),
							'headers'  => ($set_heading)?$set_heading:array($this->lang_item("lbl_sin"))
							);
		$this->excel->generate_xlsx($params);
	}

}