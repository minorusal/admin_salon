<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ciclos extends Base_Controller{

	private $modulo;
	private $submodulo;
	private $view_content;
	private $path;
	private $icon;
	private $offset, $limit_max;
	private $tab, $tab1, $tab2, $tab3;

	public function __construct(){
		parent::__construct();
		$this->modulo 			= 'nutricion';
		$this->seccion		    = 'ciclos';
		$this->icon 			= 'fa fa-cutlery'; 
		$this->path 			= $this->modulo.'/'.$this->seccion.'/'; 
		$this->view_content 	= 'content';
		$this->limit_max		= 10;
		$this->offset			= 0;
		// Tabs
		$this->tab1 			= 'agregar';
		$this->tab2 			= 'configurar';
		$this->tab3 			= 'detalle';
		// DB Model
		$this->load->model($this->modulo.'/'.$this->seccion.'_model','ciclos');
		$this->load->model('sucursales/listado_sucursales_model','sucursales');
		$this->load->model('administracion/servicios_model','servicios');
		$this->load->model('nutricion/tiempos_model','tiempos');
		$this->load->model('nutricion/familias_model','familias');
		$this->load->model('nutricion/recetario_model','recetas');
		// Diccionario
		$this->lang->load($this->modulo.'/'.$this->seccion,"es_ES");
	}

	public function config_tabs(){
		$tab_1 	= $this->tab1;
		$tab_2 	= $this->tab2;
		$path  	= $this->path;
		
		// Nombre de Tabs
		$config_tab['names']    = array(
										 $this->lang_item($tab_1) 
										,$this->lang_item($tab_2) 
								); 
		// Href de tabs
		$config_tab['links']    = array(
										 $path.$tab_1             
										,$path.$tab_2                   
								); 
		// Accion de tabs
		$config_tab['action']   = array(
										 'load_content'
										,'load_content'
								);
		// Atributos 
		$config_tab['attr']     = array('','');

		$config_tab['style_content'] = array('','');

		return $config_tab;
	}
	private function uri_view_principal(){
		return $this->modulo.'/'.$this->view_content;
	}

	public function index(){
		$tabl_inicial 			  = 1;
		$view_agregar    		  = $this->agregar();	
		$contenidos_tab           = $view_agregar;
		$data['titulo_seccion']   = $this->lang_item($this->seccion);
		$data['titulo_submodulo'] = $this->lang_item($this->modulo);
		$data['icon']             = $this->icon;
		$data['tabs']             = tabbed_tpl($this->config_tabs(),base_url(),$tabl_inicial,$contenidos_tab);	
		
		$js['js'][]  = array('name' => $this->seccion, 'dirname' => $this->modulo);
		$this->load_view($this->uri_view_principal(), $data, $js);
	}
	public function agregar(){
		$seccion   = $this->modulo.'/'.$this->seccion.'/'.$this->seccion.'_save';
		$sqlData = array(
			 'buscar' => ''
			,'offset' => 0
			,'limit' => 0
			);
		//Combo box que muestra las sucursales
		$dropdown_sucursales = array(
						 'data'		=> $this->sucursales->db_get_data($sqlData)
						,'value' 	=> 'id_sucursal'
						,'text' 	=> array('sucursal')
						,'name' 	=> "lts_sucursales"
						,'leyenda' 	=> "-----"
						,'class' 	=> "requerido"
						,'event'    => array('event'      => 'onchange', 
											'function'    => 'load_ciclos', 
											'params'      => array('this.value'), 
											'params_type' => array(false))
					);
		$sucursales = dropdown_tpl($dropdown_sucursales);

		$btn_save  = form_button(array('class'=>'btn btn-primary', 'name'=>'save_puesto', 'onclick'=>'agregar()','content'=>$this->lang_item("btn_guardar")));
		$btn_reset = form_button(array('class'=>'btn btn_primary', 'name'=>'reset','onclick'=>'clean_formulario()','content'=>$this->lang_item('btn_limpiar')));

		$tabIn['lbl_tipo_insert']    = $this->lang_item("lbl_tipo_insert");
		$tabIn['lbl_auto']           = $this->lang_item("lbl_auto");
		$tabIn['lbl_manual']         = $this->lang_item("lbl_manual");
		$tabIn['lbl_cantidad_ciclo'] = $this->lang_item("lbl_cantidad_ciclo");
		$tabIn['lbl_nombre_ciclo']   = $this->lang_item("lbl_nombre_ciclo");
		$tabIn['lbl_clave_corta']    = $this->lang_item('lbl_clave_corta');
		$tabIn['lbl_sucursal']       = $this->lang_item('lbl_sucursal');

		$tabIn['list_sucursales']    = $sucursales;

		$tabIn['btn_save']  = $btn_save;
		$tabIn['btn_reset'] = $btn_reset;

		if($this->ajax_post(false))
		{
			echo json_encode($this->load_view_unique($seccion,$tabIn,true));
		}
		else
		{
			return $this->load_view_unique($seccion, $tabIn, true);
		}
	}

	public function insert_ciclo(){
		$objData  	= $this->ajax_post('objData');
		if($objData['incomplete']>0){
			$msg = $this->lang_item("msg_campos_obligatorios",false);
			echo json_encode( array( 'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)) );
		}else{
			$sqlData = array(
				 'ciclo'       => $objData['txt_cantidad_ciclo']
				,'nom_ciclo'   => $objData['txt_ciclo']
				,'id_sucursal' => $objData['lts_sucursales']
				,'clave_corta' => $objData['txt_clave_corta']
				,'id_usuario'  => $this->session->userdata('id_usuario')
				,'timestamp'   => $this->timestamp()
				,'tipo'        => (isset($objData['tipo']))?$objData['tipo']:'auto'
				);
			$insert = $this->ciclos->insert_ciclo($sqlData);
			if($insert){
				$msg = $this->lang_item("msg_insert_success",false);
				echo json_encode(array(  'success'=>'true', 'mensaje' => $msg));
			}else{
				$msg = $this->lang_item("msg_err_clv",false);
				echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('', $msg ,false)));
			}
		}
	}

	public function configurar(){
		$seccion   = $this->modulo.'/'.$this->seccion.'/'.$this->seccion.'_config';

		$sqlData = array(
			 'buscar' => 0
			,'offset' => 0
			,'limit'  => 0
			);
		//Combo box que muestra las sucursales
		$dropdown_sucursales = array(
						 'data'		=> $this->sucursales->db_get_data($sqlData)
						,'value' 	=> 'id_sucursal'
						,'text' 	=> array('sucursal')
						,'name' 	=> "lts_sucursales"
						,'class' 	=> "requerido"
						,'leyenda' 	=> "-----"
						,'event'    => array('event'      => 'onchange', 
											'function'    => 'load_ciclos', 
											'params'      => array('this.value'), 
											'params_type' => array(false))
					);
		$sucursales = dropdown_tpl($dropdown_sucursales);

		$dropdown_ciclos = array(
					 'value' 	=> 'id_nutricion_ciclos'
					,'text' 	=> array('ciclo')
					,'class' 	=> "requerido"
					,'name' 	=> "lts_ciclos"
			   									);
		$ciclos = dropdown_tpl($dropdown_ciclos);

		$dropdown_servicios = array(
				 'value' 	=> 'id_administracion_servicio'
				,'text' 	=> array('servicio')
				,'leyenda' 	=> "-----"
				,'class' 	=> "requerido"
				,'name' 	=> "lts_servicios"
		   									);
		$servicios = dropdown_tpl($dropdown_servicios);

		$data_tiempo = $this->tiempos->db_get_data($sqlData);
		$dropdown_tiempos = array(
				 'data'     => $data_tiempo
				,'value' 	=> 'id_nutricion_tiempo'
				,'text' 	=> array('tiempo')
				,'leyenda' 	=> "-----"
				,'class' 	=> "requerido"
				,'name' 	=> "lts_tiempos"
					);						
		$tiempos = dropdown_tpl($dropdown_tiempos);

		$data_familia = $this->familias->db_get_data($sqlData);
		$dropdown_familias = array(
				 'data'     => $data_familia
				,'value' 	=> 'id_nutricion_familia'
				,'text' 	=> array('familia')
				,'leyenda' 	=> "-----"
				,'class' 	=> "requerido"
				,'name' 	=> "lts_familias"
				,'event'    => array('event'       => 'onchange', 
									 'function'    => 'load_recetas', 
									 'params'      => array('this.value'), 
									 'params_type' => array(false))
			);						
		$familias = dropdown_tpl($dropdown_familias);

		$recetas  = array(
						// 'data'		=> $this->recetas->get_data($sqlData)
						'value' 	=> 'id_nutricion_receta'
						,'text' 	=> array('receta')
						,'name' 	=> "lts_recetas"
						,'class' 	=> "requerido limpio"
					);

		$list_recetas  = multi_dropdown_tpl($recetas);

		$btn_save  = form_button(array('class'=>'btn btn-primary', 'name'=>'save', 'onclick'=>'insert_config()','content'=>$this->lang_item("btn_guardar")));
		$btn_reset = form_button(array('class'=>'btn btn_primary', 'name'=>'reset','onclick'=>'clean_formulario()','content'=>$this->lang_item('btn_limpiar')));

		$data['lbl_sucursal']     	 = $this->lang_item('lbl_sucursal');
		$data['lbl_ciclos']       	 = $this->lang_item('lbl_ciclos');
		$data['lbl_servicios']    	 = $this->lang_item('lbl_servicios');
		$data['lbl_tiempos']      	 = $this->lang_item('lbl_tiempos');
		$data['lbl_familias']     	 = $this->lang_item('lbl_familias');
		$data['lbl_asignar_recetas'] = $this->lang_item('lbl_recetas');

		$data['btn_save']     	   	 = $btn_save;
		$data['btn_reset']    	     = $btn_reset;
		$data['list_sucursales']  	 = $sucursales;
		$data['list_ciclos']  		 = $ciclos;
		$data['list_servicios']   	 = $servicios;
		$data['list_tiempos']     	 = $tiempos;
		$data['list_familias']     	 = $familias;
		$data['multiselect_recetas'] = $list_recetas;
		if($this->ajax_post(false)){
			echo json_encode($this->load_view_unique($seccion,$data,true));
		}else{
			return $this->load_view_unique($seccion, $data, true);
		}
	}

	public function cargar_ciclos(){
		$id_sucursal = $this->ajax_post('id_sucursal');
		if($id_sucursal){
			$sqlData = array(
							 'buscar' => $id_sucursal
							,'offset' => 0
							,'limit' => 0
							);
			$data_ciclo = $this->ciclos->db_get_data($sqlData);
			$dropdown_ciclos = array(
					 'data'     => $data_ciclo
					,'value' 	=> 'id_nutricion_ciclos'
					,'text' 	=> array('ciclo')
					,'class' 	=> "requerido"
					,'leyenda'  => '-----'
					,'name' 	=> "lts_ciclos"
					,'event'    => array('event' => 'onchange',
							   						'function' => 'load_contenido_ciclo',
			   										'params'   => array('this.value'),
			   										'params_type' => array(false)
			   									));
			$data_servicio = $this->servicios->db_get_data_x_sucursal($id_sucursal);
			$dropdown_servicios = array(
				 'data'     => $data_servicio
				,'value' 	=> 'id_administracion_servicio'
				,'text' 	=> array('servicio')
				,'class' 	=> "requerido"
				,'leyenda' 	=> "-----"
				,'name' 	=> "lts_servicios"
		   									);
			$servicios = dropdown_tpl($dropdown_servicios);
		}else{
			$dropdown_ciclos = array(
					'value' 	=> 'id_nutricion_ciclos'
					,'text' 	=> array('ciclo')
					,'class' 	=> "requerido"
					,'leyenda'  => '-----'
					,'name' 	=> "lts_ciclos");

			$dropdown_servicios = array(
				 'value' 	=> 'id_administracion_servicio'
				,'text' 	=> array('servicio')
				,'class' 	=> "requerido"
				,'leyenda' 	=> "-----"
				,'name' 	=> "lts_servicios"
		   									);
		}

		$ciclos = dropdown_tpl($dropdown_ciclos);
		$servicios = dropdown_tpl($dropdown_servicios);
		$data['ciclos']     = $ciclos;
		$data['servicios']  = $servicios;

		echo json_encode($data);
	}

	public function ciclo_receta(){
		$id_familia  = $this->ajax_post('id_familia');
		$id_sucursal = $this->ajax_post('id_sucursal');
		if($id_familia){
			$receta  = $this->recetas->get_data_recetas_x_familia($id_familia,$id_sucursal);

			$recetas = array(
							 'data'		=> $receta
							,'value' 	=> 'id_nutricion_receta'
							,'text' 	=> array('receta')
							,'name' 	=> "lts_recetas"
							,'class' 	=> "requerido limpio"
						);
			$list_recetas  = multi_dropdown_tpl($recetas);

			echo json_encode($list_recetas);
		}
	}
	public function ciclo_detalle($id_ciclo = false){
		$nom_ciclo = $this->ajax_post('nombre_ciclo');
		$id_ciclo = ($this->ajax_post('id_ciclo'))?$this->ajax_post('id_ciclo'):$id_ciclo;
		$list = '';
		$contenido_ciclo = $this->ciclos->get_ciclo_contenido($id_ciclo);

		if(!is_null($contenido_ciclo)){
			foreach ($contenido_ciclo as $key => $value){
				$id_familia = $value['id_familia'];
				$idc = $value['id_ciclo'];
					$servicios['s-'.$value['servicio'].','.$idc.','.$value['id_servicio']]['t-'.$value['tiempo'].','.$idc.','.$value['id_tiempo']]['f-'.$value['familia'].','.$idc.','.$id_familia][] =array(
																												'id_receta'   => $value['id_receta'],
																												'id_ciclo'    => $value['id_ciclo'],
																												'recetas'     => $value['receta'] , 
																												'porciones'   => $value['porciones'],
																												'id_vinculo'  => $value['id_nutricion_ciclo_receta']
																													);

			}
			$list  = '<span class="$icon"></span><h3>'.strtoupper($contenido_ciclo[0]['ciclo']).'&nbsp&nbsp<a style="cursor:pointer;" onclick="eliminar_ciclo('.$contenido_ciclo[0]['id_ciclo'].')"><span class=" iconfa-trash"></span></a></h3><hr>'; 
			$list .='<div id="sidetreecontrol"><a href="?#">'.$this->lang_item('collapse').'</a> | <a href="?#">'.$this->lang_item('expand').'</a></div>';
			$list .= $this->make_list($servicios,true);	
		}else{
			$list  = '<span class="$icon"></span><h3>'.strtoupper($nom_ciclo).'&nbsp<a style="cursor:pointer;" onclick="eliminar_ciclo('.$id_ciclo.')"><span class=" iconfa-trash"></span></a></h3><hr>';
		}
		echo json_encode($list);
	}

	public function make_list($items, $inicio = true){
		$elimina = '';
		$ret = ($inicio) ? "<ul id='treeview_ciclos' class='treeview-gray'>": '<ul>';	
	    foreach ($items as $item => $subitems) {
			if (!is_numeric($item)){
        		$nivel = explode('-', $item);
        		if(count($nivel)>1){
	        		$icon   = $nivel[0];
		        	$tittle = $nivel[1];
		        	switch ($icon) {
		        		case 's':
		        			$servicio    = explode(',',$item);
		        		    $id_ciclo    = $servicio[1];
		        		    $id_servicio = $servicio[2];
		        			$icon        = 'iconfa-time';
		        			$tipo        = strtoupper($this->lang_item('servicio'));
		        			$elimina     = 'eliminar_servicio('.$id_servicio.','.$id_ciclo.')';
		        			break;
		        		case 't':
		        			$tiempo    = explode(',',$item);
		        		    $id_ciclo  = $tiempo[1];
		        		    $id_tiempo = $tiempo[2];
		        			$icon 	   = 'iconfa-sitemap';
		        			$tipo 	   = strtoupper( $this->lang_item('tiempo') );
		        			$elimina   = 'eliminar_tiempo('.$id_tiempo.','.$id_ciclo.')';
		        			break;
		        		case 'f':
		        			$familia    = explode(',',$item);
		        			$id_ciclo   = $familia[1];
		        			$id_familia = $familia[2];
		        			$icon 	    = 'iconfa-certificate';
		        			$tipo 	    = strtoupper( $this->lang_item('familia') );
		        			$elimina    = 'eliminar_familia('.$id_familia.','.$id_ciclo.')';
		        			break;
		        		default:
		        			$icon = 'iconfa-fire';
		        			break;
		        	}
		        	$tittle = explode(',',$tittle);
		        	$ret .= "<li><span class='$icon'></span>".$tipo.': &nbsp;'.strtoupper($tittle[0])."<a class ='onclick_on' onclick='$elimina'><span class=' iconfa-trash'></span></a>";
	        	}
        	}
	        if (is_array($subitems)) {
	        	if(array_key_exists('recetas', $subitems)){
	        	$data_input = array(
	        				  'id'     => 'cantidad_'.$subitems['id_vinculo'],
				              'class'  => 'input-mini numerico',
				              'type'   => 'text',
				              'value'  => $subitems['porciones']
				            );
	        	$save_disk = '<a onclick="guardar_cantidad_receta_ciclo('.$subitems['id_vinculo'].')">
	        					<span style ="font-size:1.6em;" class="  iconfa-save"></span>
	        				 </a>';
	        	
	            $ret .= "<li><span class=' iconfa-bookmark'></span>".$subitems['recetas']."<a class ='onclick_on' onclick='eliminar_receta(".$subitems['id_vinculo'].",".$subitems['id_ciclo'].")'><span class=' iconfa-trash'></span></a>";
		         
		        }else{
		        	$ret .= $this->make_list($subitems, false);
		        }
	        }

	        $ret .= "</li>";
	    }
	    $ret .= "</ul>";
	    return($ret);
	}
	public function insert_config(){
		$objData  	= $this->ajax_post('objData');
		if($objData['incomplete']>0){
			$msg = $this->lang_item("msg_campos_obligatorios",false);
			echo json_encode(alertas_tpl('error', $msg ,false));
		}else{
			$arr_recetas  = explode(',',$objData['lts_recetas']);
			if(!empty($arr_recetas)){
				$sqlData = array();
				foreach ($arr_recetas as $key => $value){
					$sqlData = array(
						 'id_ciclo'    => $objData['lts_ciclos']
						,'id_servicio' => $objData['lts_servicios']
						,'id_receta'   => $value
						,'id_familia'  => $objData['lts_familias']
						,'id_tiempo'   => $objData['lts_tiempos']
						,'id_usuario'  => $this->session->userdata('id_usuario')
					    ,'timestamp'   => $this->timestamp()
						);
					$insert = $this->ciclos->insert_ciclo_receta($sqlData);
				}
				$arbol = $this->ciclo_detalle($objData['lts_ciclos']);
			}else{
				$arbol = $this->ciclo_detalle($objData['lts_ciclos']);
			}
		}
	}

	public function eliminar_servicio(){
		$id_ciclo    = $this->ajax_post('id_ciclo');
		$id_servicio = $this->ajax_post('id_servicio');
		
		$elimina = $this->ciclos->eliminar_servicio($id_servicio,$id_ciclo);
		$detalle = $this->ciclo_detalle($id_ciclo);
	}

	public function eliminar_tiempo(){
		$id_ciclo    = $this->ajax_post('id_ciclo');
		$id_tiempo   = $this->ajax_post('id_tiempo');
		
		$elimina = $this->ciclos->eliminar_tiempo($id_tiempo,$id_ciclo);
		$detalle = $this->ciclo_detalle($id_ciclo);
	}

	public function eliminar_familia(){
		$id_ciclo    = $this->ajax_post('id_ciclo');
		$id_familia   = $this->ajax_post('id_familia');
		
		$elimina = $this->ciclos->eliminar_familia($id_familia,$id_ciclo);
		$detalle = $this->ciclo_detalle($id_ciclo);
	}

	public function eliminar_receta(){

		$id_ciclo    = $this->ajax_post('id_ciclo');
		$id_vinculo  = $this->ajax_post('id_vinculo');
		
		$elimina = $this->ciclos->eliminar_receta($id_vinculo,$id_ciclo);
		$detalle = $this->ciclo_detalle($id_ciclo);
	}

	public function eliminar_ciclo(){
		$id_ciclo = $this->ajax_post('id_ciclo');
		$elimina  = $this->ciclos->eliminar_ciclo($id_ciclo);
		if($elimina){
			echo json_encode(1);
		}else{
			$detalle = $this->ciclo_detalle($id_ciclo);
		}
	}
}