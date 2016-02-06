<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class historial_ajuste extends Base_Controller {
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
		$this->vars = new config_vars();
        $this->vars->load_vars();
		$this->modulo 			= 'almacen';
		$this->submodulo        = 'historial_ajuste';
		$this->seccion          = 'ajustes';
		$this->icon 			= 'fa fa-list'; //Icono de modulo
		$this->path 			= $this->modulo.'/'.$this->submodulo.'/'; //almacen/entradas_recepcion/
		$this->view_content 	= 'content';
		$this->limit_max		= 10;
		$this->offset			= 0;
		// Tabs
		$this->tab1 			= 'agregar';
		$this->tab2 			= 'listado';
		$this->tab3 			= 'detalle';
		// DB Model
		$this->load->model($this->modulo.'/'.$this->seccion.'_model','db_model');		
		$this->load->model($this->modulo.'/catalogos_model','catalogos_model');
		$this->load->model('stock_model','stock_model');

		// Diccionario
		$this->lang->load($this->modulo.'/'.$this->seccion,"es_ES");
		// Tabs
		$this->tab_inicial 		= 2;
		$this->tab_indice 		= array(
									 $this->tab1
									,$this->tab2
									,$this->tab3
								);
		for($i=0; $i<=count($this->tab_indice)-1; $i++){
			$this->tab[$this->tab_indice[$i]] = $this->tab_indice[$i];
		}
	}
	public function config_tabs(){
		$tab_1 	= $this->tab1;
		$tab_2 	= $this->tab2;
		$tab_3 	= $this->tab3;
		$path  	= $this->path;
		$pagina =(is_numeric($this->uri_segment_end()) ? $this->uri_segment_end() : "");
		// Nombre de Tabs
		$config_tab['names']    = array(
										 $this->lang_item($tab_1) //agregar
										,$this->lang_item($tab_2) //LISTADO
										,$this->lang_item($tab_3) //detalle
								); 
		// Href de tabs
		$config_tab['links']    = array(
										 $path.$tab_1            //detalle
										,$path.$tab_2.'/'.$pagina //almacen/ajuste/listado/pagina
										,$path.$tab_3            //detalle
								); 
		// Accion de tabs
		$config_tab['action']   = array(
										 ''
										,'load_content'
										,''
								);
		// Atributos 
		$config_tab['attr']     = array(array('style' => 'display:none'), '', array('style' => 'display:none'));
		return $config_tab;
	}
	private function uri_view_principal(){
		return $this->modulo.'/'.$this->view_content; //compras/content
	}
	public function index(){
		$tabl_inicial 			  = 2;
		$view_listado    		  = $this->listado();
		$contenidos_tab           = $view_listado;
		$data['titulo_seccion']   = $this->lang_item($this->submodulo);
		$data['titulo_submodulo'] = $this->lang_item($this->seccion);
		$data['icon']             = $this->icon;
		$data['tabs']             = tabbed_tpl($this->config_tabs(),base_url(),$tabl_inicial,$contenidos_tab);
		
		$js['js'][]  = array('name' => $this->submodulo, 'dirname' => $this->modulo);
		$this->load_view($this->uri_view_principal(), $data, $js);
	}
	public function listado($offset=0){
		// Crea tabla con listado de ordenes aprobadas
		$accion 		= $this->tab['listado'];
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
		$total_rows   			  = count($this->db_model->get_data_historial($sqlData));
		$sqlData['aplicar_limit'] = false;
		$list_content 			  = $this->db_model->get_data_historial($sqlData);
		$url          			  = base_url($url_link);
		$paginador    			  = $this->pagination_bootstrap->paginator_generate($total_rows, $url, $limit, $uri_segment, array('evento_link' => 'onclick', 'function_js' => 'load_content', 'params_js'=>'1'));
		//dump_var($list_content);
		if($total_rows){
			foreach ($list_content as $value) {
				// Acciones
				$accion_id 						= $value['id_almacen_ajuste'];
				$btn_acciones['agregar'] 		= '<span id="ico-articulos_'.$accion_id.'" class="ico_detalle fa fa-search-plus" onclick="detalle('.$accion_id.')" title="'.$this->lang_item("agregar_articulos").'"></span>';
				if($value['id_estatus']==2){
					$acciones = implode('&nbsp;&nbsp;&nbsp;',$btn_acciones);
				}else{
					$acciones='';
				}
				($value['id_articulo_tipo']==2)?$etiqueta=$value['cl_um']:$etiqueta=$this->lang_item("pieza_abrev");
				// Datos para tabla
				$tbl_data[] = array('id'             		=> $value['id_almacen_ajuste'],
									'articulo'  	 		=> $value['articulo'],
									'presentacion_detalle'  => $value['presentacion_detalle'],
									'stock_mov'   	 		=> $value['stock_mov'].'-'.$etiqueta,
									'stock_um_mov'   		=> $value['stock_um_mov'].'-'.$value['cl_um'],
									'estatus'  	 	    	=> $value['estatus'],
									'timestamp'  	 		=> $value['timestamp'],
									'acciones' 		 		=> $acciones
									);
			}

			// Plantilla
			$tbl_plantilla = set_table_tpl();
			// Titulos de tabla
			$this->table->set_heading(	$this->lang_item("id_almacen_ajuste"),
										$this->lang_item("articulo"),
										$this->lang_item("presentacion"),
										$this->lang_item("stock_afec"),
										$this->lang_item("stock_um_afec"),
										$this->lang_item("estatus"),
										$this->lang_item("fecha_registro"),
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
		$id_almacen_ajuste = $this->ajax_post('id_almacen_ajuste');
		$view 			   = $this->tab['detalle'];
		$uri_view  		   = $this->modulo.'/'.$this->seccion.'/'.$this->submodulo.'/'.$view;
		$detalle  		   = $this->db_model->get_data_unico_x_historial($id_almacen_ajuste);
		$id_articulo   				  =   $detalle[0]['id_articulo'];
		$id_almacen    				  =  ($detalle[0]['id_almacen']!=0)?$detalle[0]['id_almacen']:'';
		$id_pasillo    				  =  ($detalle[0]['id_pasillo']!=0)?$detalle[0]['id_pasillo']:'';
		$id_gaveta     				  =  ($detalle[0]['id_gaveta']!=0)?$detalle[0]['id_gaveta']:'';
		$stock_mov     				  =   $detalle[0]['stock_mov'];
		$stock_um_mov  				  =   $detalle[0]['stock_um_mov'];
		$id_compras_articulo_precios  =   $detalle[0]['id_compras_articulo_precios'];
		
		$sqlData=array(
					'id_almacen'  					=> $id_almacen,
					'id_pasillo'  					=> $id_pasillo,
					'id_gaveta'   					=> $id_gaveta,
					'id_articulo' 					=> $id_articulo,
					'id_compras_articulo_precios'   => $id_compras_articulo_precios,
					'id_almacen_ajuste' 			=> $id_almacen_ajuste
				);
		$articulo_detalle = $this->db_model->get_data_stock_logs($sqlData);
		$total_rows   			  = count($this->db_model->get_data_stock_logs($sqlData));
		if($total_rows){
			foreach ($articulo_detalle as $value) {
				($value['id_articulo_tipo']==2)?$etiqueta=$value['clave_corta']:$etiqueta=$this->lang_item("pieza_abrev");
				$tbl_data[] = array( 	
										'orgen'   => '<th>'.$this->lang_item("origen").'</th><th>'.$this->lang_item("existencia").'</th>',
								);
				$tbl_data[] = array( 	'id'            	=> $value['pasillos'],
										'pasillos_ori'   	=> $value['pasillos'],
										'pasillos_des'   	=> $value['pasillos'],
								);
				$tbl_data[] = array( 	'id'            	=> $value['gavetas'],
										'gavetas_ori'   	=> $value['gavetas'],
										'gavetas_de'   		=> $value['gavetas'],
								);
				$tbl_data[] = array( 	'id'            	=> $value['log_stock_origen'],
										'stock_origen'   	=> $value['log_stock_origen'].' '.$etiqueta,
										'stock'   	 		=> $value['log_stock_destino'].' '.$etiqueta,
								);
				$tbl_data[] = array( 	'id'            	=> $value['log_stock_um_origen'],
										'stock_um_origen'   => $value['log_stock_um_origen'].' '.$value['clave_corta'],
										'stock_um'   		=> $value['log_stock_um_destino'].' '.$value['clave_corta']
								);
			}

		}
		//dump_var($tbl_data);
		($detalle[0]['id_articulo_tipo']==2)?$etiqueta=$articulo_detalle[0]['clave_corta']:$etiqueta=$this->lang_item("pieza_abrev");

		$tabData['articulo']		= 	$detalle[0]['articulo'];
		$tabData['cl_almacen']		= 	$detalle[0]['cl_almacen'];
		$tabData['cl_gaveta']		= 	$detalle[0]['cl_gaveta'];
		$tabData['cl_pasillo']		= 	$detalle[0]['cl_pasillo'];
		$tabData['stock_mov']		= 	$detalle[0]['stock_mov'];
		$tabData['stock_um_mov']	= 	$detalle[0]['stock_um_mov'];
		$tabData['cl_um']			= 	$detalle[0]['cl_um'];
		$tabData['cl_stock']		= 	$etiqueta;
		$tbl_plantilla 				=   set_table_tpl();
		//DIC
		$tabData['lbl_articulo']	 = $this->lang_item("articulo",false);
		$tabData['lbl_cl_almacen']	 = $this->lang_item("almacen_lbl",false);
		$tabData['lbl_cl_gaveta']	 = $this->lang_item("gaveta_lbl",false);
		$tabData['lbl_cl_pasillo']	 = $this->lang_item("pasillo_lbl",false);
		$tabData['lbl_stock_mov']	 = $this->lang_item("stock_mov",false);
		$tabData['lbl_stock_um_mov'] = $this->lang_item("stock_um_mov",false);
		$tabData['lbl_stock']		 = $this->lang_item("lblstock",false);
		$tabData['lbl_stock_um']	 = $this->lang_item("stock_um_lbl",false);
		// Titulos de tabla
		/*$this->table->set_heading(	$this->lang_item("id"),
									$this->lang_item("origen"),
									$this->lang_item("existencia")
								);*/

		$this->table->set_template($tbl_plantilla);
		$tabla 			 = $this->table->generate($tbl_data);
		$tabData['tabla'] = $tabla;
		echo json_encode($this->load_view_unique($uri_view ,$tabData, true));
	}
	public function export_xlsx(){
		$filtro      = ($this->ajax_get('filtro')) ?  base64_decode($this->ajax_get('filtro') ): "";
		$sqlData = array(
			 'buscar'     => $filtro
		);
		$list_content 			  = $this->db_model->get_data_historial($sqlData);

		if(count($list_content)>0){
			foreach ($list_content as $value) {
				($value['id_articulo_tipo']==2)?$etiqueta=$value['cl_um']:$etiqueta=$this->lang_item("pieza_abrev");
				$set_data[] = array(
									$value['id_almacen_ajuste'],
									$value['articulo'],
									$value['presentacion_detalle'],
									$value['stock_mov'].'-'.$etiqueta,
									$value['stock_um_mov'].'-'.$value['cl_um'],
									$value['estatus'],
									$value['timestamp']
									);
			}
			$set_heading = array(	$this->lang_item("id_almacen_ajuste"),
									$this->lang_item("articulo"),
									$this->lang_item("presentacion"),									
									$this->lang_item("stock_afec"),
									$this->lang_item("stock_um_afec"),
									$this->lang_item("estatus"),
									$this->lang_item("fecha_registro")
									);
	
		}
		$params = array(	'title'   => $this->lang_item("xlsx_historial_ajustes"),
							'items'   => $set_data,
							'headers' => $set_heading
						);
		$this->excel->generate_xlsx($params);
	}
}
?>