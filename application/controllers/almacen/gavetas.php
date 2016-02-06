<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class gavetas extends Base_Controller 
{
	private $modulo;
	private $submodulo;
	private $seccion;
	private $view_content;
	private $path;
	private $icon;

	private $offset, $limit_max;
	private $tab, $tab1, $tab2, $tab3;

	public function __construct()
	{
		parent::__construct();
		$this->modulo 			= 'almacen';
		$this->submodulo		= 'catalogos';
		$this->seccion          = 'gavetas';
		$this->icon 			= 'fa fa-inbox'; 
		$this->path 			= $this->modulo.'/'.$this->seccion.'/'; 
		$this->view_content 	= 'content';
		$this->limit_max		= 10;
		$this->offset			= 0;
		// Tabs
		$this->tab1 			= 'agregar';
		$this->tab2 			= 'listado';
		$this->tab3 			= 'detalle';
		// DB Model
		$this->load->model($this->modulo.'/'.$this->submodulo.'_model','db_model');
		// Diccionario
		$this->lang->load($this->modulo.'/'.$this->seccion,"es_ES");
	}

	public function config_tabs()
	{
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
	private function uri_view_principal()
	{
		return $this->modulo.'/'.$this->view_content; #almacen/content
	}

	public function index()
	{
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

	public function listado($offset=0)
	{
		$seccion 		= '/listado';
		$tab_detalle	= $this->tab3;	
		$limit 			= $this->limit_max;
		$uri_view 		= $this->modulo.$seccion;
		$url_link 		= $this->path.'listado';	

		$filtro      = ($this->ajax_post('filtro')) ? $this->ajax_post('filtro') : "";

		$sqlData = array(
			 'buscar'      	=> $filtro
			,'offset' 		=> $offset
			,'limit'      	=> $limit
		);
		$uri_segment  = $this->uri_segment(); 
		$total_rows	  = count($this->db_model->db_get_data_gaveta($sqlData));
		$sqlData['aplicar_limit'] = false;	
		$list_content = $this->db_model->db_get_data_gaveta($sqlData);
		$url          = base_url($url_link);
		$paginador    = $this->pagination_bootstrap->paginator_generate($total_rows, $url, $limit, $uri_segment, array('evento_link' => 'onclick', 'function_js' => 'load_content', 'params_js'=>'1'));
		if($total_rows>0)
		{
			foreach ($list_content as $value)
			{
				$atrr = array(
								'href'    => '#',
							  	'onclick' => 'detalle('.$value['id_almacen_gavetas'].')'
						);
				// Validacion de estock en gaveta
				//$stock = $this->db_model->db_get_data_stock_por_gaveta(array('id_almacen' => $value['id_almacen_almacenes'], 'id_pasillo' => $value['id_almacen_pasillos'], 'id_gaveta' => $value['id_almacen_gavetas']));
				$gabetas = $this->db_model->db_get_gabeta_usada($value['id_almacen_gavetas']);
				// Acciones
				$accion_id 						= $value['id_almacen_gavetas'];
				$btn_acciones['detalle'] 		= '<span id="ico-detalle_'.$accion_id.'" class="ico_acciones ico_detalle fa fa-search-plus" onclick="detalle('.$accion_id.')" title="'.$this->lang_item("detalle").'"></span>';
				$eliminar 	= '<span style="color:red;" id="ico-eliminar_'.$value['id_almacen_gavetas'].'" class="ico_eliminar fa fa-times" onclick="confirm_delete('.$value['id_almacen_gavetas'].')" title="'.$this->lang_item("lbl_eliminar").'"></span>';
				$btn_acciones['eliminar'] = ($gabetas[0]['num_gabetas'] == 0)?$eliminar:'<span style="color:gray;" id="ico-eliminar_'.$value['id_almacen_gavetas'].'" class="ico_eliminar fa fa-times" title="'.$this->lang_item("lbl_eliminar").'"></span>';
				$acciones = implode('&nbsp;&nbsp;&nbsp;',$btn_acciones);
				$edit = (!$value['edit'])?'*':'';
				// Datos para tabla
				$tbl_data[] = array('id'            => $value['clave_corta'],
									'gavetas'       => $edit.tool_tips_tpl($value['gavetas'], $this->lang_item("tool_tip"), 'right' , $atrr),
									'clave_corta'   => $edit.$value['clave_corta'],
									'gaveta'        => $value['almacenes'],
									'pasillos'      => $value['pasillos'],
									'descripcion'   => $value['descripcion'],
									'acciones' 		=> $acciones
									);	
			}
			// Plantilla
			$tbl_plantilla = set_table_tpl();
			// Titulos de tabla
			$this->table->set_heading(	$this->lang_item("cvl_corta"),
										$this->lang_item("gaveta"),
										$this->lang_item("cvl_corta"),
										$this->lang_item("almacen"),
										$this->lang_item("pasillos"),
										$this->lang_item("descripcion"),
										$this->lang_item("acciones")
										);
			// Generar tabla
			$this->table->set_template($tbl_plantilla);
			$tabla = $this->table->generate($tbl_data);
			$buttonTPL = array( 'text'       => $this->lang_item("btn_xlsx"), 
								'iconsweets' => 'fa fa-file-excel-o',
								'href'       => base_url($this->path.'export_xlsx?filtro='.base64_encode($filtro))
								);
		}
		else
		{
			$buttonTPL = "";
			$msg       = $this->lang_item("msg_query_null");
			$tabla     = alertas_tpl('', $msg ,false);
		}
		$tabData['filtro']    = (isset($filtro) && $filtro!="") ? sprintf($this->lang_item("msg_query_search"),$total_rows , $filtro) : "";
		$tabData['tabla']     = $tabla;
		$tabData['export']    = button_tpl($buttonTPL);
		$tabData['paginador'] = $paginador;
		$tabData['item_info'] = $this->pagination_bootstrap->showing_items($limit, $offset, $total_rows);
		if($this->ajax_post(false)){
			echo json_encode( $this->load_view_unique($uri_view , $tabData, true));
		}
		else
		{
			return $this->load_view_unique($uri_view , $tabData, true);
		}
	}

	public function detalle()
	{
		$id_almacen_gavetas   = $this->ajax_post('id_gaveta');
		$detalle  		      = $this->db_model->get_orden_unico_gaveta($id_almacen_gavetas);
		$seccion              = 'detalle';
		$tab_detalle          = $this->tab3;
		$almacenes_array      = array(
					 'data'		=> $this->db_model->db_get_data_almacen('','','',false)
					,'value' 	=> 'id_almacen_almacenes'
					,'text' 	=> array('almacenes')
					,'name' 	=> "lts_almacenes"
					,'class' 	=> "requerido"
					,'selected' => $detalle[0]['id_almacen_almacenes']
					);
		$almacenes      = dropdown_tpl($almacenes_array);
		$pasillos_array = array(
					 'data'		=> $this->db_model->db_get_data_pasillo('','','',false)
					,'value' 	=> 'id_almacen_pasillos'
					,'text' 	=> array('pasillos')
					,'name' 	=> "lts_pasillos"
					,'class' 	=> ""
					,'selected' => $detalle[0]['id_almacen_pasillos']
					);
		$pasillos     = dropdown_tpl($pasillos_array);
		$btn_save     = ($detalle[0]['edit'])?form_button(array('class'=>"btn btn-primary",'name' => 'actualizar' , 'onclick'=>'actualizar()','content' => $this->lang_item("btn_guardar") )):$this->lang_item("no_editable");
        $tabData['id_gaveta']              = $id_almacen_gavetas;
        $tabData["lbl_gavetas"]            = $this->lang_item("lbl_gavetas");
		$tabData["lbl_clave_corta"]        = $this->lang_item("lbl_clave_corta");
		$tabData["lbl_descripcion"]        = $this->lang_item("lbl_descripcion");
		$tabData["list_almacen"]           = $almacenes;
		$tabData["lbl_almacen"]                = $this->lang_item("lbl_almacen");
		$tabData["list_pasillo"]           = $pasillos;
		$tabData["lbl_pasillo"]                = $this->lang_item("lbl_pasillo");
        $tabData['gaveta']                 = $detalle[0]['gavetas'];
		$tabData['clave_corta']            = $detalle[0]['clave_corta'];
        $tabData['descripcion']            = $detalle[0]['descripcion'];
        $tabData['lbl_ultima_modificacion'] = $this->lang_item('lbl_ultima_modificacion', false);
        $tabData['val_fecha_registro']     = $detalle[0]['timestamp'];
		$tabData['lbl_fecha_registro']     = $this->lang_item('lbl_fecha_registro', false);
		$tabData['lbl_usuario_registro']    = $this->lang_item('lbl_usuario_registro', false);

        $this->load_database('global_system');
        $this->load->model('users_model');

        $usuario_registro                  = $this->users_model->search_user_for_id($detalle[0]['id_usuario']);
	    $usuario_name	                   = text_format_tpl($usuario_registro[0]['name'],"u");
	    $tabData['val_usuarios_registro']  = $usuario_name;

	    if($detalle[0]['edit_id_usuario'])
        {
        	$usuario_registro                   = $this->users_model->search_user_for_id($detalle[0]['edit_id_usuario']);
        	$usuario_name 				        = text_format_tpl($usuario_registro[0]['name'],"u");
        	$tabData['val_ultima_modificacion'] = sprintf($this->lang_item('val_ultima_modificacion', false), $this->timestamp_complete($detalle[0]['edit_timestamp']), $usuario_name);
        }
        else
        {
        	$usuario_name = '';
    		$tabData['val_ultima_modificacion'] = $this->lang_item('lbl_sin_modificacion', false);
        }

        $tabData['button_save']           = $btn_save;
        $tabData['registro_por']    	= $this->lang_item("registro_por",false);
      	$tabData['usuario_registro']	= $usuario_name;

		$uri_view   					 = $this->modulo.'/'.$this->submodulo.'/'.$this->seccion.'/'.$this->seccion.'_'.$seccion;
		echo json_encode( $this->load_view_unique($uri_view ,$tabData, true));
	}

	public function actualizar(){
		$incomplete  = $this->ajax_post('incomplete');
		if($incomplete>0){
			$msg = $this->lang_item("msg_campos_obligatorios",false);
			echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)));
		}else{
			$sqlData = array(
						 'id_almacen_gavetas'	    => $this->ajax_post('id_gaveta')
						,'gavetas' 		            => $this->ajax_post('gavetas')
						,'clave_corta' 				=> $this->ajax_post('clave_corta')
						,'descripcion'				=> $this->ajax_post('descripcion')
						,'id_almacen_almacenes'	    => $this->ajax_post('id_almacen')
						,'id_almacen_pasillos'	    => $this->ajax_post('id_pasillo')
						,'edit_timestamp'			=> $this->timestamp()
						,'edit_id_usuario'			=> $this->session->userdata('id_usuario')
						);
			$insert = $this->db_model->db_update_data_gaveta($sqlData);
			if($insert){
				$msg = $this->lang_item("msg_update_success",false);
				echo json_encode(array(  'success'=>'true', 'mensaje' => $msg ));
			}else{
				$msg = $this->lang_item("msg_err_clv",false);
				echo json_encode( array( 'success'=>'false', 'mensaje' =>alertas_tpl('', $msg ,false)));
			}
		}
	}
	public function agregar()
	{
		$seccion         = $this->modulo.'/'.$this->submodulo.'/'.$this->seccion.'/gavetas_save';  #almacen/catalogos/pasillos/pasillos_save
		$almacenes_array = array(
					 'data'		=> $this->db_model->db_get_data_almacen('','','',false)
					,'value' 	=> 'id_almacen_almacenes'
					,'text' 	=> array('almacenes')
					,'name' 	=> "lts_almacenes"
					,'class' 	=> "requerido"
					);
		$almacenes     = dropdown_tpl($almacenes_array);
		$pasillos_array = array(
					 'data'		=> $this->db_model->db_get_data_pasillo('','','',false)
					,'value' 	=> 'id_almacen_pasillos'
					,'text' 	=> array('pasillos')
					,'name' 	=> "lts_pasillos"
					,'class' 	=> ""
					);
		$pasillos     = dropdown_tpl($pasillos_array);
		$btn_save     = form_button(array('class'=>"btn btn-primary",'name' => 'save_pasillo','onclick'=>'agregar()' , 'content' => $this->lang_item("btn_guardar") ));
		$btn_reset    = form_button(array('class'=>"btn btn-primary",'name' => 'reset','value' => 'reset','onclick'=>'clean_formulario()','content' => $this->lang_item("btn_limpiar")));
		$tab_1["lbl_gavetas"]       = $this->lang_item("lbl_gavetas");
		$tab_1["lbl_clave_corta"]   = $this->lang_item("lbl_clave_corta");
		$tab_1["list_almacen"]      = $almacenes;
		$tab_1["lbl_almacen"]       = $this->lang_item("lbl_almacen");
		$tab_1["list_pasillo"]      = $pasillos;
		$tab_1["lbl_pasillo"]       = $this->lang_item("lbl_pasillo");
		$tab_1["lbl_descripcion"]           = $this->lang_item("lbl_descripcion");
        $tab_1['button_save']       = $btn_save;
        $tab_1['button_reset']      = $btn_reset;
        if($this->ajax_post(false))
        {
				echo json_encode($this->load_view_unique($seccion , $tab_1, true));
		}
		else
		{
			return $this->load_view_unique($seccion , $tab_1, true);
		}
	}

	public function insert_gaveta(){
		$incomplete  = $this->ajax_post('incomplete');
		if($incomplete>0){
			$msg = $this->lang_item("msg_campos_obligatorios",false);
			echo json_encode( array( 'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)) );
		}else{
			$gaveta       = $this->ajax_post('gaveta');
			$clave_corta  = $this->ajax_post('clave_corta');
			$id_almacen   = $this->ajax_post('id_almacen');
			$id_pasillo   = $this->ajax_post('id_pasillo');
			$descripcion  = $this->ajax_post('descripcion');
			$data_insert  = array('clave_corta'         => $clave_corta,
								 'descripcion'          => $descripcion,
								 'gavetas'              => $gaveta,
								 'id_usuario'           => $this->session->userdata('id_usuario'),
								 'id_almacen_almacenes' => $id_almacen,
								 'id_almacen_pasillos'  => $id_pasillo,
								 'timestamp'            => $this->timestamp());
			$insert       = $this->db_model->db_insert_data_gavetas($data_insert);
			if($insert){
				$msg = $this->lang_item("msg_insert_success",false);
				echo json_encode(array(  'success'=>'true', 'mensaje' => $msg));
			}else{
				$msg = $this->lang_item("msg_err_clv",false);
				echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('', $msg ,false)));
			}
		}
	}

	public function export_xlsx($offset=0)
	{
		$filtro      = ($this->ajax_get('filtro')) ?  base64_decode($this->ajax_get('filtro') ): "";
		$limit 		 = $this->limit_max;
		$sqlData     = array(
			 'buscar'      	=> $filtro
			,'offset' 		=> $offset
			,'limit'      	=> $limit
		);
		$lts_content = $this->db_model->db_get_data_gaveta($sqlData);
		if(count($lts_content)>0){
			foreach ($lts_content as $value)
			{
				$set_data[] = array(
									 $value['gavetas'],
									 $value['clave_corta'],
									 $value['almacenes'],
									 $value['pasillos'],
									 $value['descripcion']);
			}
			$set_heading = array(
									$this->lang_item("gavetas"),
									$this->lang_item("cvl_corta"),
									$this->lang_item("almacenes"),
									$this->lang_item("pasillos"),
									$this->lang_item("descripcion"));
	
		}
		$params = array(	'title'   => $this->lang_item("lbl_excel"),
							'items'   => $set_data,
							'headers' => $set_heading
						);
		
		$this->excel->generate_xlsx($params);
	}

	public function eliminar(){
		$msj_grid = $this->ajax_post('msj_grid');
		$sqlData = array(
						 'id_almacen_gavetas'	=> $this->ajax_post('id_almacen_gavetas')
						,'activo' 		 =>0
						,'edit_timestamp'  	 => $this->timestamp()
						,'edit_id_usuario'   => $this->session->userdata('id_usuario')
						);
			 $insert = $this->db_model->db_update_data_gaveta($sqlData);
			if($insert){
				$msg = $this->lang_item("msg_delete_success",false);
				$json_respuesta = array(
						 'id' 		=> 1
						,'contenido'=> alertas_tpl('success', $msg ,false)
						,'success' 	=> true
						,'msj_grid'	=> $msj_grid
				);
			}else{
				$msg = $this->lang_item("msg_err_clv",false);
				$json_respuesta = array(
						 'id' 		=> 0
						,'contenido'=> alertas_tpl('', $msg ,false)
						,'success' 	=> false
						,'msj_grid'	=> $msj_grid
				);
			}
		echo json_encode($json_respuesta);
	}
}