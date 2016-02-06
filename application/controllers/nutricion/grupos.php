<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class grupos extends Base_Controller{
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
		$this->submodulo		= 'catalogos';
		$this->seccion          = 'grupos';
		$this->icon 			= 'iconfa-group'; 
		$this->path 			= $this->modulo.'/'.$this->seccion.'/'; 
		$this->view_content 	= 'content';
		$this->limit_max		= 5;
		$this->offset			= 0;
		// Tabs
		$this->tab1 			= 'agregar';
		$this->tab2 			= 'listado';
		$this->tab3 			= 'detalle';
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
		$pagina =(is_numeric($this->uri_segment_end()) ? $this->uri_segment_end() : "");
		// Nombre de Tabs
		$config_tab['names']    = array(
										 $this->lang_item($tab_1) 
										,$this->lang_item($tab_2) 
										,$this->lang_item($tab_3) 
								); 
		// Href de tabs
		$config_tab['links']    = array(
										 $path.$tab_1             
										,$path.$tab_2.'/'.$pagina 
										,$tab_3                   
								); 
		// Accion de tabs
		$config_tab['action']   = array(
									'load_content'
								   ,'load_content'
								   ,'' 
								);
		// Atributos 
		$config_tab['attr']     = array('','', array('style' => 'display:none'));

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
		
		$js['js'][]  = array('name' => $this->seccion, 'dirname' => $this->modulo);
		$this->load_view($this->uri_view_principal(), $data, $js);
	}

	public function listado($offset=0){
		$seccion 		= '/listado';
		$tab_detalle	= $this->tab3;	
		$limit 			= $this->limit_max;
		$uri_view 		= $this->modulo.$seccion;
		$url_link 		= $this->path.'listado';
		$filtro      	= ($this->ajax_post('filtro')) ? $this->ajax_post('filtro') : "";
		$sqlData        = array(
	                            'buscar'      	=> $filtro
	                           ,'offset' 		=> $offset
	                           ,'limit'      	=> $limit
	                           );
		$uri_segment     = $this->uri_segment();
		$total_rows	     = count($this->db_model->db_get_data($sqlData));
		$sqlData['aplicar_limit'] = true;
		$list_content    = $this->db_model->db_get_data($sqlData);
		$url             = base_url($url_link);
		$array_paginator = array(
			                    'evento_link' => 'onclick'
			                   ,'function_js' => 'load_content'
			                   ,'params_js'   =>'1'
			                   );
		$paginador       = $this->pagination_bootstrap->paginator_generate($total_rows, $url, $limit, $uri_segment, $array_paginator); 
		if($total_rows){
			foreach ($list_content as $value) {
				$atrr    = array(
								'href'    => '#',
							  	'onclick' => $tab_detalle.'('.$value['id_nutricion_grupos'].')'
						       );
				$grupos     = $this->db_model->db_get_grupo_user($value['id_nutricion_grupos']);
				$eliminar 	= '<span style="color:red;" id="ico-eliminar_'.$value['id_nutricion_grupos'].'" class="ico_eliminar fa fa-times" onclick="confirm_delete('.$value['id_nutricion_grupos'].')" title="'.$this->lang_item("lbl_eliminar").'"></span>';
				$btn_acciones['eliminar'] = (!empty($grupos))?$eliminar:'<span style="color:gray;" id="ico-eliminar_'.$value['id_nutricion_grupos'].'" class="ico_eliminar fa fa-times" title="'.$this->lang_item("lbl_eliminar").'"></span>';
				$btn_acciones['detalle'] 		= '<span id="ico-detalle_'.$value['id_nutricion_grupos'].'" class="ico_acciones ico_detalle fa fa-search-plus" onclick="detalle('.$value['id_nutricion_grupos'].')" title="'.$this->lang_item("detalle").'"></span>';
				$acciones = implode('&nbsp;&nbsp;&nbsp;',$btn_acciones);
				
				$tbl_data[] = array('id'            => $value['id_nutricion_grupos'],
									'grupo'          => tool_tips_tpl($value['grupo'], $this->lang_item("tool_tip"), 'right' , $atrr),
									'clave_corta'   => $value['clave_corta'],
									'sucursal'      => $value['sucursal'],
									'descripcion'   => $value['descripcion'],
									'acciones'		=> $acciones
									);
			}

			// Plantilla
			$tbl_plantilla = set_table_tpl();
			// Titulos de tabla
			$this->table->set_heading(	$this->lang_item("ID"),
										$this->lang_item("lbl_grupo"),
										$this->lang_item("lbl_clave_corta"),
										$this->lang_item("lbl_sucursal"),
										$this->lang_item("lbl_descripcion"),
										$this->lang_item("lbl_acciones"));
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
		$id_grupo = $this->ajax_post('id_grupo');
		$detalle  = $this->db_model->get_orden_unico_grupo($id_grupo);
		$seccion  = $this->tab3;
		$sqlData = array(
			 'buscar'      	 => ''
			,'offset' 		 => 0
			,'limit'      	 => 0
		);
		$sucursales_array     = array(
					 'data'     => $this->sucursales->db_get_data($sqlData)
					,'value' 	=> 'id_sucursal'
					,'text' 	=> array('sucursal')
					,'name' 	=> "lts_sucursales"
					,'class' 	=> "requerido"
					,'selected' => $detalle[0]['id_sucursales']
					);
		$sucursales            = dropdown_tpl($sucursales_array);
		$array_btn_save = array(
			                    'class'   => 'btn btn-primary'
			                   ,'name'    => 'actualizar'
			                   ,'onclick' => 'actualizar()'
			                   ,'content' => $this->lang_item("btn_guardar")
			                   );
		$btn_save                            = form_button($array_btn_save);
		$tab_edit['id_grupo']                = $id_grupo;
		$tab_edit['lbl_grupo']               = $this->lang_item("lbl_grupo");
		$tab_edit['lbl_clave_corta']         = $this->lang_item("lbl_clave_corta");
		$tab_edit['lbl_descripcion']         = $this->lang_item("lbl_descripcion");
		$tab_edit['txt_grupo']               = $detalle[0]['grupo'];
		$tab_edit['txt_clave_corta']         = $detalle[0]['clave_corta'];
		$tab_edit['txt_descripcion']         = $detalle[0]['descripcion'];
		$tab_edit['lbl_ultima_modificacion'] = $this->lang_item('lbl_ultima_modificacion');
        $tab_edit['val_fecha_registro']      = $detalle[0]['timestamp'];
		$tab_edit['lbl_fecha_registro']      = $this->lang_item('lbl_fecha_registro');
		$tab_edit['lbl_usuario_registro']    = $this->lang_item('lbl_usuario_registro');
		$tab_edit['lbl_sucursal']            = $this->lang_item('lbl_sucursal');
		$tab_edit['dropdown_sucursal']       = $sucursales;

		$this->load_database('global_system');
        $this->load->model('users_model');

		$usuario_registro                  = $this->users_model->search_user_for_id($detalle[0]['id_usuario']);
	    $usuario_name	                   = text_format_tpl($usuario_registro[0]['name'],"u");
	    $tab_edit['val_usuarios_registro'] = $usuario_name;

		if($detalle[0]['edit_id_usuario']){
			$usuario_registro = $this->users_model->search_user_for_id($detalle[0]['edit_id_usuario']);
			$usuario_name     = text_format_tpl($usuario_registro[0]['name'],"u");
			$tab_edit['val_ultima_modificacion'] = sprintf($this->lang_item('val_ultima_modificacion',false), $this->timestamp_complete($detalle[0]['edit_timestamp']), $usuario_name);
		}else{
			$usuario_name = '';
			$tab_edit['val_ultima_modificacion'] = $this->lang_item('lbl_sin_modificacion', false);
		}
		$tab_edit['button_save']      = $btn_save;
		$tab_edit['registro_por']     = $this->lang_item('registro_por', false);
		$tab_edit['usuario_registro'] = $usuario_name;
		$uri_view = $this->modulo.'/'.$this->seccion.'/'.$this->seccion.'_'.$seccion;
		echo json_encode($this->load_view_unique($uri_view,$tab_edit,true));
	}

	public function agregar(){
		$seccion = $this->modulo.'/'.$this->seccion.'/'.$this->seccion.'_save';
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
		$array_btn_save = array(
			                    'class'   => 'btn btn-primary'
			                   ,'name'    => 'save_grupo'
			                   ,'onclick' => 'agregar()'
			                   ,'content' => $this->lang_item("btn_guardar")
			                   ); 
		$btn_save  = form_button($array_btn_save);
		$array_btn_reset = array(
			                    'class'   => 'btn btn_primary'
			                   ,'name'    => 'reset','onclick' => 'clean_formulario()'
			                   ,'content' => $this->lang_item('btn_limpiar')
			                   );
		$btn_reset = form_button($array_btn_reset);

		$tab_save['lbl_grupo']          = $this->lang_item("lbl_grupo");
		$tab_save['lbl_clave_corta']    = $this->lang_item('lbl_clave_corta');
		$tab_save['lbl_descripcion']    = $this->lang_item('lbl_descripcion');
		$tab_save['lbl_sucursal']            = $this->lang_item('lbl_sucursal');
		$tab_save['dropdown_sucursal']  = $sucursales;

		$tab_save['button_save']     = $btn_save;
		$tab_save['button_reset']    = $btn_reset;

		if($this->ajax_post(false)){
			echo json_encode($this->load_view_unique($seccion,$tab_save,true));
		}else{
			return $this->load_view_unique($seccion, $tab_save, true);
		}
	}

	public function actualizar(){
		$objData  	= $this->ajax_post('objData');
		//print_debug($objData);
		if($objData['incomplete']>0){
			$msg = $this->lang_item("msg_campos_obligatorios",false);
			echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)));
		}else{
			$sqlData = array(
				'id_nutricion_grupos'     => $objData['id_grupo']
				,'grupo'                  => $objData['txt_grupo']
				,'clave_corta'            => $objData['txt_clave_corta']
				,'id_sucursales'          => $objData['lts_sucursales']
				,'descripcion'            => $objData['txt_descripcion']
				,'edit_timestamp'         => $this->timestamp()
				,'edit_id_usuario'        => $this->session->userdata('id_usuario')
				);
			$insert = $this->db_model->db_update_data($sqlData);
			if($insert){
				$msg = $this->lang_item("msg_update_success",false);
				echo json_encode(array(  'success'=>'true', 'mensaje' => $msg ));
			}else{
				$msg = $this->lang_item("msg_err_clv",false);
				echo json_encode( array( 'success'=>'false', 'mensaje' =>alertas_tpl('', $msg ,false)));
			}
		}
	}

	public function insert(){
		$objData  	= $this->ajax_post('objData');
		//print_debug($objData);
		if($objData['incomplete']>0){
			$msg = $this->lang_item("msg_campos_obligatorios",false);
			echo json_encode( array( 'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)));
		}else{
			$data_insert = array(
				  'grupo'         => $objData['txt_grupo']
				 ,'clave_corta'   => $objData['txt_clave_corta']
				 ,'descripcion'   => $objData['txt_descripcion']
				 ,'id_sucursales' => $objData['lts_sucursales_agregar']
				 ,'id_usuario'  => $this->session->userdata('id_usuario')
				 ,'timestamp'   => $this->timestamp()
				);
			$insert = $this->db_model->db_insert_data($data_insert);
			if($insert){
				$msg = $this->lang_item("msg_insert_success",false);
				echo json_encode(array(  'success'=>'true', 'mensaje' => $msg));
			}else{
				$msg = $this->lang_item("msg_err_clv",false);
				echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('', $msg ,false)));
			}
		}
	}

	public function eliminar_registro(){
		$id_grupo = $this->ajax_post('id_grupo');
		$area = $this->db_model->db_get_grupo_user($id_grupo);
		if(empty($grupos)){
			$sqlData = array(
					'id_nutricion_grupos'     => $id_grupo
		            ,'activo'                 => 0
		            ,'edit_id_usuario'        => $this->session->userdata('id_usuario')
					,'edit_timestamp'         => $this->timestamp()
					);
			
			$update = $this->db_model->bd_delete_data($sqlData);
			if($update){
				$msg = $this->lang_item("msg_delete_success",false);
				echo json_encode(array('success'=>'true', 'mensaje' => $msg, 'id_grupo' => $id_grupo));
			}else{
				$msg = $this->lang_item("msg_err_delete",false);
				echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('', $msg ,false)));
			}
		}else{
			$msg = $this->lang_item("msg_err_delete",false);
			echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('', $msg ,false)));
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
		if(count($lts_content)>0){
			foreach ($lts_content as $value){
				$set_data[] = array(
									 $value['grupo'],
									 $value['clave_corta'],
									 $value['descripcion']
									 );
			}
			
			$set_heading = array(
									$this->lang_item("lbl_grupo"),
									$this->lang_item("lbl_clave_corta"),
									$this->lang_item("lbl_descripcion")
									);
	
		}

		$params = array(	'title'   => $this->lang_item("lbl_grupo"),
							'items'   => $set_data,
							'headers' => $set_heading
						);
		$this->excel->generate_xlsx($params);
	}
}