<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class regiones extends Base_Controller{
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
		$this->modulo 			= 'administracion';
		$this->submodulo		= 'catalogos';
		$this->seccion          = 'regiones';
		$this->icon 			= 'fa fa-map-marker'; 
		$this->path 			= $this->modulo.'/'.$this->seccion.'/'; #administracion/regiones
		$this->view_content 	= 'content';
		$this->limit_max		= 10;
		$this->offset			= 0;
		// Tabs
		$this->tab1 			= 'agregar';
		$this->tab2 			= 'listado';
		$this->tab3 			= 'detalle';
		// DB Model
		$this->load->model($this->modulo.'/'.$this->seccion.'_model','db_model');
		$this->load->model($this->modulo.'/entidades_model','db_model2');
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
	private function uri_view_principal()	{
		return $this->modulo.'/'.$this->view_content;
	}

	public function index()	{
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
	public function listado($offset=0)	{
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
		$total_rows	  = count($this->db_model->db_get_data($sqlData));
		$sqlData['aplicar_limit'] = false;
		$list_content = $this->db_model->db_get_data($sqlData);
		//print_debug($list_content);
		$url          = base_url($url_link);
		$paginador    = $this->pagination_bootstrap->paginator_generate($total_rows, $url, $limit, $uri_segment, array('evento_link' => 'onclick', 'function_js' => 'load_content', 'params_js'=>'1'));
		if($total_rows)	{
			foreach ($list_content as $value){
				// Evento de enlace
				$atrr = array(
								'href' => '#',
							  	'onclick' => $tab_detalle.'('.$value['id_administracion_region'].')'
						);
				$region = $this->db_model->bd_get_region_proveedor(20);
				
				$eliminar 	= '<span style="color:red;" id="ico-eliminar_1" class="ico_eliminar fa fa-times" onclick="confirm_delete('.$value['id_administracion_region'].')" title="'.$this->lang_item("lbl_eliminar").'"></span>';
				$btn_acciones['eliminar'] = (empty($region))?$eliminar:'<span style="color:gray;" id="ico-eliminar_1" class="ico_eliminar fa fa-times" title="'.$this->lang_item("lbl_eliminar").'"></span>';
				$btn_acciones['detalle']  = '<span id="ico-detalle_'.$value['id_administracion_region'].'" class="ico_acciones ico_detalle fa fa-search-plus" onclick="detalle('.$value['id_administracion_region'].')" title="'.$this->lang_item("detalle").'"></span>';
				$acciones = implode('&nbsp;&nbsp;&nbsp;',$btn_acciones);
				// Datos para tabla
				$tbl_data[] = array('id'            => $value['id_administracion_region'],
									'region'        => tool_tips_tpl($value['region'], $this->lang_item("tool_tip"), 'right' , $atrr),
									'clave_corta'   => $value['clave_corta'],
									'descripcion'   => $value['descripcion'],
									'acciones'		=> $acciones
									);
			}
			// Plantilla
			$tbl_plantilla = set_table_tpl();
			// Titulos de tabla
			$this->table->set_heading(	$this->lang_item("ID"),
										$this->lang_item("lbl_region"),
										$this->lang_item("lbl_clave_corta"),
										$this->lang_item("lbl_descripcion"),
										$this->lang_item("lbl_acciones"));
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
		if($this->ajax_post(false))	{
			echo json_encode( $this->load_view_unique($uri_view , $tabData, true));
		}else{
			return $this->load_view_unique($uri_view , $tabData, true);
		}
	}

	public function detalle(){
		$id_region   = $this->ajax_post('id_region');
		$detalle = $this->db_model->get_entidad_regiones($id_region);
		//print_debug($detalle);
		$seccion     = $this->tab3;

		$entidades_array = array(
					 'data'		        => $this->db_model2->get_entidades_default()
					,'data_seleted' 	=> $detalle
					,'value' 	        => 'id_administracion_entidad'
					,'text' 	        => array('entidad','ent_abrev')
					,'name' 	        => "lts_entidades"
					,'class' 	        => "requerido"
					);
		$entidades = dropMultiselect_tpl($entidades_array);

		$btn_save = form_button(array('class' => 'btn btn-primary' , 'name' => 'actualizar', 'onclick' => 'actualizar()', 'content' => $this->lang_item("btn_guardar")));
		$tabData['id_region']               = $id_region;
		$tabData['lbl_region']              = $this->lang_item("lbl_region");
		$tabData['lbl_clave_corta']         = $this->lang_item("lbl_clave_corta");
		$tabData['lbl_entidades']           = $this->lang_item("lbl_entidades");
		$tabData['lbl_descripcion']         = $this->lang_item("lbl_descripcion");
		$tabData['txt_region']              = $detalle[0]['region'];
		$tabData['txt_clave_corta']         = $detalle[0]['clave_corta'];
		$tabData['txt_descripcion']         = $detalle[0]['descripcion'];
		$tabData['list_entidad']            = $entidades;
		$tabData['lbl_ultima_modificacion'] = $this->lang_item('lbl_ultima_modificacion');
        $tabData['val_fecha_registro']      = $detalle[0]['timestamp'];
		$tabData['lbl_fecha_registro']      = $this->lang_item('lbl_fecha_registro');
		$tabData['lbl_usuario_registro']    = $this->lang_item('lbl_usuario_registro');

		$this->load_database('global_system');
        $this->load->model('users_model');

		$usuario_registro                  = $this->users_model->search_user_for_id($detalle[0]['id_usuario']);
	    $usuario_name	                   = text_format_tpl($usuario_registro[0]['name'],"u");
	    $tabData['val_usuarios_registro']  = $usuario_name;

	    if($detalle[0]['edit_id_usuario']){
			$usuario_registro = $this->users_model->search_user_for_id($detalle[0]['edit_id_usuario']);
			$usuario_name = text_format_tpl($usuario_registro[0]['name'],"u");
			$tabData['val_ultima_modificacion'] = sprintf($this->lang_item('val_ultima_modificacion',false), $this->timestamp_complete($detalle[0]['edit_timestamp']), $usuario_name);
		}else{
			$usuario_name = '';
			$tabData['val_ultima_modificacion'] = $this->lang_item('lbl_sin_modificacion', false);
		}
		$tabData['button_save'] = $btn_save;
		$tabData['registro_por'] = $this->lang_item('registro´_por', false);
		$tabData['usuario_registro'] = $usuario_name;
		$uri_view = $this->modulo.'/'.$this->seccion.'/'.$this->seccion.'_'.$seccion;
		echo json_encode($this->load_view_unique($uri_view,$tabData,true));
	}

	public function agregar(){
		$seccion = $this->modulo.'/'.$this->seccion.'/'.$this->seccion.'_save';
		$entidades_array = array(
					 'data'		=> $this->db_model2->get_entidades_default()
					,'value' 	=> 'id_administracion_entidad'
					,'text' 	=> array('entidad','ent_abrev')
					,'name' 	=> "lts_entidades"
					,'class' 	=> "requerido"
					);
		$entidades = dropMultiselect_tpl($entidades_array);

		$btn_save = form_button(array('class'=>'btn btn-primary', 'name'=>'save_region', 'onclick'=>'agregar()','content'=>$this->lang_item("btn_guardar")));
		$btn_reset = form_button(array('class'=>'btn btn_primary', 'name'=>'reset','onclick'=>'clean_formulario()','content'=>$this->lang_item('btn_limpiar')));
		
		$tab_1['lbl_region'] = $this->lang_item("lbl_region");
		$tab_1['lbl_clave_corta'] = $this->lang_item("lbl_clave_corta");
		$tab_1['lbl_descripcion'] = $this->lang_item("lbl_descripcion");
		$tab_1['lbl_entidades'] = $this->lang_item("lbl_entidades");
		$tab_1["list_entidad"] = $entidades;	
		
		$tab_1['button_save'] = $btn_save;
		$tab_1['button_reset'] = $btn_reset;

		if($this->ajax_post(false))	{
			echo json_encode($this->load_view_unique($seccion,$tab_1,true));
		}else{
			return $this->load_view_unique($seccion, $tab_1, true);
		}
	}

	public function insert_region(){
		//print_debug($this->ajax_post(false));
		$incomplete  = $this->ajax_post('incomplete');
		if($incomplete>0){
			$msg = $this->lang_item("msg_campos_obligatorios",false);
			echo json_encode( array( 'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)));
		}else{
			$objData 		= $this->ajax_post('objData');			
			$region   		= $objData['txt_region'];
			$descripcion 	= $objData['txt_descripcion'];
			$clave_corta 	= $objData['txt_clave_corta'];
			$entidades   	= $objData['entidades'];

			$data_insert = array('region'          => $region
								,'clave_corta'     => $clave_corta
								,'descripcion'     => $descripcion
								,'id_usuario'      => $this->session->userdata('id_usuario')
								,'timestamp'       => $this->timestamp()
						);
			$id_region = $this->db_model->db_insert_data($data_insert);
			//$id_region = 77;
			if($id_region){
				$entidades = array('id_entidad' => $entidades,
							  'id_administracion_region'  => $id_region);
				$insert = $this->db_model->db_insert_entidades($entidades);
			}

			if($id_region){
				$msg = $this->lang_item("msg_insert_success",false);
				echo json_encode(array(  'success'=>'true', 'mensaje' => $msg));
			}else{
				$msg = $this->lang_item("msg_err_clv",false);
				echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('', $msg ,false)));
			}
		}
	}

	public function actualizar(){
		$incomplete = $this->ajax_post('incomplete');
		if($incomplete > 0){
			$msg = $this->lang_item("msg_campos_obligatorios",false);
			echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)));
		}else{
			$objData 		= $this->ajax_post('objData');			
			$id_region   	= $objData['id_region'];
			$region   		= $objData['txt_region'];
			$descripcion 	= $objData['txt_descripcion'];
			$clave_corta 	= $objData['txt_clave_corta'];
			$entidades   	= $objData['entidades'];

			$sqlData = array(
				'id_administracion_region' => $id_region
				,'region' 				   => $region
				,'clave_corta' 			   => $clave_corta
				,'descripcion' 			   => $descripcion
				,'edit_timestamp' 		   => $this->timestamp()
				,'edit_id_usuario' 		   => $this->session->userdata('id_usuario')
				);
			$insert = $this->db_model->db_update_data($sqlData);
			
			// $id_region   = $this->ajax_post('id_region');
			// $entidades   = $this->ajax_post('entidades');

			$insertar = array('id_entidad' => $entidades,
							  'id_administracion_region'  => $id_region);
			$insert = $this->db_model->db_insert_entidades($insertar);
			
			if($insert){
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
		if(count($lts_content)>0){
			foreach ($lts_content as $value) {
				$set_data[] = array(
									 $value['region'],
									 $value['clave_corta'],
									 $value['descripcion']
									 );
			}
			
			$set_heading = array(
									$this->lang_item("region"),
									$this->lang_item("clave_corta"),
									$this->lang_item("descripcion")
									);
	
		}

		$params = array(	'title'   => $this->lang_item("lbl_excel"),
							'items'   => $set_data,
							'headers' => $set_heading
						);
		
		$this->excel->generate_xlsx($params);
	}
}