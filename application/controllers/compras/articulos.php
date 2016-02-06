<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class articulos extends Base_Controller { 
	
	var $uri_modulo     = 'compras/';
	var $uri_submodulo  = 'catalogos/';
	var $view_content   = 'content';
	var $uri_seccion   = 'articulos';
	
	public function __construct(){
		parent::__construct();
		$this->load->model($this->uri_modulo.'catalogos_model');
		$this->load->model('compras/articulos_model','articulos');
		$this->lang->load("compras/articulos","es_ES");
	}
	public function config_tabs(){
		$pagina =(is_numeric($this->uri_segment_end()) ? $this->uri_segment_end() : "");
		$config_tab['names']    = array($this->lang_item("agregar_articulo"), 
										$this->lang_item("listado_articulos"), 
										$this->lang_item("detalle_articulo")
								); 
		$config_tab['links']    = array('compras/articulos/agregar_articulo', 
										'compras/articulos/listado_articulos/'.$pagina, 
										'detalle_articulo'
										); 
		$config_tab['action']   = array('load_content',
										'load_content', 
										''
										);
		$config_tab['attr']     = array('','', array('style' => 'display:none'));

		$config_tab['style_content'] = array('');
		return $config_tab;
	}
	private function uri_view_principal(){
		return $this->uri_modulo.$this->view_content;
	}
	public function index($offset = 0){
		$view_listado_articulo    = $this->listado_articulos($offset);
		$contenidos_tab           = $view_listado_articulo;
		$data['titulo_seccion']   = $this->lang_item("articulos");
		$data['titulo_submodulo'] = $this->lang_item("titulo_submodulo");
		$data['icon']             = 'fa fa-cubes';
		$data['tabs']             = tabbed_tpl($this->config_tabs(),base_url(),2,$contenidos_tab);
		
		$js['js'][]     = array('name' => 'articulos', 'dirname' => 'compras');
		$this->load_view($this->uri_view_principal(), $data, $js);
	}
	public function listado_articulos($offset = 0){
		$data_tab_2  = "";
		$filtro      = ($this->ajax_post('filtro')) ? $this->ajax_post('filtro') : "";
		$uri_view    = $this->uri_modulo.'/listado';
		$limit       = 10;
		$uri_segment = $this->uri_segment(); 
		$lts_content = $this->catalogos_model->get_articulos($limit, $offset, $filtro);
		$total_rows  = count($this->catalogos_model->get_articulos($limit, $offset, $filtro, false));
		$url         = base_url($this->uri_modulo.$this->uri_seccion.'/listado_articulos');
		$paginador   = $this->pagination_bootstrap->paginator_generate($total_rows, $url, $limit, $uri_segment, array('evento_link' => 'onclick', 'function_js' => 'load_content', 'params_js'=>'1'));
		if($total_rows>0){
			foreach ($lts_content as $value) {
				$atrr = array(
								'href' => '#',
							  	'onclick' => 'detalle_articulo('.$value['id_compras_articulo'].')'
						);
				$articulo = $this->articulos->db_get_articulos_usados($value['id_compras_articulo']);
				//print_debug($articulo);
				$eliminar 	= '<span style="color:red;" id="ico-eliminar_'.$value['id_compras_articulo'].'" class="ico_eliminar quitar_'.$value['id_compras_articulo'].' fa fa-times" onclick="confirm_delete('.$value['id_compras_articulo'].')" title="'.$this->lang_item("lbl_eliminar").'"></span>';
				$btn_acciones['eliminar'] = ($articulo[0]['num_articulos'] == 0)?$eliminar:'<span style="color:gray;" id="ico-eliminar_'.$value['id_compras_articulo'].'" class="ico_eliminar fa fa-times" title="'.$this->lang_item("lbl__no_eliminar").'"></span>';
				$acciones = implode('&nbsp;&nbsp;&nbsp;',$btn_acciones);
				$tbl_data[] = array('id'             => $value['articulo'],
									'avatar'         => tool_tips_tpl(($value['avatar'] == '') ? return_avatar('articulos') : return_avatar('articulos', $value['avatar']), $this->lang_item("tool_tip"), 'right' , $atrr),
									'articulos'      => tool_tips_tpl($value['articulo'], $this->lang_item("tool_tip"), 'right' , $atrr),
									'clave_corta'    => $value['clave_corta'],
									'articulo_tipo'  => $value['articulo_tipo'],
									'linea'          => $value['linea'],
									'um'             => $value['um'],
									'descripcion'    => $value['descripcion'],
									'acciones'		 => $acciones);
			}

			$tbl_plantilla = set_table_tpl();
		
			$this->table->set_heading(	$this->lang_item("articulos"),
										$this->lang_item("foto"),
										$this->lang_item("articulos"),
										$this->lang_item("cvl_corta"),
										$this->lang_item("articulo_tipo"),
										$this->lang_item("lineas"),
										$this->lang_item("um"),
										$this->lang_item("descripcion"),
										$this->lang_item("lbl_acciones"));
			
			$this->table->set_template($tbl_plantilla);
			$tabla = $this->table->generate($tbl_data);
			
			$buttonTPL = array( 'text'       => $this->lang_item("btn_xlsx"), 
							'iconsweets' => 'fa fa-file-excel-o',
							'href'       => base_url($this->uri_modulo.$this->uri_seccion.'/export_xlsx?filtro='.base64_encode($filtro))
							);
		}
		else{
				$buttonTPL = "";
				$msg   = $this->lang_item("msg_query_null");
				$tabla = alertas_tpl('', $msg ,false);
			}	
				$data_tab_2['filtro']         = ($filtro!="") ? sprintf($this->lang_item("msg_query_search", false),$total_rows , $filtro) : "";
				$data_tab_2['tabla']          = $tabla;
				$data_tab_2['export']         = button_tpl($buttonTPL);
				$data_tab_2['paginador']      = $paginador;
				$data_tab_2['item_info']      = $this->pagination_bootstrap->showing_items($limit, $offset, $total_rows);

			if($this->ajax_post(false)){
				echo json_encode( $this->load_view_unique($uri_view , $data_tab_2, true));
			}else{
				return $this->load_view_unique($uri_view , $data_tab_2, true);
			}	
	}
	public function agregar_articulo(){
		
		$uri_view       = $this->uri_modulo.$this->uri_submodulo.$this->uri_seccion.'/articulo_save';
		// listas
		$dropArray2 = array(
					 'data'		=> $this->catalogos_model->get_lineas('','','',false)
					,'value' 	=> 'id_compras_linea'
					,'text' 	=> array('clave_corta','linea')
					,'name' 	=> "lts_lineas_detalle"
					,'class' 	=> "requerido"
				);
		$lineas         = dropdown_tpl($dropArray2);
		$dropArray3 = array(
					 'data'		=> $this->catalogos_model->get_um('','','',false)
					,'value' 	=> 'id_compras_um'
					,'text' 	=> array('clave_corta','um')
					,'name' 	=> "lts_um_detalle"
					,'class' 	=> "requerido"
					);
		$um             = dropdown_tpl($dropArray3);
		$dropArray4 = array(
					 'data'		=> $this->catalogos_model->get_articulo_tipo('','','',false)
					,'value' 	=> 'id_articulo_tipo'
					,'text' 	=> array('clave_corta','articulo_tipo')
					,'name' 	=> "lst_articulo_tipo"
					,'class' 	=> "requerido"
					);
		$articulo_tipo = dropdown_tpl($dropArray4);
		// 
		$btn_save       = form_button(array('class'=>"btn btn-primary",'name' => 'save_articulo','onclick'=>'insert_articulo()' , 'content' => $this->lang_item("btn_guardar") ));
		$btn_reset      = form_button(array('class'=>"btn btn-primary",'name' => 'reset','value' => 'reset','onclick'=>'clean_formulario()','content' => $this->lang_item("btn_limpiar")));

        $data_tab_1['nombre_articulo']   = $this->lang_item("nombre_articulo",false);
        $data_tab_1['articulo_tipo']	 = $this->lang_item("articulo_tipo",false);
        $data_tab_1['cvl_corta']         = $this->lang_item("cvl_corta",false);
        $data_tab_1['linea']             = $this->lang_item("linea",false);
        $data_tab_1['um']                = $this->lang_item("um",false);
        $data_tab_1['descripcion']       = $this->lang_item("descripcion",false);
        $data_tab_1['list_linea']        = $lineas;
        $data_tab_1['list_um']           = $um;
        $data_tab_1['list_articulo_tipo']= $articulo_tipo;
        $data_tab_1['button_save']       = $btn_save;
        $data_tab_1['button_reset']      = $btn_reset;

        if($this->ajax_post(false)){
				echo json_encode($this->load_view_unique($uri_view , $data_tab_1, true));
		}else{
			return $this->load_view_unique($uri_view , $data_tab_1, true);
		}
	}
	public function detalle_articulo(){
		$id_articulo       = $this->ajax_post('id_articulo');
		$detalle_articulo  = $this->catalogos_model->get_articulo_unico($id_articulo);
		// listas
		$dropArray2 = array(
					 'data'		=> $this->catalogos_model->get_lineas('','','',false)
					,'value' 	=> 'id_compras_linea'
					,'text' 	=> array('clave_corta','linea')
					,'name' 	=> "lts_lineas_detalle"
					,'class' 	=> "requerido"
					,'selected' => $detalle_articulo[0]['id_compras_linea']
				);

		$lineas         = dropdown_tpl($dropArray2);
		$dropArray3 = array(
					 'data'		=> $this->catalogos_model->get_um('','','',false)
					,'value' 	=> 'id_compras_um'
					,'text' 	=> array('clave_corta','um')
					,'name' 	=> "lts_um_detalle"
					,'class' 	=> "requerido"
					,'selected' => $detalle_articulo[0]['id_compras_um']
					);

		$um             = dropdown_tpl($dropArray3);
		$dropArray4 = array(
					 'data'		=> $this->catalogos_model->get_articulo_tipo('','','',false)
					,'value' 	=> 'id_articulo_tipo'
					,'text' 	=> array('clave_corta','articulo_tipo')
					,'name' 	=> "lst_articulo_tipo"
					,'class' 	=> "requerido"
					,'selected' => $detalle_articulo[0]['id_articulo_tipo']
					);
		$articulo_tipo = dropdown_tpl($dropArray4);
		$btn_save    = form_button(array('class'=>"btn btn-primary",'name' => 'update_articulo' , 'onclick'=>'update_articulo()','content' => $this->lang_item("btn_guardar") ));
		$btn_enabled = button_tpl(array( 'text'       => $this->lang_item("delete"), 
											   'iconsweets' => 'iconfa-trash',
											   'event'      => array('event'    => 'onclick',
											   						 'function' => 'enabled_item',
							   										 'params'   => array($this->uri_modulo.$this->uri_seccion.'/enabled', $id_articulo)
							   										)
												));
		$data_tab_3['id_articulo']       	   = $id_articulo;
		$data_tab_3['nombre_articulo']   	   = $this->lang_item("nombre_articulo",false);
		$data_tab_3['articulo_tipo']		   = $this->lang_item("articulo_tipo",false);
		$data_tab_3['cvl_corta']         	   = $this->lang_item("cvl_corta",false);
        $data_tab_3['linea']             	   = $this->lang_item("linea",false);
        $data_tab_3['um']                	   = $this->lang_item("um",false);
        $data_tab_3['descripcion']       	   = $this->lang_item("descripcion",false);
        $data_tab_3["lbl_usuario_registro"]    = $this->lang_item("lbl_usuario_registro");
		$data_tab_3["lbl_fecha_registro"]      = $this->lang_item("lbl_fecha_registro");
		$data_tab_3['lbl_ultima_modificacion'] = $this->lang_item('lbl_ultima_modificacion');
		$data_tab_3['foto'] = $this->lang_item('foto');

		$data_tab_3['articulo_value']    = $detalle_articulo[0]['articulo'];
		$data_tab_3['cvl_value']         = $detalle_articulo[0]['clave_corta'];
		$data_tab_3['descripcion_value'] = $detalle_articulo[0]['descripcion'];
		$data_tab_3['timestamp']         = $detalle_articulo[0]['timestamp'];
        $data_tab_3['list_linea']        = $lineas;
        $data_tab_3['list_um']           = $um;
        $data_tab_3['list_articulo_tipo']= $articulo_tipo;
        $data_tab_3['button_save']       = $btn_save;
        $data_tab_3['foto_img']          = ($detalle_articulo[0]['avatar']=='') ? return_avatar('articulos','', false, 100, 100,true) : return_avatar('articulos', $detalle_articulo[0]['avatar'], false,  100, 100,true);
        $data_tab_3['change_avatar']          = $this->lang_item('change_avatar', false);
		$data_tab_3['local_img_upload']       = $this->lang_item('local_img_upload', false);
		$data_tab_3['rotate_left']        	  = $this->lang_item('rotate_left', false);
		$data_tab_3['rotate_right']       	  = $this->lang_item('rotate_right', false);
		$data_tab_3['save_avatar']        	  = $this->lang_item('save_avatar', false);
		$data_tab_3['hiddencropper']          = $id_articulo.'|'.$detalle_articulo[0]['avatar'];
        $this->load_database('global_system');
        $this->load->model('users_model');
        
        $usuario_registro                     = $this->users_model->search_user_for_id($detalle_articulo[0]['id_usuario']);
	    $usuario_name	                      = text_format_tpl($usuario_registro[0]['name'],"u");
	    $data_tab_3['val_usuarios_registro']  = $usuario_name;

	    if($detalle_articulo[0]['edit_id_usuario'])
		{
			$usuario_registro = $this->users_model->search_user_for_id($detalle_articulo[0]['edit_id_usuario']);
			$usuario_name = text_format_tpl($usuario_registro[0]['name'],"u");
			$data_tab_3['val_ultima_modificacion'] = sprintf($this->lang_item('val_ultima_modificacion',false), $this->timestamp_complete($detalle_articulo[0]['edit_timestamp']), $usuario_name);
		}
		else
		{
			$usuario_name = '';
			$data_tab_3['val_ultima_modificacion'] = $this->lang_item('lbl_sin_modificacion', false);
		}
        $uri_view       = $this->uri_modulo.$this->uri_submodulo.$this->uri_seccion.'/articulo_edit';
		
		echo json_encode( $this->load_view_unique($uri_view ,$data_tab_3, true));
	}
	public function insert_articulo(){
		$incomplete  = $this->ajax_post('incomplete');
		if($incomplete>0){
			$msg = $this->lang_item("msg_campos_obligatorios",false);
			echo json_encode( array( 'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)) );
		}else{
			$articulo     = $this->ajax_post('articulo');
			$clave_corta  = $this->ajax_post('clave_corta');
			$id_articulo_tipo = $this->ajax_post('lst_articulo_tipo');
			$linea        = $this->ajax_post('lts_lineas_detalle');
			$um           = $this->ajax_post('lts_um_detalle');
			$descripcion  = $this->ajax_post('descripcion');
			$data_insert = array('articulo' => $articulo,
								 'clave_corta'=> $clave_corta, 
								 'descripcion'=> $descripcion,
								 'id_articulo_tipo'=> $id_articulo_tipo,
								 'id_compras_linea'=> $linea,
								 'id_compras_um'=> $um,
								 'id_usuario' => $this->session->userdata('id_usuario'),
								 'timestamp'  => $this->timestamp());
			$insert = $this->catalogos_model->insert_articulo($data_insert);

			if($insert){
				$msg = $this->lang_item("msg_insert_success",false);
				echo json_encode(array(  'success'=>'true', 'mensaje' => $msg));
			}else{
				$msg = $this->lang_item("msg_err_clv",false);
				echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('', $msg ,false)));
			}
		}
	}
	public function update_articulo(){
		$incomplete  = $this->ajax_post('incomplete');
		if($incomplete>0){
			$msg = $this->lang_item("msg_campos_obligatorios",false);
			echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)));
		}else{			
			$id_articulo  = $this->ajax_post('id_articulo');
			$articulo     = $this->ajax_post('articulo');
			$id_articulo_tipo = $this->ajax_post('lst_articulo_tipo');
			$clave_corta  = $this->ajax_post('clave_corta');
			$linea        = $this->ajax_post('lts_lineas_detalle');
			$um           = $this->ajax_post('lts_um_detalle');
			$descripcion  = $this->ajax_post('descripcion');
			$data_update  = array('articulo' => $articulo,
								 'clave_corta'=> $clave_corta, 
								 'descripcion'=> $descripcion,
								 'id_articulo_tipo'=> $id_articulo_tipo,
								 'id_compras_linea'=> $linea,
								 'edit_timestamp' => $this->timestamp(),
								 'edit_id_usuario' => $this->session->userdata('id_usuario'),
								 'id_compras_um'=> $um);
			$insert = $this->catalogos_model->update_articulo($data_update,$id_articulo);

			if($insert){
				$msg = $this->lang_item("msg_update_success",false);
				echo json_encode(array(  'success'=>'true', 'mensaje' => $msg ));
			}else{
				$msg = $this->lang_item("msg_err_clv",false);
				echo json_encode( array( 'success'=>'false', 'mensaje' =>alertas_tpl('', $msg ,false)));
			}
		}
	}
	public function enabled(){
		$item     =  array('id_compras_articulo=' => $this->ajax_post('item'));
		$enabled  = $this->catalogos_model->enabled_item('av_compras_articulos', $item);
	}
	public function export_xlsx(){
		$filtro      = ($this->ajax_get('filtro')) ?  base64_decode($this->ajax_get('filtro') ): "";
		$lts_content = $this->catalogos_model->get_articulos('', '', $filtro , false);
		if(count($lts_content)>0){
			foreach ($lts_content as $value) {
				$set_data[] = array(
									 $value['articulo'],
									 $value['clave_corta'],
									 $value['articulo_tipo'],
									 $value['linea'],
									 $value['um'],
									 $value['descripcion']);
			}
			
			$set_heading = array(
									$this->lang_item("articulos"),
									$this->lang_item("cvl_corta"),
									$this->lang_item("articulo_tipo"),
									$this->lang_item("lineas"),
									$this->lang_item("u.m."),
									$this->lang_item("descripcion"));
		}

		$params = array(	'title'   => $this->lang_item("seccion"),
							'items'   => $set_data,
							'headers' => $set_heading
						);
		
		$this->excel->generate_xlsx($params);
	}
	public function eliminar_articulo(){
		$id_articulo = $this->ajax_post('id_articulo');
		$articulo = $this->articulos->db_get_articulos_usados($id_articulo);
		
		if($articulo[0]['num_articulos'] == 0){
			$sqlData = array(
					'id_compras_articulo'     => $id_articulo
		            ,'activo'                 => 0
		            ,'edit_id_usuario'        => $this->session->userdata('id_usuario')
					,'edit_timestamp'         => $this->timestamp()
					);
			
			$update = $this->articulos->bd_delete_data($sqlData);
			if($update){
				$msg = $this->lang_item("msg_delete_success",false);
				echo json_encode(array('success'=>'true', 'mensaje' => $msg, 'id_articulo' => $id_articulo));
			}else{
				$msg = $this->lang_item("msg_err_delete",false);
				echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('', $msg ,false)));
			}
		}else{
			$msg = $this->lang_item("msg_err_delete",false);
			echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('', $msg ,false)));
		}
	}
}