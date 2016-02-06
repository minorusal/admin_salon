<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class recetario extends Base_Controller{
	private $modulo;
	private $submodulo;
	private $seccion;
	private $view_content;
	private $path;
	private $icon;
	private $view_modal;
	private $offset, $limit_max;
	private $tab, $tab1, $tab2, $tab3;

	public function __construct(){
		parent::__construct();
		$this->modulo 			= 'nutricion';
		$this->seccion		    = 'recetario';
		$this->icon 			= 'fa fa-pencil-square'; 
		$this->path 			= $this->modulo.'/'.$this->seccion.'/'; 
		$this->view_content 	= 'content';
		$this->view_modal       = 'modal_cropper';
		$this->limit_max		= 10;
		$this->offset			= 0;
		// Tabs
		$this->tab1 			= 'agregar';
		$this->tab2 			= 'listado';
		$this->tab3 			= 'detalle';
		$this->tab4 			= 'duplicar';
		// DB Model
		$this->load->model($this->modulo.'/'.$this->seccion.'_model','db_model');
		$this->load->model($this->modulo.'/familias_model','familias');
		$this->load->model('compras/catalogos_model','compras');
		$this->load->model('sucursales/listado_sucursales_model','sucursales');
		// Diccionario
		$this->lang->load($this->modulo.'/'.$this->seccion,"es_ES");
	}

	public function config_tabs(){
		$tab_1 	= $this->tab1;
		$tab_2 	= $this->tab2;
		$tab_3 	= $this->tab3;
		$tab_4 	= $this->tab4;
		$path  	= $this->path;
		$pagina =(is_numeric($this->uri_segment_end()) ? $this->uri_segment_end() : "");
		// Nombre de Tabs
		$config_tab['names']    = array(
										 $this->lang_item($tab_1) 
										,$this->lang_item($tab_2) 
										,$this->lang_item($tab_3) 
										,$this->lang_item($tab_4) 
								); 
		// Href de tabs
		$config_tab['links']    = array(
										 $path.$tab_1             
										,$path.$tab_2.'/'.$pagina 
										,$tab_3 
										,$path.$tab_4               
								); 
		// Accion de tabs
		$config_tab['action']   = array(
										 'load_content'
										,'load_content'
										,''
										,'load_content'
								);
		// Atributos 
		$config_tab['attr']     = array('','', array('style' => 'display:none'),'');
		$config_tab['style_content'] = array('','','','');
		
		return $config_tab;
	}

	private function uri_view_principal(){
		return $this->modulo.'/'.$this->view_content;
	}

	public function index(){
		$tabl_inicial 			  = 2;
		$view_listado    		  = $this->listado();
		$contenidos_tab           = $view_listado;
		$data['titulo_seccion']   = $this->lang_item($this->seccion);
		$data['titulo_submodulo'] = $this->lang_item("titulo_submodulo");
		$data['icon']             = $this->icon;
		$data['tabs']             = tabbed_tpl($this->config_tabs(),base_url(),$tabl_inicial,$contenidos_tab);	
		$js['js'][]               = array('name' => $this->seccion, 'dirname' => $this->modulo);
		$this->load_view($this->uri_view_principal(), $data, $js);
	}

	public function listado($offset=0){
		
		// Crea tabla con listado de elementos capturados 
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
		);
		$uri_segment  = $this->uri_segment(); 
		$total_rows	  = count($this->db_model->get_data($sqlData));
		
		$sqlData['aplicar_limit'] = true;
		
		$list_content = $this->db_model->get_data($sqlData);
		$url          = base_url($url_link);
		$arreglo      = array($total_rows, $url, $limit, $uri_segment);
		$paginador    = $this->pagination_bootstrap->paginator_generate($total_rows, $url, $limit, $uri_segment, array('evento_link' => 'onclick', 'function_js' => 'load_content', 'params_js'=>'1'));
		
		if($total_rows){
			foreach ($list_content as $value){
				// Evento de enlace
				$atrr = array(
								'href' => '#',
							  	'onclick' => $tab_detalle.'('.$value['id_nutricion_receta'].')'
						);
				$receta = $this->db_model->db_get_receta_usada($value['id_nutricion_receta']);
				$eliminar 	= '<span style="color:red;" id="ico-eliminar_'.$value['id_nutricion_receta'].'" class="ico_eliminar fa fa-times" onclick="confirm_delete('.$value['id_nutricion_receta'].')" title="'.$this->lang_item("lbl_eliminar").'"></span>';
				$btn_acciones['eliminar'] = ($receta[0]['num_recetas'] == 0)?$eliminar:'<span style="color:gray;" id="ico-eliminar_'.$value['id_nutricion_receta'].'" class="ico_eliminar fa fa-times" title="'.$this->lang_item("lbl_eliminar").'"></span>';
				$accion_id 					= $value['id_nutricion_receta'];
				$btn_acciones['excel']      = '<span id="ico-excel_'.$accion_id.'" class="ico_acciones ico_excel fa fa-file-text" onclick="window.location.href=\''.base_url($this->path.'export_rexlsx?filtro='.base64_encode($value['id_nutricion_receta'])).'\';" title="'.$this->lang_item("reporte").'"></span>';
				$acciones = implode('&nbsp;&nbsp;&nbsp;',$btn_acciones);
				
				// Datos para tabla
				$tbl_data[] = array('id'           => $value['id_nutricion_receta'],
									'avatar'       => tool_tips_tpl(($value['avatar'] == '') ? return_avatar('recetario') : return_avatar('recetario', $value['avatar']), $this->lang_item("tool_tip"), 'right' , $atrr),
									'receta'       => tool_tips_tpl($value['receta'], $this->lang_item("tool_tip"), 'right' , $atrr),
									'clave_corta'  => $value['clave_corta'],
									'sucursal'     => $value['sucursal'],
									'porciones'    => $value['porciones'],
									'costo'        => $this->detalle(true,$value['id_nutricion_receta']),
									'familia'      => $value['familia'],
									'preparacion'  => $value['preparacion'],
									'acciones'	   => $acciones
									
									);
			}

			// Plantilla
			$tbl_plantilla = set_table_tpl();
			// Titulos de tabla
			$this->table->set_heading(	$this->lang_item("ID"),
										$this->lang_item("foto"),
										$this->lang_item("lbl_receta"),
										$this->lang_item("lbl_clave_corta"),
										$this->lang_item("lbl_sucursal"),
										$this->lang_item("lbl_porciones"),
										$this->lang_item("lbl_costo_x_porcion"),
										$this->lang_item("lbl_familia"),
										$this->lang_item("lbl_preparacion"),
										$this->lang_item("lbl_acciones")
										);
			// Generar tabla
			$this->table->set_template($tbl_plantilla);
			$tabla = $this->table->generate($tbl_data);
			$buttonTPL = array( 'text'   => $this->lang_item("btn_xlsx"), 
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

	public function agregar(){
		$seccion = $this->modulo.'/'.$this->seccion.'/'.$this->seccion.'_agregar';
		$sqlData = array(
			 'buscar' => 0
			,'offset' => 0
			,'limit'  => 0
			);
		$dropdown_sucursales = array(
									 'data'		=> $this->sucursales->db_get_data($sqlData)
									,'value' 	=> 'id_sucursal'
									,'text' 	=> array('cv_sucursal','sucursal')
									,'name' 	=> "lts_sucursales_agregar"
									,'class' 	=> "requerido"
								);
		$sucursales = dropdown_tpl($dropdown_sucursales);

		$familias = array(
						 'data'		=> $this->familias->db_get_data(array())
						,'value' 	=> 'id_nutricion_familia'
						,'text' 	=> array('clave_corta','familia')
						,'name' 	=> "lts_familias_insert"
						,'class' 	=> "requerido"
					);

		$list_familias  = dropdown_tpl($familias);

		$insumos  = $this->db_model->get_insumos();
		//print_debug($insumos);

		$insumos  = array(
						 'data'		=> $insumos
						,'value' 	=> 'id_compras_articulo'
						,'text' 	=> array('clave_corta','articulo')
						,'name' 	=> "lts_insumos_insert"
						,'class' 	=> "requerido  "
					);

		$list_insumos  = multi_dropdown_tpl($insumos);

		$btn_save  = form_button(array('class'=>'btn btn-primary', 'name'=>'save_receta', 'onclick'=>'agregar()','content'=>$this->lang_item("btn_guardar")));
		$btn_reset = form_button(array('class'=>'btn btn-primary', 'name'=>'limpiar' ,'onclick'=>'clean_formulario_recetas()','content'=>$this->lang_item('btn_limpiar')));
		
		$tab_1['lbl_receta']               = $this->lang_item('lbl_receta');
		$tab_1['lbl_clave_corta']          = $this->lang_item('lbl_clave_corta');
		$tab_1['lbl_sucursal']             = $this->lang_item('lbl_sucursal');
		$tab_1['lbl_porciones']            = $this->lang_item('lbl_porciones');
		$tab_1['lbl_preparacion']          = $this->lang_item('lbl_preparacion');
		$tab_1['lbl_familia']              = $this->lang_item('lbl_familia');
		$tab_1['lbl_asignar_insumos']      = $this->lang_item('lbl_asignar_insumos');
		$tab_1['lbl_editar_porciones']     = $this->lang_item('lbl_editar_porciones');
		$tab_1['select_insumos']           = $this->lang_item('select_insumos');
		$tab_1['lbl_presentacion_insumo']  = $this->lang_item('lbl_presentacion_insumo');
		$tab_1['dropdown_sucursal']        = $sucursales;
		$tab_1['multiselect_insumos']      = $list_insumos;
		$tab_1['select_familias']          = $list_familias;
		$tab_1['button_save']              = $btn_save;
		$tab_1['button_reset']             = $btn_reset;

		if($this->ajax_post(false)){
			echo json_encode($this->load_view_unique($seccion,$tab_1 ,true));
		}
		else{
			return $this->load_view_unique($seccion, $tab_1, true);
		}
	}

	public function insert(){
		$objData  	= $this->ajax_post('objData');
		//print_debug($objData);
		if($objData['incomplete']>0){
			$msg = $this->lang_item("msg_campos_obligatorios",false);
			echo json_encode( array( 'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)) );
		}else{
			$receta        = $objData['txt_receta'];
			$clave_corta   = $objData['txt_clave_corta'];
			$familia       = $objData['lts_familias_insert'];
			$porciones     = $objData['txt_porciones'];
			$preparacion   = $objData['txt_preparacion'];
			$arg_articulo  = explode(',',$objData['lts_insumos_insert']);

			$data_insert = array(
				  'receta'                => $receta
				 ,'clave_corta'           => $clave_corta
				 ,'porciones'             => $porciones
				 ,'id_sucursal'           => $objData['lts_sucursales_agregar']
				 ,'preparacion'           => $preparacion 
				 ,'id_nutricion_familia'  => $familia
				 ,'id_usuario'            => $this->session->userdata('id_usuario')
				 ,'timestamp'             => $this->timestamp()
			);

			$last_id = $this->db_model->insert_receta($data_insert);
			//print_debug($last_id);
			if($last_id){
				$data_insert = array();
				foreach ($arg_articulo as $key => $value) {
					$data_insert[] = array( 
												'id_nutricion_receta'   => $last_id
											   ,'id_compras_articulo'   => $value
									           ,'porciones'             => $objData['articulo_'.$value]
									           ,'id_usuario'            => $this->session->userdata('id_usuario')
				                               ,'timestamp'             => $this->timestamp()
				                        );
				}
				$insert = $this->db_model->insert_receta_articulos($data_insert);

				$msg = $this->lang_item("msg_insert_success",false);
				echo json_encode(array(  'success'=>'true', 'mensaje' => $msg));
			}else{
				$msg = $this->lang_item("msg_err_clv",false);
				echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('', $msg ,false)));
			}
		}	
	}

	public function detalle($return_costo = false, $id = ''){
		$id_receta  = ($return_costo) ? $id : $this->ajax_post('id_receta');

		$cantidades = '';
		$sqlData    = array(
							 'buscar'        => false
							,'offset' 		 => false
							,'limit'      	 => false
							,'aplicar_limit' => false
							,'unique'        => $id_receta
						);
		$id_compras_articulo = array();
		$insumos_sin_costo   = array();
		$recetario           = $this->db_model->get_data_unique($sqlData);
		$costo_porcion       = 0;

		foreach ($recetario as $key => $value) {
			//print_debug($value);
			$id_nutricion_receta  = $value['id_nutricion_receta'];
			$receta               = $value['receta'];
			$id_sucursal          = $value['id_sucursal'];
			$clave_corta          = $value['clave_corta'];
			$id_nutricion_familia = $value['id_nutricion_familia'];
			$familia              = $value['familia'];
			$porciones            = $value['porciones'];
			$preparacion          = $value['preparacion'];
			$id_usuario           = $value['id_usuario'];
			$timestamp            = $value['timestamp'];
			$edit_id_usuario      = $value['edit_id_usuario'];
			$edit_timestamp       = $value['edit_timestamp'];
			
			if($value['id_compras_articulo']){
				$id_compras_articulo[] = $value['id_compras_articulo'];

				if(!$value['costo_x_um']){
					$insumos_sin_costo[] = $value['articulo'];
				}else{
					$costo_porcion = $costo_porcion + ($value['porciones_articulo'] * $value['costo_x_um']);
				}
				$input        = form_input($this->att_addon('articulo_'.$value['id_compras_articulo'],$value['porciones_articulo']));

				$cantidades  .=  "<p id='articulo_".$value['id_compras_articulo']."'>
									<label>".$value['articulo']." (*)</label>
					                ".add_on_tpl($input,$value['um'] )."
					              </p>";
			}
		}

		if(!empty($insumos_sin_costo)){
			$lista_insumos_sin_costo = ol($insumos_sin_costo, array('class' => 'list-ordered'));
			$msg_insumos_sin_costo   = $this->lang_item("msg_insumos_sin_costo",false).br(1).$lista_insumos_sin_costo;
			$msg_insumos_sin_costo   = alertas_tpl('', $msg_insumos_sin_costo ,false, '20%');

		}else{
			$msg_insumos_sin_costo = '';
		}

		
		$costo_porcion = ($costo_porcion > 0 ) ? $costo_porcion/$porciones : 0;

		if($return_costo){
			return $costo_porcion;
		}

		$sqlData = array(
			 'buscar' => 0
			,'offset' => 0
			,'limit'  => 0
			);
		$sucursales_list = $this->sucursales->db_get_data($sqlData);
		$dropdown_sucursales = array(
									 'data'		=> $sucursales_list
									,'value' 	=> 'id_sucursal'
									,'text' 	=> array('cv_sucursal','sucursal')
									,'name' 	=> "lts_sucursales_update"
									,'class' 	=> "requerido"
									,'selected' => $id_sucursal
									,'event'    => array('event'      => 'onchange', 
										                'function'    => 'load_region', 
										                'params'      => array('this.value'), 
										                'params_type' => array(0))
									);
		$sucursales                = dropdown_tpl($dropdown_sucursales);


		$seccion  = $this->modulo.'/'.$this->seccion.'/'.$this->seccion.'_editar';
		$familias = array(
						 'data'		=> $this->familias->db_get_data(array())
						,'value' 	=> 'id_nutricion_familia'
						,'text' 	=> array('clave_corta','familia')
						,'name' 	=> "lts_familias_insert"
						,'class' 	=> "requerido"
						,'selected' => $id_nutricion_familia
					);
		$list_familias  = dropdown_tpl($familias);
		//print_debug($id_sucursal);
		$sucursal_use = $this->sucursales->db_get_sucursal_usada($id_sucursal);
		$regiones_suc = $this->db_model->get_region_sucursal($id_sucursal);
		
		$tab_3['lbl_region']               = $regiones_suc[0]['region'];
		$insumos        = array(
								 'data'		=> $insumos  = $this->db_model->get_insumos()
								,'value' 	=> 'id_compras_articulo'
								,'text' 	=> array('clave_corta','articulo')
								,'name' 	=> "lts_insumos_update"
								,'class' 	=> "requerido  "
								,'selected' => $id_compras_articulo
							);
		$list_insumos  = multi_dropdown_tpl($insumos);
		$btn_save      = form_button(array('class'=>'btn btn-primary', 'name'=>'update_receta', 'onclick'=>'actualizar()','content'=>$this->lang_item("btn_guardar")));
		/*$buttonTPL     = array( 'text'   => $this->lang_item("btn_xlsx"), 
								'iconsweets' => 'fa fa-file-excel-o',
								'href'       => base_url($this->path.'export_rexlsx?filtro='.base64_encode($id_receta))
								);*/
		
		$tab_3['filtro']                   = (isset($id_receta) && $id_receta!="") ? sprintf($this->lang_item("msg_query_search",false),array() , $id_receta) : ""; 
		//$tab_3['export']                   = button_tpl($buttonTPL);
		$tab_3['id_receta']                = $id_nutricion_receta;
		$tab_3['lbl_receta']               = $this->lang_item('lbl_receta');
		$tab_3['lbl_clave_corta']          = $this->lang_item('lbl_clave_corta');
		$tab_3['lbl_porciones']            = $this->lang_item('lbl_porciones');
		$tab_3['lbl_preparacion']          = $this->lang_item('lbl_preparacion');
		$tab_3['lbl_familia']              = $this->lang_item('lbl_familia');
		$tab_3['lbl_asignar_insumos']      = $this->lang_item('lbl_asignar_insumos');
		$tab_3['lbl_editar_porciones']     = $this->lang_item('lbl_editar_porciones');
		$tab_3['select_insumos']           = $this->lang_item('select_insumos');
		$tab_3['lbl_presentacion_insumo']  = $this->lang_item('lbl_presentacion_insumo');
		$tab_3['lbl_costo_x_porcion']      = $this->lang_item('lbl_costo_x_porcion');
		
		$tab_3['value_receta']             = $receta;
		$tab_3['value_clave_corta']        = $clave_corta;
		$tab_3['value_costo_x_porcion']    = $costo_porcion;
		$tab_3['msg_insumos_sin_costo']    = $msg_insumos_sin_costo;

		$tab_3['value_porciones']          = $porciones;
		$tab_3['value_preparacion']        = $preparacion;
		$tab_3['multiselect_insumos']      = $list_insumos;
		$tab_3['cantidades_insumos']       = $cantidades;
		$tab_3['select_familias']          = $list_familias;
		$tab_3['button_save']              = $btn_save;
		$tab_3['lbl_sucursal']             = $this->lang_item('lbl_sucursal');

		$tab_3['foto_img']          = ($recetario[0]['avatar']=='') ? return_avatar('recetario','', false, 100, 100,true) : return_avatar('recetario', $recetario[0]['avatar'], false,  100, 100,true);
        $tab_3['change_avatar']       = $this->lang_item('change_avatar', false);
		$tab_3['local_img_upload']       = $this->lang_item('local_img_upload', false);
		$tab_3['rotate_left']       = $this->lang_item('rotate_left', false);
		$tab_3['rotate_right']       = $this->lang_item('rotate_right', false);
		$tab_3['save_avatar']        = $this->lang_item('save_avatar', false);
		$tab_3['hiddencropper']        = $id_nutricion_receta.'|'.$recetario[0]['avatar'];
		//print_debug($sucursal_use);
		$data_suc_hidden = array(
								 'type'        => 'hidden'
								,'data-campo'  => 'lts_sucursales_update_hidden'
								,'name'        => 'lts_sucursales_update'
								,'class'       => 'requerido'
								,'value'	   => $id_sucursal
			                    );
		$input_sucursal_hidden = form_input($data_suc_hidden);
		$data = array(
              'value'       => $sucursal_use[0]['sucursal'].'-'.$sucursal_use[0]['clave_corta'],
              'readonly'    => 'readonly',
              'class'       => 'input-xlarge',
            );
		$receta_insumo = $this->db_model->db_get_receta_insumos($id_receta);

		$input_sucursal = form_input($data);
		$tab_3['id_sucursal'] = $input_sucursal_hidden;
		$tab_3['dropdown_sucursal']        = ($receta_insumo[0]['id_nutricion_receta'] == $id_receta && $receta_insumo[0]['id_compras_articulo'] != 0)?$input_sucursal:$sucursales;
		$this->load_database('global_system');	
        $this->load->model('users_model');

		$usuario_registro                  = $this->users_model->search_user_for_id($id_usuario);
	    $usuario_name	                   = text_format_tpl($usuario_registro[0]['name'],"u");
	    $tab_3['value_usuarios_registro']  = $usuario_name;

		if($edit_id_usuario){
			$usuario_registro = $this->users_model->search_user_for_id($edit_id_usuario);
			$usuario_name = text_format_tpl($usuario_registro[0]['name'],"u");
			$tab_3['value_ultima_modificacion'] = sprintf($this->lang_item('val_ultima_modificacion',false), $this->timestamp_complete($edit_timestamp), $usuario_name);
		}else{
			$usuario_name = '';
			$tab_3['value_ultima_modificacion'] = $this->lang_item('lbl_sin_modificacion', false);
		}

		$tab_3['value_timestamp'] = $timestamp;

		$tab_3['lbl_ultima_modificacion'] = $this->lang_item('lbl_ultima_modificacion', false);
		$tab_3['lbl_fecha_registro']      = $this->lang_item('lbl_fecha_registro', false);
		$tab_3['lbl_usuario_registro']    = $this->lang_item('lbl_usuario_registro', false);

		if($this->ajax_post(false)){
			echo json_encode($this->load_view_unique($seccion,$tab_3 ,true));
		}
		else{
			return $this->load_view_unique($seccion, $tab_3, true);
		}
	}

	public function update(){
		$objData  	= $this->ajax_post('objData');
		//print_debug($objData);
		if($objData['incomplete']>0){
			$msg = $this->lang_item("msg_campos_obligatorios",false);
			echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)));
		}else{
			$id_receta     = $objData['id_receta'];
			$receta        = $objData['txt_receta'];
			$clave_corta   = $objData['txt_clave_corta'];
			$id_sucursal   = (!$objData['lts_sucursales_update'])?$objData['lts_sucursales_update_hidden']:$objData['lts_sucursales_update'];
			$familia       = $objData['lts_familias_insert'];
			$porciones     = $objData['txt_porciones'];
			$preparacion   = $objData['txt_preparacion'];
			$arg_articulo  = explode(',',$objData['lts_insumos_update']);

			$data_insert = array(
				  'id_nutricion_receta'   => $id_receta
				 ,'receta'                => $receta
				 ,'clave_corta'           => $clave_corta
				 ,'id_sucursal'           => $id_sucursal
				 ,'porciones'             => $porciones
				 ,'preparacion'           => $preparacion 
				 ,'id_nutricion_familia'  => $familia
				 ,'edit_id_usuario'       => $this->session->userdata('id_usuario')
				 ,'edit_timestamp'        => $this->timestamp()
			);

			$this->db_model->update_receta($data_insert);

			if($id_receta){
				$data_insert = array();
				foreach ($arg_articulo as $key => $value) {
					$data_insert[] = array( 
												'id_nutricion_receta'   => $id_receta
											   ,'id_compras_articulo'   => $value
									           ,'porciones'             => $objData['articulo_'.$value]
									           ,'id_usuario'            => $this->session->userdata('id_usuario')
				                               ,'timestamp'             => $this->timestamp()
				                        );
				}
				$insert = $this->db_model->insert_receta_articulos($data_insert,$id_receta);

				$msg = $this->lang_item("msg_update_success",false);
				echo json_encode(array(  'success'=>'true', 'mensaje' => $msg ));
			}else{
				$msg = $this->lang_item("msg_err_clv",false);
				echo json_encode( array( 'success'=>'false', 'mensaje' =>alertas_tpl('', $msg ,false)));
			}
		}	
	}

	public function detalle_articulo(){
		$id_articulo = $this->ajax_post('id_articulo');

		$articulo  = $this->db_model->get_insumos(array('id_compras_articulo' => $id_articulo));

		$articulo_nombre = $articulo[0]['articulo'];
		$articulo_um     = $articulo[0]['um'];
		$articulo_id     = $articulo[0]['id_compras_articulo'];


		$input= form_input($this->att_addon('articulo_'.$articulo_id));

		$data =  "<p id='articulo_$articulo_id'><label>$articulo_nombre (*)<br></label>
	                ".add_on_tpl($input,$articulo_um )."
	            </p>";
		echo json_encode($data);
	}

	public function att_addon($campo, $value= ''){
		return $att = array(
                            'data-campo'    => $campo,
                            'type'          => 'text',
                            'class'         => 'numerico requerido input-small',
                            'placeholder'   => $this->lang_item('lbl_cantidad'),
                            'value'         => $value
                        );  
	}

	public function export_rexlsx(){
		$filtro      = ($this->ajax_get('filtro')) ?  base64_decode($this->ajax_get('filtro') ): "";
		$receta = $this->db_model->get_data_receta($filtro);
		foreach ($receta as $value){
			$set_general_receta[] = array(
							 $value['receta']
							,$value['clave_receta']
							,$value['sucursal']
							,$value['porciones']
							//,$value['preparacion']
							,$value['familia']
					);
			}
			$set_heading_receta = array(
						 $this->lang_item("lbl_receta")
						,$this->lang_item("lbl_clave_corta")
						,$this->lang_item("lbl_sucursal")
						,$this->lang_item("lbl_porciones")
						//,$this->lang_item("lbl_preparacion")
						,$this->lang_item("lbl_familia")
			);

			$set_preparacion[] = array($value['preparacion']);
		
		$sqlData    = array(
							 'buscar'        => false
							,'offset' 		 => false
							,'limit'      	 => false
							,'aplicar_limit' => false
							,'unique'        => $filtro
						);
		$contenido = $this->db_model->get_data_receta_vnutricion($sqlData);
		
		foreach($contenido as  $value){
			$costo_total = $value['costo_x_um']*$value['porciones_articulo'];
			$set_valores_nutricionales[] = array(
				 ($value['clave_articulo'])?$value['clave_articulo']:'0'
				,($value['articulo'])?$value['articulo']:'0'
				,($value['porciones_articulo'].' '.$value['cv_um'])?$value['porciones_articulo'].' '.$value['cv_um']:'0'
			    ,($value['costo_x_um'])?$value['costo_x_um']:'0'
			    ,($costo_total)?$costo_total:'0'
				,($value['cantidad_sugerida'])?$value['cantidad_sugerida']:'0'
				,($value['peso_bruto'])?$value['peso_bruto']:'0'
				,($value['peso_neto'])?$value['peso_neto']:'0'
				,($value['energia'])?$value['energia']:'0'
				,($value['proteina'])?$value['proteina']:'0'
				,($value['lipidos'])?$value['lipidos']:'0'
				,($value['hidratos_carbono'])?$value['hidratos_carbono']:'0'
				,($value['fibra'])?$value['fibra']:'0'
				,($value['vitamina_a'])?$value['vitamina_a']:'0'
				,($value['acido_ascorbico'])?$value['acido_ascorbico']:'0'
				,($value['acido_folico'])?$value['acido_folico']:'0'
				,($value['hierro_nohem'])?$value['hierro_nohem']:'0'
				,($value['potasio'])?$value['potasio']:'0'
				,($value['azucar'])?$value['azucar']:'0'
				,($value['indice_glicemico'])?$value['indice_glicemico']:'0'
				,($value['carga_glicemica'])?$value['carga_glicemica']:'0'
				,($value['calcio'])?$value['calcio']:'0'
				,($value['sodio'])?$value['sodio']:'0'
				,($value['selenio'])?$value['selenio']:'0'
				,($value['fosforo'])?$value['fosforo']:'0'
				,($value['colesterol'])?$value['colesterol']:'0'
				,($value['ag_saturados'])?$value['ag_saturados']:'0'
				,($value['ag_mono'])?$value['ag_mono']:'0'
				,($value['ag_poli'])?$value['ag_poli']:'0'
				);
		}
		
		$set_heading_valores_nutricionales = array(
			 $this->lang_item('lbl_clave_articulo')
			,$this->lang_item("lbl_articulo")
			,$this->lang_item('lbl_cantidad')
			,$this->lang_item('lbl_costo_x_um')
			,$this->lang_item('lbl_total')
			,$this->lang_item("lbl_cantidad_sugerida")
			,$this->lang_item("lbl_peso_bruto")
			,$this->lang_item("lbl_peso_neto")
			,$this->lang_item("lbl_energia")
			,$this->lang_item("lbl_proteina")
			,$this->lang_item("lbl_lipidos")
			,$this->lang_item("lbl_hidratos_carbono")
			,$this->lang_item("lbl_fibra")
			,$this->lang_item("lbl_vitamina_a")
			,$this->lang_item("lbl_acido_ascorbico")
			,$this->lang_item("lbl_acido_folico")
			,$this->lang_item("lbl_hierro_nohem")
			,$this->lang_item("lbl_potasio")
			,$this->lang_item("lbl_azucar")
			,$this->lang_item("lbl_indice_glicemico")
			,$this->lang_item("lbl_carga_glicemica")
			,$this->lang_item("lbl_calcio")
			,$this->lang_item("lbl_sodio")
			,$this->lang_item("lbl_selenio")
			,$this->lang_item("lbl_fosforo")
			,$this->lang_item("lbl_colesterol")
			,$this->lang_item("lbl_ag_saturados")
			,$this->lang_item("lbl_ag_mono")
			,$this->lang_item("lbl_ag_poli")
			);
		$set_items_costo_total[] = array(
					$costo_total = ($value['costo_x_um']+$value['porciones_articulo'])?$value['costo_x_um']+$value['porciones_articulo']:'0'
					);
		$set_heading_costo_total = array(
							$this->lang_item('lbl_costo_total')
			);
		$params = array(	'title'                => $this->lang_item("Ficha Técnica de Platillos"),
							'items_receta'         => $set_general_receta,
							'headers_receta'       => $set_heading_receta,
							'items_valores'        => $set_valores_nutricionales,
							'headers_valores'      => $set_heading_valores_nutricionales,
							'headers_costo_total'  => $set_heading_costo_total,
							'items_costo_total'    => $set_items_costo_total,
							'preparacion'		   => $set_preparacion
						);
		$this->excel->receta_generate_xlsx($params);
	}

	public function export_xlsx($offset=0){
		$filtro      = ($this->ajax_get('filtro')) ?  base64_decode($this->ajax_get('filtro') ): "";
		$limit 		 = $this->limit_max;
		$sqlData     = array(
			 'buscar'      	=> $filtro,
			 'offset' 		=> $offset
		);
		$lts_content = $this->db_model->get_data($sqlData);
		foreach ($lts_content as $value){
					$set_data[] = array(	
											$value['receta'],
											$value['clave_corta'],
											$value['sucursal'],
											$value['porciones'],
											$value['familia'],
											$value['preparacion']
									);

		}
		$set_heading = array(
								$this->lang_item("lbl_receta"),
								$this->lang_item("lbl_clave_corta"),
								$this->lang_item("lbl_sucursal"),
								$this->lang_item("lbl_porciones"),
								$this->lang_item("lbl_familia"),
								$this->lang_item("lbl_preparacion")
							);
		$params = array(	'title'   => $this->lang_item("ficha tecnica"),
							'items'   => $set_data,
							'headers' => $set_heading
						);
		$this->excel->generate_xlsx($params);
	}
	public function upload_photo(){
      	$src =  $this->ajax_post('avatar_src');
      	$data = $this->ajax_post('avatar_data');
     

      	$response = $this->jcrop->initialize_crop($src,$tab_1,$file);

       echo json_encode($response);
    }

    public function eliminar_registro(){
    	$id = $this->ajax_post('id');
		$receta = $this->db_model->db_get_receta_usada($id);
		if($receta[0]['num_recetas'] == 0){
			$sqlData = array(
					'id_nutricion_receta'     => $id
		            ,'activo'                 => 0
		            ,'edit_id_usuario'        => $this->session->userdata('id_usuario')
					,'edit_timestamp'         => $this->timestamp()
					);
			
			$update = $this->db_model->bd_delete_data($sqlData);
			if($update){
				$msg = $this->lang_item("msg_delete_success",false);
				echo json_encode(array('success'=>'true', 'mensaje' => $msg, 'id' => $id));
			}else{
				$msg = $this->lang_item("msg_err_delete",false);
				echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('', $msg ,false)));
			}
		}else{
			$msg = $this->lang_item("msg_err_delete",false);
			echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('', $msg ,false)));
		}
    }

    public function load_region(){
    	$id_sucursal = $this->ajax_post('id_sucursal');
    	$regiones_suc = $this->db_model->get_region_sucursal($id_sucursal);
    	$array_json = array(
    						 'region' => $regiones_suc[0]['region']
    						);
    	echo json_encode($array_json);
    }

    public function duplicar(){
    	$seccion   = $this->modulo.'/'.$this->seccion.'/'.$this->seccion.'_duplicar';
    	$btn_duplicar  = form_button(array('class'=>'btn btn-primary', 'name'=>'', 'onclick'=>'duplicar_recetas()','content'=>$this->lang_item("btn_duplicar")));
		$btn_reset     = form_button(array('class'=>'btn btn_primary', 'name'=>'resetduplicar_recetas','onclick'=>'clean_formulario()','content'=>$this->lang_item('btn_limpiar')));
    	$sqlData = array(
			 'buscar' => ''
			,'offset' => 0
			,'limit' => 0
			);
    	$sucursales = $this->sucursales->db_get_data($sqlData);
    	
		$dropdown_sucursales_x_receta = array(
						 'data'		=> $sucursales
						,'value' 	=> 'id_sucursal'
						,'text' 	=> array('sucursal')
						,'name' 	=> "lts_sucursales_receta"
						,'leyenda' 	=> "-----"
						,'class' 	=> "requerido"
						,'event'    => array('event'      => 'onchange', 
											'function'    => 'load_receta', 
											'params'      => array('this.value'), 
											'params_type' => array(0))
					);
		$sucursales_x_receta = dropdown_tpl($dropdown_sucursales_x_receta);
		
		$recetas = array(
						 'name' 	=> "lts_recetas"
						,'class' 	=> "requerido limpio"
						);
		$list_recetas  = multi_dropdown_tpl($recetas);

		$dropdown_sucursales_x_familia = array(
						 'data'		=> $sucursales
						,'value' 	=> 'id_sucursal'
						,'text' 	=> array('sucursal')
						,'name' 	=> "lts_sucursales_familia"
						,'leyenda' 	=> "-----"
						,'class' 	=> "requerido"
						,'event'    => array('event'      => 'onchange', 
											'function'    => 'load_familia', 
											'params'      => array('this.value'), 
											'params_type' => array(0))
					);
		$sucursales_x_familia = dropdown_tpl($dropdown_sucursales_x_familia);

		$familias = array(
						 'name' 	=> "lts_familias"
						,'class' 	=> "requerido"
						);
		$list_familias  = multi_dropdown_tpl($familias);
		$dropdown_sucursales_destino = array(
						 'data'		=> $sucursales
						,'value' 	=> 'id_sucursal'
						,'text' 	=> array('sucursal')
						,'name' 	=> "lts_sucursales_destino"
						,'leyenda' 	=> "-----"
						,'class' 	=> "requerido"
					);
		$sucursales_destino = dropdown_tpl($dropdown_sucursales_destino);

    	$tabData['lbl_tipo_duplicado']      = $this->lang_item('lbl_tipo_duplicado');
    	$tabData['lbl_receta']              = $this->lang_item('lbl_receta');
    	$tabData['lbl_familia']             = $this->lang_item('lbl_familia');
    	$tabData['receta']                  = $this->lang_item('receta',false);
    	$tabData['familia']                 = $this->lang_item('familia',false);
    	$tabData['lbl_sucursales']          = $this->lang_item('lbl_sucursales');
    	$tabData['list_sucursales_receta']  = $sucursales_x_receta;
    	$tabData['list_sucursales_familia'] = $sucursales_x_familia;
    	$tabData['lbl_recetas']             = $this->lang_item('lbl_recetas');
    	$tabData['list_recetas']            = $list_recetas;
    	$tabData['lbl_familias']            = $this->lang_item('lbl_familias');
    	$tabData['list_familias']           = $list_familias;
    	$tabData['origen']					= $this->lang_item('origen');
    	$tabData['destino']					= $this->lang_item('destino');
    	$tabData['list_sucursales_destino'] = $sucursales_destino;
    	$tabData['button_duplicar']     = $btn_duplicar;
		$tabData['button_reset']      = $btn_reset;
    	if($this->ajax_post(false)){
			echo json_encode($this->load_view_unique($seccion,$tabData ,true));
		}
		else{
			return $this->load_view_unique($seccion, $tabData, true);
		}
    }

    public function load_receta(){
    	$id_sucursal = $this->ajax_post('id');
    	$recetas = $this->db_model->db_get_recetas_x_sucursal($id_sucursal);
    	if(!empty($recetas)){
    		$recetas = array(
							 'data'		=> $recetas
							,'value' 	=> 'id_nutricion_receta'
							,'text' 	=> array('receta')
							,'name' 	=> "lts_recetas"
							,'class' 	=> "requerido limpio"
						);
			$list_recetas  = multi_dropdown_tpl($recetas);

			echo json_encode($list_recetas);
    	}
    }

    public function load_familia(){
    	$id_sucursal = $this->ajax_post('id');
    	$familias = $this->db_model->db_get_familias_x_sucursal($id_sucursal);
    	if(!empty($familias)){
    		$familia = array(
							 'data'		=> $familias
							,'value' 	=> 'id_nutricion_familia'
							,'text' 	=> array('familia')
							,'name' 	=> "lts_familias"
							,'class' 	=> "requerido"
						);
			$list_familias  = multi_dropdown_tpl($familia);

			echo json_encode($list_familias);
    	}
    }

    public function duplicar_recetas(){
    	$objData = $this->ajax_post('objData');
    	if($objData['tipo'] && $objData['tipo'] == 'familia'){
    		if($objData['incomplete'] == 0 && $objData['lts_familias'] != ''){
    			if($objData['lts_sucursales_familia'] != $objData['lts_sucursales_destino']){
    				$get_recetas = $this->db_model->db_get_recetas_x_familia($objData['lts_familias']);
    				foreach ($get_recetas as $keyr => $valuer) {
    					$get_recetas[$keyr]['clave_corta'] = $this->cadena_aleatoria();
    					$get_recetas[$keyr]['id_sucursal'] = $objData['lts_sucursales_destino'];
    					$get_recetas[$keyr]['id_usuario']  = $this->session->userdata('id_usuario');
    					$get_recetas[$keyr]['timestamp']   = $this->timestamp();
    				}
	    			$insert = $this->db_model->db_insert_recetas_x_familia($get_recetas);
	    			if ($insert) {
	    				$msg = $this->lang_item("msg_insert_success",false);
						echo json_encode(array(  'success'=>'true', 'mensaje' => $msg));
	    			}else{
	    				$msg = $this->lang_item("msg_err_clv",false);
						echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('', $msg ,false)));
	    			}
    			}else{
    				$msg = $this->lang_item("msg_err_coincide_sucursal",false);
					echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('', $msg ,false)));
    			}
    		}else{
    			$msg = $this->lang_item("msg_campos_obligatorios",false);
				echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)));
    		}
    	}else{
    		if($objData['incomplete'] == 0 && $objData['lts_recetas'] != ''){
    			if($objData['lts_sucursales_receta'] != $objData['lts_sucursales_destino']){
    				$get_recetas = $this->db_model->db_get_recetas_x_receta($objData['lts_recetas']);
    				foreach ($get_recetas as $keyr => $valuer) {
    					$get_recetas[$keyr]['clave_corta'] = $this->cadena_aleatoria();
    					$get_recetas[$keyr]['id_sucursal'] = $objData['lts_sucursales_destino'];
	    				$get_recetas[$keyr]['id_usuario']  = $this->session->userdata('id_usuario');
	    				$get_recetas[$keyr]['timestamp']   = $this->timestamp();
    				}
	    				$insert = $this->db_model->db_insert_recetas_x_receta($get_recetas);
	    				if ($insert){
		    				$msg = $this->lang_item("msg_insert_success",false);
							echo json_encode(array(  'success'=>'true', 'mensaje' => $msg));
		    			}else{
		    				$msg = $this->lang_item("msg_err_clv",false);
							echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('', $msg ,false)));
		    			}
    			}else{
    				$msg = $this->lang_item("msg_err_coincide_sucursal",false);
					echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('', $msg ,false)));
    			}
    		}else{
    			$msg = $this->lang_item("msg_campos_obligatorios",false);
				echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)));
    		}
    	}
    }
}