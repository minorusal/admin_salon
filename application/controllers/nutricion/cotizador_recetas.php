<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class cotizador_recetas extends Base_Controller{
	private $modulo;
	private $submodulo;
	private $seccion;
	private $view_content;
	private $path;
	private $icon;

	private $offset, $limit_max;
	private $tab, $tab1, $tab2, $tab3;

	public function __construct(){
		parent::__construct();
		$this->modulo 			= 'nutricion';
		$this->seccion          = 'cotizador_recetas';
		$this->icon 			= 'iconfa-align-justify'; 
		$this->path 			= $this->modulo.'/'.$this->seccion.'/'; 
		$this->view_content 	= 'content';
		$this->limit_max		= 5;
		$this->offset			= 0;
		// Tabs
		$this->tab1 			= 'familias';
		$this->tab2 			= 'grupos';
		$this->tab3 			= 'insumos';
		// DB Model
		$this->load->model($this->modulo.'/'.$this->seccion.'_model','db_model');
		$this->load->model('sucursales/listado_sucursales_model','sucursales');
		// Diccionario
		$this->lang->load($this->modulo.'/'.$this->seccion,"es_ES");
	}

	public function config_tabs(){
		$tab_1 	= $this->tab1;
		$tab_2 	= $this->tab2;
		$tab_3 	= $this->tab3;
		$path  	= $this->path;
		//$pagina =(is_numeric($this->uri_segment_end()) ? $this->uri_segment_end() : "");
		// Nombre de Tabs
		$config_tab['names']    = array(
										 $this->lang_item($tab_1)
										,$this->lang_item($tab_2)
										,$this->lang_item($tab_3)
								); 
		// Href de tabs
		$config_tab['links']    = array(
										 $path.$tab_1             
										,$path.$tab_2
										,$path.$tab_3                   
								); 
		// Accion de tabs
		$config_tab['action']   = array('load_content','load_content','load_content');
		// Atributos 
		$config_tab['attr']     = array('','','');
		$config_tab['style_content'] = array('','','');

		return $config_tab;
	}
	private function uri_view_principal(){
		return $this->modulo.'/'.$this->view_content;
	}

	public function index(){
		$tabl_inicial 			  = 1;
		$view_familia    		  = $this->familias();	
		$contenidos_tab           = $view_familia;
		$data['titulo_seccion']   = $this->lang_item($this->seccion);
		$data['titulo_submodulo'] = $this->lang_item("titulo_submodulo");
		$data['icon']             = $this->icon;
		$data['tabs']             = tabbed_tpl($this->config_tabs(),base_url(),$tabl_inicial,$contenidos_tab);	
		
		$js['js'][]  = array('name' => $this->seccion, 'dirname' => $this->modulo);
		$this->load_view($this->uri_view_principal(), $data, $js);
	}

	public function familias(){
		$uri_view = "nutricion/cotizador_recetas/cotizador_familias";
		$dropdown_sucursales = array(
						 'data'		=> $this->sucursales->db_get_data($sqlData)
						,'value' 	=> 'id_sucursal'
						,'text' 	=> array('sucursal')
						,'name' 	=> "lts_sucursales"
						,'leyenda' 	=> "-----"
						,'class' 	=> "requerido"
						,'event'    => array('event'      => 'onchange', 
											'function'    => 'load_familias', 
											'params'      => array('this.value'), 
											'params_type' => array(0))
					);
		$sucursales = dropdown_tpl($dropdown_sucursales);
		$tabData['lbl_sucursal'] = $this->lang_item('lbl_sucursal');
		$tabData['sucursales']   = $sucursales;
		if($this->ajax_post(false)){
			echo json_encode( $this->load_view_unique($uri_view , $tabData, true));
		}else{
			return $this->load_view_unique($uri_view , $tabData, true);
		}
	}

	public function load_familias(){
		$id_sucursal = $this->ajax_post('id_sucursal');
		$selected_family = $this->db_model->db_get_family_selected($id_sucursal);
		if($selected_family){
			foreach ($selected_family as $value){
				$id_familia[]  = $value['id_familia'];
			}
		}
		$familias = $this->db_model->db_get_familia_by_sucursal($id_sucursal);
		$dropdown_familias = array(
						 'data'		=> $familias
						,'value' 	=> 'id_nutricion_familia'
						,'text' 	=> array('familia')
						,'name' 	=> "lts_familias"
						,'leyenda' 	=> "-----"
						,'class' 	=> "requerido"
						,'selected' => $id_familia
					);
		$familias_list = multi_dropdown_tpl($dropdown_familias);
		$array_btn_save = array(
			                    'class'   => 'btn btn-primary'
			                   ,'name'    => 'cotizar'
			                   ,'onclick' => 'guarda_cotizacion_by_familia()'
			                   ,'content' => $this->lang_item("btn_cotizar")
			                   );
		$btn_cotizar = form_button($array_btn_save);
		$array_elements = array(
						  'lbl_familia'  => $this->lang_item('lbl_familia')
						 ,'familia_list' => $familias_list
						 ,'btn_cotizar'  => $btn_cotizar
						  );
		echo json_encode($array_elements);
	}

	public function insert_cotizacion_familia(){
		$objData = $this->ajax_post('objData');
		if($objData['incomplete'] == 0 && $objData['lts_familias'] > 0){
			$delete = $this->db_model->eliminar_registro_familia_sucursal($objData['lts_sucursales']);
			if($delete){
				$arr_familia    = explode(',',$objData['lts_familias']);
				$sqlData = array();
				foreach ($arr_familia as $key => $value){
					$sqlData = array(
								 'id_familia'      => $value
								,'id_sucursal'	   => $objData['lts_sucursales']
								,'id_usuario'      => $this->session->userdata('id_usuario')
								,'timestamp'       => $this->timestamp()
								);
					$insert = $this->db_model->db_insert_cotizacion_familia($sqlData);
				}
			}

			$sqlData = array(
							'lista_familias' => $objData['lts_familias']
						   ,'id_sucursal'    => $objData['lts_sucursales']    
				            );
			$recetas = $this->db_model->db_get_recetas_from_familias($sqlData);
			//print_debug($recetas);
			if($recetas){
				$costo_porcion = 0;
				$count_receta_sin_precio = 0;
				foreach ($recetas as $value) {
					$recetas_total[$value['receta']] = $value['receta'];
					if($value['costo_x_um']==''){
						$insumos_sin_costo[] = $value['receta'];
						$count_receta_sin_precio++;
						$costo_porcion = 0;
					}else{
						if(in_array($value['receta'], $value)){
							$costo_porcion = $costo_porcion + (($value['porciones_articulo'] * $value['costo_x_um'])/$value['porciones']);	
							$insumos_con_costo[$value['receta']] = $costo_porcion;
						}
					}
				}
				$count_recetas = count($recetas_total);
				//print_debug($insumos_con_costo);
				if ($insumos_con_costo) {
		     		if(($count_receta_sin_precio-1) == $count_recetas){
						$array_json = array('success' => false, 'contenido' => 'No existen recetas en estas familias para esta sucursal');
					}else{
						foreach ($insumos_con_costo as $key => $value) {
							$suma_precio_receta = $suma_precio_receta + $value;
							// Datos para tabla
							$tbl_data[] = array('recetas_o'  => $key,
												'recetas'  => $key,
												'costo'    => '$ '.$value,
												);

						}
						$promedio = $suma_precio_receta/$count_recetas;
						$tbl_data[] = array(
							                'total_o'    => $suma_precio_receta
							               ,'total'      => '<strong>'.$this->lang_item('total',false).'</strong>'
							               ,'suma_total' => '$ '.$suma_precio_receta
							               );
						$tbl_data[] = array(
							                'promedio_o'     => $promedio
							               ,'promedio'       => '<strong>'.$this->lang_item('promedio',false).'</strong>'
							               ,'promedio_total' => $promedio
							               );
						// Plantilla
						$tbl_plantilla = set_table_tpl();
						// Titulos de tabla
						$this->table->set_heading(	'id',
													$this->lang_item("recetas"),
													$this->lang_item("costo_x_receta"));
						// Generar tabla
						$plantilla_column = array ( 'table_open'  => '<table class="table table-striped responsive" >' );
						$this->table->set_template($plantilla_column);
						$this->table->set_template($plantilla_column);
						$tabla = $this->table->generate($tbl_data);
						$array_json = array('success' => true, 'contenido' => $tabla);
					}
				}else{
					$array_json = array('success' => false, 'contenido' => 'Las recetas de esta familia no tienen precios definidos');
				}
			}else{
				$array_json = array('success' => false, 'contenido' => 'No existen recetas en estas familias para esta sucursal');
			}
		}else{
			$msg = $this->lang_item("msg_err_familia_vacio");
			$array_json = array('success' => 'vacio', 'contenido' => alertas_tpl('error', $msg ,false));
		}
		echo json_encode($array_json);
	}

	public function grupos(){
		$uri_view = "nutricion/cotizador_recetas/cotizador_recetas";
		$dropdown_sucursales = array(
						 'data'		=> $this->sucursales->db_get_data($sqlData)
						,'value' 	=> 'id_sucursal'
						,'text' 	=> array('sucursal')
						,'name' 	=> "lts_sucursales"
						,'leyenda' 	=> "-----"
						,'class' 	=> "requerido"
						,'event'    => array('event'      => 'onchange', 
											'function'    => 'load_grupos', 
											'params'      => array('this.value'), 
											'params_type' => array(0))
					);
		$sucursales = dropdown_tpl($dropdown_sucursales);
		$tabData['lbl_sucursal'] = $this->lang_item('lbl_sucursal');
		$tabData['sucursales']   = $sucursales;
		if($this->ajax_post(false)){
			echo json_encode( $this->load_view_unique($uri_view , $tabData, true));
		}else{
			return $this->load_view_unique($uri_view , $tabData, true);
		}
	}

	public function load_grupos(){
		$id_sucursal = $this->ajax_post('id_sucursal');
		$grupos = $this->db_model->db_get_grupo_by_sucursal($id_sucursal);
		$dropdown_grupos = array(
						 'data'		=> $grupos
						,'value' 	=> 'id_nutricion_grupos'
						,'text' 	=> array('clave_corta','grupo')
						,'name' 	=> "lts_grupos"
						,'leyenda' 	=> "-----"
						,'class' 	=> "requerido"
						,'event'    => array('event'      => 'onchange', 
											'function'    => 'load_recetas', 
											'params'      => array('this.value',$id_sucursal), 
											'params_type' => array(0,0))
					);
		$grupos_list = dropdown_tpl($dropdown_grupos);
		$recetas = $this->db_model->db_get_receta_by_sucursal($id_sucursal);
		$array_elements = array(
						  'lbl_grupo'     => $this->lang_item('lbl_grupo')
						 ,'grupos_list'   => $grupos_list
						 ,'btn_cotizar'   => $btn_cotizar
						  );
		echo json_encode($array_elements);
	}

	public function load_recetas(){
		$id_grupo       = $this->ajax_post('id_grupo');
		$id_sucursal    = $this->ajax_post('id_sucursal');
		$array_btn_save = array(
			                    'class'   => 'btn btn-primary'
			                   ,'name'    => 'cotizar'
			                   ,'onclick' => 'guarda_cotizacion_by_receta()'
			                   ,'content' => $this->lang_item("btn_cotizar")
			                   );
		$btn_cotizar = form_button($array_btn_save);
		$recetas = $this->db_model->db_get_receta_by_sucursal($id_sucursal);
		$selected_receta = $this->db_model->db_get_receta_selected($id_grupo);
		if($selected_receta){
			foreach ($selected_receta as $value){
				$id_receta[]  = $value['id_receta'];
			}
		}

		$dropdown_recetas = array(
						 'data'		=> $recetas
						,'value' 	=> 'id_nutricion_receta'
						,'text' 	=> array('receta')
						,'name' 	=> "lts_receta"
						,'leyenda' 	=> "-----"
						,'class' 	=> "requerido"
						,'selected' => $id_receta
					);
		$recetas_list = multi_dropdown_tpl($dropdown_recetas);
		$array_elements = array(
						  'lbl_familia'   => $this->lang_item('lbl_familia')
						 ,'recetas_list'  => $recetas_list
						 ,'btn_cotizar'   => $btn_cotizar
						  );
		echo json_encode($array_elements);
	}


	public function insert_cotizacion_receta(){
		$objData = $this->ajax_post('objData');

		if($objData['incomplete'] == 0 && $objData['lts_receta'] > 0){
			$delete = $this->db_model->eliminar_registro_grupo_receta($objData['lts_grupos']);
			if($delete){
				$arr_receta    = explode(',',$objData['lts_receta']);
				$sqlData = array();
				foreach ($arr_receta as $key => $value){
					$sqlData = array(
								 'id_grupo'	       => $objData['lts_grupos']
								,'id_receta'       => $value
								,'id_usuario'      => $this->session->userdata('id_usuario')
								,'timestamp'       => $this->timestamp()
								);
					$insert = $this->db_model->db_insert_cotizacion_receta($sqlData);
				}
			}
			$sqlData = array(
							'lista_recetas'  => $objData['lts_receta']
						   ,'id_grupo'       => $objData['lts_grupos']    
				            );
			$recetas = $this->db_model->db_get_recetas_from_grupos($sqlData);
			if($recetas){
				$costo_porcion = 0;
				$count_receta_sin_precio = 0;
				foreach ($recetas as $value) {
					$recetas_total[$value['receta']] = $value['receta'];
					if($value['costo_x_um']==''){
						$insumos_sin_costo[] = $value['receta'];
						$count_receta_sin_precio++;
						$costo_porcion = 0;
					}else{
						if(in_array($value['receta'], $value)){
							$costo_porcion = $costo_porcion + (($value['porciones_articulo'] * $value['costo_x_um'])/$value['porciones']);	
							$insumos_con_costo[$value['receta']] = $costo_porcion;
						}
					}
				}
				$count_recetas = count($recetas_total);
				if ($insumos_con_costo) {
					//print_debug($insumos_con_costo);
					if(($count_receta_sin_precio-1) == $count_recetas){
						$array_json = array('success' => false, 'contenido' => 'No existen recetas en estas familias para esta sucursal');
					}else{
						foreach ($insumos_con_costo as $key => $value) {
							//print_debug($insumos_con_costo);
							$suma_precio_receta = $suma_precio_receta + $value;
							// Datos para tabla
							$tbl_data[] = array('recetas_o'  => $key,
												'recetas'  => $key,
												'costo'    => '$ '.$value,
												);

						}
						$promedio = $suma_precio_receta/$count_recetas;
						$tbl_data[] = array(
							                'total_o'    => $suma_precio_receta
							               ,'total'      => '<strong>'.$this->lang_item('total',false).'</strong>'
							               ,'suma_total' => '$ '.$suma_precio_receta
							               );
						$tbl_data[] = array(
							                'promedio_o'     => $promedio
							               ,'promedio'       => '<strong>'.$this->lang_item('promedio',false).'</strong>'
							               ,'promedio_total' => $promedio
							               );
						// Plantilla
						$tbl_plantilla = set_table_tpl();
						// Titulos de tabla
						$this->table->set_heading(	'id',
													$this->lang_item("recetas"),
													$this->lang_item("costo_x_receta"));
						// Generar tabla
						$plantilla_column = array ( 'table_open'  => '<table class="table table-striped responsive" >' );
						$this->table->set_template($plantilla_column);
						$this->table->set_template($plantilla_column);
						$tabla = $this->table->generate($tbl_data);
						$array_json = array('success' => true, 'contenido' => $tabla);
					}
				}else{
					$array_json = array('success' => false, 'contenido' => 'Las recetas de esta familia no tienen precios definidos');
				}
			}else{
				$array_json = array('success' => false, 'contenido' => 'No existen recetas en estas familias para esta sucursal');
			}
		}else{
			$msg = $this->lang_item("msg_err_receta_vacio");
			$array_json = array('success' => 'vacio', 'contenido' => alertas_tpl('error', $msg ,false));
		}
		echo json_encode($array_json);
	}

	public function insumos(){
		$uri_view = "nutricion/cotizador_recetas/cotizador_insumos";
		$dropdown_sucursales = array(
						 'data'		=> $this->sucursales->db_get_data($sqlData)
						,'value' 	=> 'id_sucursal'
						,'text' 	=> array('sucursal')
						,'name' 	=> "lts_sucursales"
						,'leyenda' 	=> "-----"
						,'class' 	=> "requerido"
						,'event'    => array('event'      => 'onchange', 
											'function'    => 'load_insumos', 
											'params'      => array('this.value'), 
											'params_type' => array(0))
					);
		$sucursales = dropdown_tpl($dropdown_sucursales);
		$tabData['lbl_sucursal'] = $this->lang_item('lbl_sucursal');
		$tabData['sucursales']   = $sucursales;
		if($this->ajax_post(false)){
			echo json_encode( $this->load_view_unique($uri_view , $tabData, true));
		}else{
			return $this->load_view_unique($uri_view , $tabData, true);
		}
	}

	public function load_insumos(){
		$id_sucursal = $this->ajax_post('id_sucursal');
		$array_btn_save = array(
			                    'class'   => 'btn btn-primary'
			                   ,'name'    => 'cotizar'
			                   ,'onclick' => 'guarda_cotizacion_by_insumo()'
			                   ,'content' => $this->lang_item("btn_cotizar")
			                   );
		$btn_cotizar = form_button($array_btn_save);
		$insumo = $this->db_model->db_get_insumos_by_sucursal($id_sucursal);
		$selected_insumo = $this->db_model->db_get_insumo_selected($id_sucursal);
		//print_debug($selected_insumo);
		if($selected_insumo){
			foreach ($selected_insumo as $value){
				$id_insumo[]  = $value['id_articulo'];
			}
		}
		$dropdown_insumos = array(
						 'data'		=> $insumo
						,'value' 	=> 'id_articulo'
						,'text' 	=> array('articulo_nombre')
						,'name' 	=> "lts_insumo"
						,'leyenda' 	=> "-----"
						,'class' 	=> "requerido"
						,'selected' => $id_insumo
					);
		$insumos_list = multi_dropdown_tpl($dropdown_insumos);
		$array_elements = array(
						  'lbl_familia'   => $this->lang_item('lbl_insumo')
						 ,'insumos_list'  => $insumos_list
						 ,'btn_cotizar'   => $btn_cotizar
						  );
		echo json_encode($array_elements);
	}

	public function guarda_cotizacion_by_insumo(){
		$objData = $this->ajax_post('objData');
		if($objData['incomplete'] == 0 && $objData['lts_insumo'] > 0){
			$delete = $this->db_model->eliminar_registro_insumo_sucursal($objData['lts_sucursales']);
			$arr_insumo    = explode(',',$objData['lts_insumo']);
				$sqlData = array();
				foreach ($arr_insumo as $key => $value){
					$sqlData = array(
								 'id_sucursal'	   => $objData['lts_sucursales']
								,'id_articulo'     => $value
								,'id_usuario'      => $this->session->userdata('id_usuario')
								,'timestamp'       => $this->timestamp()
								);
					$insert = $this->db_model->db_insert_cotizacion_insumo($sqlData);
				}
				$sqlData = array(
							'lts_sucursales'  => $objData['lts_sucursales']
						   ,'lts_insumos'     => $objData['lts_insumo']    
				            );
			$insumos = $this->db_model->db_get_insumos_from_sucursales($sqlData);
			if ($insumos) {
				foreach ($insumos as $value) {
					$insumos_total[$value['articulo_nombre']] = $value['articulo_nombre'];
					if(in_array($value['articulo_nombre'], $value)){
							$costo_porcion = $costo_porcion + (($value['porciones_articulo'] * $value['costo_x_um'])/$value['porciones']);	
							$insumos_con_costo[$value['articulo_nombre']] = $costo_porcion;
						}
				}
				$suma_precio_insumo = 0;
				$count_insumos = count($insumos_total);
				foreach ($insumos_con_costo as $key => $value) {
					$suma_precio_insumo = $suma_precio_insumo + $value;
					// Datos para tabla
					$tbl_data[] = array('insumos_o'  => $key,
										'insumos'    => $key,
										'costo'      => '$ '.$value,
										);
				}
				
				$promedio = $suma_precio_insumo/$count_insumos;
				$tbl_data[] = array(
					                'total_o'    => $suma_precio_insumo
					               ,'total'      => '<strong>'.$this->lang_item('total',false).'</strong>'
					               ,'suma_total' => '$ '.$suma_precio_insumo
					               );
				$tbl_data[] = array(
					                'promedio_o'     => $promedio
					               ,'promedio'       => '<strong>'.$this->lang_item('promedio',false).'</strong>'
					               ,'promedio_total' => $promedio
					               );
				// Plantilla
				$tbl_plantilla = set_table_tpl();
				// Titulos de tabla
				$this->table->set_heading(	'id',
											$this->lang_item("insumos"),
											$this->lang_item("costo_x_insumo"));

				// Generar tabla
				$plantilla_column = array ( 'table_open'  => '<table class="table table-striped responsive" >' );
				$this->table->set_template($plantilla_column);
				$this->table->set_template($plantilla_column);
				$tabla = $this->table->generate($tbl_data);
				$array_json = array('success' => true, 'contenido' => $tabla);
			}
		}else{
		$msg = $this->lang_item("msg_err_receta_vacio");
			$array_json = array('success' => 'vacio', 'contenido' => alertas_tpl('error', $msg ,false));
		}
		echo json_encode($array_json);
	}
}