<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class aprobar_ordenes extends Base_Controller { 
	/**
* Nombre:		Ordenes de compra
* Ubicación:	Compras>Ordenes
* Descripción:	Funcionamiento para la sección de ordenes de compra
* @author:		Alejandro Enciso
* Creación: 	2015-05-19
*/
	private $modulo;
	private $submodulo;
	private $seccion;
	private $view_content, $uri_view_principal;
	private $path;
	private $icon;
	private $offset, $limit_max;
	private $tab_inicial, $tab = array(), $tab_indice = array();
	
	public function __construct(){
		parent::__construct();
		$this->modulo 				= 'compras';
		$this->seccion 				= 'ordenes';
		$this->submodulo			= 'aprobar_ordenes';
		$this->icon 				= 'fa fa-check-square'; #Icono de modulo
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
		$this->load->model($this->modulo.'/ordenes_model','ordenes_model');
		$this->load->model('users_model','users_model');
		$this->load->model('sucursales/listado_sucursales_model','sucursales_model');
		$this->load->model('administracion/formas_de_pago_model','formas_de_pago_model');
		$this->load->model('administracion/creditos_model','creditos_model');
		$this->load->model('administracion/variables_model','variables_model');
		$this->load->model('compras/listado_precios_model','listado_precios_model');
		// Diccionario
		$this->lang->load($this->modulo.'/'.$this->seccion,"es_ES");
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
										,$this->modulo.'/'.$this->submodulo.'/'.$tab_2.$pagina
										,$tab_3
										,$tab_4
								); 
		// Accion de tabs
		$config_tab['action']   = array(
										 'load_content'
										,'load_content'
										,''
										,''
								);
		// Atributos 
		$config_tab['attr']     = array(array('style' => 'display:none'),'', array('style' => 'display:none'),array('style' => 'display:none'));
		return $config_tab;
	}
	public function index(){		
		// Carga de pagina inicial
		$tabl_inicial 			  = $this->tab_inicial;
		$view_listado    		  = $this->listado();		
		$contenidos_tab           = $view_listado;
		$data['titulo_seccion']   = $this->lang_item("aprobar_ordenes");
		$data['titulo_submodulo'] = $this->lang_item("titulo_submodulo");
		$data['icon']             = $this->icon;
		$data['tabs']             = tabbed_tpl($this->config_tabs(),base_url(),$tabl_inicial,$contenidos_tab);	
		$js['js'][]  = array('name' => $this->submodulo, 'dirname' => $this->modulo);
		$this->load_view($this->uri_view_principal, $data, $js);
	}
	public function listado($offset=0){
		// Crea tabla con listado de ordenes aprobadas 
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
				$btn_acciones['agregar'] 		= '<span id="ico-articulos_'.$accion_id.'" class="ico_detalle fa fa-search-plus" onclick="articulos('.$accion_id.')" title="'.$this->lang_item("agregar_articulos").'"></span>';
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
		$detalle  			= $this->ordenes_model->get_orden_unico($id_compras_orden);
		$btn_save       	= form_button(array('class'=>"btn btn-primary",'name' => 'actualizar' , 'onclick'=>'actualizar()','content' => $this->lang_item("btn_guardar") ));
		$btn_eliminar       = form_button(array('class'=>"btn btn-primary",'name' => 'eliminar' , 'onclick'=>'eliminar()','content' => $this->lang_item("btn_eliminar") ));
		//se agrega para mostrar la opcion de proveedor y No. prefactura, solo si se selcciono proveedor en tipo de orden
		if($detalle[0]['id_orden_tipo']==2){
			$style='style="display:none"';
			$class ='';
		}else{
			$style='';
			$class ='requerido';
		}	
		$dropArray = array(
					'data'		=> $this->ordenes_model->db_get_proveedores()
					,'selected' => $detalle[0]['id_proveedor'] 
					,'value' 	=> 'id_compras_proveedor'
					,'text' 	=> array('clave_corta','razon_social')
					,'name' 	=> "id_proveedor"
					,'class' 	=> $class
					,'disabled' => 'disabled="disabled"'
				);
		$proveedores    = dropdown_tpl($dropArray);

		$dropArray2 = array(
					 'data'		=> $this->sucursales_model->db_get_data()
					 ,'selected'=> $detalle[0]['id_sucursal']
					,'value' 	=> 'id_sucursal'
					,'text' 	=> array('clave_corta','sucursal')
					,'name' 	=> "id_sucursal"
					,'class' 	=> "requerido"
					,'disabled' => 'disabled="disabled"'
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
					,'disabled' => 'disabled="disabled"'
				);
		$forma_pago	    = dropdown_tpl($dropArray3);

		$dropArray4 = array(
					 'data'		=> $this->creditos_model->db_get_data()
					 ,'selected'=> $detalle[0]['id_credito']
					,'value' 	=> 'id_administracion_creditos'
					,'text' 	=> array('clave_corta','credito')
					,'name' 	=> "id_administracion_creditos"
					,'class' 	=> "requerido"
					,'disabled' => 'disabled="disabled"'
				);
		$creditos	    = dropdown_tpl($dropArray4);

		$dropArray5 = array(
					 'data'		=> $this->ordenes_model->db_get_tipo_orden()
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
		// 

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
        $tabData['button_save']       		 = $btn_save;
        $tabData['button_delete']       	 = $btn_eliminar;
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
		$tabData['registro_por']  			 = $this->lang_item("registro_por",false);

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
	public function articulos_listado(){
		// Agregar articulos a una orden de compra
		$table 				= '';
		$accion 			= $this->tab['articulos'];
		$id_compras_orden 	= $this->ajax_post('id_compras_orden');
		$detalle  			= $this->ordenes_model->get_orden_unico($id_compras_orden);
		$btn_aprobar       	= form_button(array('class'=>"btn btn-primary",'name' => 'save' , 'onclick'=>'aprobar_orden_listado()','content' => $this->lang_item("btn_aprobar") ));
		$btn_canceled       = form_button(array('class'=>"btn btn-primary",'name' => 'canceled' , 'onclick'=>'rechazar_orden_listado()','content' => $this->lang_item("btn_rechazar") ));
		//se agrega para mostrar la opcion de proveedor y No. prefactura, solo si se selcciono proveedor en tipo de orden
		if($detalle[0]['id_orden_tipo']==2){
			$style='style="display:none"';
			$class ='';
		}else{
			$style='';
			$class ='requerido';
		}	
		$data_sql = array('id_compras_orden'=>$id_compras_orden);
		$data_listado=$this->ordenes_model->db_get_data_orden_listado_registrado($data_sql);
		$moneda = $this->session->userdata('moneda');
		if(count($data_listado)>0){
				$style_table='display:block';
			for($i=0;count($data_listado)>$i;$i++){
			
			$peso_unitario = (substr($data_listado[$i]['peso_unitario'], strpos($data_listado[$i]['peso_unitario'], "." ))=='.000')?number_format($data_listado[$i]['peso_unitario'],0):$data_listado[$i]['peso_unitario'];
			$presentacion_x_embalaje = (substr($data_listado[$i]['presentacion_x_embalaje'], strpos($data_listado[$i]['presentacion_x_embalaje'], "." ))=='.000')?number_format($data_listado[$i]['presentacion_x_embalaje'],0):$data_listado[$i]['presentacion_x_embalaje'];
			$embalaje = ($data_listado[$i]['embalaje'])?$data_listado[$i]['embalaje'].' CON ':'';
			$table.='<tr id="'.$data_listado[$i]['id_compras_articulo_precios'].'">
						<td class="center">
							<span name="consecutivo">'.($i+1).'</span>
						</td>
						<td>
							<span name="proveedor">'.$data_listado[$i]['nombre_comercial'].'</span>
						</td>
						<td>
							<span name="articulo">'.$data_listado[$i]['articulo'].' - '.$peso_unitario.' '.$data_listado[$i]['cl_um'].'<br/>'.$data_listado[$i]['upc'].'</span>
						</td>
						<td>
							'.$embalaje.$presentacion_x_embalaje.' '.$data_listado[$i]['presentacion'].'
						</td>
						<td class="right">
							<span class="add-on">'.$moneda.'</span> '.number_format($data_listado[$i]['costo_sin_impuesto'],2).'
						</td>
						<td class="right">
							'.$data_listado[$i]['cantidad'].' <span class="add-on">Pz</span>
						</td>
						<td class="right">
							<span class="add-on">'.$moneda.'</span> 
							'.number_format($data_listado[$i]['costo_x_cantidad'],2).'
						</td>
						<td class="right">
                              '.$data_listado[$i]['descuento'].' <span class="add-on">%</span>
						</td>
						<td class="right">
                              <span class="add-on">'.$moneda.'</span> 
                              '.number_format($data_listado[$i]['subtotal'],2).'
						</td>
						<td class="right">
							'.number_format($data_listado[$i]['impuesto_porcentaje'],0).'
							<span class="add-on">%</span>
						</td>
						<td class="right">
							<span class="add-on">'.$moneda.'</span> 
							'.number_format($data_listado[$i]['valor_impuesto'],2).'
						</td>
						<td class="right">
							<span class="add-on">'.$moneda.'</span> 
							'.number_format($data_listado[$i]['total'],2).'</span>
						</td>
					</tr>';
			}
		}
		else{
			$style_table='display:none';
			$table='';
		}

		$data='';
		$proveedores    = $this->ordenes_model->db_get_proveedores($data,$detalle[0]['id_proveedor']);
		$sucursales	    = $this->sucursales_model->get_orden_unico_sucursal($detalle[0]['id_sucursal']);
		$forma_pago	    = $this->formas_de_pago_model->get_orden_unico_formapago($detalle[0]['id_forma_pago']);
		$creditos	    = $this->creditos_model->get_orden_unico_credito($detalle[0]['id_credito']);
		$orden_tipo	    = $this->ordenes_model->db_get_tipo_orden($detalle[0]['id_orden_tipo']);
		
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
		$tabData['proveedor']  			 	 = $this->lang_item("proveedor",false);
		$tabData['articulo']  			 	 = $this->lang_item("articulo",false);
		$tabData['clave_corta']  			 = $this->lang_item("clave_corta",false);
		$tabData['Costo']  			 		 = $this->lang_item("Costo",false);
		$tabData['unitario']  			 	 = $this->lang_item("unitario",false);
		$tabData['cantidad']  			 	 = $this->lang_item("cantidad",false);
		$tabData['costo_cantidad']  	     = $this->lang_item("costo_cantidad",false);
		$tabData['descuento']  			 	 = $this->lang_item("descuento",false);
		$tabData['subtotal']  			 	 = $this->lang_item("subtotal",false);
		$tabData['imp']  			 		 = $this->lang_item("imp",false);
		$tabData['valor_imp']  			 	 = $this->lang_item("valor_imp",false);
		$tabData['total']  			 		 = $this->lang_item("total",false);
		$tabData['accion']  				 = $this->lang_item("accion",false);
		$tabData['aprobar_orden']  		 	 = $this->lang_item("aprobar_orden",false);
		$tabData['rechazar_orden']			 = $this->lang_item("rechazar_orden",false);
		$tabData['subtotal']  				 = $this->lang_item("subtotal",false);
		$tabData['impuesto']  				 = $this->lang_item("impuesto",false);
		$tabData['a_pagar']  				 = $this->lang_item("a_pagar",false);
		$tabData['costo_unitario']	 		 = $this->lang_item("costo_unitario",false);
		$tabData['presentacion']			 = $this->lang_item("presentacion",false);
		$tabData['estatus']			 		 = $this->lang_item("estatus",false);
		$tabData['consecutivo']				 = $this->lang_item("consecutivo",false);
		//DATA
		$tabData['orden_num_value']	 		 = $detalle[0]['orden_num'];
		$tabData['list_proveedores']		 = $proveedores[0]['razon_social'];
		$tabData['list_sucursales']			 = $sucursales[0]['sucursal'];
		$tabData['descripcion_value'] 		 = $detalle[0]['descripcion'];
		$tabData['timestamp']         		 = $detalle[0]['timestamp'];
		$tabData['btn_aprobar']       		 = $btn_aprobar;
		$tabData['btn_canceled']       		 = $btn_canceled;
		$tabData['orden_fecha_value']	 	 = $orden_fecha;
		$tabData['entrega_direccion_value']	 = $detalle[0]['entrega_direccion'];
		$tabData['entrega_fecha_value']	     = $entrega_fecha;
		$tabData['prefactura_num_value'] 	 = $detalle[0]['prefactura_num'];
		$tabData['observaciones_value']      = $detalle[0]['observaciones'];
		$tabData['list_forma_pago']			 = $forma_pago[0]['forma_pago'];
		$tabData['list_creditos']			 = $creditos[0]['credito'];
		$tabData['list_orden_tipo']			 = $orden_tipo[0]['descripcion'];
		$tabData['style']					 = $style;
		$tabData['class']					 = $class;
		$tabData['table']					 = $table;
		$tabData['style_table']				 = $style_table;
		$tabData['lbl_ultima_modificacion']  = $this->lang_item('lbl_ultima_modificacion', false);
		$tabData['estatus_value']  			 = $detalle[0]['estatus'].' - '.$detalle[0]['edit_timestamp'];
		$tabData['fecha_hoy']				 = $this->lang_item('impreso_el', false).': '.date('Y-m-d H:i:s');
		// Totales
		$tabData['subtotal_value']			 = $moneda.' '.number_format($detalle[0]['subtotal'],2);
		$tabData['descuento_value']			 = $moneda.' '.number_format($detalle[0]['descuento'],2);
		$tabData['impuesto_value']			 = $moneda.' '.number_format($detalle[0]['impuesto'],2);
		$tabData['total_value']				 = $moneda.' '.number_format($detalle[0]['total'],2);

		$uri_view  = $this->path.$this->submodulo.'_'.$accion;
		echo json_encode( $this->load_view_unique($uri_view ,$tabData, true));
	}
	public function aprobar_orden_listado(){
		$id_compras_orden 	= $this->ajax_post('id_compras_orden');
		$sqldata= array(
					'id_compras_orden' 			   =>$id_compras_orden,
					'estatus' 					   => 2,
					'edit_timestamp'  	 		   => $this->timestamp(),
					'edit_id_usuario'   		   => $this->session->userdata('id_usuario')
					);
		$update = $this->ordenes_model->db_update_data($sqldata);
		if($update){
			$msg = sprintf($this->lang_item('orden_aprobada', false));
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
	public function rechazar_orden_listado(){
		$id_compras_orden 	= $this->ajax_post('id_compras_orden');
		$sqldata= array(
					'id_compras_orden' 			   =>$id_compras_orden,
					'estatus' 					   => 3,
					'edit_timestamp'  	 		   => $this->timestamp(),
					'edit_id_usuario'   		   => $this->session->userdata('id_usuario')
					);
		$update = $this->ordenes_model->db_update_data($sqldata);
		if($update){
			$msg = sprintf($this->lang_item('orden_rechazada', false));
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

		$params = array(	'title'   => $this->lang_item("aprobar_ordenes"),
							'items'   => $set_data,
							'headers' => $set_heading
						);
		$this->excel->generate_xlsx($params);
	}
}