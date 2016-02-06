<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class lineas extends Base_Controller { 

	var $uri_modulo     = 'compras/';
	var $uri_submodulo  = 'catalogos';
	var $uri_seccion    = 'lineas';
	var $view_content   = 'content';
	
	public function __construct(){
		parent::__construct();
		$this->load->model($this->uri_modulo.'catalogos_model');
		$this->lang->load("compras/catalogos","es_ES");
	}

	public function config_tabs(){
		$pagina =(is_numeric($this->uri_segment_end()) ? $this->uri_segment_end() : "");
		$config_tab['names']    = array($this->lang_item("nueva_linea"), 
										$this->lang_item("listado_linea"), 
										$this->lang_item("detalle_linea")
								); 
		$config_tab['links']    = array('compras/lineas/agregar_linea', 
										'compras/lineas/listado_lineas/'.$pagina, 
										'detalle_linea'
										); 
		$config_tab['action']   = array('load_content',
										'load_content', 
										''
										);
		$config_tab['attr']     = array('','', array('style' => 'display:none'));

		return $config_tab;
	}
	private function uri_view_principal(){
		return $this->uri_modulo.$this->view_content;
	}

	public function index($offset = 0){
		
		$view_listado_lineas      = $this->listado_lineas($offset);
		
		$contenidos_tab           = $view_listado_lineas;

		$data['titulo_seccion']   = $this->lang_item("linea");
		$data['titulo_submodulo'] = $this->lang_item("titulo_submodulo");
		$data['icon']             = 'fa fa-tasks';
		$data['tabs']             = tabbed_tpl($this->config_tabs(),base_url(),2,$contenidos_tab);
		
		$js['js'][]     = array('name' => 'lineas', 'dirname' => 'compras');
		$this->load_view($this->uri_view_principal(), $data, $js);
	}

	public function listado_lineas($offset = 0){
		$data_tab_2  = "";
		$filtro      = ($this->ajax_post('filtro')) ? $this->ajax_post('filtro') : "";
		$uri_view    = $this->uri_modulo.'/listado';
		$limit       = 10;
		$uri_segment = $this->uri_segment(); 
		$lts_content = $this->catalogos_model->get_lineas($limit, $offset, $filtro);

		$total_rows  = count($this->catalogos_model->get_lineas($limit, $offset, $filtro, false));
		$url         = base_url($this->uri_modulo.$this->uri_submodulo.'/'.$this->uri_seccion.'/listado_lineas');
		$paginador   = $this->pagination_bootstrap->paginator_generate($total_rows, $url, $limit, $uri_segment, array('evento_link' => 'onclick', 'function_js' => 'load_content', 'params_js'=>'1'));
	
		if($total_rows>0){
			foreach ($lts_content as $value){
				$atrr = array(
								'href' => '#',
							  	'onclick' => 'detalle_linea('.$value['id_compras_linea'].')'
						);
				$lineas = $this->catalogos_model->db_get_lineas_user($value['id_compras_linea']);
				$eliminar 	= '<span style="color:red;" id="ico-eliminar_'.$value['id_compras_linea'].'" class="ico_eliminar fa fa-times" onclick="confirm_delete('.$value['id_compras_linea'].')" title="'.$this->lang_item("lbl_eliminar").'"></span>';
				$btn_acciones['eliminar'] = ($lineas[0]['num_lineas'] == 0)?$eliminar:'<span style="color:gray;" id="ico-eliminar_'.$value['id_compras_linea'].'" class="ico_eliminar fa fa-times" title="'.$this->lang_item("lbl_eliminar").'"></span>';
				$acciones = implode('&nbsp;&nbsp;&nbsp;',$btn_acciones);
				$tbl_data[] = array('id'             => $value['linea'],
									'linea'          => tool_tips_tpl($value['linea'], $this->lang_item("tool_tip"), 'right' , $atrr),
									'clave_corta'    => $value['clave_corta'],
									'descripcion'    => $value['descripcion'],
									'acciones'       => $acciones);
			}

			$tbl_plantilla = set_table_tpl();
		
			$this->table->set_heading(	$this->lang_item("id"),
										$this->lang_item("linea"),
										$this->lang_item("cvl_corta"),
										$this->lang_item("descripcion"),
										$this->lang_item("acciones"));
			$this->table->set_template($tbl_plantilla);
			$tabla = $this->table->generate($tbl_data);
			
			$buttonTPL = array( 'text'       => $this->lang_item("btn_xlsx"), 
								'iconsweets' => 'fa fa-file-excel-o',
								'href'       => base_url($this->uri_modulo.$this->uri_seccion.'/export_xlsx?filtro='.base64_encode($filtro))
								);

		}else{
			$buttonTPL = "";
			$msg   = $this->lang_item("msg_query_null");
			$tabla = alertas_tpl('', $msg ,false);
		}
			
			$data_tab_2['filtro']    = ($filtro!="") ? sprintf($this->lang_item("msg_query_search"),$total_rows , $filtro) : "";
			$data_tab_2['tabla']     = $tabla;
			$data_tab_2['export']    = button_tpl($buttonTPL);
			$data_tab_2['paginador'] = $paginador;
			$data_tab_2['item_info'] = $this->pagination_bootstrap->showing_items($limit, $offset, $total_rows);

			if($this->ajax_post(false)){
				echo json_encode( $this->load_view_unique($uri_view , $data_tab_2, true));
			}else{
				return $this->load_view_unique($uri_view , $data_tab_2, true);
			}
	}
	public function detalle_linea(){

		$uri_view              = $this->uri_modulo.$this->uri_submodulo.'/'.$this->uri_seccion.'/lineas_edit';
		$id_linea              = $this->ajax_post('id_linea');
		$detalle_linea         = $this->catalogos_model->get_linea_unico($id_linea);
		$btn_save              = form_button(array('class'=>"btn btn-primary",'name' => 'update_linea' , 'onclick'=>'update_linea()','content' => $this->lang_item("btn_guardar") ));
		
		$data_tab_3['id_linea']              = $id_linea;
        $data_tab_3["nombre_linea"]          = $this->lang_item("nombre_linea");
		$data_tab_3["cvl_corta"]        	 = $this->lang_item("cvl_corta");
		$data_tab_3["descrip"]         	     = $this->lang_item("descripcion");
		$data_tab_3["registro_por"]    	     = $this->lang_item("registro_por");
		$data_tab_3["fecha_registro"]        = $this->lang_item("fecha_registro");
		$data_tab_3["lbl_usuario_registro"]    = $this->lang_item("lbl_usuario_registro");
		$data_tab_3["lbl_fecha_registro"]      = $this->lang_item("lbl_fecha_registro");
		$data_tab_3['lbl_ultima_modificacion'] = $this->lang_item('lbl_ultima_modificacion');
        $data_tab_3['linea']                 = $detalle_linea[0]['linea'];
		$data_tab_3['clave_corta']           = $detalle_linea[0]['clave_corta'];
        $data_tab_3['descripcion']           = $detalle_linea[0]['descripcion'];
        $data_tab_3['timestamp']             = $detalle_linea[0]['timestamp'];
        $data_tab_3['button_save']           = $btn_save;
        
        $this->load_database('global_system');
        $this->load->model('users_model');
        if($detalle_linea[0]['edit_id_usuario']){
        	$usuario_registro                   = $this->users_model->search_user_for_id($detalle_linea[0]['edit_id_usuario']);
        	$usuario_name 				        = text_format_tpl($usuario_registro[0]['name'],"u");
        	$data_tab_3['val_ultima_modificacion'] = sprintf($this->lang_item('val_ultima_modificacion', false), $this->timestamp_complete($detalle_linea[0]['edit_timestamp']), $usuario_name);
    	}else{
    		$usuario_name = '';
    		$data_tab_3['val_ultima_modificacion'] = $this->lang_item('lbl_sin_modificacion', false);
    	}
        $usuario_registro               = $this->users_model->search_user_for_id($detalle_linea[0]['id_usuario']);
        $data_tab_3['usuario_registro'] = text_format_tpl($usuario_registro[0]['name'],"u");
		echo json_encode( $this->load_view_unique($uri_view ,$data_tab_3, true));
	}

	public function agregar_linea(){
		
		$uri_view       = $this->uri_modulo.$this->uri_submodulo.'/'.$this->uri_seccion.'/lineas_save';
		$btn_save       = form_button(array('class'=>"btn btn-primary",'name' => 'save_linea','onclick'=>'insert_linea()' , 'content' => $this->lang_item("btn_guardar") ));
		$btn_reset      = form_button(array('class'=>"btn btn-primary",'name' => 'reset','value' => 'reset','onclick'=>'clean_formulario()','content' => $this->lang_item("btn_limpiar")));

		$data_tab_1["nombre_linea"] = $this->lang_item("nombre_linea");
		$data_tab_1["cvl_corta"]    = $this->lang_item("cvl_corta");
		$data_tab_1["descrip"]      = $this->lang_item("descripcion");

        $data_tab_1['button_save']       = $btn_save;
        $data_tab_1['button_reset']      = $btn_reset;

        if($this->ajax_post(false)){
				echo json_encode($this->load_view_unique($uri_view , $data_tab_1, true));
		}else{
			return $this->load_view_unique($uri_view , $data_tab_1, true);
		}
	}

	public function insert_linea(){
		$incomplete  = $this->ajax_post('incomplete');
		if($incomplete>0){
			$msg = $this->lang_item("msg_campos_obligatorios",false);
			echo json_encode( array( 'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)) );
		}else{
			
			$linea        = $this->ajax_post('linea');
			$clave_corta  = $this->ajax_post('clave_corta');
			$descripcion  = $this->ajax_post('descripcion');
			$data_insert = array('linea'          => $linea,
								 'clave_corta'    => $clave_corta, 
								 'descripcion'    => $descripcion,
								 'id_usuario'     => $this->session->userdata('id_usuario'),
								 'timestamp'      => $this->timestamp());
			$insert = $this->catalogos_model->insert_linea($data_insert);

			if($insert){
				$msg = $this->lang_item("msg_insert_success",false);
				echo json_encode(array(  'success'=>'true', 'mensaje' => $msg));
			}else{
				$msg = $this->lang_item("msg_err_clv",false);
				echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('', $msg ,false)));
			}
		}
	}

	public function update_linea(){
		$incomplete  = $this->ajax_post('incomplete');
		if($incomplete>0){
			$msg = $this->lang_item("msg_campos_obligatorios",false);
			echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)));
		}else{
			$id_linea         = $this->ajax_post('id_linea');
			$linea            = $this->ajax_post('linea');
			$clave_corta      = $this->ajax_post('clave_corta');
			$descripcion      = $this->ajax_post('descripcion');
			
			$data_update      = array('linea'            => $linea,
									  'clave_corta'      => $clave_corta, 
									  'descripcion'      => $descripcion
									  ,'edit_id_usuario' => $this->session->userdata('id_usuario')
									  ,'edit_timestamp'  => $this->timestamp());


			$insert = $this->catalogos_model->update_linea($data_update,$id_linea);

			if($insert){
				$msg = $this->lang_item("msg_update_success",false);
				echo json_encode(array(  'success'=>'true', 'mensaje' => $msg ));
			}else{
				$msg = $this->lang_item("msg_err_clv",false);
				echo json_encode( array( 'success'=>'false', 'mensaje' =>alertas_tpl('', $msg ,false)));
			}
		}
	}

	public function export_xlsx(){
		$filtro      = ($this->ajax_get('filtro')) ?  base64_decode($this->ajax_get('filtro') ): "";
		$lts_content = $this->catalogos_model->get_lineas('', '', $filtro , false);
		if(count($lts_content)>0){
			foreach ($lts_content as $value) {
				$set_data[] = array(
									$value['linea'],
									$value['clave_corta'],
									$value['descripcion']);
			}
			
			$set_heading = array(
									$this->lang_item("linea"),
									$this->lang_item("cvl_corta"),
									$this->lang_item("descripcion"));
	
		}

		$params = array(	'title'   => $this->lang_item("catalogo", false).$this->lang_item("linea"),
							'items'   => $set_data,
							'headers' => $set_heading
						);
		
		$this->excel->generate_xlsx($params);
	}

	public function eliminar_registro(){
		$id_linea = $this->ajax_post('id_linea');
		$lineas = $this->catalogos_model->db_get_lineas_user($id_linea);
		if($lineas[0]['num_lineas'] == 0){
			$sqlData = array(
					'id_compras_linea'        => $id_linea
		            ,'activo'                 => 0
		            ,'edit_id_usuario'        => $this->session->userdata('id_usuario')
					,'edit_timestamp'         => $this->timestamp()
					);
			
			$update = $this->catalogos_model->bd_delete_data_linea($sqlData);
			if($update){
				$msg = $this->lang_item("msg_delete_success",false);
				echo json_encode(array('success'=>'true', 'mensaje' => $msg, 'id_linea' => $id_linea));
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