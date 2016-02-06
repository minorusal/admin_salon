<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class almacenes extends Base_Controller{

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
		$this->seccion          = 'almacenes';
		$this->icon 			= 'fa fa-database'; 
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
		$this->load->model('sucursales/listado_sucursales_model','db_model2');
		// Diccionario
		$this->lang->load($this->modulo.'/'.$this->submodulo,"es_ES");
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
		return $this->modulo.'/'.$this->view_content;
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
		$filtro         = ($this->ajax_post('filtro')) ? $this->ajax_post('filtro') : "";
		$sqlData        = array(
			 'buscar'      	=> $filtro
			,'offset' 		=> $offset
			,'limit'      	=> $limit
		);
		$uri_segment    = $this->uri_segment(); 
		$total_rows	    = count($this->db_model->db_get_data_almacen($sqlData));
		$sqlData['aplicar_limit'] = false;	
		$list_content   = $this->db_model->db_get_data_almacen($sqlData);
		$url            = base_url($url_link);
		$paginador      = $this->pagination_bootstrap->paginator_generate($total_rows, $url, $limit, $uri_segment, array('evento_link' => 'onclick', 'function_js' => 'load_content', 'params_js'=>'1'));
		if($total_rows>0)
		{
			foreach ($list_content as $value)
			{
				$atrr = array(
								'href' => '#',
							  	'onclick' => 'detalle('.$value['id_almacen_almacenes'].')'
						);
				// Validación de pasillos y/o gavetas en almacen
				$pasillos = $this->db_model->db_get_data_pasillos_por_almacen(array('id_almacen' => $value['id_almacen_almacenes']));
				$gavetas  = $this->db_model->db_get_data_gavetas_por_almacen(array('id_almacen' => $value['id_almacen_almacenes']));
				// Acciones
				$almacenes = $this->db_model->db_get_almacen_usado($value['id_almacen_almacenes']);
				$accion_id 						= $value['id_almacen_almacenes'];
				$btn_acciones['detalle'] 		= '<span id="ico-detalle_'.$accion_id.'" class="ico_acciones ico_detalle fa fa-search-plus" onclick="detalle('.$accion_id.')" title="'.$this->lang_item("detalle").'"></span>';
				$eliminar 	= '<span style="color:red;" id="ico-eliminar_'.$value['id_almacen_almacenes'].'" class="ico_eliminar fa fa-times" onclick="confirm_delete('.$value['id_almacen_almacenes'].')" title="'.$this->lang_item("lbl_eliminar").'"></span>';
				$btn_acciones['eliminar'] = ($almacenes[0]['num_almacenes'] == 0)?$eliminar:'<span style="color:gray;" id="ico-eliminar_'.$value['id_almacen_almacenes'].'" class="ico_eliminar fa fa-times" title="'.$this->lang_item("lbl_eliminar").'"></span>';
				$acciones = implode('&nbsp;&nbsp;&nbsp;',$btn_acciones);
				$edit = (!$value['edit'])?'*':'';
				// Datos para tabla
				$tbl_data[] = array('id'             => $value['clave_corta'],
									'almacenes'      => $edit.tool_tips_tpl($value['almacenes'], $this->lang_item("tool_tip"), 'right' , $atrr),
									'clave_corta'    => $edit.$value['clave_corta'],
									'sucursal'       => $value['sucursal'],
									'tipos'          => $value['tipos'],
									'descripcion'    => $value['descripcion'],
									'acciones' 		 => $acciones
									);
			}
			// Plantilla
			$tbl_plantilla = set_table_tpl();
			// Titulos de tabla
			$this->table->set_heading(	$this->lang_item("cvl_corta"),
										$this->lang_item("almacen"),
										$this->lang_item("cvl_corta"),
										$this->lang_item("sucursal"),
										$this->lang_item("tipo"),
										$this->lang_item("descripcion"),
										$this->lang_item("acciones")
										);
			// Generar tabla
			$this->table->set_template($tbl_plantilla);
			$tabla     = $this->table->generate($tbl_data);
			$buttonTPL = array( 'text'       => $this->lang_item("btn_xlsx"), 
								'iconsweets' => 'fa fa-file-excel-o',
								'href'       => base_url($this->path.'export_xlsx?filtro='.base64_encode($filtro))
								);
		}
		else
		{
			$buttonTPL            = "";
			$msg                  = $this->lang_item("msg_query_null");
			$tabla                = alertas_tpl('', $msg ,false);
		}
			$tabData['filtro']    = (isset($filtro) && $filtro!="") ? sprintf($this->lang_item("msg_query_search"),$total_rows , $filtro) : "";
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

	public function detalle()
	{
		$id_almacen_almacenes = $this->ajax_post('id_almacen');
		$detalle  		      = $this->db_model->get_orden_unico_almacen($id_almacen_almacenes);
		
		$seccion 		      = 'detalle';
		$tab_detalle	      = $this->tab3;
		$sqlData = array(
			 'buscar'      	 => ''
			,'offset' 		 => 0
			,'limit'      	 => 0
		);
		$sucursales_array     = array(
					 'data'     => $this->db_model2->db_get_data($sqlData)
					,'value' 	=> 'id_sucursal'
					,'text' 	=> array('sucursal')
					,'name' 	=> "lts_sucursales"
					,'class' 	=> "requerido"
					,'selected' => $detalle[0]['id_sucursal']
					);
		$sucursales            = dropdown_tpl($sucursales_array);
		$tipos_array           = array(
					 'data'		=> $this->db_model->db_get_data_tipos('','','',false)
					,'value' 	=> 'id_almacen_tipos'
					,'text' 	=> array('tipos')
					,'name' 	=> "lts_tipos"
					,'class' 	=> "requerido"
					,'selected' => $detalle[0]['id_almacen_tipos']
					);
		$tipos                 = dropdown_tpl($tipos_array);
		$btn_save              = ($detalle[0]['edit'])?form_button(array('class'=>"btn btn-primary",'name' => 'actualizar' , 'onclick'=>'actualizar()','content' => $this->lang_item("btn_guardar") )):$this->lang_item("no_editable");
                
        $tabData['id_almacen']             = $id_almacen_almacenes;
        $tabData["nombre_almacen"]         = $this->lang_item("almacen");
		$tabData["cvl_corta"]        	   = $this->lang_item("cvl_corta");
		$tabData["descrip"]         	   = $this->lang_item("descripcion");
		$tabData["list_sucursal"]          = $sucursales;
		$tabData["sucursal"]               = $this->lang_item("sucursal");
		$tabData["list_tipo"]              = $tipos;
		$tabData["tipo"]                   = $this->lang_item("tipo");
        $tabData['almacen']                = $detalle[0]['almacenes'];
		$tabData['clave_corta']            = $detalle[0]['clave_corta'];
        $tabData['descripcion']            = $detalle[0]['descripcion'];
        $tabData['lbl_ultima_modificacion'] = $this->lang_item('lbl_ultima_modificacion', false);
        $tabData['val_fecha_registro']     	= $detalle[0]['timestamp'];
		$tabData['lbl_fecha_registro']     	= $this->lang_item('lbl_fecha_registro', false);
		$tabData['lbl_usuario_registro']    = $this->lang_item('lbl_usuario_registro', false);

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

		$uri_view   					  = $this->modulo.'/'.$this->submodulo.'/'.$this->seccion.'/'.$this->seccion.'_'.$seccion;
		echo json_encode( $this->load_view_unique($uri_view ,$tabData, true));
	}


	public function actualizar(){
		$incomplete  = $this->ajax_post('incomplete');
		if($incomplete>0){
			$msg = $this->lang_item("msg_campos_obligatorios",false);
			echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)));

		}else{
			$sqlData = array(
						 'id_almacen_almacenes'	    => $this->ajax_post('id_almacen')
						,'almacenes' 		        => $this->ajax_post('almacen')
						,'clave_corta' 				=> $this->ajax_post('clave_corta')
						,'descripcion'				=> $this->ajax_post('descripcion')
						,'id_sucursal'				=> $this->ajax_post('id_sucursal')
						,'id_almacen_tipos'			=> $this->ajax_post('id_tipo')
						,'edit_timestamp'			=> $this->timestamp()
						,'edit_id_usuario'			=> $this->session->userdata('id_usuario')
						);
			$insert = $this->db_model->db_update_data_almacen($sqlData);
			if($insert){
				$msg = $this->lang_item("msg_update_success",false);
				echo json_encode(array(  'success'=>'true', 'mensaje' => $msg ));
			}else{
				$msg = $this->lang_item("msg_err_clv",false);
				echo json_encode( array( 'success'=>'false', 'mensaje' =>alertas_tpl('', $msg ,false)));
			}
		}
	}
	
	public function agregar(){
		$seccion       = $this->modulo.'/'.$this->submodulo.'/'.$this->seccion.'/almacenes_save';
		$sqlData = array(
			 'buscar'      	=> ''
			,'offset' 		=> ''
			,'limit'      	=> ''
		);
		$sucursales_array = array(
					 'data'		=> $this->db_model2->db_get_data()
					,'value' 	=> 'id_sucursal'
					,'text' 	=> array('sucursal')
					,'name' 	=> "lts_sucursales"
					,'class' 	=> "requerido"
					);
		$sucursales    = dropdown_tpl($sucursales_array);
		
		$tipos_array = array(
					 'data'		=> $this->db_model->db_get_data_tipos($sqlData)
					,'value' 	=> 'id_almacen_tipos'
					,'text' 	=> array('tipos')
					,'name' 	=> "lts_tipos"
					,'class' 	=> "requerido"
					);
		$tipos         = dropdown_tpl($tipos_array);
		$btn_save      = form_button(array('class'=>"btn btn-primary",'name' => 'save_almacen','onclick'=>'agregar()' , 'content' => $this->lang_item("btn_guardar") ));
		$btn_reset     = form_button(array('class'=>"btn btn-primary",'name' => 'reset','value' => 'reset','onclick'=>'clean_formulario()','content' => $this->lang_item("btn_limpiar")));

		$tab_1["nombre_almacenes"]      = $this->lang_item("nombre_almacenes");
		$tab_1["cvl_corta"]             = $this->lang_item("cvl_corta");
		$tab_1["list_sucursal"]         = $sucursales;
		$tab_1["sucursal"]              = $this->lang_item("sucursal");
		$tab_1["list_tipo"]             = $tipos;
		$tab_1["tipo"]                  = $this->lang_item("tipo");
		$tab_1["descrip"]               = $this->lang_item("descripcion");

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

	public function insert_almacen(){
		$incomplete  = $this->ajax_post('incomplete');

		if($incomplete>0){
			$msg = $this->lang_item("msg_campos_obligatorios",false);
			echo json_encode( array( 'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)) );
		}else{
			$almacen = $this->ajax_post('almacenes');
			$clave_corta  = $this->ajax_post('clave_corta');
			$sucursal  = $this->ajax_post('id_sucursal');
			$tipo  = $this->ajax_post('id_tipo');
			$descripcion  = $this->ajax_post('descripcion');
			$data_insert = array('clave_corta'       => $clave_corta,
								 'descripcion'       => $descripcion,
								 'id_usuario'        => $this->session->userdata('id_usuario'),
								 'id_sucursal'       => $sucursal,
								 'id_almacen_tipos'  => $tipo,
								 'almacenes'         => $almacen,  
								 'timestamp'         => $this->timestamp());
			
			$insert = $this->db_model->db_insert_data($data_insert);
			
			if($insert){
				$msg = $this->lang_item("msg_insert_success",false);
				echo json_encode(array(  'success'=>'true', 'mensaje' => $msg));
			}else			{
				$msg = $this->lang_item("msg_err_clv",false);
				echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('', $msg ,false)));
			}
		}
	}

	public function export_xlsx($offset=0)
	{
		$filtro      = ($this->ajax_get('filtro')) ?  base64_decode($this->ajax_get('filtro') ): "";
		$limit 		 = $this->limit_max;
		$sqlData = array(
			 'buscar'      	=> $filtro
			,'offset' 		=> $offset
			,'limit'      	=> $limit
		);
		$lts_content = $this->db_model->db_get_data_almacen($sqlData);
		if(count($lts_content)>0){
			foreach ($lts_content as $value)
			{
				$set_data[] = array(
									 $value['almacenes'],
									 $value['clave_corta'],
									 $value['sucursal'],
									 $value['tipos'],
									 $value['descripcion']);
			}
			$set_heading = array(
									$this->lang_item("almacenes"),
									$this->lang_item("cvl_corta"),
									$this->lang_item("sucursal"),
									$this->lang_item("tipo"),
									$this->lang_item("descripcion"));
	
		}
		$params = array(	'title'   => $this->lang_item("lbl_excel_almacen"),
							'items'   => $set_data,
							'headers' => $set_heading
						);
		
		$this->excel->generate_xlsx($params);
	}

	public function eliminar(){
		$msj_grid = $this->ajax_post('msj_grid');
		$sqlData = array(
						 'id_almacen_almacenes'	=> $this->ajax_post('id_almacen_almacenes')
						,'activo' 		 =>0
						,'edit_timestamp'  	 => $this->timestamp()
						,'edit_id_usuario'   => $this->session->userdata('id_usuario')
						);
			 $insert = $this->db_model->db_update_data_almacen($sqlData);
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