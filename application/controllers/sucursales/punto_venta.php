<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class punto_venta extends Base_Controller{
	private $modulo;
	private $seccion;
	private $view_content;
	private $path;
	private $icon;

	private $offset, $limit_max;
	private $tab, $tab1, $tab2, $tab3;

	public function __construct(){
		parent::__construct();
		$this->modulo 			= 'sucursales';
		$this->seccion          = 'punto_venta';
		$this->icon 			= 'fa fa-pie-chart'; 
		$this->path 			= $this->modulo.'/'.$this->seccion.'/'; 
		$this->view_content 	= 'content';
		$this->limit_max		= 10;
		$this->offset			= 0;
		// Tabs
		$this->tab1 			= 'agregar';
		$this->tab2 			= 'listado';
		$this->tab3 			= 'detalle';
		// DB Model
		$this->load->model($this->modulo.'/'.$this->seccion.'_model','db_model');
		$this->load->model('sucursales/listado_sucursales_model','sucursales');
		$this->load->model('almacen/catalogos_model','almacenes');
		$this->load->model('sucursales/clientes_model','clientes');
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
		$config_tab['attr']     = array('','', '');
		
		$config_tab['style_content'] = array('','','');

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
		$data['titulo_modulo']    = $this->lang_item("titulo_modulo");
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
		$sqlData = array(
			 'buscar'      	=> $filtro
			,'offset' 		=> $offset
			,'limit'      	=> $limit
		);
		$uri_segment  = $this->uri_segment();
		$total_rows	  = count($this->db_model->db_get_data($sqlData));
		$sqlData['aplicar_limit'] = true;
		$list_content = $this->db_model->db_get_data($sqlData);
		$url          = base_url($url_link);
		$paginador    = $this->pagination_bootstrap->paginator_generate($total_rows, $url, $limit, $uri_segment, array('evento_link' => 'onclick', 'function_js' => 'load_content', 'params_js'=>'1'));
		if($total_rows){
			foreach ($list_content as $value){
				//print_debug($value);
				$atrr = array(
								'href'    => '#',
							  	'onclick' => $tab_detalle.'('.$value['id_sucursales_punto_venta'].')'
						);
				$punto_venta = $this->db_model->get_data_punto_venta_delete($value['id_sucursales_punto_venta']);
				$eliminar 	= '<span style="color:red;" id="ico-eliminar_'.$value['id_sucursales_punto_venta'].'" class="ico_eliminar fa fa-times" onclick="confirm_delete('.$value['id_sucursales_punto_venta'].')" title="'.$this->lang_item("lbl_eliminar").'"></span>';
				$btn_acciones['eliminar'] = ($punto_venta[0]['num_pventa'] == 0)?$eliminar:'<span style="color:gray;" id="ico-eliminar_'.$value['id_sucursales_punto_venta'].'" class="ico_eliminar fa fa-times" title="'.$this->lang_item("lbl_eliminar").'"></span>';
				//$btn_acciones['clientes'] = '<span style="color:blue;" class="ico_eliminar fa fa-ticket" onclick="add_cliente('.$value['id_sucursales_punto_venta'].','.$value['id_sucursal'].')" title="'.$this->lang_item("lbl_agregar_cliente").'"';
				$acciones = implode('&nbsp;&nbsp;&nbsp;',$btn_acciones);
				// Datos para tabla
				$tbl_data[] = array('id'            => $value['id_sucursales_punto_venta'],
									'area'          => tool_tips_tpl($value['punto_venta'], $this->lang_item("tool_tip"), 'right' , $atrr),
									'clave_corta'   => $value['clave_corta'],
									'sucursal'      => $value['sucursal'],
									'almacen'       => $value['cv_almacen'],
									'gavetas'       => $value['gavetas'],
									'descripcion'   => $value['descripcion'],
									'acciones'		=> $acciones
									);
			}
			// Plantilla
			$tbl_plantilla = set_table_tpl();
			// Titulos de tabla
			$this->table->set_heading(	$this->lang_item("ID"),
										$this->lang_item("lbl_punto_venta"),
										$this->lang_item("lbl_clave_corta"),
										$this->lang_item('lbl_sucursales'),
										$this->lang_item('lbl_almacenes'),
										$this->lang_item('lbl_gavetas'),
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
		$id_punto_venta = $this->ajax_post('id_punto_venta');
		$detalle 		= $this->db_model->get_orden_unico_punto_venta($id_punto_venta);
		//print_debug($detalle);
		$seccion 		= $this->tab3;
		$save = array(
			 'class'   => 'btn btn-primary'
			,'name'    => 'actualizar'
			,'onclick' => 'actualizar()'
			,'content' => $this->lang_item("btn_guardar")
			);
		$btn_save = form_button($save);
		$tabData['id_punto_venta']  = $id_punto_venta;
		$tabData['lbl_punto_venta'] = $this->lang_item('lbl_punto_venta');
		$tabData['txt_punto_venta'] = $detalle[0]['punto_venta'];
		$tabData['lbl_clave_corta'] = $this->lang_item('lbl_clave_corta');
		$tabData['txt_clave_corta'] = $detalle[0]['cv_punto_venta'];
		$tabData['lbl_sucursales']  = $this->lang_item('lbl_sucursales');
		$sucursales_array = array(
							 'data'		=> $this->sucursales->db_get_data()
							,'value' 	=> 'id_sucursal'
							,'text' 	=> array('sucursal')
							,'name' 	=> "lts_sucursales"
							,'class' 	=> "requerido"
							,'selected' => $detalle[0]['id_sucursal']
 							,'event'    => array('event'       => 'onchange', 
												 'function'    => 'load_almacenes', 
												 'params'      => array('this.value'), 
												 'params_type' => array(false))
							);
		$sucursales    = dropdown_tpl($sucursales_array);
		$tabData['list_sucursales'] = $sucursales;
		$tabData['lbl_almacenes'] = $this->lang_item('lbl_almacenes');
		$almacenes_array = array(
					 'data'		=> $this->db_model->get_data_almacenes_x_sucursal($detalle[0]['id_sucursal'])
					,'value' 	=> 'id_almacen_almacenes'
					,'text' 	=> array('clave_corta','descripcion')
					,'name' 	=> "lts_almacenes"
					,'class' 	=> "requerido"
					,'selected' => $detalle[0]['id_almacen_almacenes']
					);
		$almacenes     = dropdown_tpl($almacenes_array);
		$gavetas_array = array(
					 'data'		=> $this->db_model->get_data_gavetas_x_almacen($detalle[0]['id_almacen_almacenes'])
					,'value' 	=> 'id_almacen_gavetas'
					,'text' 	=> array('clave_corta','gavetas')
					,'name' 	=> "lts_gavetas"
					,'class' 	=> "requerido"
					,'selected' => $detalle[0]['id_almacen_gavetas']
					);
		//print_debug($detalle);
		$list_gavetas  = dropdown_tpl($gavetas_array);

		$tabData['lbl_gaveta']      = $this->lang_item("lbl_gavetas");
		$tabData['list_gavetas']  = $list_gavetas;
		$tabData['list_almacenes']  = $almacenes;
		$tabData['lbl_descripcion'] = $this->lang_item('lbl_descripcion');
		$tabData['txt_descripcion'] = $detalle[0]['descripcion'];
		$tabData['lbl_ultima_modificacion'] = $this->lang_item('lbl_ultima_modificacion');
        $tabData['val_fecha_registro']      = $detalle[0]['timestamp'];
		$tabData['lbl_fecha_registro']      = $this->lang_item('lbl_fecha_registro');
		$tabData['lbl_usuario_registro']    = $this->lang_item('lbl_usuario_registro');

		$this->load_database('global_system');
        $this->load->model('users_model');

		$usuario_registro                  = $this->users_model->search_user_for_id($detalle[0]['id_user']);
	    $usuario_name	                   = text_format_tpl($usuario_registro[0]['name'],"u");
	    $tabData['val_usuarios_registro']  = $usuario_name;

		if($detalle[0]['edit_id_usuario']){
			$usuario_registro = $this->users_model->search_user_for_id($detalle[0]['edit_id_usuario']);
			$usuario_name     = text_format_tpl($usuario_registro[0]['name'],"u");
			$tabData['val_ultima_modificacion'] = sprintf($this->lang_item('val_ultima_modificacion',false), $this->timestamp_complete($detalle[0]['edicion']), $usuario_name);
		}else{
			$usuario_name = '';
			$tabData['val_ultima_modificacion'] = $this->lang_item('lbl_sin_modificacion', false);
		}
		$tabData['button_save']      = $btn_save;
		$tabData['registro_por']     = $this->lang_item('registro_por', false);
		$tabData['usuario_registro'] = $usuario_name;
		$uri_view = $this->modulo.'/'.$this->seccion.'/'.$this->seccion.'_'.$seccion;
		echo json_encode($this->load_view_unique($uri_view,$tabData,true));
	}

	public function actualizar(){
		$objData  	= $this->ajax_post('objData');
		//print_debug($objData);	
		if($objData['incomplete']>0){
			$msg = $this->lang_item("msg_campos_obligatorios",false);
			echo json_encode( array( 'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)));
		}else{
			$sqlData = array(
				'id_sucursales_punto_venta' => $objData['id_punto_venta']
				,'punto_venta'             	=> $objData['txt_punto_venta']
				,'clave_corta'            	=> $objData['txt_clave_corta']
				,'id_sucursal'            	=> $objData['lts_sucursales']
				,'id_almacen'            	=> $objData['lts_almacenes']
				,'id_almacen_gavetas'  => $objData['lts_gavetas']
				,'descripcion'            	=> $objData['txt_descripcion']
				,'edit_timestamp'         	=> $this->timestamp()
				,'edit_id_usuario'        	=> $this->session->userdata('id_usuario')
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

	public function agregar(){
		$seccion = $this->modulo.'/'.$this->seccion.'/'.$this->seccion.'_save';
		$save = array(
					 'class'   => 'btn btn-primary'
					,'name'    => 'save_punto_venta'
					,'onclick' => 'insert()'
					,'content' => $this->lang_item("btn_guardar")
					);
		$btn_save  = form_button($save);
		$clean = array(
					 'class'   => 'btn btn_primary'
					,'name'    => 'reset'
					,'onclick' => 'clean_formulario()'
					,'content' => $this->lang_item('btn_limpiar')
					);
		$btn_reset = form_button($clean);
		$sucursales_array = array(
					 'data'		=> $this->sucursales->db_get_data()
					,'value' 	=> 'id_sucursal'
					,'text' 	=> array('sucursal')
					,'name' 	=> "lts_sucursales"
					,'class' 	=> "requerido"
					,'event'    => array('event'       => 'onchange', 
										 'function'    => 'load_almacenes', 
										 'params'      => array('this.value'), 
										 'params_type' => array(false))
					);
		$sucursales    = dropdown_tpl($sucursales_array);
		$almacenes_array = array(
					 'name' 	=> "lts_almacenes"
					,'class' 	=> "requerido"
					);
		$almacenes     = dropdown_tpl($almacenes_array);

		$gavetas_array = array(
					 'name' 	=> "lts_gavetas"
					,'class' 	=> "requerido"
					);
		$gavetas     = dropdown_tpl($gavetas_array);

		$tab_1['lbl_punto_venta'] = $this->lang_item("lbl_punto_venta");
		$tab_1['lbl_gaveta']      = $this->lang_item("lbl_gavetas");
		$tab_1['lbl_clave_corta'] = $this->lang_item('lbl_clave_corta');
		$tab_1['lbl_sucursales']  = $this->lang_item('lbl_sucursales');
		$tab_1['list_sucursales'] = $sucursales;
		$tab_1['lbl_almacenes']   = $this->lang_item('lbl_almacenes');
		$tab_1['list_almacenes']  = $almacenes;
		$tab_1['list_gavetas']    = $gavetas;
		$tab_1['lbl_descripcion'] = $this->lang_item('lbl_descripcion');

		$tab_1['button_save']  = $btn_save;
		$tab_1['button_reset'] = $btn_reset;

		if($this->ajax_post(false)){
			echo json_encode($this->load_view_unique($seccion,$tab_1,true));
		}else{
			return $this->load_view_unique($seccion, $tab_1, true);
		}
	}

	public function load_almacenes(){
		$id_sucursal = $this->ajax_post('id_sucursal');
		$almacenes = $this->db_model->get_data_almacenes_x_sucursal($id_sucursal);
		$almacenes_array = array(
					 'data'		=> $almacenes
					,'value' 	=> 'id_almacen_almacenes'
					,'text' 	=> array('clave_corta')
					,'name' 	=> "lts_almacenes"
					,'class' 	=> "requerido"
					,'event'    => array('event'       => 'onchange', 
										 'function'    => 'load_gavetas', 
										 'params'      => array('this.value'), 
										 'params_type' => array(false))
					
					);
		$list_almacenes     = dropdown_tpl($almacenes_array);
		echo json_encode($list_almacenes);
	}

	public function load_gavetas(){
		$id_almacen = $this->ajax_post('id_almacen');
		$gavetas = $this->db_model->get_data_gavetas_x_almacen($id_almacen);
		$gavetas_array = array(
					 'data'		=> $gavetas
					,'value' 	=> 'id_almacen_gavetas'
					,'text' 	=> array('clave_corta')
					,'name' 	=> "lts_gavetas"
					,'class' 	=> "requerido"
					);
		$list_gavetas  = dropdown_tpl($gavetas_array);
		echo json_encode($list_gavetas);
	}

	public function insert(){
		$objData = $this->ajax_post('objData');
		if($objData['incomplete']>0){
			$msg = $this->lang_item("msg_campos_obligatorios",false);
			echo json_encode( array( 'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)));
		}else{
			$data_insert = array(
				  'punto_venta' => $objData['txt_punto_venta']
				 ,'clave_corta' => $objData['txt_clave_corta']
				 ,'id_sucursal' => $objData['lts_sucursales']
				 ,'id_almacen'  => $objData['lts_almacenes']
				 ,'id_almacen_gavetas'  => $objData['lts_gavetas']
				 ,'descripcion' => $objData['txt_descripcion']
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

	public function add_cliente(){
		$id_punto_venta = $this->ajax_post('id_punto_venta');
		$id_sucursal    = $this->ajax_post('id_sucursal');
		$seccion        = $this->modulo.'/'.$this->seccion.'/'.$this->seccion.'_cliente';
		$detalle        = $this->db_model->get_punto_venta_x_pventa($id_punto_venta);

		$save = array(
					 'class'   => 'btn btn-primary'
					,'name'    => 'add_punto_venta'
					,'onclick' => 'duplicar()'
					,'content' => $this->lang_item("btn_guardar")
					);
		$btn_save  = form_button($save);

		$punto_venta_array  = array(
						 'data'		=> $this->db_model->get_punto_venta_x_venta_sucursal($id_punto_venta)
						,'value' 	=> 'id_sucursales_punto_venta'
						,'text' 	=> array('clave_corta','punto_venta')
						,'name' 	=> "lts_punto_venta"
						,'class' 	=> "requerido"
					);
		$list_punto_venta  = dropdown_tpl($punto_venta_array);

		$tabData['instrucciones']		 = $this->lang_item('lbl_instrucciones').' '.$detalle[0]['punto_venta'];
		$tabData['id_punto_venta']       = $id_punto_venta;
		$tabData['lbl_punto_venta']      = $this->lang_item("lbl_punto_venta");
		$tabData['dropdown_punto_venta'] = $list_punto_venta;
		$tabData['button_save']          = $btn_save;

		if($this->ajax_post(false)){
			echo json_encode($this->load_view_unique($seccion,$tabData,true));
		}else{
			return $this->load_view_unique($seccion, $tabData, true);
		}
	}

	public function duplicar(){
		$objData = $this->ajax_post('objData');
		if($objData['incomplete']>0){
			$msg = $this->lang_item("msg_campos_obligatorios",false);
			echo json_encode( array( 'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)));
		}else{
			$clientes = $this->db_model->get_clientes_x_punto_venta($objData['lts_punto_venta']);
			if(!$clientes){
				$msg = $this->lang_item("msg_err_exist",false);
				echo json_encode( array( 'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)));
			}else{
				foreach ($clientes as $key => $value) {

					$existe_cliente = $this->db_model->get_cliente_punto_venta($value['id_ventas_clientes'],$objData['lts_punto_venta']);
					if(!$existe){
						
						//print_debug('cliente repetido');
					}else{
						$sqlData = array(
							 'id_cliente'      => $value['id_ventas_clientes']
							,'id_punto_venta'  => $objData['id_punto_venta']
							,'id_usuario'  	   => $this->session->userdata('id_usuario')
						    ,'timestamp'   	   => $this->timestamp()
							);
						$insert = $this->db_model->insert_cliente_venta($sqlData);
					}	
				}

				if($insert){
					$msg = $this->lang_item("msg_insert_success",false);
					echo json_encode(array(  'success'=>'true', 'mensaje' => $msg));
				}else{
					$msg = $this->lang_item("msg_err_insert",false);
					echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('', $msg ,false)));
				}
			}
		}
	}

	public function eliminar_registro(){
		$id_pventa = $this->ajax_post('id_pventa');
		$punto_venta = $this->db_model->get_data_punto_venta_delete($id_pventa);
		if($punto_venta[0]['num_pventa'] == 0){
			$sqlData = array(
					 'id_sucursales_punto_venta' => $id_pventa
		            ,'activo'                   => 0
		            ,'edit_id_usuario'          => $this->session->userdata('id_usuario')
					,'edit_timestamp'           => $this->timestamp()
					);
			
			$update = $this->db_model->bd_delete_data($sqlData);
			if($update){
				$msg = $this->lang_item("msg_delete_success",false);
				echo json_encode(array('success'=>'true', 'mensaje' => $msg, 'id_pventa' => $id_pventa));
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