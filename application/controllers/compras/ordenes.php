<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ordenes extends Base_Controller { 
/**
* Nombre:		Ordenes de compra
* Ubicación:	Compras>Ordenes
* Descripción:	Funcionamiento para la sección de ordenes de compra
* @author:		Oscar Maldonado - OM
* Creación: 	2015-05-11
* Modificación:	OM-2015-05-14
*/
	private $modulo;
	private $submodulo;
	private $view_content, $uri_view_principal;
	private $path;
	private $icon;
	private $offset, $limit_max;
	private $tab_inicial, $tab = array(), $tab_indice = array();
	
	public function __construct(){
		parent::__construct();
		$this->modulo 				= 'compras';
		$this->seccion 				= 'ordenes';
		$this->submodulo			= 'ordenes';
		$this->icon 				= 'fa fa-pencil-square'; #Icono de modulo
		$this->path 				= $this->modulo.'/'.$this->seccion.'/'.$this->submodulo.'/';
		$this->view_content 		= 'content';
		$this->uri_view_principal 	= $this->modulo.'/'.$this->view_content;
		$this->limit_max			= 10;
		$this->offset				= 0;
		// Tabs
		$this->tab_inicial 			= 2;
		$this->tab_indice 		= array(
									 'agregar'
									,'listado'
									,'detalle'
									,'articulos'
								);
		for($i=0; $i<=count($this->tab_indice)-1; $i++){
			$this->tab[$this->tab_indice[$i]] = $this->tab_indice[$i];
		}
		// DB Model
		$this->load->model($this->modulo.'/'.$this->submodulo.'_model','db_model');
		$this->load->model('users_model','users_model');
		$this->load->model('sucursales/listado_sucursales_model','sucursales_model');
		$this->load->model('administracion/formas_de_pago_model','formas_de_pago_model');
		$this->load->model('administracion/creditos_model','creditos_model');
		$this->load->model('administracion/variables_model','variables_model');
		$this->load->model('compras/listado_precios_model','listado_precios_model');
		// Diccionario
		$this->lang->load($this->modulo.'/'.$this->submodulo,"es_ES");
	}
	public function config_tabs(){
		// Creación de tabs en el contenedor principal
		for($i=1; $i<=count($this->tab); $i++){
			${'tab_'.$i} = $this->tab [$this->tab_indice[$i-1]];
		}
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
										 $this->modulo.'/'.$this->submodulo.'/'.$tab_1
										 ,$this->modulo.'/'.$this->seccion.'/'.$tab_2.$pagina
										//,$path.$tab_2.'/'.$pagina
										,$tab_3
										,$tab_4
								); 
		// Accion de tabs
		$config_tab['action']   = array(
										 'load_content'
										,'load_content'
										,''
										,'load_content'
								);
		// Atributos 
		$config_tab['attr']     = array('','', array('style' => 'display:none'),array('style' => 'display:none'));
		$config_tab['style_content'] = array('');
		return $config_tab;
	}
	public function index(){		
		// Carga de pagina inicial
		$tabl_inicial 			  = $this->tab_inicial;
		$view_listado    		  = $this->listado();		
		$contenidos_tab           = $view_listado;
		$data['titulo_seccion']   = $this->lang_item("titulo_seccion");
		$data['titulo_submodulo'] = $this->lang_item("titulo_submodulo");
		$data['icon']             = $this->icon;
		$data['tabs']             = tabbed_tpl($this->config_tabs(),base_url(),$tabl_inicial,$contenidos_tab);	
		$js['js'][]  = array('name' => $this->submodulo, 'dirname' => $this->modulo);
		$js['js'][]  = array('name' => 'numeral', 'dirname' => '');
		$this->load_view($this->uri_view_principal, $data, $js);
	}
	public function listado($offset=0){
		// Crea tabla con listado de elementos capturados 
		$accion 		= $this->tab['listado'];
		$tab_detalle	= $this->tab['detalle'];
		$limit 			= $this->limit_max;
		$uri_view 		= $this->modulo.'/'.$accion;
		$url_link 		= $this->modulo.'/'.$this->submodulo.'/'.$accion;		
		$buttonTPL 		= '';

		$filtro  = ($this->ajax_post('filtro')) ? $this->ajax_post('filtro') : "";
		$sqlData = array(
			 'buscar' => $filtro
			,'offset' => $offset
			,'limit'  => $limit
		);
		$uri_segment  			  = $this->uri_segment(); 
		$total_rows   			  = count($this->db_model->db_get_data($sqlData));
		$sqlData['aplicar_limit'] = false;
		$list_content 			  = $this->db_model->db_get_data($sqlData);
		$url          			  = base_url($url_link);
		$paginador    			  = $this->pagination_bootstrap->paginator_generate($total_rows, $url, $limit, $uri_segment, array('evento_link' => 'onclick', 'function_js' => 'load_content', 'params_js'=>'1'));

		if($total_rows){
			foreach ($list_content as $value) {
				// Evento de enlace
				$atrr = array(
								'href' => '#',
							  	'onclick' => $tab_detalle.'('.$value['id_compras_orden'].')'
						);
				// Acciones
				$accion_id 						= $value['id_compras_orden'];
				$btn_acciones['detalle'] 		= '<span id="ico-detalle_'.$accion_id.'" class="ico_acciones ico_detalle fa fa-search-plus" onclick="detalle('.$accion_id.')" title="'.$this->lang_item("detalle").'"></span>';
				$btn_acciones['agregar'] 		= '<span id="ico-articulos_'.$accion_id.'" class="ico_acciones ico_articulos fa fa-cart-plus" onclick="articulos('.$accion_id.')" title="'.$this->lang_item("agregar_articulos").'"></span>';
				$btn_acciones['eliminar']       = '<span id="ico-eliminar_'.$accion_id.'" class="ico_acciones ico_eliminar fa fa-times" onclick="confirm_delete('.$accion_id.')" title="'.$this->lang_item("eliminar").'"></span>';
				$btn_acciones['imprimir']       = '<span id="ico-imprimir_'.$accion_id.'" class="ico_acciones ico_imprimir fa fa-print" onclick="ver_pdf(\''.base_url($this->modulo.'/'.$this->submodulo).'/export_imprimir?id='.$accion_id.'\');" title="'.$this->lang_item("imprimir").'"></span>';
				$acciones = implode('&nbsp;&nbsp;&nbsp;',$btn_acciones);
				// Datos para tabla
				$tbl_data[] = array('id'             => $value['id_compras_orden'],
									'orden_num'      => tool_tips_tpl($value['orden_num'], $this->lang_item("tool_tip"), 'right' , $atrr),
									'descripcion'    => tool_tips_tpl($value['descripcion'], $this->lang_item("tool_tip"), 'right' , $atrr),
									'sucursal'       => $value['sucursal'],
									'timestamp'      => $value['timestamp'],
									'entrega_fecha'  => $value['entrega_fecha'],
									'estatus'   	 => $value['estatus'],
									'acciones' 		 => $acciones
									);
			}
			// Plantilla
			$tbl_plantilla = set_table_tpl();
			// Titulos de tabla
			$this->table->set_heading(	$this->lang_item("id"),
										$this->lang_item("orden_num"),										
										$this->lang_item("descripcion"),
										$this->lang_item("sucursal"),
										$this->lang_item("fecha_registro"),
										$this->lang_item("entrega_fecha"),
										$this->lang_item("estatus"),
										$this->lang_item("acciones")
									);
			// Generar tabla
			$this->table->set_template($tbl_plantilla);
			$tabla = $this->table->generate($tbl_data);
			// XLS
			$buttonTPL = array( 'text'   => $this->lang_item("btn_xlsx"), 
							'iconsweets' => 'fa fa-file-excel-o',
							'href'       => base_url($this->modulo.'/'.$this->submodulo).'/export_xlsx?filtro='.base64_encode($filtro)
							);
		}else{
			$msg   = $this->lang_item("msg_query_null");
			$tabla = alertas_tpl('', $msg ,false);
		}
		$tabData['filtro']    = (isset($filtro) && $filtro!="") ? sprintf($this->lang_item("msg_query_search",false),$total_rows , $filtro) : "";
		$tabData['tabla']     = $tabla;
		$tabData['paginador'] = $paginador;
		$tabData['item_info'] = $this->pagination_bootstrap->showing_items($limit, $offset, $total_rows);
		$tabData['export']    = button_tpl($buttonTPL);

		if($this->ajax_post(false)){
			echo json_encode( $this->load_view_unique($uri_view , $tabData, true));
		}else{
			return $this->load_view_unique($uri_view , $tabData, true);
		}
	}
	public function detalle(){
		// Crea formulario de detalle y edición
		$accion 			= $this->tab['detalle'];
		$id_compras_orden 	= $this->ajax_post('id_compras_orden');
		$detalle  			= $this->db_model->get_orden_unico($id_compras_orden);
		$button_save       	= form_button(array('class'=>"btn btn-primary",'name' => 'actualizar' , 'onclick'=>'actualizar()','content' => $this->lang_item("btn_guardar") ));
		//$button_clean       = form_button(array('class'=>"btn btn-primary",'name' => 'limpiar' , 'onclick'=>'clean_formulario()','content' => $this->lang_item("btn_limpiar") ));
		//se agrega para mostrar la opcion de proveedor y No. prefactura, solo si se selcciono proveedor en tipo de orden
		if($detalle[0]['id_orden_tipo']==2){
			$style='style="display:none"';
			$class ='';
		}else{
			$style='';
			$class ='requerido';
		}	
		$dropArray = array(
					'data'		=> $this->db_model->db_get_proveedores()
					,'selected' => $detalle[0]['id_proveedor'] 
					,'value' 	=> 'id_compras_proveedor'
					,'text' 	=> array('clave_corta','razon_social')
					,'name' 	=> "id_proveedor"
					,'class' 	=> $class
				);
		$proveedores    = dropdown_tpl($dropArray);

		$dropArray2 = array(
					 'data'		=> $this->sucursales_model->db_get_data()
					 ,'selected'=> $detalle[0]['id_sucursal']
					,'value' 	=> 'id_sucursal'
					,'text' 	=> array('cv_sucursal','sucursal')
					,'name' 	=> "id_sucursal"
					,'class' 	=> "requerido"
					,'event'    => array('event'       => 'onchange',
				   						 'function'    => 'show_direccion',
				   						 'params'      => array('this.value'),
				   						 'params_type' => array(0)
									)
				);
		$sucursales	    = dropdown_tpl($dropArray2);

		$dropArray3 = array(
					 'data'		=> $this->formas_de_pago_model->db_get_data()
					 ,'selected'=> $detalle[0]['id_forma_pago']
					,'value' 	=> 'id_forma_pago'
					,'text' 	=> array('clave_corta','descripcion')
					,'name' 	=> "id_forma_pago"
					,'class' 	=> "requerido"
				);
		$forma_pago	    = dropdown_tpl($dropArray3);

		$dropArray4 = array(
					 'data'		=> $this->creditos_model->db_get_data()
					 ,'selected'=> $detalle[0]['id_credito']
					,'value' 	=> 'id_administracion_creditos'
					,'text' 	=> array('clave_corta','credito')
					,'name' 	=> "id_administracion_creditos"
					,'class' 	=> ""
				);
		$creditos	    = dropdown_tpl($dropArray4);

		$dropArray5 = array(
					 'data'		=> $this->db_model->db_get_tipo_orden()
					 ,'selected'=> $detalle[0]['id_orden_tipo']
					,'value' 	=> 'id_orden_tipo'
					,'text' 	=> array('orden_tipo')
					,'name' 	=> "id_orden_tipo"
					,'class' 	=> "requerido"
					,'disabled' => 'disabled="disabled"'
					,'event'    => array('event'       => 'onchange',
				   						 'function'    => 'show_proveedor',
				   						 'params'      => array('this.value'),
				   						 'params_type' => array(0)
									)
				);
		$orden_tipo	    = dropdown_tpl($dropArray5);

		$fec=explode('-',$detalle[0]['entrega_fecha']);
		$entrega_fecha=$fec[2].'/'.$fec[1].'/'.$fec[0];
		$fec2=explode('-',$detalle[0]['orden_fecha']);
		$orden_fecha=$fec2[2].'/'.$fec2[1].'/'.$fec2[0];
		$tabData['id_compras_orden']		 = $id_compras_orden;
		$tabData['orden_num']   			 = $this->lang_item("orden_num",false);
		$tabData['orden_num_value']	 		 = $detalle[0]['orden_num'];
        $tabData['proveedor'] 	 			 = $this->lang_item("proveedor",false);
		$tabData['list_proveedores']		 = $proveedores;
		$tabData['sucursal']     			 = $this->lang_item("sucursal",false);
        $tabData['list_sucursales']			 = $sucursales;
        $tabData['descripcion']       		 = $this->lang_item("descripcion",false);
        $tabData['descripcion_value'] 		 = $detalle[0]['descripcion'];
        $tabData['lbl_fecha_registro']    	 = $this->lang_item("lbl_fecha_registro",false);
        $tabData['timestamp']         		 = $detalle[0]['timestamp'];
        $tabData['button_save']       		 = $button_save;
        $tabData['orden_fecha']   		     = $this->lang_item("orden_fecha",false);
		$tabData['orden_fecha_value']	 	 = $orden_fecha;
        $tabData['entrega_direccion']        = $this->lang_item("entrega_direccion",false);
        $tabData['entrega_direccion_value']	 = $detalle[0]['entrega_direccion'];
		$tabData['entrega_fecha']            = $this->lang_item("entrega_fecha",false);
        $tabData['entrega_fecha_value']	     = $entrega_fecha;
        $tabData['prefactura_num']       	 = $this->lang_item("prefactura_num",false);
        $tabData['prefactura_num_value'] 	 = $detalle[0]['prefactura_num'];
        $tabData['observaciones']    	     = $this->lang_item("observaciones",false);
        $tabData['observaciones_value']      = $detalle[0]['observaciones'];
        $tabData['forma_pago']     			 = $this->lang_item("forma_pago",false);
        $tabData['creditos']     			 = $this->lang_item("creditos",false);
        $tabData['list_forma_pago']			 = $forma_pago;
		$tabData['list_creditos']			 = $creditos;
		$tabData['orden_tipo']  			 = $this->lang_item("orden_tipo",false);
		$tabData['list_orden_tipo']			 = $orden_tipo;
		$tabData['style']					 = $style;
		$tabData['class']					 = $class;
		$tabData['lbl_ultima_modificacion']  = $this->lang_item('lbl_ultima_modificacion', false);
		$tabData['registro_por']  		= $this->lang_item("registro_por",false);

		$this->load->model('users_model');
    	$usuario_registro              = $this->users_model->search_user_for_id($detalle[0]['id_usuario']);
        $tabData['usuario_registro']   = text_format_tpl($usuario_registro[0]['name'],"u");

       if($detalle[0]['edit_id_usuario']){
        	$usuario_registro                   = $this->users_model->search_user_for_id($detalle[0]['edit_id_usuario']);
        	$usuario_name 				        = text_format_tpl($usuario_registro[0]['name'],"u");
        	$tabData['val_ultima_modificacion'] = sprintf($this->lang_item('val_ultima_modificacion', false), $this->timestamp_complete($detalle[0]['edit_timestamp']), $usuario_name);
    	}else{
    		$usuario_name = '';
    		$tabData['val_ultima_modificacion'] = $this->lang_item('lbl_sin_modificacion', false);
    	}
		$uri_view  = $this->path.$this->submodulo.'_'.$accion;
		echo json_encode( $this->load_view_unique($uri_view ,$tabData, true));
	}
	public function agregar(){
		// Crea formulario para agregar nuevo elemento
		$accion 		= $this->tab['agregar'];
		$uri_view   	= $this->path.$this->submodulo.'_'.$accion;
		// Listas
		$dropArray = array(
					 'data'		=> $this->db_model->db_get_proveedores()
					,'value' 	=> 'id_compras_proveedor'
					,'text' 	=> array('clave_corta','razon_social')
					,'name' 	=> "id_proveedor"
				);
		$proveedores    = dropdown_tpl($dropArray);

		$dropArray2 = array(
					 'data'		=> $this->sucursales_model->db_get_data()
					,'value' 	=> 'id_sucursal'
					,'text' 	=> array('cv_sucursal','sucursal')
					,'name' 	=> "id_sucursal"
					,'class' 	=> "requerido"
					,'event'    => array('event'       => 'onchange',
				   						 'function'    => 'show_direccion',
				   						 'params'      => array('this.value'),
				   						 'params_type' => array(0)
									)
				);
		$sucursales	    = dropdown_tpl($dropArray2);

		$dropArray3 = array(
					 'data'		=> $this->formas_de_pago_model->db_get_data()
					,'value' 	=> 'id_forma_pago'
					,'text' 	=> array('clave_corta','descripcion')
					,'name' 	=> "id_forma_pago"
					,'class' 	=> "requerido"
				);
		$forma_pago	    = dropdown_tpl($dropArray3);

		$dropArray4 = array(
					 'data'		=> $this->creditos_model->db_get_data()
					,'value' 	=> 'id_administracion_creditos'
					,'text' 	=> array('clave_corta','credito')
					,'name' 	=> "id_administracion_creditos"
					,'class' 	=> ""
				);
		$creditos	    = dropdown_tpl($dropArray4);

		$dropArray5 = array(
					 'data'		=> $this->db_model->db_get_tipo_orden()
					,'value' 	=> 'id_orden_tipo'
					,'text' 	=> array('orden_tipo')
					,'name' 	=> "id_orden_tipo"
					,'class' 	=> "requerido"
					,'event'    => array('event'       => 'onchange',
				   						 'function'    => 'show_proveedor',
				   						 'params'      => array('this.value'),
				   						 'params_type' => array(0)
									)
				);
		$orden_tipo	    = dropdown_tpl($dropArray5);
		// Botones
		$btn_save       = form_button(array('class'=>"btn btn-primary",'name' => 'save','onclick'=>'insert()' , 'content' => $this->lang_item("btn_guardar") ));
		$btn_reset      = form_button(array('class'=>"btn btn-primary",'name' => 'reset','value' => 'reset','onclick'=>'clean_formulario()','content' => $this->lang_item("btn_limpiar")));
		// Etiquetas
		$tabData['proveedor'] 			= $this->lang_item("proveedor",false);
        $tabData['list_proveedores']	= $proveedores;
        $tabData['sucursal']     		= $this->lang_item("sucursal",false);
        $tabData['list_sucursales']		= $sucursales;
        $tabData['forma_pago']     		= $this->lang_item("forma_pago",false);
        $tabData['list_forma_pago']		= $forma_pago;
        $tabData['creditos']     		= $this->lang_item("creditos",false);
        $tabData['list_creditos']		= $creditos;
        $tabData['descripcion']     	= $this->lang_item("descripcion",false);
        $tabData['fecha_registro']  	= $this->lang_item("fecha_registro",false);
        $tabData['registro_por']  		= $this->lang_item("registro_por",false);
        $tabData['timestamp']       	= date('Y-m-d H:i');
        $tabData['orden_fecha']     	= $this->lang_item("orden_fecha",false);
        $tabData['entrega_direccion']   = $this->lang_item("entrega_direccion",false);
        $tabData['entrega_fecha']     	= $this->lang_item("entrega_fecha",false);
        $tabData['prefactura_num']     	= $this->lang_item("prefactura_num",false);
        $tabData['observaciones']     	= $this->lang_item("observaciones",false);        
        $tabData['orden_tipo']  		= $this->lang_item("orden_tipo",false);
        $tabData['list_orden_tipo']		= $orden_tipo;    
        $tabData['button_save']     	= $btn_save;
        $tabData['button_reset']    	= $btn_reset;
        // Respuesta
        if($this->ajax_post(false)){
				echo json_encode($this->load_view_unique($uri_view , $tabData, true));
		}else{
			return $this->load_view_unique($uri_view , $tabData, true);
		}
	}
	public function insert(){
		// Recibe datos de formulario e inserta un nuevo registro en la BD
		$incomplete  = $this->ajax_post('incomplete');
		if($incomplete>0){
			$msg = $this->lang_item("msg_campos_obligatorios",false);
			echo json_encode( array( 'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)) );
		}else{
			$fec=explode('/',$this->ajax_post('entrega_fecha'));
			$entrega_fecha=$fec[2].'-'.$fec[1].'-'.$fec[0];
			$fec2=explode('/',$this->ajax_post('orden_fecha'));
			$orden_fecha=$fec2[2].'-'.$fec2[1].'-'.$fec2[0];
			$filtro=1;
				$sqlData = array('buscar'=> $filtro);
				$no_orden=$this->variables_model->db_get_data($sqlData);
				//dump_var($no_orden);
			$sqlData = array(
						'orden_num' 		 => $no_orden[0]['valor']+1
						,'id_orden_tipo' 	 => $this->ajax_post('id_orden_tipo')
						,'orden_fecha' 		 => $orden_fecha
						,'id_proveedor' 	 => $this->ajax_post('id_proveedor')
						,'descripcion'		 => $this->ajax_post('descripcion')
						,'id_sucursal'  	 => $this->ajax_post('id_sucursal')
						,'entrega_direccion' => $this->ajax_post('entrega_direccion')
						,'entrega_fecha'     => $entrega_fecha
						,'id_forma_pago'     => $this->ajax_post('id_forma_pago')
						,'id_credito' 		 => $this->ajax_post('id_administracion_creditos')
						,'prefactura_num' 	 => $this->ajax_post('prefactura_num')
						,'observaciones' 	 => $this->ajax_post('observaciones')
						,'id_usuario' 		 => $this->session->userdata('id_usuario')
						,'timestamp'  		 => $this->timestamp()
						);
			$insert = $this->db_model->insert($sqlData);
			if($insert){
				$sqlData2 = array(
							'valor' 		  => $no_orden[0]['valor']+1,
							'id_vars'		  => 1,
							'edit_id_usuario'           	=> $this->session->userdata('id_usuario'),
							'edit_timestamp' 				=>$this->timestamp()
									);	
				$insert2 = $this->variables_model->update($sqlData2);	
				if($insert2){
					$msg = $this->lang_item("msg_insert_success",false);
					echo json_encode(array(  'success'=>'true', 'mensaje' => $msg));
				}else{
					$msg = $this->lang_item("msg_err_clv",false);
					echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('', $msg ,false)));
				}
			}else{
				$msg = $this->lang_item("msg_err_clv",false);
				echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('', $msg ,false)));
			}
		}
	}
	public function actualizar(){
		// Recibe datos de formulario y actualiza un registro existente en la BD
		$incomplete  = $this->ajax_post('incomplete');
		if($incomplete>0){
			$msg = $this->lang_item("msg_campos_obligatorios",false);
			echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)));
		}else{
			$fec=explode('/',$this->ajax_post('entrega_fecha'));
			$entrega_fecha=$fec[2].'-'.$fec[1].'-'.$fec[0];
			$fec2=explode('/',$this->ajax_post('orden_fecha'));
			$orden_fecha=$fec2[2].'-'.$fec2[1].'-'.$fec2[0];
			$sqlData = array(
						 'id_compras_orden'	 => $this->ajax_post('id_compras_orden')
						,'orden_fecha' 		 => $orden_fecha
						,'id_proveedor' 	 => $this->ajax_post('id_proveedor')
						,'descripcion'		 => $this->ajax_post('descripcion')
						,'id_sucursal'  	 => $this->ajax_post('id_sucursal')
						,'entrega_direccion' => $this->ajax_post('entrega_direccion')
						,'entrega_fecha'     => $entrega_fecha
						,'id_forma_pago'     => $this->ajax_post('id_forma_pago')
						,'id_credito' 		 => $this->ajax_post('id_administracion_creditos')
						,'prefactura_num' 	 => $this->ajax_post('prefactura_num')
						,'observaciones' 	 => $this->ajax_post('observaciones')
						,'edit_timestamp'  	 => $this->timestamp()
						,'edit_id_usuario'   => $this->session->userdata('id_usuario')
						);
			$update = $this->db_model->db_update_data($sqlData);
			if($update){
				$msg = $this->lang_item("msg_update_success",false);
				echo json_encode(array(  'success'=>'true', 'mensaje' => $msg ));
			}else{
				$msg = $this->lang_item("msg_err_clv",false);
				echo json_encode( array( 'success'=>'false', 'mensaje' =>alertas_tpl('', $msg ,false)));
			}
		}
	}
	public function eliminar(){
		$msj_grid = $this->ajax_post('msj_grid');
		$sqlData = array(
						 'id_compras_orden'	=> $this->ajax_post('id_compras_orden')
						,'estatus' 		 =>6
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
	public function articulos($id_compras_orden=false){
		// Agregar articulos a una orden de compra
		$table 				= '';
		$accion 			= $this->tab['articulos'];
		$uso_interno		= (!$id_compras_orden)?false:true;
		$id_compras_orden 	= (!$id_compras_orden)?$this->ajax_post('id_compras_orden'):$id_compras_orden;
		$detalle  			= $this->db_model->get_orden_unico($id_compras_orden);
		//	dump_var($detalle);
		$btn_save       	= form_button(array('class'=>"btn btn-primary",'name' => 'save' , 'onclick'=>'cerrar_orden_listado()','content' => $this->lang_item("btn_cerrar") ));
		$btn_canceled       = form_button(array('class'=>"btn btn-primary",'name' => 'canceled' , 'onclick'=>'cancelar_orden_listado()','content' => $this->lang_item("btn_cancelar") ));
		//se agrega para mostrar la opcion de proveedor y No. prefactura, solo si se selcciono proveedor en tipo de orden
		if($detalle[0]['id_orden_tipo']==2){
			$style='style="display:none"';
			$class ='';
		}else{
			$style='';
			$class ='requerido';
		}	
		if($detalle[0]['id_proveedor']>0){
			$get_data=$this->listado_precios_model->db_get_data_x_proveedor($detalle[0]['id_proveedor']);
		}else{
			$get_data=$this->listado_precios_model->db_get_data_x_proveedor();
		}
		$dropArray4 = array(
					 'data'		=> $get_data
					,'value' 	=> 'id_compras_articulo_precios'
					,'text' 	=> array('articulo','presentacion_detalle','sku','upc')
					,'name' 	=> "lts_articulos"
					,'event'    => array('event'       => 'onchange',
				   						 'function'    => 'get_orden_listado_articulo',
				   						 'params'      => array('this.value'),
				   						 'params_type' => array(0)
									)
					,'class' 	=> "articulos_lista"
				);
		if($detalle[0]['estatus']==7){
			$list_articulos  = "";
			$readonly="readonly";
		}else{
			$list_articulos  = dropdown_tpl($dropArray4);
			$readonly="";
		}

		$data_sql = array('id_compras_orden'=>$id_compras_orden);
		$data_listado=$this->db_model->db_get_data_orden_listado_registrado($data_sql);
		$moneda = $this->session->userdata('moneda');

		$subtotal_value 	= 0;
		$descuento_value 	= 0;
		$impuesto_value 	= 0;
		$total_value	 	= 0;
		if(count($data_listado)>0){
				$style_table='display:block';				
				$html='';
			for($i=0;count($data_listado)>$i;$i++){
					// Totales
					$subtotal_value 	+= $data_listado[$i]['subtotal'];
					$descuento_value 	+= $data_listado[$i]['costo_x_cantidad']*($data_listado[$i]['descuento']/100);
					$impuesto_value 	+= $data_listado[$i]['valor_impuesto'];
					// Lineas
					$btn_acciones['eliminar']       = '<span id="ico-eliminar_'.$data_listado[$i]['id_compras_articulo_precios'].'" class="ico_eliminar fa fa-times" onclick="deshabilitar_orden_lisatdo('.$data_listado[$i]['id_compras_articulo_precios'].')" title="'.$this->lang_item("eliminar").'"></span>';
					$acciones = implode('&nbsp;&nbsp;&nbsp;',$btn_acciones);
					$peso_unitario = (substr($data_listado[$i]['peso_unitario'], strpos($data_listado[$i]['peso_unitario'], "." ))=='.000')?number_format($data_listado[$i]['peso_unitario'],0):$data_listado[$i]['peso_unitario'];
					$presentacion_x_embalaje = (substr($data_listado[$i]['presentacion_x_embalaje'], strpos($data_listado[$i]['presentacion_x_embalaje'], "." ))=='.000')?number_format($data_listado[$i]['presentacion_x_embalaje'],0):$data_listado[$i]['presentacion_x_embalaje'];
					$embalaje = ($data_listado[$i]['embalaje'])?$data_listado[$i]['embalaje'].' CON ':'';
				$Data['id_compras_articulo_precios'] 	= $data_listado[$i]['id_compras_articulo_precios'];
				$Data['consecutivo']				 	= $i+1;
				$Data['nombre_comercial']				= $data_listado[$i]['nombre_comercial'];
				$Data['articulo']						= $data_listado[$i]['articulo'].' - '.$data_listado[$i]['presentacion_detalle'];
				$Data['peso_unitario']					= $peso_unitario;
				$Data['cl_um']							= $data_listado[$i]['cl_um'];
				$Data['upc']							= 'SKU:'.$data_listado[$i]['sku'].' UPC:'.$data_listado[$i]['upc'];
				$Data['embalaje_X_presentacion']		= $embalaje.$presentacion_x_embalaje;
				$Data['presentacion']					= $data_listado[$i]['presentacion'];
				$Data['moneda']							= $moneda;
				$Data['costo_sin_impuesto']				= $data_listado[$i]['costo_sin_impuesto'];
				$Data['format_costo_sin_impuesto']		= number_format($data_listado[$i]['costo_sin_impuesto'],2);
				$Data['read_only'] 						= $readonly;
				$Data['cantidad'] 						= $data_listado[$i]['cantidad'];
				$Data['costo_x_cantidad']				= $data_listado[$i]['costo_x_cantidad'];
				$Data['format_costo_x_cantidad']		= number_format($data_listado[$i]['costo_x_cantidad'],2);
				$Data['descuento'] 						= $data_listado[$i]['descuento'];
				$Data['fomrat_subtotal']  				= number_format($data_listado[$i]['subtotal'],2);
				$Data['impuesto_porcentaje'] 			= $data_listado[$i]['impuesto_porcentaje'];
				$Data['format_impuesto_porcentaje']  	= number_format($data_listado[$i]['impuesto_porcentaje'],0);
				$Data['valor_impuesto'] 				= $data_listado[$i]['valor_impuesto'];
				$Data['format_valor_impuesto'] 			= number_format($data_listado[$i]['valor_impuesto'],2);
				$Data['total'] 							= $data_listado[$i]['total'];
				$Data['total_format'] 					= number_format($data_listado[$i]['total'],2);
				$Data['acciones'] 						= $acciones;
				$Data['subtotal']						= $data_listado[$i]['subtotal'];
				$url_listado_tpl = $this->modulo.'/'.$this->seccion.'/'.$this->submodulo.'/'.'ordenes_articulo_listado_registrado';
				//dump_var($Data);
				$html.=$this->load_view_unique($url_listado_tpl ,$Data, true);
			}
		}
		else{
			$style_table='display:none';
			$html='';
		}
		$data='';
		$proveedores    = $this->db_model->db_get_proveedores($data,$detalle[0]['id_proveedor']);
		$sucursales	    = $this->sucursales_model->get_orden_unico_sucursal($detalle[0]['id_sucursal']);
		$forma_pago	    = $this->formas_de_pago_model->get_orden_unico_formapago($detalle[0]['id_forma_pago']);
		$creditos	    = $this->creditos_model->get_orden_unico_credito($detalle[0]['id_credito']);
		$orden_tipo	    = $this->db_model->db_get_tipo_orden($detalle[0]['id_orden_tipo']);
		
		$fec=explode('-',$detalle[0]['entrega_fecha']);
		$entrega_fecha=$fec[2].'/'.$fec[1].'/'.$fec[0];
		$fec2=explode('-',$detalle[0]['orden_fecha']);
		$orden_fecha=$fec2[2].'/'.$fec2[1].'/'.$fec2[0];
		$tabData['id_compras_orden']		 = $id_compras_orden;
		$tabData['orden_num']   			 = $this->lang_item("orden_num",false);
        $tabData['proveedor'] 	 			 = $this->lang_item("proveedor",false);
		$tabData['sucursal']     			 = $this->lang_item("sucursal",false);
        $tabData['descripcion']       		 = $this->lang_item("descripcion",false);
        $tabData['lbl_fecha_registro']    	 = $this->lang_item("lbl_fecha_registro",false);
        $tabData['orden_fecha']   		     = $this->lang_item("orden_fecha",false);
        $tabData['entrega_direccion']        = $this->lang_item("entrega_direccion",false);
		$tabData['entrega_fecha']            = $this->lang_item("entrega_fecha",false);
        $tabData['prefactura_num']       	 = $this->lang_item("prefactura_num",false);
        $tabData['observaciones']    	     = $this->lang_item("observaciones",false);
        $tabData['forma_pago']     			 = $this->lang_item("forma_pago",false);
        $tabData['credito']     			 = $this->lang_item("credito",false);
		$tabData['orden_tipo']  			 = $this->lang_item("orden_tipo",false);
		$tabData['lst_articulos_label'] 	 = (!$uso_interno)?$this->lang_item("lst_articulos_label",false):'';
		$tabData['proveedor']  			 	 = $this->lang_item("proveedor",false);
		$tabData['articulo']  			 	 = $this->lang_item("articulo",false);
		$tabData['clave_corta']  			 = $this->lang_item("clave_corta",false);
		$tabData['costo_unitario']	 		 = $this->lang_item("costo_unitario",false);
		$tabData['cantidad']  			 	 = $this->lang_item("cantidad",false);
		$tabData['costo_cantidad']  	     = $this->lang_item("costo_cantidad",false);
		$tabData['descuento']  			 	 = $this->lang_item("descuento",false);
		$tabData['subtotal']  			 	 = $this->lang_item("subtotal",false);
		$tabData['imp']  			 		 = $this->lang_item("imp",false);
		$tabData['valor_imp']  			 	 = $this->lang_item("valor_imp",false);
		$tabData['total']  			 		 = $this->lang_item("total",false);
		$tabData['accion']  				 = $this->lang_item("accion",false);
		$tabData['subtotal']  				 = $this->lang_item("subtotal",false);
		$tabData['impuesto']  				 = $this->lang_item("impuesto",false);
		$tabData['a_pagar']  				 = $this->lang_item("a_pagar",false);
		$tabData['cerrar_orden']  		 	 = (!$uso_interno)?$this->lang_item("cerrar_orden",false):'';
		$tabData['cancelar_orden']			 = (!$uso_interno)?$this->lang_item("cancelar_orden",false):'';
		$tabData['presentacion']			 = $this->lang_item("presentacion",false);
		$tabData['consecutivo']				 = $this->lang_item("consecutivo",false);
		$tabData['estatus']	 		 		 = $this->lang_item("estatus",false);;
		//DATA
		$tabData['orden_num_value']	 		 = $detalle[0]['orden_num'];
		$tabData['list_proveedores']		 = $proveedores[0]['razon_social'];
		$tabData['list_sucursales']			 = $sucursales[0]['sucursal'];
		$tabData['descripcion_value'] 		 = $detalle[0]['descripcion'];
		$tabData['timestamp']         		 = $detalle[0]['timestamp'];
		$tabData['button_save']       		 = $btn_save;
		$tabData['btn_canceled']       		 = $btn_canceled;
		$tabData['orden_fecha_value']	 	 = $orden_fecha;
		$tabData['entrega_direccion_value']	 = $detalle[0]['entrega_direccion'];
		$tabData['entrega_fecha_value']	     = $entrega_fecha;
		$tabData['prefactura_num_value'] 	 = $detalle[0]['prefactura_num'];
		$tabData['observaciones_value']      = $detalle[0]['observaciones'];
		$tabData['list_forma_pago']			 = $forma_pago[0]['forma_pago'];
		$tabData['list_creditos']			 = $creditos[0]['credito'];
		$tabData['list_orden_tipo']			 = $orden_tipo[0]['descripcion'];
		$tabData['list_arti']			     = (!$uso_interno)?$list_articulos:'';
		$tabData['style']					 = $style;
		$tabData['class']					 = $class;
		$tabData['table']					 = $html;
		$tabData['style_table']				 = $style_table;
		$tabData['lbl_ultima_modificacion']  = $this->lang_item('lbl_ultima_modificacion', false);
		$tabData['moneda']					 = $moneda;
		$tabData['estatus_value']  			 = $detalle[0]['estatus'].' - '.$detalle[0]['edit_timestamp'];
		// Totales
		$tabData['subtotal_value']			 = $moneda.' '.number_format($subtotal_value,2);
		$tabData['descuento_value']			 = '- '.$moneda.' '.number_format($descuento_value,2);
		$tabData['impuesto_value']			 = $moneda.' '.number_format($impuesto_value,2);
		$tabData['total_value']				 = $moneda.' '.number_format(($subtotal_value-$descuento_value)+$impuesto_value,2);
		$tabData['subtotal_data']			 = $subtotal_value;
		$tabData['descuento_data']			 = $descuento_value*-1;
		$tabData['impuesto_data']			 = $impuesto_value;
		$tabData['total_data']				 = ($subtotal_value-$descuento_value)+$impuesto_value;

		$tabData['estilo_extra'] 			 = ($uso_interno)?'<style>
																	body{background: #FFF !important;} 
																	#tbl1{float: left !important; font-size:.7em;}
																	#tbl2{float: left !important; font-size:.7em;}
																	.amountdue{align: right !important;}
																</style>':'';


		//dump_var($tabData);

		$uri_view  = $this->path.$this->submodulo.'_'.$accion;
		if(!$uso_interno){
			echo json_encode($this->load_view_unique($uri_view ,$tabData, true));
		}else{
			$includes['css'][]  = array('name' => 'style.default', 'dirname' => '');
			$includes['css'][]  = array('name' => 'estilos-custom', 'dirname' => '');
			return $this->load_view_unique($uri_view ,$tabData, true, $includes);
		}
	}
	public function get_data_articulo(){
		$moneda = $this->session->userdata('moneda');
		$consecutivo = intval($this->ajax_post('consecutivo'))+1;
		$id_compras_articulo_precios 	= $this->ajax_post('id_compras_articulo_precios');
		$id_compras_orden 	= $this->ajax_post('id_compras_orden');
		$sqlData =	array(
					'id_compras_orden' 			   => $id_compras_orden,
					'id_compras_articulo_precios'  => $id_compras_articulo_precios,
					'activo'					   =>1,
					'edit_timestamp'  	 		   => $this->timestamp(),
					'edit_id_usuario'   		   => $this->session->userdata('id_usuario')
					); 
		$update=$this->db_model->db_update_orden_listado_articulos($sqlData);
		//$update=1;

		if($update){
			/////¨PASAR LA CONSULTA A RREGLO PARA SOLO TENER UNA *******************************************************************************************************
			$get_data=$this->listado_precios_model->db_get_data_x_articulos($id_compras_articulo_precios);
			//dump_var($get_data);
			if($get_data[0]['id_impuesto']==''){
				$impuesto=0;
			}else{
				$impuesto=$get_data[0]['impuesto'];
			}
			$btn_acciones['eliminar']       = '<span id="ico-eliminar_'.$get_data[0]['id_compras_articulo_precios'].'" class="ico_eliminar fa fa-times" onclick="deshabilitar_orden_lisatdo('.$get_data[0]['id_compras_articulo_precios'].')" title="'.$this->lang_item("eliminar").'"></span>';
			$acciones = implode('&nbsp;&nbsp;&nbsp;',$btn_acciones);

			$tabData['id_compras_articulo_precios'] =  $id_compras_articulo_precios;
			$tabData['consecutivo'] 				=  $consecutivo;
			$tabData['articulo'] 					=  $get_data[0]['articulo'];
			$tabData['upc'] 						=  $get_data[0]['upc'];
			$tabData['costo_sin_impuesto'] 	    	=  $get_data[0]['costo_sin_impuesto'];
			$tabData['cl_presentacion'] 			=  $get_data[0]['cl_presentacion'];
			$tabData['nombre_comercial'] 			=  $get_data[0]['nombre_comercial'];
			$tabData['impuesto'] 					=  $get_data[0]['impuesto'];
			$tabData['moneda'] 	 					=  $moneda;
			$tabData['acciones']  	    			=  $acciones;
			
			$url_listado_tpl = $this->modulo.'/'.$this->seccion.'/'.$this->submodulo.'/'.'ordenes_articulo_listado';
			$html = $this->load_view_unique($url_listado_tpl ,$tabData, true);

		echo json_encode($html);

		}else{}
	}
	public function validar_exist_listado(){
		$id_compras_articulo_precios = $this->ajax_post('id_compras_articulo_precios');
		$id_compras_orden 			 = $this->ajax_post('id_compras_orden');
		 $sqldata=array(
		 			'id_compras_orden'            => $id_compras_orden,
		 			'id_compras_articulo_precios' => $id_compras_articulo_precios
		 			);
		 $get_data = $this->db_model->db_get_data_orde_listado_precio($sqldata);
		 if($get_data>0){
		 		$msg = $this->lang_item("msg_existencia_listado",false);
				$json_respuesta = array(
						 'id' 		=> 1
						,'contenido'=> alertas_tpl('success', $msg ,false)
						,'success' 	=> true
				);
		 }
		 else{
				$json_respuesta = array(
						 'id' 		=> 0
						,'contenido'=> 'no existe'
						,'success' 	=> false
				);
		 }
		 echo json_encode($json_respuesta);
	}
	public function insert_orden_listado_articulos(){
		$id_compras_articulo_precios 	= $this->ajax_post('id_compras_articulo_precios');
		$insert=false;
		//echo $id_compras_articulo_precios[$id_compras_articulo_precios].'<br>';
		//$keys=array_keys($id_compras_articulo_precios);
		$id_compras_orden = $this->ajax_post('id_compras_orden');
		$sqldata= array(
					'id_compras_articulo_precios' => $id_compras_articulo_precios
					,'id_compras_orden'			  => $id_compras_orden
					,'id_usuario' 		 		  => $this->session->userdata('id_usuario')
					,'timestamp'  		 		  => $this->timestamp()
				);
		//dump_var($sqldata);
		$insert = $this->db_model->db_insert_orde_listado_articulos($sqldata);
		//$insert=1;
		if($insert){
			$msg = sprintf($this->lang_item('msg_insert_success', false));
			$json_respuesta = array(
				 'id' 		=> 1
				,'contenido'=> alertas_tpl('success', $msg ,false)
				,'success' 	=> true
			);
		}else{
			$msg = $this->lang_item("msg_err_clv",false);
			$json_respuesta = array(
					 'id' 		=> 0
					,'contenido'=> alertas_tpl('', $msg ,false)
					,'success' 	=> false
			);
		}
		echo json_encode($json_respuesta);	
	}
	public function update_orden_listado_precios(){
		$id_compras_articulo_precios 	= $this->ajax_post('id_compras_articulo_precios');
		$costo_x_cantidad_hidden 		= $this->ajax_post('costo_x_cantidad_hidden');
		$id_compras_orden 				= $this->ajax_post('id_compras_orden');
		$cantidad 						= $this->ajax_post('cantidad');
		$costo2 						= $this->ajax_post('costo2');
		$descuento 						= $this->ajax_post('descuento');
		$subtotal__hidden 				= $this->ajax_post('subtotal__hidden');
		$valor_hidden_impuesto 			= $this->ajax_post('valor_hidden_impuesto');
		$total_hidden 					= $this->ajax_post('total_hidden');
		$impuesto 						= $this->ajax_post('impuesto');

		//dump_var($subtotal__hidden);
		$array=array(	
					0  	=> $cantidad, 
					1   => $costo_x_cantidad_hidden,
					2  	=> $descuento,
					3  	=> $impuesto,
					4  	=> $subtotal__hidden,
					5 	=> $valor_hidden_impuesto,
					6  	=> $total_hidden
				);
		$keys=array_keys($id_compras_articulo_precios);
		for($i=0; count($id_compras_articulo_precios)>$i;$i++){
			for($j=0; count($array)>$j;$j++){
				$data[$i][]=$array[$j][$keys[$i]];
			}
		}
		for($d=0;count($data)>$d;$d++){
			$sqldata= array(
						'id_compras_orden' 			   =>$id_compras_orden,
						'id_compras_articulo_precios'  =>$keys[$d],
						'cantidad'					   =>$data[$d][0],
						'costo_x_cantidad'			   =>$data[$d][1],
						'descuento'					   =>$data[$d][2],
						'impuesto_porcentaje'		   =>$data[$d][3],
						'subtotal'					   =>$data[$d][4],
						'valor_impuesto'			   =>$data[$d][5],
						'total'						   =>$data[$d][6],
						'edit_timestamp'  	 		   => $this->timestamp(),
						'edit_id_usuario'   		   => $this->session->userdata('id_usuario')
					);
			//dump_var($sqldata);
			$update = $this->db_model->db_update_orden_listado_articulos($sqldata);	
		}
		//dump_var($sqldata);
	}
	public function deshabilitar_orden_lisatdo(){
		$id_compras_articulo_precios 	= $this->ajax_post('id_compras_articulo_precios');
		$id_compras_orden 	= $this->ajax_post('id_compras_orden');
		$sqldata= array('id_compras_articulo_precios'	=> $id_compras_articulo_precios,
						'id_compras_orden'				=> $id_compras_orden,
						'activo'						=> 0,
						'edit_timestamp'  	 		   	=> $this->timestamp(),
						'edit_id_usuario'   		  	=> $this->session->userdata('id_usuario')
					);
		$update = $this->db_model->db_update_activo_orden_listado($sqldata);
		if($update){
				$msg = $this->lang_item("msg_delete_success",false);
				$json_respuesta = array(
						 'id' 		=> 1
						,'contenido'=> alertas_tpl('success', $msg ,false)
						,'success' 	=> true
				);
			}else{
				$msg = $this->lang_item("msg_err_clv",false);
				$json_respuesta = array(
						 'id' 		=> 0
						,'contenido'=> alertas_tpl('', $msg ,false)
						,'success' 	=> false
				);
			}
		echo json_encode($json_respuesta);
	}
	public function cerrar_orden_listado(){
		$id_compras_orden 	= $this->ajax_post('id_compras_orden');
		$estatus 	= $this->ajax_post('estatus');
		$subtotal 	= $this->ajax_post('subtotal');
		$descuento 	= $this->ajax_post('descuento_total');
		$impuesto 	= $this->ajax_post('impuesto_total');
		$total 		= $this->ajax_post('total_data');
		if($estatus==3){
			$valor_estatus=4;
		}
		else{
			$valor_estatus=7;
		}	
		$sqldata= array(
					'id_compras_orden' 			=> $id_compras_orden,
					'estatus' 					=> $valor_estatus,
					'subtotal'					=> $subtotal,
					'descuento'					=> $descuento,
					'impuesto'					=> $impuesto,
					'total'						=> $total,
					'edit_timestamp'  	 		=> $this->timestamp(),
					'edit_id_usuario'   		=> $this->session->userdata('id_usuario')
					);
		$update = $this->db_model->db_update_data($sqldata);
		if($update){
			$msg = sprintf($this->lang_item('orden_cerrada', false));
			$json_respuesta = array(
				 'id' 		=> 1
				,'contenido'=> alertas_tpl('success', $msg ,false)
				,'success' 	=> true
			);
		}else{
			$msg = $this->lang_item("msg_err_clv",false);
			$json_respuesta = array(
					 'id' 		=> 0
					,'contenido'=> alertas_tpl('', $msg ,false)
					,'success' 	=> false
			);
		}
		echo json_encode($json_respuesta);
	}
	public function cancelar_orden_listado(){
		$id_compras_orden 	= $this->ajax_post('id_compras_orden');
		$sqldata= array(
					'id_compras_orden' 			   => $id_compras_orden,
					'estatus'					   => 5,
					'edit_timestamp'  	 		   => $this->timestamp(),
					'edit_id_usuario'   		   => $this->session->userdata('id_usuario')
					);
		$update = $this->db_model->db_update_data($sqldata);
		
		if($update){
			$msg = sprintf($this->lang_item('orden_cancelada', false));
			$json_respuesta = array(
				 'id' 		=> 1
				,'contenido'=> alertas_tpl('success', $msg ,false)
				,'success' 	=> true
			);
		}else{
			$msg = $this->lang_item("msg_err_clv",false);
			$json_respuesta = array(
					 'id' 		=> 0
					,'contenido'=> alertas_tpl('', $msg ,false)
					,'success' 	=> false
			);
		}
		echo json_encode($json_respuesta);
	}
	public function export_xlsx(){
		$filtro      = ($this->ajax_get('filtro')) ?  base64_decode($this->ajax_get('filtro') ): "";
		$sqlData = array('buscar' => $filtro);
		$list_content = $this->db_model->db_get_data($sqlData);		

		if($list_content){
			foreach ($list_content as $value) {
				$set_data[] = array(
									$value['id_compras_orden'],
									$value['orden_num'],
									$value['orden_tipo'],
									$value['orden_fecha'],									 
									$value['razon_social'],
									$value['descripcion'],
									$value['sucursal'],
									$value['entrega_direccion'],
									$value['entrega_fecha'],
									$value['forma_pago'],
									$value['credito'],
									$value['prefactura_num'],
									$value['observaciones'],
									$value['timestamp'],
									$value['estatus']
								);
			}
			$set_heading = array(
								$this->lang_item("ID"),
								$this->lang_item("orden_num"),
								$this->lang_item("orden_tipo"),
								$this->lang_item("orden_fecha"),
								$this->lang_item("proveedor"),
								$this->lang_item("descripcion"),
								$this->lang_item("sucursal"),
								$this->lang_item("entrega_direccion"),
								$this->lang_item("entrega_fecha"),
								$this->lang_item("forma_pago"),
								$this->lang_item("credito"),
								$this->lang_item("prefactura_num"),
								$this->lang_item("observaciones"),
								$this->lang_item("fecha_registro"),
								$this->lang_item("estatus")
							);
		}

		$params = array(	'title'   => $this->lang_item("ordenes"),
							'items'   => $set_data,
							'headers' => $set_heading
						);
		$this->excel->generate_xlsx($params);
	}
	public function show_direccion(){
		$id_sucursal = $this->ajax_post('id_sucursal');
		$sucursal= $this->sucursales_model->get_orden_unico_sucursal($id_sucursal);
		echo json_encode($sucursal[0]['direccion']);
	}
	public function export_imprimir(){
	 	// Genera PDF para impresion
		// Obtiene datos
		$id_compras_orden = $this->ajax_get('id');		
		$html = $this->articulos($id_compras_orden);
		// $html = file_get_contents("assets/tmp/pdf.html");
		// dump_var($html);
		// Crea PDF
		$arrayPDF = array(
						 'html' 	=> $html
						,'output'	=> 'I'
						,'archivo' 	=> false
						,'debug' 	=> false
					);
		// $p_inicio = 'Proceso iniciado a las: '.date('Y-m-d H:i:s');
		// ob_start();
		// if(!$pdfFile=$this->mpdf60->crear($arrayPDF)){
		// 	echo "Error al crear documento PDF.";
		// }else{
		// 	echo "Archivo Creado a las ".date('Y-m-d H:i:s').' -> '.'<a href="'.$pdfFile['uri'].'">'.$pdfFile['uri'].'</a>';
		// }
		// $respuesta = ob_get_contents();
		// ob_end_clean();
		// $pdfFile['inicio'] = $p_inicio;
		// $pdfFile['respuesta'] = $respuesta;
		// $pdfFile['fin'] = 'Proceso terminado a las: '.date('Y-m-d H:i:s');
		// // Imprime PDF
		// print_r($respuesta);
		$pdfFile=$this->mpdf60->crear($arrayPDF);
	}
}