<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class programacion extends Base_Controller{
	private $modulo;
	private $submodulo;
	private $seccion;
	private $view_content;
	private $path;
	private $icon;
	private $view_modal;
	private $offset, $limit_max;
	private $tab, $tab1, $tab2, $tab3;

	public function __construct(){
		parent::__construct();
		$this->modulo 			= 'nutricion';
		$this->seccion		    = 'programacion';
		$this->icon 			= 'fa fa-calendar'; 
		$this->path 			= $this->modulo.'/'.$this->seccion.'/'; 
		$this->view_content 	= 'content';
		$this->tab1 			= 'config_programacion';
		$this->tab2 			= 'ver_calendario';
		$this->tab3 			= 'generar_formatos';

		$this->load->model($this->modulo.'/'.$this->seccion.'_model','db_model');
		$this->load->model('sucursales/listado_sucursales_model','sucursales');
		$this->load->model('nutricion/ciclos_model','ciclos');
		$this->lang->load($this->modulo.'/'.$this->seccion,"es_ES");
	}
	public function config_tabs(){
		$tab_1 						 = $this->tab1;
		$tab_2 						 = $this->tab2;
		$tab_3 						 = $this->tab3;
		$path  						 = $this->path;
		$config_tab['names']         = array($this->lang_item($tab_1) ,$this->lang_item($tab_2),$this->lang_item($tab_3)); 
		$config_tab['links']         = array($path.$tab_1 ,$path.$tab_2,$path.$tab_2); 
		$config_tab['action']        = array('','load_calendario_tab','');
		$config_tab['attr']          = array('','','');
		$config_tab['style_content'] = array('','','');
		return $config_tab;
	}
	private function uri_view_principal(){
		return $this->modulo.'/'.$this->view_content;
	}
	public function index(){
		$tabl_inicial 			  = 1;
		$view_agregar    		  = $this->inicio_config_programacion();	
		$contenidos_tab           = array($view_agregar,$this->calendario(),$this->formatos());
		$data['titulo_seccion']   = $this->lang_item($this->seccion);
		$data['titulo_submodulo'] = $this->lang_item($this->modulo);
		$data['icon']             = $this->icon;
		$data['tabs']             = tabbed_tpl($this->config_tabs(),base_url(),$tabl_inicial,$contenidos_tab);	
		
		$js['js'][]  = array(
								'name' => $this->seccion, 
								'dirname' => $this->modulo
							);
		$this->load_view($this->uri_view_principal(), $data, $js);
	}
	public function inicio_config_programacion(){
		$tab['lbl_sucursal']               = $this->lang_item('lbl_sucursal');
		$sqlData = array(
			 'buscar' => 0
			,'offset' => 0
			,'limit'  => 0
			);
		$dropdown_sucursales = array(
									 'data'		=> $this->sucursales->db_get_data($sqlData)
									,'value' 	=> 'id_sucursal'
									,'text' 	=> array('cv_sucursal','sucursal')
									,'name' 	=> "lts_sucursales"
									,'class' 	=> "requerido"
									,'leyenda' 	=> "-----"
									,'event'    => array('event'      => 'onchange', 
														'function'    => 'load_programacion', 
														'params'      => array('this.value'), 
														'params_type' => array(false))
								);
		$sucursales                = dropdown_tpl($dropdown_sucursales);
		$tab['dropdow_sucursales'] = $sucursales;
		$tab['config_form']        = $this->form_config_programacion();
		$uri_view                  = $this->modulo.'/'.$this->seccion.'/header_config_programacion';
		return $this->load_view_unique($uri_view , $tab, true);
	}
	public function form_config_programacion(){

		$id_sucursal = ($this->ajax_post('id_sucursal')) ? $this->ajax_post('id_sucursal') : 0;
		
		if($id_sucursal){
			$params_ciclo     = $this->db_model->get_params_ciclos($id_sucursal);
			$dias_festivos    = $this->db_model->get_dias_festivos($id_sucursal);
			$dias_especiales  = $this->db_model->get_dias_especiales($id_sucursal);
			$dias_descartados = $this->db_model->get_dias_descartados($id_sucursal);

			/*Periodo Programado*/
			if(is_array($params_ciclo)){
				$tab['value_fecha_inicio']     = $params_ciclo[0]['fecha_inicio'];
				$tab['value_fecha_termino']    = $params_ciclo[0]['fecha_termino'];
			}else{
				$tab['value_fecha_inicio']     = '';
				$tab['value_fecha_termino']    = '';
			}

			/*Dias Descartados*/
			if(is_array($dias_descartados)){
				foreach ($dias_descartados as $key => $value) {
					$dias_index[] = $value['dia_index'];
				}
			}else{
				$dias_index = array();
			}

			foreach ($this->days_all() as $key => $value) {
				$value = '<span>'.ucwords($value).'</span>';
				if(in_array($key, $dias_index)){
					$checked =  true;
				}else{
					$checked =  false;
				}
				$dias_descartados_checkbox[]=  form_checkbox('dias_descartados', $key, $checked).'&nbsp;'.$value;
			}

			/*recuperacion de Ciclos en programacion*/
			$ciclos               = $this->db_model->get_ciclos($id_sucursal);
			$ciclos_programados   = $this->db_model->get_ciclos_programados($id_sucursal);
			$li_ciclos            = '';
			if(is_array($ciclos_programados)){
				foreach ($ciclos_programados as $key => $value) {
					$li_ciclos .= "<li id='".$value['id_nutricion_ciclos']."' class='onclick_on'><h4><span class='icon-chevron-right'></span>&nbsp;".$value['id_nutricion_ciclos'].'-'.$value['ciclo']."</h4></li>";
				}
			}else{
				$li_ciclos = '';
				$ciclos_programados = null;
			}

			if(!is_null($ciclos)){
				$multiselect_ciclos = dropMultiselect_tpl(array(
																 'data'		        => $ciclos
																,'data_seleted' 	=> $ciclos_programados
																,'value' 	        => 'id_nutricion_ciclos'
																,'text' 	        => array('id_nutricion_ciclos','ciclo')
																,'name' 	        => "multiselect_ciclos"
																,'name2' 	        => "multiselect_ciclos_agregados"
																,'prev' 	        => "quitar_ciclo()"
																,'next' 	        => "agregar_ciclo()"
															)
														);
				$dropdown_ciclos = dropdown_tpl(array(
														 'data'		=> $ciclos
														,'value' 	=> 'id_nutricion_ciclos'
														,'text' 	=> array('id_nutricion_ciclos','ciclo')
														,'name' 	=> "dropdown_ciclos"
														,'event'    => array('event' => 'onchange',
																   						'function' => 'load_contenido_ciclo',
												   										'params'   => array('this.value'),
												   										'params_type' => array(false)
												   							)
													));
				$dropdown_ciclos_especiales = dropdown_tpl(array(
																	 'data'		=> $ciclos
																	,'value' 	=> 'id_nutricion_ciclos'
																	,'text' 	=> array('id_nutricion_ciclos','ciclo')
																	,'name' 	=> "dropdown_ciclos_especiales"
																));

				$btn_guardar_parametros = form_button(array( 	
																'content'  => $this->lang_item('btn_guardar'),
																'class'    => 'btn btn-primary',
																'name'     => 'guardar_programacion',
																'onclick'  => 'guardar_configuracion_programacion()'
															));
			}else{
				$btn_guardar_parametros = form_button(array( 
																'content'  => $this->lang_item('btn_guardar'),
																'class'    => 'btn btn-primary',
																'disabled' => 'disabled',
																'name'     => 'guardar_programacion'
															));
				$dropdown_ciclos_especiales = dropdown_tpl(array(
														 'data'		=> null
														,'name' 	=> "dropdown_ciclos_especiales"
														
													));
				$dropdown_ciclos = dropdown_tpl(array(
														 'data'		=> null
														,'name' 	=> "dropdown_ciclos"
														
													));
				$multiselect_ciclos = dropMultiselect_tpl(array(
														 'data'		=> null
														,'name' 	=> "multiselect_ciclos"
														
													));
			}			
			$multidropdown_especiales   = array(
													'text' 	    => array('fecha','ciclo')
													,'data'		=> $dias_especiales
													,'name' 	=> "multidropdown_ciclos_especiales"
													,'value' 	=> array('id_nutricion_ciclos', 'fecha')
													,'selected' => 'all'
												);
			$multidropdown_festivos     = array(
													'text' 	    => array('fecha')
													,'data'		=> $dias_festivos
													,'name' 	=> "multidropdown_festivos"
													,'value' 	=> array('fecha')
													,'selected' => 'all'
												);
			
			$tab['btn_guardar_parametros']        = $btn_guardar_parametros;
			$tab['values_dias_descartados']       = implode('&nbsp;', $dias_descartados_checkbox);
			$tab['multiselect_ciclos']            = $multiselect_ciclos;
			$tab['ciclos_programados']            = '<ol id="ciclos_programados" class="list-ordered">'.$li_ciclos.'</ol>';
			$tab['dropdown_ciclos']               = $dropdown_ciclos;
			$tab['dropdown_ciclos_especiales']    = $dropdown_ciclos_especiales;
			$tab['multidropdown_especiales']      = multi_dropdown_tpl($multidropdown_especiales);
			$tab['multidropdown_festivos']        = multi_dropdown_tpl($multidropdown_festivos);
		}else{
			$tab['btn_guardar_parametros']      = '';
			$tab['value_fecha_inicio']          = '';
			$tab['value_fecha_termino']         = '';
			$tab['values_dias_descartados']     = '';
			$tab['multiselect_ciclos']          = '';
			$tab['ciclos_programados']          = '';
			$tab['dropdown_ciclos']             = '';
			$tab['dropdown_ciclos_especiales']  = '';
			$tab['multidropdown_especiales']    = '';
			$tab['multidropdown_festivos']      = '';
		}
		
		$tab['lbl_config_programacion']    = $this->lang_item('lbl_config_programacion');
		$tab['lbl_input_fecha_inicio']     = $this->lang_item('lbl_input_fecha_inicio');
		$tab['lbl_input_fecha_termino']    = $this->lang_item('lbl_input_fecha_termino');
		$tab['lbl_dias_descartados']       = $this->lang_item('lbl_dias_descartados');
		$tab['lbl_dias_festivos']          = $this->lang_item('lbl_dias_festivos');
		$tab['lbl_info_dias_descartados']  = $this->lang_item('lbl_info_dias_descartados');
		$tab['lbl_input_fecha_descartada'] = $this->lang_item('lbl_input_fecha_descartada');
		$tab['lbl_agregar_ciclos']         = $this->lang_item('lbl_agregar_ciclos');
		$tab['lbl_info_agregar_ciclos']    = $this->lang_item('lbl_info_agregar_ciclos');
		$tab['lbl_orden_ciclos']           = $this->lang_item('lbl_orden_ciclos');
		$tab['lbl_info_orden_ciclos']      = $this->lang_item('lbl_info_orden_ciclos');
		$tab['lbl_cantidad_recetas']       = $this->lang_item('lbl_cantidad_recetas');
		$tab['lbl_info_cantidad_recetas']  = $this->lang_item('lbl_info_cantidad_recetas');
		$tab['lbl_dias_especiales']        = $this->lang_item('lbl_dias_especiales');
		$tab['info_agregar_ciclo']         = $this->lang_item('info_agregar_ciclo');
		$tab['info_dias_festivos']         = $this->lang_item('info_dias_festivos');
		$tab['info_agregar_festivo']       = $this->lang_item('info_agregar_festivo');
		$tab['info_select_dia']            = $this->lang_item('info_select_dia');
		$tab['info_select_ciclo']          = $this->lang_item('info_select_ciclo');
		$tab['info_select_dia']            = $this->lang_item('info_select_dia');
		$tab['lbl_ciclos_disponibles']     = $this->lang_item('lbl_ciclos_disponibles');
		$tab['info_ciclos_especiales']     = $this->lang_item('info_ciclos_especiales');

		
		$uri_view = $this->modulo.'/'.$this->seccion.'/content_config_programacion';
		$response = $this->load_view_unique($uri_view , $tab, true);
		if($this->ajax_post(false)){
			echo json_encode( $response );	
		}else{
			return $response;
		}	
	}
	public function ciclo_cantidad_recetas(){
		$id_ciclo        = $this->ajax_post('id_ciclo');
		$list            = '';
		$contenido_ciclo = $this->db_model->get_contenido_ciclo($id_ciclo);
		if(!is_null($contenido_ciclo)){
			foreach ($contenido_ciclo as $key => $value) {
					$servicios['s-'.$value['servicio']]['t-'.$value['tiempo']]['f-'.$value['familia']][] =array(
																													 'recetas'     => $value['receta'] , 
																													 'porciones'   => $value['porciones'],
																													 'id_vinculo'  => $value['id_nutricion_ciclo_receta']
																											);

			}
			$list  ='<br><div id="sidetreecontrol"><a href="?#">'.$this->lang_item('collapse').'</a> | <a href="?#">'.$this->lang_item('expand').'</a></div>';
			$list .= $this->make_list($servicios);	
		}else{
			$list = alertas_tpl('', $this->lang_item('msg_sin_recetas'),false);
		}
		echo json_encode($list);
	}
	public function guardar_parametros_programacion(){
		$values              = '';
		$params_programacion = $this->ajax_post('params');
		$id_sucursal         = $params_programacion['id_sucursal'];
		$fecha_inicio        = $params_programacion['fecha_inicio'];
		$fecha_termino       = $params_programacion['fecha_termino'];
		$fecha_inicio        = explode('/', $fecha_inicio);
		$fecha_inicio        = $fecha_inicio[2].'-'.$fecha_inicio[1].'-'.$fecha_inicio[0];
		$fecha_termino       = explode('/', $fecha_termino);
		$fecha_termino       = $fecha_termino[2].'-'.$fecha_termino[1].'-'.$fecha_termino[0];
		$dias_descartados    = (array_key_exists('dias_descartados', $params_programacion)) ? $params_programacion['dias_descartados'] : false;
		$dias_festivos       = (array_key_exists('festivos', $params_programacion)) ? $params_programacion['festivos'] : false;
		$dias_especiales     = (array_key_exists('especiales', $params_programacion)) ? $params_programacion['especiales'] : false;
		$orden_ciclos        = (array_key_exists('orden_ciclos', $params_programacion)) ? $params_programacion['orden_ciclos'] : false;
		
		$this->db_model->delete_paramas_programacion($id_sucursal);

		$data_insert = array(
							  'fecha_inicio'   => $fecha_inicio
							 ,'fecha_termino'  => $fecha_termino
							 ,'id_sucursal'    => $id_sucursal
							 ,'id_usuario'     => $this->session->userdata('id_usuario')
							 ,'timestamp'      => $this->timestamp()
						);

		$this->db_model->insert_params_programacion($data_insert);
		$data_insert  = array();
		if($dias_festivos){
			foreach ($dias_festivos as $key => $value) {
				$fecha = '';
				$fecha = explode('/', $value);
				$fecha = $fecha[2].'-'.$fecha[1].'-'.$fecha[0];
				$data_insert[] = array(
								  'fecha'          => $fecha
								 ,'id_sucursal'    => $id_sucursal
								 ,'id_usuario'     => $this->session->userdata('id_usuario')
								 ,'timestamp'      => $this->timestamp()
							);
			}	
			$this->db_model->insert_dias_festivos($data_insert);
		}
		$data_insert  = array();
		if($dias_especiales){
			foreach ($dias_especiales as $key => $value) {
				$fecha = '';
				$fecha = explode('/', $value['fecha']);
				$fecha = $fecha[2].'-'.$fecha[1].'-'.$fecha[0];
				$data_insert[] = array(
								  'fecha'               => $fecha
								 ,'id_nutricion_ciclos' => $value['id_ciclo']
								 ,'id_sucursal'         => $id_sucursal
								 ,'id_usuario'          => $this->session->userdata('id_usuario')
								 ,'timestamp'           => $this->timestamp()
							);
			}	
			$this->db_model->insert_dias_especiales($data_insert);
		}

		$data_insert  = array();
		if($dias_descartados){
			foreach ($dias_descartados as $key => $value){

				$dia = $this->days_item($value, true);
				$data_insert[] = array(
								  'dia_index'      => $value
								 ,'dia_name'       => $dia
								 ,'id_sucursal'    => $id_sucursal
								 ,'id_usuario'     => $this->session->userdata('id_usuario')
								 ,'timestamp'      => $this->timestamp()
							);
			}
			$this->db_model->insert_dias_descartados($data_insert);
		}
		$data_insert  = array();
		if($orden_ciclos){
			foreach ($orden_ciclos as $key => $value) {
				$index    = $value['index'];
				$id_ciclo = $value['ciclo_id'];

				$verifica_contenido = $this->db_model->get_ciclos( $id_sucursal, $id_ciclo );

				if($verifica_contenido){
					$data_insert[] = array(
								  'id_nutricion_ciclos'   => $id_ciclo
								 ,'orden'                 => $index
								 ,'id_sucursal'           => $id_sucursal
								 ,'id_usuario'            => $this->session->userdata('id_usuario')
								 ,'timestamp'             => $this->timestamp()
							);	
				}
			}
			$this->db_model->insert_programacion_ciclos($data_insert);
		}
		echo json_encode($this->lang_item("msg_update_success",false));
	}
	public function actuaizar_cantidad_receta(){
		$cantidad    = $this->ajax_post('cantidad');
		$id_vinculo  = $this->ajax_post('id_vinculo');

		$data = array(
						'porciones' => $cantidad,
						'id_nutricion_ciclo_receta' => $id_vinculo
						,'edit_timestamp' => $this->timestamp()
						,'edit_id_usuario' => $this->session->userdata('id_usuario')
						);
		$update = $this->db_model->update_cantidad_ciclo_receta($data);

		echo json_encode($this->lang_item("msg_update_success",false));
	}
	public function calendario(){
		$data['lbl_sucursal'] = $this->lang_item('lbl_sucursal');
		$sqlData = array(
			 'buscar' => 0
			,'offset' => 0
			,'limit'  => 0
			);
		$dropdown = array(
						 'data'		=> $this->sucursales->db_get_data($sqlData)
						,'value' 	=> 'id_sucursal'
						,'text' 	=> array('cv_sucursal','sucursal')
						,'name' 	=> "lts_sucursales_calendario"
						,'event'    => array('event'      => 'onchange', 
											'function'    => 'load_calendario', 
											'params'      => array('this.value'), 
											'params_type' => array(false))
					);

		$data['dropdpwn_sucursales'] = dropdown_tpl($dropdown);
		$data['calendar']            = $this->cargar_calendario(0);
		$view = $this->load_view_unique($this->modulo.'/'.$this->seccion.'/calendario_content',$data, true);
		return $view ;
	}
	public function cargar_calendario(){
		$index                = 0;
		$descartados          = array();
		$festivos             = array();
		$especiales           = array();
		$json                 = array();
		$id_sucursal          = ($this->ajax_post('id_sucursal')) ? $this->ajax_post('id_sucursal') : 0;
		$params_ciclo         = $this->db_model->get_params_ciclos($id_sucursal);
		$ciclos_programados   = $this->db_model->get_programacion_contenido_ciclo($id_sucursal);
		$dias_descartados     = $this->db_model->get_dias_descartados($id_sucursal);
		$dias_festivos        = $this->db_model->get_dias_festivos($id_sucursal);
		$dias_especiales      = $this->db_model->get_dias_especiales_contenido_ciclo($id_sucursal);
		
		if(is_array($dias_festivos)){
			foreach ($dias_festivos as $key => $items) {
				$festivos[] = strtotime(str_replace('/', '-', $items['fecha'])); ;
			}
		}

		if(is_array($dias_especiales)){
			foreach ($dias_especiales as $key => $items) {

				$especiales[strtotime(str_replace('/', '-', $items['fecha']))]['nombre'] = $items['ciclo'];
				$especiales[strtotime(str_replace('/', '-', $items['fecha']))]['servicios']['s-'.$items['servicio']]['t-'.$items['tiempo']]['f-'.$items['familia']][] =array('recetas' => $items['receta'],
																																											'porciones'   => $items['porciones'],
																																											'id_vinculo'  => $items['id_nutricion_ciclo_receta']);
			}
		}
		if(is_array($dias_descartados)){
			foreach ($dias_descartados as $key => $value) {
				$descartados[] = $value['dia_index'];
			}
		}

		if(is_array($ciclos_programados)){
			foreach ($ciclos_programados as $key => $value) {
				$ciclos[$value['orden']]['nombre'] = $value['ciclo'];
				$ciclos[$value['orden']]['servicios']['s-'.$value['servicio']]['t-'.$value['tiempo']]['f-'.$value['familia']][] =array(
																														 'recetas'     => $value['receta'] , 
																														 'porciones'   => $value['porciones'],
																														 'id_vinculo'  => $value['id_nutricion_ciclo_receta']
																												);

			}
			
			

			$fechaInicio = strtotime(str_replace('/', '-', $params_ciclo[0]['fecha_inicio']));
			$fechaFin    = strtotime(str_replace('/', '-', $params_ciclo[0]['fecha_termino']));
			
			for($i=$fechaInicio; $i<=$fechaFin; $i = strtotime("+1 day", $i)){
					if(array_key_exists($i, $especiales)){
						$dia  =  date('j', $i);
						$mes  = (date('n', $i)-1);
						$anio =  date('Y', $i); 
						
						$list = $this->make_list($especiales[$i]['servicios'],true, false);
						
						$json[] = '{
									title: "<span class=\'fa fa-birthday-cake\'></span>&nbsp;&nbsp;'.strtoupper($this->lang_item('lbl_especial', false)).'<br>'.'<span class=\'fa fa-coffee\'></span>&nbsp;&nbsp;<span>'.strtoupper($especiales[$i]['nombre']).'</span><hr>'.$list .'",
									start: new Date('.$anio.', '.$mes.', '.$dia.'),
				                    allDay: true
								}';
						$index++;
						continue;
					}
					if(!array_key_exists($index, $ciclos)){
						$index = 0;
					}
					if(!in_array($i, $festivos)){
						$day = (date('N', $i) == 7) ? 0 : date('N', $i);
						
						if(!in_array($day, $descartados)){
							$dia  =  date('j', $i);
							$mes  = (date('n', $i)-1);
							$anio =  date('Y', $i); 
							
							$list = $this->make_list($ciclos[$index]['servicios'],true, false);
							
							$json[] = '{
										title: "<span class=\'fa fa-coffee\'></span>&nbsp;&nbsp;<span>'.strtoupper($ciclos[$index]['nombre']).'</span><hr>'.$list .'",
										start: new Date('.$anio.', '.$mes.', '.$dia.'),
					                    allDay: true
									}';
							$index++;
						}
					}else{
						$index++;
					}
			}
			//print_debug($json);
			$json         = implode(',',$json);
			$data['json'] = $json;
			$success = 1;
			$msg = '';
		}else{
			$data['json'] = '';
			$success = 0;
			$msg = alertas_tpl('', $this->lang_item('msg_ciclos_null') ,false);
		}
		$json = $this->load_view_unique($this->modulo.'/'.$this->seccion.'/calendario',$data, true);
		$response = array('success' => $success,'result' => $json, 'msg' => $msg);
		if($this->ajax_post(false)){
			echo json_encode( $response);
		}else{
			return $json;
		}	
	}
	public function enlistar_contenido($array){
		$list .= '<ul  class="list-nostyle ">';
		foreach ($array as $item => $content) {
			$list .= '<li><span class=" iconfa-fire"></span><span style="font-size:12px;">'.$item.'</span>';
			if(is_array($content)){
				$list .= '<ul>';
				foreach ($content as $value) {
					$list .= '<li><span class=" iconfa-bookmark"></span>'.$value['recetas'].'</li>';
				}
				$list .= '</ul>';
			}
		}
		$list .= '</ul>';
		return $list;
	}
	public function make_list($items, $inicio = true, $inputs = true) {

		if($inputs){
			$ret = ($inicio) ? "<ul id='treeview_ciclos' class='treeview-gray'>": '<ul>';
		}else{
			$ret = ($inicio) ? "<ul class='list-normalstyle' >": '<ul>';
		}
	    foreach ($items as $item => $subitems) {
	        if (!is_numeric($item)) {
	        	$nivel = explode('-', $item);
	        	if(count($nivel)>1){
	        		$icon   = $nivel[0];
		        	$tittle = $nivel[1];
		        	switch ($icon) {
		        		case 's':
		        			$icon = ' iconfa-time';
		        			$tipo = strtoupper( $this->lang_item('servicio') );
		        			break;
		        		case 't':
		        			$icon = 'iconfa-sitemap';
		        			$tipo = strtoupper( $this->lang_item('tiempo') );
		        			break;
		        		case 'f':
		        			$icon = 'iconfa-certificate';
		        			$tipo = strtoupper( $this->lang_item('familia') );
		        			break;
		        		default:
		        			$icon = 'iconfa-fire';
		        			break;
		        	}
		        	if(!$inputs){
		        		$tipo = '';
		        	}else{
		        		$tipo = $tipo.':';
		        	}	
		            $ret .= "<li><span class='$icon'></span>".$tipo.'&nbsp;'.strtoupper($tittle);
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
	        	if($inputs){
	        		$cantidades = form_input($data_input).$save_disk;
	        	}else{
	        		$cantidades = '';
	        	}
	            $ret .= "<li>".$cantidades."<span class=' iconfa-bookmark'></span>".$subitems['recetas'];
		         
		        }else{
		        	$ret .= $this->make_list($subitems, false,$inputs);
		        }
	        }

	        $ret .= "</li>";
	    }
	    $ret .= "</ul>";
	    return($ret);
	}
	public function formatos(){
		
		$sqlData = array(
			 'buscar' => 0
			,'offset' => 0
			,'limit'  => 0
			);
		$dropdown_sucursales = array(
									 'data'		=> $this->sucursales->db_get_data($sqlData)
									,'value' 	=> 'id_sucursal'
									,'text' 	=> array('cv_sucursal','sucursal')
									,'name' 	=> "lts_sucursales_formatos"
									,'leyenda' 	=> "-----"
									,'class'    => "requerido"
								);
		$sucursales                = dropdown_tpl($dropdown_sucursales);

		$btn_guardar = form_button(array( 
										'content'  => $this->lang_item('btn_formato'),
										'class'    => 'btn btn-primary',
										'name'     => 'generar_formato',
										'onclick'  => 'generar_formato()'
									));
		$data['lbl_sucursal']                   = $this->lang_item('lbl_sucursal_formatos');
		$data['dropdown_sucursales']            = $sucursales;
		$data['lbl_input_fecha_inicio']         = $this->lang_item('lbl_fecha_inicio');
		$data['lbl_input_fecha_termino']        = $this->lang_item('lbl_fecha_termino');
		$data['lbl_tipo_formato']               = $this->lang_item('lbl_tipo_formato');
		$data['checked_orden_compra']           = $this->lang_item('lbl_formato_abasto');
		$data['checked_valores_nutricionales']  = $this->lang_item('lbl_formato_nutricional');
		$data['checked_licitacion']             = $this->lang_item('lbl_formato_licitacion');
		$data['btn_formato']                    = $btn_guardar;
		

		$view = $this->load_view_unique($this->modulo.'/'.$this->seccion.'/formatos',$data, true);
		return $view;
	}
	public function formato_abasto(){
		$params = $this->ajax_post('objData');
		if($params['incomplete']>0){
			$response['msg']      = alertas_tpl('error',$this->lang_item("msg_campos_obligatorios",false),false);
			$response['success']  = 0;
			echo json_encode($response);
		}else{
			$index                    = 0;
			$indexar                  = false;
			$rows_format              = array();
			$descartados              = array();
			$festivos                 = array();
			$especiales               = array();
			$periodo_inicio           = $params['fecha_inicio_formatos'];
			$periodo_termino          = $params['fecha_termino_formatos'];
			$id_sucursal              = $params['lts_sucursales_formatos'];
			$params_ciclo             = $this->db_model->get_params_ciclos($id_sucursal);
			$dias_descartados         = $this->db_model->get_dias_descartados($id_sucursal);
			$dias_festivos            = $this->db_model->get_dias_festivos($id_sucursal);
			$dias_especiales          = $this->db_model->get_dias_especiales_contenido_ciclo_insumos($id_sucursal);
			$ciclos_programados       = $this->db_model->get_programacion_contenido_ciclo_insumos($id_sucursal);
			$programacion_historico   = $this->db_model->get_programacion_contenido_ciclo_historial($id_sucursal,$periodo_inicio);

			$params_inicio        = strtotime(str_replace('/', '-', $params_ciclo[0]['fecha_inicio']));
			$params_termino       = strtotime(str_replace('/', '-', $params_ciclo[0]['fecha_termino']));
			$periodo_inicio       = strtotime(str_replace('/', '-', $periodo_inicio));
			$periodo_termino      = strtotime(str_replace('/', '-', $periodo_termino));

			/*CODIGO NUEVO*/
			$fecha_actual         = strtotime(date('Y/m/d'));
			/*Termina codigo nuevo*/

			if(!is_array($params_ciclo)){
				$response['msg']     = alertas_tpl('',$this->lang_item("msg_sucursal_sin_contenido",false),false);
				$response['success'] = 0;
				echo json_encode($response);
				exit;
			}
			if($periodo_termino<$params_inicio){
            	$response['msg']     = alertas_tpl('',$this->lang_item("msg_periodo_sin_contenido",false),false);
				$response['success'] = 0;
				echo json_encode($response);
				exit;
            }
            if($periodo_inicio>$params_termino){
            	$response['msg']     = alertas_tpl('',$this->lang_item("msg_periodo_sin_contenido",false),false);
				$response['success'] = 0;
				echo json_encode($response);
				exit;
            }

            if($periodo_inicio<$params_inicio){
            	$periodo_inicio = $params_inicio;
            }

            if($periodo_termino>$params_termino){
            	$periodo_termino = $params_termino;
            }

			if(is_array($dias_festivos)){
				foreach ($dias_festivos as $key => $items) {
					$festivos[] = strtotime(str_replace('/', '-', $items['fecha'])); 
				}
			}
			
			if(is_array($dias_descartados)){
				foreach ($dias_descartados as $key => $items) {
					$descartados[] = $items['dia_index'];
				}
			}

			if(is_array($dias_especiales)){
				foreach ($dias_especiales as $key => $items) {
					$especiales[strtotime(str_replace('/', '-', $items['fecha']))][] =  $items;

				}
			}

			// print_debug($dias_especiales);
			if(is_array($ciclos_programados)){

				foreach ($ciclos_programados as $key => $items) {
					$ciclos[$items['orden']][] = $items;
				}

				for($i=$params_inicio; $i<=$periodo_termino; $i= strtotime("+1 day", $i)){
					if($i==$fecha_actual){
						$indexar= true;
					}
					
					if(array_key_exists($i, $especiales)){
						if($indexar){
							$rows_format[$i]= $especiales[$i];
							// print_debug($rows_format);
						}
						$index++;
						continue;
					}
					if(!array_key_exists($index, $ciclos)){
						$index = 0;
					}
					if(!in_array($i, $festivos)){
						$day = (date('N', $i) == 7) ? 0 : date('N', $i);
						
						if(!in_array($day, $descartados)){
							
							if($indexar){
								$rows_format[$i] = $ciclos[$index];
							}
							$index++;
						}
					}else{
						$index++;
					}
				}
				if($periodo_termino<$fecha_actual){
					$invert = explode("/",$params['fecha_termino_formatos']); 
					$fecha_invert = $invert[2]."/".$invert[1]."/".$invert[0];
					$programacion_historico   = $this->db_model->get_programacion_contenido_ciclo_historial_aux($id_sucursal,$fecha_invert);
					// print_debug($programacion_historico);
				}
				if(is_array($programacion_historico)){
					foreach ($programacion_historico as $date => $value) {
						// print_debug($value['fecha']);
						$day   = $this->days_item(( date('N', strtotime($value['fecha'])) == 7 ) ? 0 : date('N', strtotime($value['fecha'])) ) ;

						$data_items[] = array(	
													 $value['sucursal']
													,$day
													,$value['fecha']
													,$value['ciclo']
													,$value['servicio']
													,$value['receta']
													,$value['clave_receta']
													,$value['porciones_receta_preparacion']
													,$value['porciones_recetas_ciclos']
													,$value['articulo']
													,$value['porciones_articulo']
													,$value['linea']
													,$value['marca']
													,$value['proveedor']
													,$value['presentacion']
													,$value['um']
													,$value['rendimiento']
													,$value['costo_x_um']
													);
					}
				}
				


				if(!empty($rows_format)){
					foreach ($rows_format as $date => $items) {
						$day   = $this->days_item( ( date('N', $date) == 7 ) ? 0 : date('N', $date) ) ;
						$fecha = date('Y/m/d', $date);
						foreach ($items as $row => $value) {
							$data_items[] = array(	
													 $value['sucursal']
													,$day
													,$fecha
													,$value['ciclo']
													,$value['servicio']
													,$value['receta']
													,$value['clave_receta']
													,$value['porciones_receta_preparacion']
													,$value['porciones_recetas_ciclos']
													,$value['articulo']
													,$value['porciones_articulo']
													,$value['linea']
													,$value['marca']
													,$value['proveedor']
													,$value['presentacion']
													,$value['um']
													,$value['rendimiento']
													,$value['costo_x_um']
													);
						}
					}
					// print_debug($data_items);

					$set_heading = array(
											 $this->lang_item('sucursal', false)
											,$this->lang_item('dia', false)
										 	,$this->lang_item('fecha', false)
											,$this->lang_item('ciclo', false)
											,$this->lang_item('servicio', false)
											,$this->lang_item('receta', false)
											,$this->lang_item('clave_receta', false)
											,$this->lang_item('porciones_receta_preparacion', false)
											,$this->lang_item('porciones_recetas_ciclos', false)
											,$this->lang_item('articulo', false)
											,$this->lang_item('porciones_articulo', false)
											,$this->lang_item('linea', false)
											,$this->lang_item('marca', false)
											,$this->lang_item('proveedor', false)
											,$this->lang_item('presentacion', false)
											,$this->lang_item('um', false)
											,$this->lang_item('rendimiento', false)
											,$this->lang_item('costo_x_um', false)
									);

					$params = array(	'title'   => $this->lang_item('formato_programacion'),
										'items'   => $data_items,
										'headers' => $set_heading
									);

					$response['file']       = $this->excel->generate_xlsx($params, true);
					$response['msg']        = 'exito';
					$response['success']    = 1;
					echo json_encode($response);
				}else{
						// print_debug($periodo_termino>$fecha_actual);
					if($periodo_termino<$fecha_actual){

						$set_heading = array(
												 $this->lang_item('sucursal', false)
												,$this->lang_item('dia', false)
											 	,$this->lang_item('fecha', false)
												,$this->lang_item('ciclo', false)
												,$this->lang_item('servicio', false)
												,$this->lang_item('receta', false)
												,$this->lang_item('clave_receta', false)
												,$this->lang_item('porciones_receta_preparacion', false)
												,$this->lang_item('porciones_recetas_ciclos', false)
												,$this->lang_item('articulo', false)
												,$this->lang_item('porciones_articulo', false)
												,$this->lang_item('linea', false)
												,$this->lang_item('marca', false)
												,$this->lang_item('proveedor', false)
												,$this->lang_item('presentacion', false)
												,$this->lang_item('um', false)
												,$this->lang_item('rendimiento', false)
												,$this->lang_item('costo_x_um', false)
										);

						$params = array(	'title'   => $this->lang_item('formato_programacion'),
											'items'   => $data_items,
											'headers' => $set_heading
										);

						$response['file']       = $this->excel->generate_xlsx($params, true);
						$response['msg']        = 'exito';
						$response['success']    = 1;
						echo json_encode($response);
					}
					
	    //         	$response['msg']     = alertas_tpl('',$this->lang_item("msg_periodo_sin_contenido",false),false);
					// $response['success'] = 0;
					// echo json_encode($response);
					// exit;
            		
				}
			}else{
				$response['msg']     = alertas_tpl('',$this->lang_item("msg_sucursal_sin_contenido",false),false);
				$response['success'] = 0;
				echo json_encode($response);
				exit;
			}
			
		}
	}
}
