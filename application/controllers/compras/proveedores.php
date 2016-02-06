<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class proveedores extends Base_Controller { 

	private $modulo;
	private $submodulo;
	private $view_content;
	private $path;
	private $icon;
	private $offset, $limit_max;
	private $tab = array(), $tab_indice = array();

	public function __construct(){
		parent::__construct();
		$this->modulo 			= 'compras';
		$this->submodulo		= 'proveedores';
		$this->icon 			= 'fa fa-users';
		$this->path 			= $this->modulo.'/'.$this->submodulo.'/';
		$this->view_content 	= 'content';
		$this->limit_max		= 10;
		$this->offset			= 0;

		$this->tab_indice 		= array(
									 'agregar'
									,'listado'
									,'detalle'
								);
		for($i=0; $i<=count($this->tab_indice)-1; $i++){
			$this->tab[$this->tab_indice[$i]] = $this->tab_indice[$i];
		}

		$this->load->model($this->modulo.'/'.$this->submodulo.'_model','db_model');
		$this->load->model('administracion/entidades_model','entidad');
		$this->load->model('administracion/regiones_model','regiones');
		$this->lang->load($this->modulo.'/'.$this->submodulo,"es_ES");
	}
	public function config_tabs(){
		for($i=1; $i<=count($this->tab); $i++){
			${'tab_'.$i} = $this->tab [$this->tab_indice[$i-1]];
		}
		$path  	= $this->path;
		$pagina =(is_numeric($this->uri_segment_end()) ? $this->uri_segment_end() : "");
		
		$config_tab['names']    = array(
										 $this->lang_item($tab_1)
										,$this->lang_item($tab_2)
										,$this->lang_item($tab_3)
								); 
		$config_tab['links']    = array(
										 $path.$tab_1
										,$path.$tab_2.'/'.$pagina
										,$tab_3
								); 
		$config_tab['action']   = array(
										 'load_content'
										,'load_content'
										,''
								);
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
		$data['titulo_seccion']   = $this->lang_item("seccion");
		$data['titulo_submodulo'] = $this->lang_item("submodulo");
		$data['icon']             = $this->icon;
		$data['tabs']             = tabbed_tpl($this->config_tabs(),base_url(),$tabl_inicial,$contenidos_tab);	
		$js['js'][]               = array('name' => $this->submodulo, 'dirname' => $this->modulo);
		$this->load_view($this->uri_view_principal(), $data, $js);
	}
	public function listado($offset=0){
		$seccion 		= '';
		$filtro         = ($this->ajax_post('filtro')) ? $this->ajax_post('filtro') : "";
		$accion 		= $this->tab['listado'];
		$tab_detalle	= $this->tab['detalle'];
		$limit 			= $this->limit_max;
		$uri_view 		= $this->modulo.'/'.$accion;
		$url_link 		= $this->path.$seccion.$accion;		
		$sqlData = array(
						 'buscar'      	=> $filtro
						,'offset' 		=> $offset
						,'limit'      	=> $limit
						,'aplicar_limit'=> true
					);
		$uri_segment  = $this->uri_segment(); 
		$total_rows	  = $this->db_model->db_get_total_rows($sqlData);
		$list_content = $this->db_model->db_get_data($sqlData);
		$url          = base_url($url_link);
		$paginador    = $this->pagination_bootstrap->paginator_generate($total_rows, $url, $limit, $uri_segment, array('evento_link' => 'onclick', 'function_js' => 'load_content', 'params_js'=>'1'));

		if($total_rows>0){
			foreach ($list_content as $value) {
				// Evento de enlace
				$atrr = array(
								'href' => '#',
							  	'onclick' => $tab_detalle.'('.$value['id_compras_proveedor'].')'
						);
				// Acciones
				$btn_acciones['detalle'] 		= '<span id="ico-detalle" style="cursor:pointer;" onclick="detalle('.$value['id_compras_proveedor'].')"><i class="fa fa-search-plus" style="color:blue;" title="'.$this->lang_item("detalle").'"></i></span>';
				$btn_acciones['eliminar']       = '<span id="ico-eliminar" style="cursor:pointer;" onclick="eliminar('.$value['id_compras_proveedor'].')"><i class="fa fa-times" style="color:red;" title="'.$this->lang_item("eliminar").'"></i></span>';
				$acciones = implode('&nbsp;&nbsp;&nbsp;',$btn_acciones);
				// Datos para tabla
				$tbl_data[] = array('id'                => $value['razon_social'],
									'razon_social'      => tool_tips_tpl($value['razon_social'], $this->lang_item("tool_tip"), 'right' , $atrr),
									'nombre_comercial'  => $value['nombre_comercial'],
									'rfc'               => $value['rfc'],
									'clave_corta'       => $value['clave_corta'],
									'region'            => $value['region'],
									'entidad'           => $value['entidad'],
									'acciones'	 		 => $acciones
									);
			}

			$tbl_plantilla = set_table_tpl();
			
			$this->table->set_heading(	$this->lang_item("id"),
										$this->lang_item("lbl_rsocial"),
										$this->lang_item("lbl_nombre"),
										$this->lang_item("lbl_rfc"),
										$this->lang_item("lbl_clv"),
										$this->lang_item("lbl_clv_region"),
										$this->lang_item("lbl_entidad"),
										$this->lang_item("acciones")
									);
			$buttonTPL = array( 'text'       => $this->lang_item("btn_xlsx"), 
								'iconsweets' => 'fa fa-file-excel-o',
								'href'       => base_url($this->path.'export_xlsx?filtro='.base64_encode($filtro))
								);

			$this->table->set_template($tbl_plantilla);
			$tabla = $this->table->generate($tbl_data);
		}else{
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
			}else{
				return $this->load_view_unique($uri_view , $tabData, true);
			}
	}
	public function export_xlsx(){
		$filtro  = ($this->ajax_get('filtro')) ?  base64_decode($this->ajax_get('filtro') ): "";
		$sqlData = array(
						 'buscar'      	 => $filtro
						,'aplicar_limit' => 0
					);
		$lts_content = $this->db_model->db_get_data($sqlData);
		if(count($lts_content)>0){
			foreach ($lts_content as $value) {
				$set_data[] = array(
									$value['razon_social'],
									$value['nombre_comercial'],
									$value['clave_corta'],
									$value['rfc'],
									$value['calle'],
									$value['num_int'],
									$value['num_ext'],
									$value['colonia'],
									$value['municipio'],
									$value['entidad'],
									$value['cp'],
									$value['telefonos'],
									$value['email'],
									$value['contacto'],
									$value['comentarios']
							);
			}

			$set_heading = array(
									$this->lang_item("lbl_rsocial"),
									$this->lang_item("lbl_nombre"),
									$this->lang_item("lbl_clv"),
									$this->lang_item("lbl_rfc"),
									$this->lang_item("lbl_calle"),
									$this->lang_item("lbl_num_int"),
									$this->lang_item("lbl_num_ext"),
									$this->lang_item("lbl_colonia"),
									$this->lang_item("lbl_municipio"),
									$this->lang_item("lbl_entidad"),
									$this->lang_item("lbl_cp"),
									$this->lang_item("lbl_telefono"),
									$this->lang_item("lbl_email"),
									$this->lang_item("lbl_contacto"),
									$this->lang_item("lbl_comentario")
								);
	
		}
		$params = array(	'title'   => $this->lang_item("catalogo", false).$this->lang_item("seccion"),
							'items'   => $set_data,
							'headers' => $set_heading
						);
		
		$this->excel->generate_xlsx($params);
	}
	public function detalle(){
		$seccion 			    = '';
		$accion 			    = $this->tab['detalle'];
		$id_compras_proveedor 	= $this->ajax_post('id_compras_proveedor');
		$detalle  			    = $this->db_model->get_proveedor_unico($id_compras_proveedor);
		$btn_save       	    = form_button(array('class'=>"btn btn-primary",'name' => 'actualizar' , 'onclick'=>'actualizar()','content' => $this->lang_item("btn_guardar") ));
		$sqlData        = array(
			 'buscar'      	=> ''
			,'offset' 		=> 0
			,'limit'      	=> 0
		);		
		
		$region_array = array(
							'data'		=> $this->regiones->db_get_data($sqlData)
							,'selected' => $detalle[0]['id_administracion_region']
							,'value' 	=> 'id_administracion_region'
							,'text' 	=> array('region')
							,'name' 	=> "id_administracion_region"
							,'class' 	=> "requerido"
						);
		$dropArray = array(
					'data'		=> $this->entidad->get_entidades_default(array('aplicar_limit'=> false))
					,'selected' => $detalle[0]['id_administracion_entidad'] 
					,'value' 	=> 'id_administracion_entidad'
					,'text' 	=> array('ent_abrev','entidad')
					,'name' 	=> "id_administracion_entidad"
					,'class' 	=> "requerido"
				);

		$tabData['lbl_rsocial']            =  $this->lang_item('lbl_rsocial', false);
		$tabData['lbl_nombre']             =  $this->lang_item('lbl_nombre', false);
		$tabData['lbl_clv']                =  $this->lang_item('lbl_clv', false);
		$tabData['lbl_rfc']                =  $this->lang_item('lbl_rfc', false);
		$tabData['lbl_calle']              =  $this->lang_item('lbl_calle', false);
		$tabData['lbl_num_int']            =  $this->lang_item('lbl_num_int', false);
		$tabData['lbl_num_ext']            =  $this->lang_item('lbl_num_ext', false);
		$tabData['lbl_colonia']            =  $this->lang_item('lbl_colonia', false);
		$tabData['lbl_municipio']          =  $this->lang_item('lbl_municipio', false);
		$tabData['lbl_entidad']            =  $this->lang_item('lbl_entidad', false);
		$tabData['dropdown_entidad']       =  dropdown_tpl($dropArray);
		$tabData['lbl_region']             =  $this->lang_item('lbl_region', false);
		$tabData['dropdown_region']        =  dropdown_tpl($region_array);
		$tabData['lbl_cp']                 =  $this->lang_item('lbl_cp', false);
		$tabData['lbl_telefono']           =  $this->lang_item('lbl_telefono', false);
		$tabData['lbl_email']              =  $this->lang_item('lbl_email', false);
		$tabData['lbl_contacto']           =  $this->lang_item('lbl_contacto', false);
		$tabData['lbl_comentario']         =  $this->lang_item('lbl_comentario', false);
		$tabData['lbl_ultima_modificacion'] =  $this->lang_item('lbl_ultima_modificacion', false);
		$tabData['lbl_fecha_registro']     =  $this->lang_item('lbl_fecha_registro', false);
		$tabData['lbl_usuario_registro']    =  $this->lang_item('lbl_usuario_registro', false);
		$tabData['id_compras_proveedor']   =  $detalle[0]['id_compras_proveedor'];
		$tabData['val_rsocial']            =  $detalle[0]['razon_social'];
		$tabData['val_nombre']             =  $detalle[0]['nombre_comercial'];
		$tabData['val_clv']                =  $detalle[0]['clave_corta'];
		$tabData['val_rfc']                =  $detalle[0]['rfc'];
		$tabData['val_calle']              =  $detalle[0]['calle'];
		$tabData['val_num_int']            =  $detalle[0]['num_int'];
		$tabData['val_num_ext']            =  $detalle[0]['num_ext'];
		$tabData['val_colonia']            =  $detalle[0]['colonia'];
		$tabData['val_municipio']          =  $detalle[0]['municipio'];
		$tabData['val_cp']                 =  $detalle[0]['cp'];
		$tabData['val_telefono']           =  $detalle[0]['telefonos'];
		$tabData['val_email']              =  $detalle[0]['email'];
		$tabData['val_contacto']           =  $detalle[0]['contacto'];
		$tabData['val_comentario']         =  $detalle[0]['comentarios'];
		$tabData['val_fecha_registro']     =  $detalle[0]['timestamp'];
		
		$this->load_database('global_system');
        $this->load->model('users_model');
    	
    	$usuario_registro                  = $this->users_model->search_user_for_id($detalle[0]['id_usuario']);
    	$usuario_name 				       = text_format_tpl($usuario_registro[0]['name'],"u");
    	$tabData['val_usuarios_registro']  = $usuario_name ;
	

        if($detalle[0]['edit_id_usuario']){
        	$usuario_registro                   = $this->users_model->search_user_for_id($detalle[0]['edit_id_usuario']);
        	$usuario_name 				        = text_format_tpl($usuario_registro[0]['name'],"u");
        	$tabData['val_ultima_modificacion'] = sprintf($this->lang_item('val_ultima_modificacion', false), $this->timestamp_complete($detalle[0]['edit_timestamp']), $usuario_name);
    	}else{
    		$usuario_name = '';
    		$tabData['val_ultima_modificacion'] = $this->lang_item('lbl_sin_modificacion', false);
    	}

    	$tabData['button_save']         = $btn_save;
        $tabData['registro_por']    	= $this->lang_item("registro_por",false);
      	$tabData['usuario_registro']	= $usuario_name;
		$uri_view   					= $this->path.$this->submodulo.'_'.$accion;
		echo json_encode( $this->load_view_unique($uri_view ,$tabData, true));
	}
	public function agregar(){
		$seccion 		= '';
		$accion 		= $this->tab['agregar'];
		$uri_view   	= $this->path.$this->submodulo.'_'.$accion;


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
						);
		$dropArray = array(
					 'data'		=> $this->entidad->get_entidades_default(array('aplicar_limit'=> false))
					,'value' 	=> 'id_administracion_entidad'
					,'text' 	=> array('ent_abrev','entidad')
					,'name' 	=> "id_administracion_entidad"
					,'class' 	=> "requerido"
				);
		
		$btn_save       = form_button(array('class'=>"btn btn-primary",'name' => 'save','onclick'=>'insert()' , 'content' => $this->lang_item("btn_guardar") ));
		$btn_reset      = form_button(array('class'=>"btn btn-primary",'name' => 'reset','value' => 'reset','onclick'=>'clean_formulario()','content' => $this->lang_item("btn_limpiar")));
		
		$tabData['lbl_rsocial']            =  $this->lang_item('lbl_rsocial', false);
		$tabData['lbl_nombre']             =  $this->lang_item('lbl_nombre', false);
		$tabData['lbl_clv']                =  $this->lang_item('lbl_clv', false);
		$tabData['lbl_rfc']                =  $this->lang_item('lbl_rfc', false);
		$tabData['lbl_calle']              =  $this->lang_item('lbl_calle', false);
		$tabData['lbl_num_int']            =  $this->lang_item('lbl_num_int', false);
		$tabData['lbl_num_ext']            =  $this->lang_item('lbl_num_ext', false);
		$tabData['lbl_colonia']            =  $this->lang_item('lbl_colonia', false);
		$tabData['lbl_municipio']          =  $this->lang_item('lbl_municipio', false);
		$tabData['lbl_entidad']            =  $this->lang_item('lbl_entidad', false);
		$tabData['lbl_region']             =  $this->lang_item('lbl_region', false);
		$tabData['lbl_cp']                 =  $this->lang_item('lbl_cp', false);
		$tabData['lbl_telefono']           =  $this->lang_item('lbl_telefono', false);
		$tabData['lbl_email']              =  $this->lang_item('lbl_email', false);
		$tabData['lbl_contacto']           =  $this->lang_item('lbl_contacto', false);
		$tabData['lbl_comentario']         =  $this->lang_item('lbl_comentario', false);
		$tabData['lbl_ultima_modiciacion'] =  $this->lang_item('lbl_ultima_modificacion', false);
		$tabData['lbl_fecha_registro']     =  $this->lang_item('lbl_fecha_registro', false);
		$tabData['lbl_usuario_regitro']    =  $this->lang_item('lbl_usuario_regitro', false);

		$tabData['dropdown_region']        =  dropdown_tpl($region_array);
		$tabData['dropdown_entidad']       =  dropdown_tpl($dropArray);
		$tabData['button_save']            =  $btn_save;
        $tabData['button_reset']           =  $btn_reset;

        if($this->ajax_post(false)){
				echo json_encode($this->load_view_unique($uri_view , $tabData, true));
		}else{
			return $this->load_view_unique($uri_view , $tabData, true);
		}
	}
	public function insert(){
		$objData  	= $this->ajax_post('objData');
		if($objData['incomplete']>0){
			$msg = $this->lang_item("msg_campos_obligatorios",false);
			echo json_encode( array( 'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)) );
		}else{
			if($this->validate_email($objData['email'])){
				$sqlData = array(
								 'razon_social'              => $objData['rsocial']
								,'nombre_comercial'          => $objData['nombre']
								,'clave_corta'               => $objData['clave_corta']
								,'rfc'                       => $objData['rfc']
								,'calle'                     => $objData['calle']
								,'num_int'                   => $objData['num_int']
								,'num_ext'                   => $objData['num_ext']
								,'colonia'                   => $objData['colonia']
								,'municipio'                 => $objData['municipio']
								,'id_administracion_entidad' => $objData['id_administracion_entidad']
								,'id_administracion_region'  => $objData['id_administracion_region']
								,'cp'                        => $objData['cp']
								,'telefonos'                 => $objData['telefono']
								,'email'                     => $objData['email']
								,'contacto'                  => $objData['contacto']
								,'comentarios'               => $objData['comentario']
								,'id_usuario' 		         => $this->session->userdata('id_usuario')
								,'timestamp'  		         => $this->timestamp()

							);
				$insert = $this->db_model->insert($sqlData);
				if($insert){
					$msg = $this->lang_item("msg_insert_success",false);
					echo json_encode(array(  'success'=>'true', 'mensaje' => $msg));
				}else{
					$msg = $this->lang_item("msg_err_clv",false);
					echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('', $msg ,false)));
				}
			}else{
				$msg = $this->lang_item("invalid_email",false);
				echo json_encode( array( 'success'=>'false', 'mensaje' =>alertas_tpl('', $msg ,false)));
			}
		}
	}
	public function actualizar(){
		$objData  	= $this->ajax_post('objData');
		if($objData['incomplete']>0){
			$msg = $this->lang_item("msg_campos_obligatorios",false);
			echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)));
		}else{
			if($this->validate_email($objData['email'])){

				$sqlData = array(
								'id_compras_proveedor'	     => $objData['id_compras_proveedor']
								,'razon_social'              => $objData['rsocial']
								,'nombre_comercial'          => $objData['nombre']
								,'clave_corta'               => $objData['clave_corta']
								,'rfc'                       => $objData['rfc']
								,'calle'                     => $objData['calle']
								,'num_int'                   => $objData['num_int']
								,'num_ext'                   => $objData['num_ext']
								,'colonia'                   => $objData['colonia']
								,'municipio'                 => $objData['municipio']
								,'id_administracion_entidad' => $objData['id_administracion_entidad']
								,'id_administracion_region'  => $objData['id_administracion_region']
								,'cp'                        => $objData['cp']
								,'telefonos'                 => $objData['telefono']
								,'email'                     => $objData['email']
								,'contacto'                  => $objData['contacto']
								,'comentarios'               => $objData['comentario']
								,'edit_id_usuario' 		     => $this->session->userdata('id_usuario')
								,'edit_timestamp'  		     => $this->timestamp()

							);
				$insert = $this->db_model->db_update_data($sqlData);
				if($insert){
					$msg = $this->lang_item("msg_update_success",false);
					echo json_encode(array(  'success'=>'true', 'mensaje' => $msg ));
				}else{
					$msg = $this->lang_item("msg_err_clv",false);
					echo json_encode( array( 'success'=>'false', 'mensaje' =>alertas_tpl('', $msg ,false)));
				}
			}else{
				$msg = $this->lang_item("invalid_email",false);
				echo json_encode( array( 'success'=>'false', 'mensaje' =>alertas_tpl('', $msg ,false)));
			}
		}
	}

	public function eliminar(){
		$msj_grid = $this->ajax_post('msj_grid');
		$sqlData = array(
						 'id_compras_proveedor'	=> $this->ajax_post('id_compras_proveedor')
						,'activo' 		 =>0
						,'edit_timestamp'  	 => $this->timestamp()
						,'edit_id_usuario'   => $this->session->userdata('id_usuario')
						);
			 $insert = $this->db_model->db_update_data($sqlData);
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