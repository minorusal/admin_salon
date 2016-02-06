<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class embalaje extends Base_Controller { 
	private $modulo;
	private $submodulo;
	private $seccion;
	private $view_content;
	private $path;
	private $icon;


	private $offset, $limit_max;
	private $tab1, $tab2, $tab3;

	public function __construct(){
		parent::__construct();
		$this->modulo 			= 'compras';
		$this->submodulo		= 'catalogos';
		$this->seccion          = 'embalaje';
		$this->icon 			= 'fa fa-road'; #Icono de modulo
		$this->path 			= $this->modulo.'/'.$this->seccion.'/'; #compras/embalaje/
		$this->view_content 	= 'content';
		$this->limit_max		= 10;
		$this->offset			= 0;
		// Tabs
		$this->tab1 			= 'agregar';
		$this->tab2 			= 'listado';
		$this->tab3 			= 'detalle';
		// DB Model
		$this->load->model($this->modulo.'/'.$this->submodulo.'_model','db_model');
		// Diccionario
		$this->lang->load($this->modulo.'/'.$this->submodulo,"es_ES");
	}
	public function config_tabs(){
		$tab_1 	= $this->tab1;
		$tab_2 	= $this->tab2;
		$tab_3 	= $this->tab3;
		$path  	= $this->path;
		$pagina =(is_numeric($this->uri_segment_end()) ? $this->uri_segment_end() : "");
		// Nombre de Tabs
		$config_tab['names']    = array(
										 $this->lang_item('agregar_em') #agregar
										,$this->lang_item('listado_em') #listado
										,$this->lang_item($tab_3) #detalle
								); 
		// Href de tabs
		$config_tab['links']    = array(
										 $path.$tab_1             #compras/catalagos/agregar
										,$path.$tab_2.'/'.$pagina #compras/catalagos/listado/pagina
										,$tab_3                   #detalle
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
		return $this->modulo.'/'.$this->view_content; #compras/content
	}

	public function index(){
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
	public function listado($offset=0){
		$seccion 		= '/listado';
		$tab_detalle	= $this->tab3;	
		$limit 			= $this->limit_max;
		$uri_view 		= $this->modulo.$seccion;
		$url_link 		= $this->path.'listado';	

		$filtro      = ($this->ajax_post('filtro')) ? $this->ajax_post('filtro') : "";

		$sqlData = array(
			 'buscar'      	=> $filtro
			,'offset' 		=> $offset
			,'limit'      	=> $limit
		);
		
		$uri_segment  = $this->uri_segment(); 
		$total_rows	  = count($this->db_model->get_embalaje($sqlData));
		$sqlData['aplicar_limit'] = false;	
		$list_content = $this->db_model->get_embalaje($sqlData);
		
		$url          = base_url($url_link);
		$paginador    = $this->pagination_bootstrap->paginator_generate($total_rows, $url, $limit, $uri_segment, array('evento_link' => 'onclick', 'function_js' => 'load_content', 'params_js'=>'1'));
		if($total_rows>0){
			foreach ($list_content as $value) {
				$atrr = array(
								'href' => '#',
							  	'onclick' => 'detalle('.$value['id_compras_embalaje'].')'
						);
				$embalaje = $this->db_model->db_get_embalaje_user($value['id_compras_embalaje']);
				$eliminar 	= '<span style="color:red;" id="ico-eliminar_'.$value['id_compras_embalaje'].'" class="ico_eliminar fa fa-times" onclick="confirm_delete('.$value['id_compras_embalaje'].')" title="'.$this->lang_item("lbl_eliminar").'"></span>';
				$btn_acciones['eliminar'] = ($embalaje[0]['num_embalaje'] == 0)?$eliminar:'<span style="color:gray;" id="ico-eliminar_'.$value['id_compras_embalaje'].'" class="ico_eliminar fa fa-times" title="'.$this->lang_item("lbl_eliminar").'"></span>';
				$acciones = implode('&nbsp;&nbsp;&nbsp;',$btn_acciones);
				$tbl_data[] = array('id'             => $value['clave_corta'],
									'embalaje'       => tool_tips_tpl($value['embalaje'], $this->lang_item("tool_tip"), 'right' , $atrr),
									'clave_corta'    => $value['clave_corta'],
									'descripcion'    => $value['descripcion'],
									'acciones'		 => $acciones);	
			}
			
			// Plantilla
			$tbl_plantilla = set_table_tpl();
			// Titulos de tabla
			$this->table->set_heading(	$this->lang_item("embalaje"),
										$this->lang_item("nombre_embalaje"),
										$this->lang_item("cvl_corta"),
										$this->lang_item("descripcion"),
										$this->lang_item("acciones"));
			// Generar tabla
			$this->table->set_template($tbl_plantilla);
			$tabla = $this->table->generate($tbl_data);

			$buttonTPL = array( 'text'   => $this->lang_item("btn_xlsx"), 
								'iconsweets' => 'fa fa-file-excel-o',
								'href'       => base_url($this->path.'export_xlsx?filtro='.base64_encode($filtro))
								);
		}
		else
		{
			$buttonTPL = "";
			$msg   = $this->lang_item("msg_query_null");
			$tabla = alertas_tpl('', $msg ,false);
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
	public function agregar(){

		$seccion       = $this->modulo.'/'.$this->submodulo.'/'.$this->seccion.'/embalaje_save';  #almacen/catalogos/pasillos/pasillos_saveq
		$btn_save      = form_button(array('class'=>"btn btn-primary",'name' => 'save_pasillo','onclick'=>'agregar()' , 'content' => $this->lang_item("btn_guardar") ));
		$btn_reset     = form_button(array('class'=>"btn btn-primary",'name' => 'reset','value' => 'reset','onclick'=>'clean_formulario()','content' => $this->lang_item("btn_limpiar")));

		$tab_1["nombre_embalaje"]      = $this->lang_item("nombre_embalaje");
		$tab_1["cvl_corta"]            = $this->lang_item("cvl_corta");
		$tab_1["descrip"]              = $this->lang_item("descripcion");

        $tab_1['button_save']       = $btn_save;
        $tab_1['button_reset']      = $btn_reset;


        if($this->ajax_post(false)){
				echo json_encode($this->load_view_unique($seccion , $tab_1, true));
		}else{
			return $this->load_view_unique($seccion , $tab_1, true);
		}
	}
	public function insert(){
		$incomplete  = $this->ajax_post('incomplete');
		
		if($incomplete>0){
			$msg = $this->lang_item("msg_campos_obligatorios",false);
			echo json_encode( array( 'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)) );
		}else{
			$embalaje     = $this->ajax_post('embalaje');
			$clave_corta  = $this->ajax_post('clave_corta');
			$descripcion  = ($this->ajax_post('descripcion')=='')? $this->lang_item("sin_descripcion") : $this->ajax_post('descripcion');

			$data_insert  = array(
								'embalaje'             => $embalaje,  
								'clave_corta'         => $clave_corta,
								 'descripcion'          => $descripcion,
								 'id_usuario'           => $this->session->userdata('id_usuario'),
								 'timestamp'            => $this->timestamp());
			
			$insert = $this->db_model->insert_embalaje($data_insert);
			
			if($insert){
				$msg = $this->lang_item("msg_insert_success",false);
				echo json_encode(array(  'success'=>'true', 'mensaje' => $msg));
			}else{
				$msg = $this->lang_item("msg_err_clv",false);
				echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('', $msg ,false)));
			}
		}
	}
	public function detalle(){

		$seccion        = $this->modulo.'/'.$this->submodulo.'/'.$this->seccion.'/embalaje_edit';
		$id_embalaje    = $this->ajax_post('id_embalaje');
		$detalle  		= $this->db_model->get_embalaje_unico($id_embalaje);
		$btn_save       = form_button(array('class'=>"btn btn-primary",'name' => 'update' , 'onclick'=>'update()','content' => $this->lang_item("btn_guardar") ));
		
		$data_tab['id_embalaje']      		 = $id_embalaje;
        $data_tab["nombre_embalaje"]	 	 = $this->lang_item("nombre_embalaje");
		$data_tab["cvl_corta"]        	  	 = $this->lang_item("cvl_corta");
		$data_tab["descrip"]         	     = $this->lang_item("descripcion");
		$data_tab['lbl_fecha_registro']      = $this->lang_item('lbl_fecha_registro');
		$data_tab['lbl_usuario_registro']    = $this->lang_item('lbl_usuario_registro');
		$data_tab["lbl_ultima_modificacion"] = $this->lang_item('lbl_ultima_modificacion', false);
        $data_tab['embalaje']        		 = $detalle[0]['embalaje'];
		$data_tab['clave_corta']           	 = $detalle[0]['clave_corta'];
        $data_tab['descripcion']           	 = $detalle[0]['descripcion'];
        $data_tab['timestamp']             	 = $detalle[0]['timestamp'];
        $data_tab['button_save']           	 = $btn_save;
        
        $this->load_database('global_system');
        $this->load->model('users_model');
        	
        $usuario_registro               = $this->users_model->search_user_for_id($detalle[0]['id_usuario']);
        $data_tab['usuario_registro']   = text_format_tpl($usuario_registro[0]['name'],"u");

        if($detalle[0]['edit_id_usuario']){
        	$usuario_registro                   = $this->users_model->search_user_for_id($detalle[0]['edit_id_usuario']);
        	$usuario_name 				        = text_format_tpl($usuario_registro[0]['name'],"u");
        	$data_tab['val_ultima_modificacion'] = sprintf($this->lang_item('val_ultima_modificacion', false), $this->timestamp_complete($detalle[0]['edit_timestamp']), $usuario_name);
    	}else{
    		$usuario_name = '';
    		$data_tab['val_ultima_modificacion'] = $this->lang_item('lbl_sin_modificacion', false);
    	}
		echo json_encode( $this->load_view_unique($seccion ,$data_tab, true));
	}
	public function update(){
		$incomplete  = $this->ajax_post('incomplete');
		if($incomplete>0){
			$msg = $this->lang_item("msg_campos_obligatorios",false);
			echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)));
		}else{
			$id_embalaje  	= $this->ajax_post('id_embalaje');
			$embalaje   	= $this->ajax_post('embalaje');
			$clave_corta    = $this->ajax_post('clave_corta');
			$descripcion    = $this->ajax_post('descripcion');
			
			$data_update      = array('embalaje'    	 => $embalaje,
									  'clave_corta'      => $clave_corta, 
									  'descripcion'      => $descripcion
									  ,'edit_id_usuario' => $this->session->userdata('id_usuario')
									  ,'edit_timestamp'  => $this->timestamp()
									  );
			$insert = $this->db_model->update_embalaje($data_update,$id_embalaje);

			if($insert){
				$msg = $this->lang_item("msg_update_success",false);
				echo json_encode(array(  'success'=>'true', 'mensaje' => $msg ));
			}else{
				$msg = $this->lang_item("msg_err_clv",false);
				echo json_encode( array( 'success'=>'false', 'mensaje' =>alertas_tpl('', $msg ,false)));
			}
		}
	}
	public function export_xlsx(){
		$filtro      = ($this->ajax_get('filtro')) ?  base64_decode($this->ajax_get('filtro') ): "";
		$sqlData = array(
			 'buscar'      	=> $filtro
		);
		$list_content = $this->db_model->get_embalaje($sqlData);
		if(count($list_content)>0){
			foreach ($list_content as $value) {
				$set_data[] = array(
									$value['embalaje'],
									$value['clave_corta'],
									$value['descripcion']);
			}
			
			$set_heading = array(
									$this->lang_item("embalaje"),
									$this->lang_item("cvl_corta"),
									$this->lang_item("descripcion"));
	
		}

		$params = array(	'title'   => $this->lang_item("catalogo", false).$this->lang_item("embalaje"),
							'items'   => $set_data,
							'headers' => $set_heading
						);
		
		$this->excel->generate_xlsx($params);
	}

	public function eliminar_registro(){
		$id_embalaje = $this->ajax_post('id_embalaje');
		$embalaje = $this->db_model->db_get_embalaje_user($id_embalaje);
		if($embalaje[0]['num_embalaje'] == 0){
			$sqlData = array(
					'id_compras_embalaje'     => $id_embalaje
		            ,'activo'                 => 0
		            ,'edit_id_usuario'        => $this->session->userdata('id_usuario')
					,'edit_timestamp'         => $this->timestamp()
					);
			
			$update = $this->db_model->bd_delete_embalaje_data($sqlData);
			if($update){
				$msg = $this->lang_item("msg_delete_success",false);
				echo json_encode(array('success'=>'true', 'mensaje' => $msg, 'id_embalaje' => $id_embalaje));
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
?>