<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ventas_punto_venta extends Base_Controller{
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
		$this->modulo 			= 'ventas';
		//$this->submodulo		= 'catalogos';
		$this->seccion          = 'ventas_punto_venta';
		$this->icon 			= 'iconfa-dashboard'; 
		$this->path 			= $this->modulo.'/'.$this->seccion.'/'; 
		$this->view_content 	= 'content';
		$this->limit_max		= 10;
		$this->offset			= 0;
		// Tabs
		$this->tab1 			= 'build_form_filtro';
		$this->tab2 			= 'detalle_venta';
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
										 $this->lang_item($tab_1) #listado_punto_venta
										,$this->lang_item($tab_2) #listado_venta
								); 
		// Href de tabs
		$config_tab['links']    = array(
										 $path.$tab_1             #ventas/ventas_punto_venta/listado_punto_venta
										,$path.$tab_2
								); 
		// Accion de tabs
		$config_tab['action']   = array(
									'load_content'
								   ,'' 
								);
		// Atributos 
		$config_tab['attr']     = array('',array('style' => 'display:none'));
		$config_tab['style_content'] = array('','');

		return $config_tab;
	}

	private function uri_view_principal(){
		return $this->modulo.'/'.$this->view_content;
	}

	public function index(){
		$tabl_inicial 			  = 1;
		$view_listado    		  = $this->build_form_filtro();	
		$contenidos_tab           = $view_listado;
		$data['titulo_seccion']   = $this->lang_item($this->seccion);
		$data['titulo_submodulo'] = $this->lang_item("titulo_submodulo");
		$data['icon']             = $this->icon;
		$data['tabs']             = tabbed_tpl($this->config_tabs(),base_url(),$tabl_inicial,$contenidos_tab);	
		
		$js['js'][]  = array('name' => $this->seccion, 'dirname' => $this->modulo);
		$this->load_view($this->uri_view_principal(), $data, $js);
	}

	public function build_form_filtro(){
		$seccion = '/buscar_punto_venta';
		$uri_view 		    = $this->path.$seccion;
		$dropdown_sucursales = array(
						 'data'		=> $this->sucursales->db_get_data($sqlData)
						,'value' 	=> 'id_sucursal'
						,'text' 	=> array('sucursal')
						,'name' 	=> "lts_sucursales"
						,'leyenda' 	=> "Todas las sucursales"
						,'class' 	=> "requerido"
						,'event'    => array('event'      => 'onchange', 
											'function'    => 'load_punto_venta', 
											'params'      => array('this.value'), 
											'params_type' => array(0))
					);
		$sucursales = dropdown_tpl($dropdown_sucursales);

		$dropdown_punto_venta = array(
						 'name' 	=> "listado_punto_venta"
						,'leyenda' 	=> 'Todos los puntos de venta'
						,'class' 	=> "requerido"
						);
		$puntos_venta = dropdown_tpl($dropdown_punto_venta);

		$btn_search  = form_button(array('class'=>'btn btn-primary', 'name'=>'buscar_ventas', 'onclick'=>'buscar_ventas()','content'=>$this->lang_item("btn_buscar_ventas")));
		$btn_reset   = form_button(array('class'=>'btn btn_primary', 'name'=>'reset','onclick'=>'clean_formulario()','content'=>$this->lang_item('btn_limpiar')));

		$tabData['lbl_puntos_venta']  = $this->lang_item('lbl_puntos_venta');
		$tabData['puntos_venta_list'] = $puntos_venta;
		$tabData['lbl_sucursal']      = $this->lang_item('lbl_sucursal');
		$tabData['lbl_fecha_inicio']  = $this->lang_item('lbl_fecha_inicio');
		$tabData['lbl_fecha_final']   = $this->lang_item('lbl_fecha_final');
		$tabData['sucursales_list']   = $sucursales;
		$tabData['button_search']     = $btn_search;
		$tabData['button_reset']      = $btn_reset;
		$tabData['hoy']               = date('d/m/Y');
		if($this->ajax_post(false)){
			echo json_encode( $this->load_view_unique($uri_view , $tabData, true));
		}else{
			return $this->load_view_unique($uri_view , $tabData, true);
		}
	}

	public function load_punto_venta(){
		$id_sucursal = $this->ajax_post('id');
		$dropdown_punto_venta = array(
			 			 'data'		=> $this->db_model->db_get_punto_venta_by_sucursal($id_sucursal)
						,'value' 	=> 'id_sucursales_punto_venta'
						,'text' 	=> array('clave_corta','punto_venta')
						,'name' 	=> "listado_punto_venta"
						,'leyenda' 	=> 'Todos los puntos de venta'
						);
		$puntos_venta = dropdown_tpl($dropdown_punto_venta);
		echo json_encode($puntos_venta);
	}

	public function buscar_ventas($offset = 0){
		$objData = $this->ajax_post('objData');
		//print_debug(implode(',',$objData));
		$limit 	 = $this->limit_max;
		$sqlData = array(
						  'sucursal'     => ($objData['lts_sucursales'] != 0)?$objData['lts_sucursales']:''
						 ,'punto_venta'  => (isset($objData['listado_punto_venta']))?$objData['listado_punto_venta']:''
						 ,'fecha_inicio' => (!empty($objData['fecha_inicio']))?$objData['fecha_inicio']:date("d/m/Y")
						 ,'fecha_final'  => (!empty($objData['fecha_final'] ))?$objData['fecha_final']:''
						 ,'rango'        => (!empty($objData['fecha_inicio']) && !empty($objData['fecha_final']))?true:false
						 ,'offset' 		 => $offset
			             ,'limit'        => $limit
			            );
		$uri_segment  = $this->uri_segment();
		$total_rows	  = count($this->db_model->db_get_all_sucursal($sqlData));
		$sqlData['aplicar_limit'] = true;
		$list_content = $this->db_model->db_get_all_sucursal($sqlData);
		$url         = base_url().'ventas/ventas_punto_venta/paginar';
		$paginador    = $this->pagination_bootstrap->paginator_generate($total_rows, $url, $limit, $uri_segment, array('evento_link' => 'onclick', 'function_js' => 'load_content', 'params_js'=>'1'));
		if($total_rows){
			
			foreach ($list_content as $value){
				$btn_acciones['detalle'] 		= '<span id="ico-detalle_'.$value['id_venta'].'" class="ico_acciones ico_detalle fa fa-search-plus" onclick="detalle('.$value['id_venta'].')" title="'.$this->lang_item("detalle").'"></span>';
				$btn_acciones['comprobante']    = '<span style="color:green; cursor: pointer;" id="ico-comprobante_'.$value['id_venta'].'" class="ico_ver_comprobante iconfa-ok" onclick="ver_comprobante('.$value['id_venta'].')" title="'.$this->lang_item("lbl_ver_comprobante").'"></span>';
				$acciones = implode('&nbsp;&nbsp;&nbsp;',$btn_acciones);
				
				$tbl_data[] = array('id'            => $value['id_venta'],
									'ticket'        => $value['consecutivo'],
									'fecha_venta'   => $value['fecha_venta'],
									'esquema'       => $value['esquema'],
									'subtotal'      => $this->session->userdata('moneda').' '.$value['monto_subtotal'],
									'descuento'     => ($value['monto_descuento'] == 0)?$this->lang_item("lbl_no_aplica"):$this->session->userdata('moneda').' '.$value['monto_descuento'],
									'total'         => $this->session->userdata('moneda').' '.$value['monto_total'],
									'efectivo'      => $this->session->userdata('moneda').' '.$value['efectivo'],
									'tarjeta'       => str_replace('|',',',$value['tarjeta']),
									'banco'         => str_replace('|',',',$value['banco']),
									'cambio'        => $this->session->userdata('moneda').' '.$value['cambio'],
									'acciones'      => $acciones
									);
			}

			// Plantilla
			$tbl_plantilla = set_table_tpl();
			// Titulos de tabla
			$this->table->set_heading(	$this->lang_item("ID"),
										$this->lang_item("lbl_ticket"),
										$this->lang_item("lbl_fecha_venta"),
										$this->lang_item("lbl_esquema"),
										$this->lang_item("lbl_subtotal"),
										$this->lang_item("lbl_descuento"),
										$this->lang_item("lbl_total"),
										$this->lang_item("lbl_efectivo"),
										$this->lang_item("lbl_tarjeta"),
										$this->lang_item("lbl_banco"),
										$this->lang_item("lbl_cambio"),
										$this->lang_item("lbl_acciones"));
			// Generar tabla
			$this->table->set_template($tbl_plantilla);
			$tabla = $this->table->generate($tbl_data);

			$cadena = implode(',',$objData);
			//print_debug($filtro);
			$buttonTPL = array( 'text'       => $this->lang_item("btn_xlsx"), 
								'iconsweets' => 'fa fa-file-excel-o',
								'href'       => base_url($this->path.'export_xlsx?cadena='.base64_encode($cadena))
								);
		}else{
			$buttonTPL = "";
			$msg   = $this->lang_item("msg_query_null");
			$tabla = alertas_tpl('', $msg ,false);
		}
		$tabData['export']    = button_tpl($buttonTPL);
		$tabData['tabla']     = $tabla;
		$tabData['paginador'] = $paginador;
		$tabData['item_info'] = $this->pagination_bootstrap->showing_items($limit, $offset, $total_rows);
		echo json_encode($tabData);
	}

	public function detalle_venta(){
		$id_venta = $this->ajax_post('id');
		$seccion  = '/ventas_detalle';
		$uri_view = $this->path.$seccion;
		$venta = $this->db_model->db_get_detalle_venta($id_venta);

		foreach ($venta as $key => $value) {
			$descripcion[]         = $value['descripcion'];
			$total[]               = $value['total'];
		}
		$total                    = '$ '.implode('<br>$ ', $total);
		$descripcion              = implode('<br>', $descripcion);
			$info_sale = array(	
					             'lbl_descripcion'				=> '<strong>'.$this->lang_item("lbl_descripcion").'</strong>'
					            ,'lbl_total'				    => '<strong>'.$this->lang_item("lbl_total").'</strong>'
					            ,'descripcion'                  => $descripcion
					            ,'total'                        => $total
					            ,'lbl_subtotal'                 => $this->lang_item("lbl_subtotal")
					            ,'monto_subtotal'               => '<strong>$ '.$venta[0]['monto_subtotal'].'</strong>'
					            ,'lbl_efectivo'				    => '<strong>'.$this->lang_item("lbl_efectivo").'</strong>'
					            ,'efectivo'                     => '$ '.$venta[0]['efectivo']
					            ,'lbl_cambio'				    => '<strong>'.$this->lang_item("lbl_cambio").'</strong>'
					            ,'cambio'                       => '$ '.$venta[0]['cambio']
					            ,'lbl_monto_descuento'	        => '<strong>'.$this->lang_item("lbl_monto_descuento").'</strong>'
					            ,'monto_descuento'              => ($venta[0]['monto_descuento'] == 0)?$this->lang_item("lbl_no_aplica"):'$ '.$venta[0]['monto_descuento']
					            ,'lbl_impuestos'	            => '<strong>'.$this->lang_item("lbl_impuestos").'</strong>'
					            ,'monto_impuestos'              => ($venta[0]['monto_impuestos'] == 0)?$this->lang_item("lbl_no_aplica"):'$ '.$venta[0]['monto_impuestos']
					            ,'lbl_subrogacion'	            => '<strong>'.$this->lang_item("lbl_subrogacion").'</strong>'
					            ,'monto_subrrogacion'           => ($venta[0]['monto_subrrogacion'] == 0)?$this->lang_item("lbl_no_aplica"):'$ '.$venta[0]['monto_subrrogacion']
					            ,'lbl_monto_total'              => $this->lang_item("lbl_monto_total")
					            ,'monto_total'                  => '<strong>$ '.$venta[0]['monto_total'].'</strong>'
								);

		$info_sale2 = array(	
							'lbl_ticket'                    => $this->lang_item("lbl_ticket")
				            ,'ticket'                       => $venta[0]['id_venta']
				            ,'lbl_fecha_venta'              => $this->lang_item("lbl_fecha_venta")
				            ,'timestamp'                    => $venta[0]['timestamp']
				            ,'lbl_punto_venta'              => $this->lang_item("lbl_punto_venta")
				            ,'punto_venta'                  => $venta[0]['cv_punto_venta'].' '.$venta[0]['punto_venta']
				            ,'lbl_esquema'                  => $this->lang_item("lbl_esquema")
				            ,'esquema'                      => $venta[0]['esquema']
				            ,'lbl_tarjeta'                  => $this->lang_item("lbl_tarjeta")
				            ,'tarjeta'                      => ($venta[0]['tarjeta'] == 0)?$this->lang_item("lbl_no_aplica"):$venta[0]['tarjeta']
				            ,'lbl_banco'                    => $this->lang_item("lbl_banco")
				            ,'banco'                        => ($venta[0]['tarjeta'] == 0)?$this->lang_item("lbl_no_aplica"):$venta[0]['banco']
				            ,'lbl_cliente'                  => $this->lang_item("lbl_cliente")
				            ,'nombre_cliente'               => ($venta[0]['esquema'] == 'retail')?$this->lang_item("lbl_no_aplica"):$venta[0]['nombre_cliente']
				            ,'lbl_ver_comprobante'          => $this->lang_item("lbl_ver_comprobante")
				            ,'comprobante'                  => '<span style="color:green; cursor: pointer;" id="ico-comprobante_'.$venta[0]['id_venta_local'].'" class="ico_ver_comprobante iconfa-ok" onclick="ver_comprobante('.$venta[0]['id_venta_local'].')" title="'.$this->lang_item("lbl_ver_comprobante").'"></span>'
							);

		
		$plantilla_column = array ( 'table_open'  => '<table   class="table table-bordered table-invoice">' );
		$this->table->set_template($plantilla_column);
		$info_sale = $this->table->make_columns($info_sale, 2);
		$table_info_venta = $this->table->generate($info_sale);

		$info_sale2 = $this->table->make_columns($info_sale2, 2);
		$table_info_venta2 = $this->table->generate($info_sale2);

		$tabData['url_image']   = base_url().'assets/images/logo.bmp';
		$tabData['tabla']       = $table_info_venta;
		$tabData['tabla2']      = $table_info_venta2;
		echo json_encode( $this->load_view_unique($uri_view , $tabData, true));
	}

	public function load_ticket(){
		$id_venta = $this->ajax_post('id');
		$venta = $this->db_model->db_get_detalle_venta($id_venta);
		$url_image   = base_url().'assets/images/logo.bmp';
		$content = '';
		$content.= '<div class="span5 papel">';
		$content.= '<center><img  src="'.$url_image.'" title="Admin Control"></center><br>';
		$content.= '<pre>'.$venta[0]['comprobante'].'</pre>';
		$content.= '</div>';
		echo json_encode( $content);
	}

	public function export_xlsx($offset=0){
		$cadena      = ($this->ajax_get('cadena')) ?  base64_decode($this->ajax_get('cadena')): "s";
		$arrayFiltro = list($sucursal, $punto_venta, $del, $al) = explode(',',$cadena);
		$arraySize   = count($arrayFiltro);
		if($arraySize == 9){
			$arrayElements = list($id_sucursal,$id_punto_venta,$del,$al) = explode(',',$cadena);
			$arraySql = array('sucursal'     => $id_sucursal
				             ,'punto_venta'  => $id_punto_venta
				             ,'fecha_inicio' => $del
				             ,'fecha_final'  => $al
				             ,'rango'        => ($al == '')?false:true
				             );
		} 

		if($arraySize == 7){
			$arrayElements = list($id_sucursal,$del,$al) = explode(',',$cadena);
			$arraySql = array('sucursal'      => $id_sucursal
				             ,'fecha_inicio'  => $del
				             ,'fecha_final'   => $al
				             ,'rango'         => ($al == '')?false:true
				             );
		}
		$venta = $this->db_model->db_get_ventas($arraySql);
		if(count($venta) > 0){
			foreach ($venta as $value){
				$set_data[] = array(
									 $value['id_venta'],
									 $value['timestamp'],
									 $value['punto_venta'],
									 $value['esquema'],
									 $value['monto_subtotal'],
									 $value['monto_descuento'],
									 $value['venta_impuestos_monto'],
									 $value['venta_subrrogacion_monto'],
									 $value['monto_total'],
									 $value['cliente_nom'],
									 $value['descripcion'],
									 $value['precio'],
									 $value['cantidad'],
									 $value['cambio'],
									 $value['efectivo'],
									 $value['tarjeta'],
									 $value['banco'],
									 );
			}

			$set_heading = array(
									$this->lang_item("lbl_ticket"),
									$this->lang_item("lbl_fecha_venta"),
									$this->lang_item("lbl_punto_venta"),
									$this->lang_item("lbl_esquema"),
									$this->lang_item("lbl_subtotal"),
									$this->lang_item("lbl_descuento"),
									$this->lang_item("lbl_impuestos"),
									$this->lang_item("lbl_subrogacion"),
									$this->lang_item("lbl_monto_total"),
									$this->lang_item("lbl_cliente"),
									$this->lang_item("lbl_descripcion"),
									$this->lang_item("lbl_precio"),
									$this->lang_item("lbl_cantidad"),
									$this->lang_item("lbl_cambio"),
									$this->lang_item("lbl_efectivo"),
									$this->lang_item("lbl_tarjeta"),
									$this->lang_item("lbl_banco")
									);
		}

		$params = array(	'title'   => $this->lang_item("lbl_excel"),
							'items'   => $set_data,
							'headers' => $set_heading
						);
		$this->excel->generate_xlsx($params);
	}
}