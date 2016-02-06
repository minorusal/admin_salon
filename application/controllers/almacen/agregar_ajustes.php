<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once('stock.php');
class agregar_ajustes extends stock{
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
		$this->submodulo        = 'agregar_ajustes';
		$this->seccion          = 'ajustes';
		$this->icon 			= 'fa fa-inbox'; //Icono de modulo
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
										,$this->lang_item($tab_2) //listado
										,$this->lang_item($tab_3) //listado
								); 
		// Href de tabs
		$config_tab['links']    = array(
										 $path.$tab_1            //compras/listado_precios/agregar
										,$path.$tab_2.'/'.$pagina //compras/listado_precios/listado/pagina
										,$path.$tab_3            //compras/listado_precios/agregar
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
	private function uri_view_principal(){
		return $this->modulo.'/'.$this->view_content;
	}
	public function index(){
		$tabl_inicial 			  = $this->tab_inicial;
		$view_listado    		  = $this->listado();
		$contenidos_tab           = $view_listado;
		$data['titulo_submodulo'] = $this->lang_item($this->seccion);
		$data['titulo_seccion']   = $this->lang_item($this->submodulo);
		$data['icon']             = $this->icon;
		$data['tabs']             = tabbed_tpl($this->config_tabs(),base_url(),$tabl_inicial,$contenidos_tab);	
		//$data['modal']            = $this->modal();

		$js['js'][]  = array('name' => $this->submodulo, 'dirname' => $this->modulo);
		$js['js'][]  = array('name' => 'numeral', 'dirname' => '');
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
		$total_rows   			  = count($this->db_model->db_get_data($sqlData));
		$sqlData['aplicar_limit'] = false;
		$list_content 			  = $this->db_model->db_get_data($sqlData);
		$url          			  = base_url($url_link);
		$paginador    			  = $this->pagination_bootstrap->paginator_generate($total_rows, $url, $limit, $uri_segment, array('evento_link' => 'onclick', 'function_js' => 'load_content', 'params_js'=>'1'));
		//dump_var($list_content);
		if($total_rows){
			foreach ($list_content as $value) {
				// Evento de enlace
				// Acciones
				$accion_id 						= $value['id_almacen_ajuste'];
				$btn_acciones['agregar'] 		= '<span id="ico-articulos_'.$accion_id.'" class="ico_detalle fa fa-search-plus" onclick="detalle('.$accion_id.')" title="'.$this->lang_item("agregar_articulos").'"></span>';
				$acciones = implode('&nbsp;&nbsp;&nbsp;',$btn_acciones);
				($value['id_articulo_tipo']==2)?$etiqueta=$value['cl_um']:$etiqueta=$this->lang_item("pieza_abrev");
				// Datos para tabla
				$tbl_data[] = array('id'             		=> $value['id_almacen_ajuste'],
									'articulo'  	 		=> $value['articulo'],
									'presentacion_detalle'  => $value['presentacion_detalle'],
									'stock_mov'   	 		=> $value['stock_mov'].'-'.$etiqueta,
									'stock_um_mov'   		=> $value['stock_um_mov'].'-'.$value['cl_um'],
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
										$this->lang_item("stock_mov"),
										$this->lang_item("stock_um_mov"),
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
	public function agregar(){
		$view = $this->tab['agregar'];
		$btn_save      = form_button(array('class'=>"btn btn-primary",'name' => 'ajuste_save','onclick'=>'agregar()' , 'content' => $this->lang_item("btn_guardar") ));
		$listado_almacen=$this->catalogos_model->db_get_data_almacen();
		$dropArray = array(
				 'data'		=> $listado_almacen
				,'value' 	=> 'id_almacen_almacenes'
				,'text' 	=> array('clave_corta','almacenes')
				,'name' 	=> "lts_almacen"
				,'event'    => array('event'       => 'onchange',
			   						 'function'    => 'load_gaveta_pas',
			   						 'params'      => array('this.value'),
			   						 'params_type' => array(0)
								)
				,'class' 	=> "requerido"
			);

		$lts_almacen  		         = dropdown_tpl($dropArray);
		$tabData['lts_almacen']      = $lts_almacen;
		$tabData['button_save']      = $btn_save;
		$tabData['lbl_articulo']	 = $this->lang_item("articulo",false);
		$tabData['lbl_almacen']	     = $this->lang_item("almacen_lbl",false);
		$tabData['lbl_gaveta']	     = $this->lang_item("gaveta_lbl",false);
		$tabData['lbl_pasillo']	     = $this->lang_item("pasillo_lbl",false);
		$tabData['lbl_stock_mov']	 = $this->lang_item("stock_mov",false);
		$tabData['lbl_stock_um_mov'] = $this->lang_item("stock_um_mov",false);
		$tabData['lblstock']	     = $this->lang_item("lblstock",false);
		$tabData['stock_um_lbl']	 = $this->lang_item("stock_um_lbl",false);
		$tabData['configuracion']	 = $this->lang_item("configuracion",false);

		$uri_view  = $this->modulo.'/'.$this->seccion.'/'.$this->submodulo.'/'.$view;
		if($this->ajax_post(false)){
			echo json_encode( $this->load_view_unique($uri_view ,$tabData, true));
		}else{
			echo json_encode( $this->load_view_unique($uri_view ,$tabData, true,$includes));
		}
	}
	public function detalle(){
		$id_almacen_ajuste = $this->ajax_post('id_almacen_ajuste');
		$detalle  		   = $this->db_model->get_data_unico($id_almacen_ajuste);
		$view 			   = $this->tab['detalle'];
		//DATA
		$tabData['articulo']	         = $detalle[0]['articulo'];
		$tabData['cl_almacen']	         = $detalle[0]['cl_almacen'];
		$tabData['cl_gaveta']	         = $detalle[0]['cl_gaveta'];
		$tabData['cl_pasillo']	         = $detalle[0]['cl_pasillo'];
		$tabData['stock_mov']	         = $detalle[0]['stock_mov'];
		$tabData['stock_um_mov']	     = $detalle[0]['stock_um_mov'];
		$tabData['cl_um']			     = $detalle[0]['cl_um'];
		//DIC
		$tabData['lbl_articulo']	     = $this->lang_item("articulo",false);
		$tabData['lbl_cl_almacen']	     = $this->lang_item("almacen_lbl",false);
		$tabData['lbl_cl_gaveta']	     = $this->lang_item("gaveta_lbl",false);
		$tabData['lbl_cl_pasillo']	     = $this->lang_item("pasillo_lbl",false);
		$tabData['lbl_stock_mov']	     = $this->lang_item("stock_mov",false);
		$tabData['lbl_stock_um_mov']	 = $this->lang_item("stock_um_mov",false);
		
		$uri_view  = $this->modulo.'/'.$this->seccion.'/'.$this->submodulo.'/'.$view;

		echo json_encode( $this->load_view_unique($uri_view ,$tabData, true));
	}
	public function load_stock(){
		$id_articulo   =  $this->ajax_post('id_articulo');
		$id_almacen    =  ($this->ajax_post('id_almacen')!=0)?$this->ajax_post('id_almacen'):'';
		$id_pasillo    =  ($this->ajax_post('id_pasillo')!=0)?$this->ajax_post('id_pasillo'):'';
		$id_gavetas    =  ($this->ajax_post('id_gavetas')!=0)?$this->ajax_post('id_gavetas'):'';

		$slqdata=array(
					'id_articulo' => $id_articulo,
					'id_almacen'  => $id_almacen,
					'id_pasillo'  => $id_pasillo, 
					'id_gaveta'   => $id_gavetas);

		$detalle  = $this->db_model->db_get_data_x_articulo($slqdata);

		$dropArray = array(
				 'data'		=> $detalle
				,'value' 	=> 'id_compras_presentacion'
				,'text' 	=> array('articulo','presentacion')
				,'name' 	=> "lts_presentacion"
				,'event'    => array('event'       => 'onchange',
			   						 'function'    => 'load_stock_x_presentacion',
			   						 'params'      => array('this.value'),
			   						 'params_type' => array(0)
								)
				,'class' 	=> "requerido"
			);

		$lts_presentacion  		    = dropdown_tpl($dropArray);

		echo json_encode($lts_presentacion);
	}
	public function load_stock_x_presentacion(){
		$id_articulo   				=  $this->ajax_post('id_articulo');
		$id_compras_presentacion    =  $this->ajax_post('id_compras_presentacion');
		$id_almacen    				=  ($this->ajax_post('id_almacen')!=0)?$this->ajax_post('id_almacen'):'';
		$id_pasillo    				=  ($this->ajax_post('id_pasillo')!=0)?$this->ajax_post('id_pasillo'):'';
		$id_gavetas    				=  ($this->ajax_post('id_gavetas')!=0)?$this->ajax_post('id_gavetas'):'';

		$slqdata=array(
					'id_articulo'			  => $id_articulo,
					'id_almacen' 			  => $id_almacen,
					'id_pasillo' 			  => $id_pasillo, 
					'id_gaveta' 			  => $id_gavetas,
					'id_compras_presentacion' => $id_compras_presentacion);


		$detalle  = $this->db_model->db_get_data_articulo_x_presentacion($slqdata);
		$stock=0;
		$stock_um=0;
		for($i=0; count($detalle)>$i;$i++){
			$stock+=$detalle[$i]['stock'];
			$stock_um+=$detalle[$i]['stock_um'];
		}
		($detalle[0]['id_articulo_tipo']==2)?$cl_um= $detalle[0]['cl_um']:$cl_um= $this->lang_item("pieza_abrev");
		$um_mimina 					 = $detalle[0]['unidad_minima_cve'];
		$id_compras_articulo_precios = $detalle[0]['id_compras_articulo_precios'];
		$data=array('stock'=> $stock,'stock_um'=>$stock_um, 'u_m_cv'=>$um_mimina, 'um' => $cl_um, 'id_compras_articulo_precios' => $id_compras_articulo_precios);
		echo json_encode($data);
	}
	public function load_gaveta_pas(){
		$id_almacen  = $this->ajax_post('id_almacen');
		$vent 		 ='load_gaveta';
		$name 		 = 'lts_pasillos';
		$name_gav 	 = 'lts_gavetas';
		$event_2	 ='load_articulos';

		$datasql=array('id_almacen'=>$id_almacen);
		$dropArray = array(
				 'data'		=> $this->catalogos_model->db_get_data_pasillos_por_almacen($datasql)
				,'value' 	=> 'id_almacen_pasillos'
				,'text' 	=> array('clave_corta','pasillos')
				,'name' 	=> $name
				,'event'    => array('event'       => 'onchange',
			   						 'function'    => $vent,
			   						 'params'      => array('this.value'),
			   						 'params_type' => array(0)
								)
			);
		$dropArray2 = array(
				 'data'		=> $this->catalogos_model->db_get_data_gavetas_por_almacen($datasql)
				,'value' 	=> 'id_almacen_gavetas'
				,'text' 	=> array('clave_corta','gavetas')
				,'name' 	=> $name_gav
				,'event'    => array('event'       => 'onchange',
			   						 'function'    => $event_2,
			   						 'params'      => array('this.value'),
			   						 'params_type' => array(0)
									)
			);
		//se cargar el select de lso articulos para modificar su cantidad
		$datasql=array(
				'id_almacen' => $id_almacen,
				'id_pasillo' => '',
				'id_gaveta'  => '');
		$dropArray3 = array(
				 'data'		=> $this->db_model->db_get_data_articulos($datasql)
				,'value' 	=> 'id_articulo'
				,'text' 	=> array('cl_articulo','articulo')
				,'name' 	=> "lts_ajustes"
				,'event'    => array('event'       => 'onchange',
				   						 'function'    => 'load_stock',
				   						 'params'      => array('this.value'),
				   						 'params_type' => array(0)
   									)
			);
		$lts_pasillo  = dropdown_tpl($dropArray);
		$lts_gavetas  = dropdown_tpl($dropArray2);
		$lts_ajustes  = dropdown_tpl($dropArray3);

		$data['pasillos']    = $lts_pasillo;
		$data['gavetas']     = $lts_gavetas;
		$data['lts_ajustes'] = $lts_ajustes;
		echo json_encode($data);
	}
	public function load_gaveta(){
		$id_almacen    =  ($this->ajax_post('id_almacen')!=0)?$this->ajax_post('id_almacen'):'';
		$id_pasillo    =  ($this->ajax_post('id_pasillo')!=0)?$this->ajax_post('id_pasillo'):'';
		$id_gaveta     =  ($this->ajax_post('id_gaveta')!=0)?$this->ajax_post('id_gaveta'):'';
		$event='load_articulos';
		$name = 'lts_gavetas';

		if($id_pasillo==0){
			$datasql=array('id_almacen'=>$id_almacen);
			$dropArray = array(
							 'data'		=> $this->catalogos_model->db_get_data_gavetas_por_almacen($datasql)
							,'value' 	=> 'id_almacen_gavetas'
							,'text' 	=> array('clave_corta','gavetas')
							,'name' 	=> $name
							,'event'    => array('event'       => 'onchange',
						   						 'function'    => $event,
						   						 'params'      => array('this.value'),
						   						 'params_type' => array(0)
		   									)
						);
		}else{
			$datasql=array('id_pasillo'=>$id_pasillo);
			$dropArray = array(
							 'data'		=> $this->catalogos_model->db_get_data_gavetas_por_pasillo($datasql)
							,'value' 	=> 'id_almacen_gavetas'
							,'text' 	=> array('clave_corta','gavetas')
							,'name' 	=> $name
							,'event'    => array('event'       => 'onchange',
						   						 'function'    => $event,
						   						 'params'      => array('this.value'),
						   						 'params_type' => array(0)
	   										)
						);
		}
		$datasql=array(
					'id_almacen' => $id_almacen,
					'id_pasillo' => $id_pasillo,
					'id_gaveta'  => '');
		$dropArray3 = array(
						 'data'		=> $this->db_model->db_get_data_articulos($datasql)
						,'value' 	=> 'id_articulo'
						,'text' 	=> array('cl_um','articulo')
						,'name' 	=> "lts_ajustes"
						,'class' 	=> "requerido"
						,'event'    => array('event'       => 'onchange',
						   						 'function'    => 'load_stock',
						   						 'params'      => array('this.value'),
						   						 'params_type' => array(0)
		   									)
					);
		$lts_ajustes  = dropdown_tpl($dropArray3);
		$lts_gavetas  = dropdown_tpl($dropArray);
		$data['lts_gavetas'] = $lts_gavetas;
		$data['lts_ajustes'] = $lts_ajustes;
		echo json_encode($data);
	}
	public function load_articulos(){
		$id_almacen   =  ($this->ajax_post('id_almacen')!=0)?$this->ajax_post('id_almacen'):'';
		$id_pasillo   =  ($this->ajax_post('id_pasillo')!=0)?$this->ajax_post('id_pasillo'):'';
		$id_gaveta    =  ($this->ajax_post('id_gaveta')!=0)?$this->ajax_post('id_gaveta'):'';

		$datasql=array(
					'id_almacen' => $id_almacen,
					'id_pasillo' => $id_pasillo,
					'id_gaveta'  => $id_gaveta);

		$dropArray3 = array(
						 'data'	 => $this->db_model->db_get_data_articulos($datasql)
						,'value' => 'id_articulo'
						,'text'  => array('cl_um','articulo')
						,'name'  => "lts_ajustes"
						,'class' => "requerido"
						,'event' => array(
										'event'       => 'onchange',
				   						 'function'    => 'load_stock',
				   						 'params'      => array('this.value'),
				   						 'params_type' => array(0)
									)
			);
		$lts_ajustes  = dropdown_tpl($dropArray3);
		echo json_encode($lts_ajustes);
	}
	public function insert(){
		$incomplete	 = $this->ajax_post('incomplete');
		if($incomplete>0){
			$msg = $this->lang_item("msg_campos_obligatorios",false);
			echo json_encode( array( 'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)) );
		}else{
			$stock_mov 	  					= $this->ajax_post('stock');
			$stock_um_mov 					= $this->ajax_post('stock_um_destino');
			$id_articulo  					= $this->ajax_post('id_articulo');
			$id_almacen   					= $this->ajax_post('id_almacen');
			$id_pasillo   					= $this->ajax_post('id_pasillo');
			$id_gaveta 	  					= $this->ajax_post('id_gavetas');
			$id_compras_articulo_precios 	= $this->ajax_post('id_compras_articulo_precios');
			
			//
			$sqlData = array(
						'stock_mov'    				  => $stock_mov,
						'stock_um_mov' 				  => $stock_um_mov,
						'id_articulo'  				  => $id_articulo,
						'id_compras_articulo_precios' => $id_compras_articulo_precios,
						'id_almacen'   				  => $id_almacen,
						'id_pasillo'   				  => $id_pasillo,
						'id_gaveta'    				  => $id_gaveta,
						'estatus'      				  => 1,//en espera de aprobacion 
						'timestamp'    				  => $this->timestamp(),
						'id_usuario'   				  => $this->session->userdata('id_usuario')
						);
				//dump_var($sqlData);
				$insert=$this->db_model->insert($sqlData);
			//
			if($insert){
				$msg = $this->lang_item("msg_update_success",false);
				echo json_encode(array(  'success'=>'true', 'mensaje' => $msg ));
			}else{
				$msg = $this->lang_item("msg_campos_obligatorios",false);
				echo json_encode( array( 'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)) );
			}	
		}
	}
	public function export_xlsx(){
		$filtro      = ($this->ajax_get('filtro')) ?  base64_decode($this->ajax_get('filtro') ): "";
		$sqlData = array(
			 'buscar'     => $filtro
		);
		$list_content 			  = $this->db_model->db_get_data($sqlData);

		if(count($list_content)>0){
			foreach ($list_content as $value) {
				($value['id_articulo_tipo']==2)?$etiqueta=$value['cl_um']:$etiqueta=$this->lang_item("pieza_abrev");
				$set_data[] = array(
									$value['id_almacen_ajuste'],
									$value['articulo'],
									$value['presentacion_detalle'],
									$value['stock_mov'].'-'.$etiqueta,
									$value['stock_um_mov'].'-'.$value['cl_um'],
									$value['timestamp']
									);
			}
			$set_heading = array(	$this->lang_item("id_almacen_ajuste"),
									$this->lang_item("articulo"),										
									$this->lang_item("presentacion"),
									$this->lang_item("stock_mov"),
									$this->lang_item("stock_um_mov"),
									$this->lang_item("fecha_registro")
									);
	
		}

		$params = array(	'title'   => $this->lang_item("xlsx_agregar_ajustes"),
							'items'   => $set_data,
							'headers' => $set_heading
						);
		$this->excel->generate_xlsx($params);
	}
}
?>