<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class menus extends Base_Controller{

	private $modulo;
	private $submodulo;
	private $view_content;
	private $seccion;
	private $path;
	private $icon;
	private $offset, $limit_max;
	private $tab, $tab1, $tab2, $tab3;

	public function __construct(){
		parent::__construct();
		$this->modulo 			= 'nutricion';
		$this->submodulo		= 'menus';
		$this->icon 			= 'fa fa-cutlery';
		$this->tab1 			= 'agregar';
		$this->tab2 			= 'listado';
		$this->tab3             = 'detalle';
		$this->view_content 	= 'content';
		$this->path 			= $this->modulo.'/'.$this->submodulo.'/';
		$this->path_view        = $this->path.'/'.$this->submodulo;
		$this->limit_max		= 10;
		$this->offset			= 0;
		// DB Model
		$this->load->model($this->modulo.'/'.$this->submodulo.'_model','db_model' );
		$this->load->model('sucursales/listado_sucursales_model','sucursales');
		// Diccionario
		$this->lang->load( $this->modulo.'/'.$this->submodulo,"es_ES" );
	}

	public function config_tabs(){
		$tab_1 	= $this->tab1;
		$tab_2 	= $this->tab2;
		$tab_3 	= $this->tab3;
		$path  	= $this->path;
		$pagina = (is_numeric($this->uri_segment_end()) ? $this->uri_segment_end() : "");

		$config_tab['names']    = array( $this->lang_item($tab_1), $this->lang_item($tab_2), $this->lang_item($tab_3) ); 
		$config_tab['links']    = array( $path.$tab_1, $path.$tab_2.'/'.$pagina, $tab_3 ); 
		$config_tab['action']   = array('load_content', 'load_content', '');
		$config_tab['attr']     = array('','', array('style' => 'display:none'));
		
		return $config_tab;
	}
	
	private function uri_view_principal(){
		return $this->modulo.'/'.$this->view_content;
	}

	public function index(){
		$tabl_inicial 			  = 2;	
		$contenidos_tab           = $this->listado();
		$data['titulo_submodulo'] = $this->lang_item($this->modulo);
		$data['titulo_seccion']   = $this->lang_item('conformacion_menus');
		$data['icon']             = $this->icon;
		$data['tabs']             = tabbed_tpl($this->config_tabs(),base_url(),$tabl_inicial,$contenidos_tab);	
		
		$js['js'][]  = array('name' => $this->submodulo, 'dirname' => $this->modulo);
		$this->load_view($this->uri_view_principal(), $data, $js);
	}

	public function listado($offset=0){
		$seccion 		= '/listado';
		$tab_detalle	= $this->tab3;	
		$limit 			= $this->limit_max;
		$uri_view 		= $this->modulo.$seccion;
		$url_link 		= $this->path.'listado';
		$filtro      	= ($this->ajax_post('filtro')) ? $this->ajax_post('filtro') : "";
		$sqlData = array( 
			 'buscar'      	=> $filtro
			,'offset' 		=> $offset
			,'limit'      	=> $limit
			,'orden'		=> TRUE
		);
		$uri_segment  = $this->uri_segment(); 
		$total_rows	  = count($this->db_model->db_get_data($sqlData));
		$sqlData['aplicar_limit'] = true;
		$list_content = $this->db_model->db_get_data($sqlData);
		$url          = base_url($url_link);
		$paginador    = $this->pagination_bootstrap->paginator_generate($total_rows, $url, $limit, $uri_segment, array('evento_link' => 'onclick', 'function_js' => 'load_content', 'params_js'=>'1'));
		
		if($total_rows){
			foreach ($list_content as $value){
				// Evento de enlace
				$atrr = array(
								'href'    => '#',
							  	'onclick' => $tab_detalle.'('.$value['id_nutricion_menu'].')'
						);
				// Datos para tabla
				$tbl_data[] = array('id'            => $value['id_nutricion_menu'],
									'area'          => tool_tips_tpl($value['menu'], $this->lang_item("tool_tip"), 'right' , $atrr),
									'clave_corta'   => $value['clave_corta'],
									'descripcion'   => $value['sucursal']
									);
			}
			// Plantilla
			$tbl_plantilla = set_table_tpl();
			// Titulos de tabla
			$this->table->set_heading(	$this->lang_item("ID"),
										$this->lang_item("lbl_menu"),
										$this->lang_item("lbl_clave_corta"),
										$this->lang_item("lbl_sucursal"));
			// Generar tabla
			$this->table->set_template($tbl_plantilla);
			$tabla = $this->table->generate($tbl_data);
			$buttonTPL = array( 'text'       => $this->lang_item("btn_xlsx"), 
								'iconsweets' => 'fa fa-file-excel-o',
								'href'       => base_url($this->path.'export_xlsx?filtro='.base64_encode($filtro))
								);
		}else{
			$buttonTPL = "";
			$msg   = $this->lang_item("msg_query_null");
			$tabla = alertas_tpl('', $msg ,false);
		}
		$tabData['filtro']    = (isset($filtro) && $filtro!="") ? sprintf($this->lang_item("msg_query_search",false),$total_rows , $filtro) : "";
		$tabData['tabla']     = $tabla;
		$tabData['export']    = button_tpl($buttonTPL);
		$tabData['paginador'] = $paginador;
		$tabData['item_info'] = $this->pagination_bootstrap->showing_items($limit, $offset, $total_rows);
		if($this->ajax_post(false)){
			echo json_encode( $this->load_view_unique($uri_view , $tabData, true));
		}else{
			return $this->load_view_unique($uri_view , $tabData, true);
		}
	}

	public function detalle(){
		$id_menu     = $this->ajax_post('id_menu');
		$sqlData = array(
			 'buscar'      	=> $id_menu
			,'offset' 		=> 0
			,'limit'      	=> 0
		);
		$detalle     = $this->db_model->db_get_data($sqlData);
		
		$btn_save    = form_button(array('class'   => 'btn btn-primary'
													   ,'name'    => 'actualizar'
													   ,'onclick' => 'modificar_menu()'
													   ,'content' => $this->lang_item("btn_guardar")));
		$tabData['id_nutricion_menu']                     = $id_menu;
		$tabData['lbl_nombre_menu']             = $this->lang_item("lbl_menu");
		$tabData['lbl_clave_corta']             = $this->lang_item("lbl_clave_corta");
		$tabData['lbl_sucursal']                = $this->lang_item("lbl_sucursal");
		$tabData['lbl_asigna_recetas']          = $this->lang_item("lbl_asigna_recetas");
		$tabData['lbl_asigna_articulos']        = $this->lang_item("lbl_asigna_articulos");
		$tabData['lbl_list_recetas_selected']   = $this->lang_item("lbl_list_recetas_selected");
		$tabData['lbl_list_articulos_selected'] = $this->lang_item("lbl_list_articulos_selected");
		$tabData['txt_menu']        = $detalle[0]['menu'];
		$tabData['txt_clave_corta'] = $detalle[0]['clave_corta'];
		$tabData['lbl_ultima_modificacion'] = $this->lang_item('lbl_ultima_modificacion');
        $tabData['val_fecha_registro']      = $detalle[0]['timestamp'];
		$tabData['lbl_fecha_registro']      = $this->lang_item('lbl_fecha_registro');
		$tabData['lbl_usuario_registro']    = $this->lang_item('lbl_usuario_registro');
		$tabData['btn_formato']             = $btn_save;
		$data = array(
						 'id_sucursal' => $detalle[0]['id_sucursal']
						,'id_menu'	   => $detalle[0]['id_menu']
					);
		$recetas = $this->db_model->get_lts_recetas_x_menu($data);
		foreach ($recetas as $value){
			$id_receta[]  = $value['id_nutricion_receta'];
		}

		$articulos = $this->db_model->get_lts_articulos_x_menu($data);
		foreach ($articulos as $value){
			$id_articulo[]  = $value['id_articulo'];
		}
		$recetas_array  = array(
						'data'		=> $this->db_model->get_lts_recetas($detalle[0]['id_sucursal'])
						,'value' 	=> 'id_nutricion_receta'
						,'text' 	=> array('clave_corta','receta')
						,'name' 	=> "lts_recetas"
						,'class' 	=> "requerido"
						,'selected' => $id_receta
					);
		$list_recetas  = multi_dropdown_tpl($recetas_array);

		$articulos_array  = array(
						'data'		=> $this->db_model->get_lts_articulos($detalle[0]['id_sucursal'])
						,'value' 	=> 'id_compras_articulo_precios'
						,'text' 	=> array('articulo')
						,'name' 	=> "lts_articulos"
						,'class' 	=> "requerido"
						,'selected' => $id_articulo
					);
		$list_articulos  = multi_dropdown_tpl($articulos_array);
		
		$sqlData['buscar'] = '';
		$sucursales_array     = array(
					 'data'     => $this->sucursales->db_get_data($sqlData)
					,'value' 	=> 'id_sucursal'
					,'text' 	=> array('sucursal')
					,'name' 	=> "lts_sucursales"
					,'class' 	=> "requerido"
					,'selected' => $detalle[0]['id_sucursal']
					,'event'    => array('event'      => 'onchange', 
										'function'    => 'load_dropdowns', 
										'params'      => array('this.value'), 
										'params_type' => array(false))
					);
		$sucursales            = dropdown_tpl($sucursales_array);
		$tabData['dropdown_sucursales'] = $sucursales;
		$tabData['recetas_selected'] = $list_recetas;
		$tabData['articulos_selected'] = $list_articulos;
		$this->load_database('global_system');
        $this->load->model('users_model');

        $usuario_registro                  = $this->users_model->search_user_for_id($detalle[0]['id_usuario']);
	    $usuario_name	                   = text_format_tpl($usuario_registro[0]['name'],"u");
	    $tabData['val_usuarios_registro']  = $usuario_name;
	    if($detalle[0]['edit_id_usuario']){
        	$usuario_registro                   = $this->users_model->search_user_for_id($detalle[0]['edit_id_usuario']);
        	$usuario_name 				        = text_format_tpl($usuario_registro[0]['name'],"u");
        	$tabData['val_ultima_modificacion'] = sprintf($this->lang_item('val_ultima_modificacion', false), $this->timestamp_complete($detalle[0]['edit_timestamp']), $usuario_name);
        }else{
        	$usuario_name = '';
    		$tabData['val_ultima_modificacion'] = $this->lang_item('lbl_sin_modificacion', false);
        }

        $tabData['button_save']           = $btn_save;
        $tabData['registro_por']    	= $this->lang_item("registro_por",false);
      	$tabData['usuario_registro']	= $usuario_name;

		$uri_view   					  = 'nutricion/menus/menus_detalle';
		echo json_encode( $this->load_view_unique($uri_view ,$tabData, true));
	}

	public function agregar(){
		$seccion = $this->modulo.'/'.$this->submodulo.'/menus_agregar';
		$data['lbl_sucursal'] = $this->lang_item('lbl_sucursal');
		$sqlData = array(
							 'buscar' => 0
							,'offset' => 0
							,'limit'  => 0
						);


		$dropdown_sucursales = array(
						 'data'		=> $this->sucursales->db_get_data($sqlData)
						,'value' 	=> 'id_sucursal'
						,'text' 	=> array('cv_sucursal','sucursal')
						,'name' 	=> "lts_sucursales"
						,'leyenda' 	=> "-----"
						,'class' 	=> "requerido"
						,'event'    => array('event'      => 'onchange',  
											'function'    => 'load_dropdowns', 
											'params'      => array('this.value'), 
											'params_type' => array(false))
					);
		$btn_guardar = form_button(array( 
											'content'  => $this->lang_item('btn_guardar'),
											'class'    => 'btn btn-primary',
											//'disabled' => 'disabled',
											'onclick'  => 'conformar_menu()',
											'name'     => 'guardar_menu'
										));

		$sucursales                           = dropdown_tpl($dropdown_sucursales);
		$data['lbl_clave_corta']              = $this->lang_item('lbl_clave_corta', false);
		$data['lbl_nombre_menu']              = $this->lang_item('lbl_nombre_menu', false);
		$data['lbl_sucursal']                 = $this->lang_item('lbl_sucursal', false);
		$data['lbl_asigna_recetas']           = $this->lang_item('lbl_asigna_recetas', false);
		$data['lbl_list_recetas']             = $this->lang_item('lbl_list_recetas', false);
		$data['lbl_list_recetas_selected']    = $this->lang_item('lbl_list_recetas_selected', false);
		$data['lbl_asigna_articulos']         = $this->lang_item('lbl_asigna_articulos', false);
		$data['lbl_list_articulos']           = $this->lang_item('lbl_list_articulos', false);
		$data['lbl_list_articulos_selected']  = $this->lang_item('lbl_list_articulos_selected', false);
		$data['dropdown_sucursales']          = $sucursales;
		$data['dropdown_recetas']             = dropdown_tpl(array('data' => null,'name' => "lts_recetas", 'leyenda' => "-----"));
		$data['dropdown_articulos']           = dropdown_tpl(array('data' => null,'name' => "lts_articulos", 'leyenda' => "-----"));
		$data['btn_formato']                  = $btn_guardar;

		if($this->ajax_post(false)){
			echo json_encode($this->load_view_unique($seccion,$data,true));
		}
		else{
			return $this->load_view_unique($this->view_agregar, $data, true);
		}
	}

	public function load_dropdowns(){
		$id_sucursal    = ($this->ajax_post('id_sucursal')) ? $this->ajax_post('id_sucursal') : false;
		$json_recetas   = array();
		$json_articulos = array();
		if($id_sucursal){
			$lts_recetas = $this->db_model->get_lts_recetas( $id_sucursal );
			if(is_array($lts_recetas)){
				foreach ($lts_recetas as $key => $value) {
					$json_recetas[] = array(
											'key'  => $value['id_nutricion_receta'], 
											'item' => $value['clave_corta'].'-'.$value['receta']
									);
				}
			}
			
			$lts_articulos = $this->db_model->get_lts_articulos($id_sucursal);
			if(is_array($lts_articulos)){
				foreach ($lts_articulos as $key => $value) {
					$json_articulos[] = array(
											'key'  => $value['id_compras_articulo_precios'],
											'item' => $value['articulo']
										);
				}
			}
		}
		echo json_encode(array('recetas' => $json_recetas, 'articulos' => $json_articulos));
	}

	public function conformar_menu(){
		$objData  	= $this->ajax_post('objData');
		if($objData['incomplete']>0){
			$msg = $this->lang_item("msg_campos_obligatorios",false);
			echo json_encode( array( 'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)));
		}else{
			$data_insert = array(
				  'menu'        => $objData['menu']
				 ,'clave_corta' => $objData['clave_corta']
				 ,'id_usuario'  => $this->session->userdata('id_usuario')
				 ,'timestamp'   => $this->timestamp()
				);
			$insert = $this->db_model->db_insert_data($data_insert);
			
			$arr_receta  = explode(',',$objData['recetas_selected']);
			
			if(!empty($arr_receta)){
				$sqlData = array();
				foreach ($arr_receta as $key => $value){
					$sqlData = array(
						 'id_sucursal'  => $objData['lts_sucursales']
						,'id_menu'      => $insert
						,'id_receta'    => $value
						,'id_usuario'   => $this->session->userdata('id_usuario')
						,'timestamp'    => $this->timestamp()
						);
					$insert_pago = $this->db_model->db_insert_receta($sqlData);
				}
			}

			$arr_articulo  = explode(',',$objData['articulos_selected']);
			
			if(!empty($arr_articulo)){
				$sqlData = array();
				foreach ($arr_articulo as $key => $value){
					$sqlData = array(
						 'id_sucursal'  => $objData['lts_sucursales']
						,'id_menu'      => $insert
						,'id_articulo'  => $value
						,'id_usuario'   => $this->session->userdata('id_usuario')
						,'timestamp'    => $this->timestamp()
						);
					$insert_pago = $this->db_model->db_insert_articulo($sqlData);
				}
			}

			if($insert){
				$msg = $this->lang_item("msg_insert_success",false);
				echo json_encode(array(  'success'=>'true', 'mensaje' => $msg));
			}else{
				$msg = $this->lang_item("msg_err_clv",false);
				echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('', $msg ,false)));
			}
		}
	}

	public function modificar_menu(){
		$objData  	= $this->ajax_post('objData');
		//print_debug($objData);
		if($objData['incomplete']>0){
			$msg = $this->lang_item("msg_campos_obligatorios",false);
			echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)));
		}else{
			$sqlData = array(
				 'id_nutricion_menu'=> $objData['id_nutricion_menu']
				,'menu'             => $objData['txt_menu']
				,'clave_corta'      => $objData['txt_clave_corta']
				,'id_sucursal'      => $objData['lts_sucursales']
				,'edit_timestamp'   => $this->timestamp()
				,'edit_id_usuario'  => $this->session->userdata('id_usuario')
				);

			$insert = $this->db_model->db_update_data($sqlData);
			if($insert){

				$arr_receta  = explode(',',$objData['lts_recetas']);
				$receta      = $this->db_model->delete_receta($objData['lts_sucursales']);
				
				if(!empty($arr_receta)){
					$sqlData = array();
					foreach ($arr_receta as $key => $value){
						$sqlData = array(
							 'id_sucursal'   => $objData['lts_sucursales']
							,'id_menu'       => $objData['id_nutricion_menu']
							,'id_receta'     => $value
							,'id_usuario'    => $this->session->userdata('id_usuario')
							,'timestamp'     => $this->timestamp()
							);
						$insert_pago = $this->db_model->db_update_data_receta($sqlData);
					}
				}

				$arr_articulo  = explode(',',$objData['lts_articulos']);
				$articulo      = $this->db_model->delete_articulo($objData['lts_sucursales']);
				
				if(!empty($arr_articulo)){
					$sqlData = array();
					foreach ($arr_articulo as $key => $value){
						$sqlData = array(
							 'id_sucursal'   => $objData['lts_sucursales']
							,'id_menu'       => $objData['id_nutricion_menu']
							,'id_articulo'   => $value
							,'id_usuario'    => $this->session->userdata('id_usuario')
							,'timestamp'     => $this->timestamp()
							);
						$insert_pago = $this->db_model->db_update_data_articulo($sqlData);
					}
				}
				$msg = $this->lang_item("msg_update_success",false);
				echo json_encode(array(  'success'=>'true', 'mensaje' => $msg ));
			}else{
				$msg = $this->lang_item("msg_err_clv",false);
				echo json_encode( array( 'success'=>'false', 'mensaje' =>alertas_tpl('', $msg ,false)));
			}
		}
	}

	public function export_xlsx($offset=0){
		$filtro      = ($this->ajax_get('filtro')) ?  base64_decode($this->ajax_get('filtro') ): "";
		
		$limit 		 = $this->limit_max;
		$sqlData     = array(
			 'buscar'      	=> $filtro
			,'offset' 		=> $offset
			,'limit'      	=> $limit
		);

		$lts_content = $this->db_model->db_get_data($sqlData);
		//print_debug($lts_content);
		if(count($lts_content)>0){
			foreach ($lts_content as $value){
				$set_data[] = array(
									 $value['menu'],
									 $value['clave_corta'],
									 $value['sucursal']
									 );
			}
			
			$set_heading = array(
									$this->lang_item("lbl_menu"),
									$this->lang_item("lbl_clave_corta"),
									$this->lang_item("lbl_sucursal")
									);
	
		}

		$params = array(	'title'   => $this->lang_item("lbl_excel"),
							'items'   => $set_data,
							'headers' => $set_heading
						);
		//print_debug($params);
		$this->excel->generate_xlsx($params);
	}
}