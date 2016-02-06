<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class clientes extends Base_Controller { 

	var $uri_modulo     = 'sucursales/';
	var $uri_submodulo  = 'clientes/';
	var $view_content   = 'content';

	public function __construct(){
		parent::__construct();
		$this->load->model($this->uri_modulo.'clientes_model');
		$this->load->model('administracion/entidades_model','ent_model');
		$this->load->model('sucursales/listado_sucursales_model','sucur_model');
		$this->load->model('sucursales/punto_venta_model','pventa');
		$this->lang->load("sucursales/clientes","es_ES");
	}
	public function config_tabs(){
		$pagina = (is_numeric($this->uri_segment_end()) ? $this->uri_segment_end() : "");
		$config_tab['names']    = array($this->lang_item("agregar_cliente"), 
										$this->lang_item("listado_cliente"),
										$this->lang_item("detalle_cliente")); 
		$config_tab['links']    = array('sucursales/clientes/agregar', 
										'sucursales/clientes/listado/'.$pagina,
										'detalle'); 
		$config_tab['action']   = array('load_content',
										'load_content',
										'');
		$config_tab['attr']     = array('','', array('style' => 'display:none'));

		return $config_tab;
	}
	private function uri_view_principal(){
		return $this->uri_modulo.$this->view_content;
	}
	public function index(){
		$view_listado 	= $this->listado();

		$data['titulo_seccion']   = $this->lang_item("lbl_cliente");
		$data['titulo_modulo'] = $this->lang_item("titulo_modulo");
		$data['icon']             = 'fa fa-ticket';
		$data['tabs']             = tabbed_tpl($this->config_tabs(),base_url(),2,$view_listado);
		
		$js['js'][]     = array('name' => 'clientes', 'dirname' => 'sucursales');
		$this->load_view($this->uri_view_principal(), $data, $js);
	}
	public function agregar(){
		// Listas
		$punto_venta_array  = array(
						'name' 	=> "lts_punto_venta"
						,'class' 	=> "requerido"
					);
		$list_punto_venta  = multi_dropdown_tpl($punto_venta_array);
		$dropArray = array(
					 'data'		=> $this->ent_model->get_entidades_default()
					,'value' 	=> 'id_administracion_entidad'
					,'text' 	=> array('clave_corta','entidad')
					,'name' 	=> "lts_entidades"
					,'class' 	=> "requerido"
				);
		$dropArray2 = array(
					 'data'		=> $this->sucur_model->db_get_data()
					,'value' 	=> 'id_sucursal'
					,'text' 	=> array('sucursal')
					,'name' 	=> "lts_sucursales"
					,'class' 	=> "requerido"
					,'event'    => array('event'       => 'onchange', 
										 'function'    => 'load_punto_venta', 
										 'params'      => array('this.value'), 
										 'params_type' => array(0))
					);
		$lts_entidades               = dropdown_tpl($dropArray); 
		$lts_sucursales              = dropdown_tpl($dropArray2); 
		$data_1['nombre_cliente']    = $this->lang_item("nombre_cliente");
		$data_1['apellido_paterno']  = $this->lang_item("apellido_paterno");
		$data_1['apellido_materno']  = $this->lang_item("apellido_materno");
		$data_1['rfc'] 			     = $this->lang_item("rfc_clientes");
		$data_1['razon_social']      = $this->lang_item("razon_social");
		$data_1['clave_corta'] 	     = $this->lang_item("clave_corta");
		$data_1['calle']  		     = $this->lang_item("calle");
		$data_1['num_int'] 		     = $this->lang_item("num_int");
		$data_1['num_ext'] 	 	     = $this->lang_item("num_ext");
		$data_1['colonia'] 		     = $this->lang_item("colonia");
		$data_1['municipio'] 	     = $this->lang_item("municipio");
		$data_1['entidad'] 		     = $this->lang_item("entidad");
		$data_1['sucursal'] 	     = $this->lang_item("sucursal");
		$data_1['lbl_punto_venta']   = $this->lang_item('lbl_punto_venta');
		$data_1['cp'] 			     = $this->lang_item("cp");
		$data_1['telefonos'] 	     = $this->lang_item("telefonos");
		$data_1['email'] 		     = $this->lang_item("email");
		$data_1['contacto'] 	     =$this->lang_item("contacto");
		$data_1['button_save']       = form_button(array('class'=>"btn btn-primary",'name' => 'save_cliente','onclick'=>'insert()' , 'content' => $this->lang_item("btn_guardar") ));
		$data_1['button_reset']      = form_button(array('class'=>"btn btn-primary",'name' => 'reset','value' => 'reset','onclick'=>'clean_formulario()','content' => $this->lang_item("btn_limpiar")));
		$data_1['list_punto_venta']  = $list_punto_venta;
		$data_1['dropdown_entidad']  = $lts_entidades;
		$data_1['dropdown_sucursal'] = $lts_sucursales;

		if($this->ajax_post(false)){
			echo json_encode($this->load_view_unique($this->uri_modulo.$this->uri_submodulo.'clientes_save', $data_1, true));
		}else{
			return $this->load_view_unique($this->uri_modulo.$this->uri_submodulo.'clientes_save', $data_1, true);
		}
	}

	public function load_punto_venta(){
		$id_sucursal = $this->ajax_post('id_sucursal');
		$punto_venta_array  = array(
						 'data'		=> $this->pventa->get_punto_venta_x_sucursal($id_sucursal)
						,'value' 	=> 'id_sucursales_punto_venta'
						,'text' 	=> array('clave_corta','punto_venta')
						,'name' 	=> "lts_punto_venta"
						,'class' 	=> "requerido"
					);
		$list_punto_venta  = multi_dropdown_tpl($punto_venta_array);
		echo json_encode($list_punto_venta);
	}
	public function listado($offset = 0){
		$data_tab_2  = "";
		$filtro      = ($this->ajax_post('filtro')) ? $this->ajax_post('filtro') : "";
		$uri_view    = $this->uri_modulo.'listado';
		$limit       = 10;
		$uri_segment = $this->uri_segment(); 

		$lts_content =$this->clientes_model->consulta_clientes($limit,$offset,$filtro);
		$total_rows  = count($this->clientes_model->consulta_clientes($limit, $offset, $filtro, false));
		$url         = base_url($this->uri_modulo.$this->uri_submodulo.'listado');

		$paginador   = $this->pagination_bootstrap->paginator_generate($total_rows, $url, $limit, $uri_segment, array('evento_link' => 'onclick', 'function_js' => 'load_content', 'params_js'=>'1'));

		if($total_rows>0){
			foreach ($lts_content as $value) {
				$atrr = array(
								'href' => '#',
							  	'onclick' => 'detalle('.$value['id_ventas_clientes'].')'
						);
				$clientes = $this->clientes_model->sucursales_cliente_venta($value['id_ventas_clientes']);
				$eliminar 	= '<span style="color:red;" id="ico-eliminar_'.$value['id_ventas_clientes'].'" class="ico_eliminar fa fa-times" onclick="confirm_delete('.$value['id_ventas_clientes'].')" title="'.$this->lang_item("lbl_eliminar").'"></span>';
				$btn_acciones['eliminar'] = ($clientes[0]['num_clientes'] == 0)?$eliminar:'<span style="color:gray;" id="ico-eliminar_'.$value['id_ventas_clientes'].'" class="ico_eliminar fa fa-times" title="'.$this->lang_item("lbl_eliminar").'"></span>';
				$acciones = implode('&nbsp;&nbsp;&nbsp;',$btn_acciones);
				$tbl_data[] = array('id'              => $value['nombre'],
									'nombre_cliente'  => tool_tips_tpl($value['nombre'].' '.$value['paterno'].' '.$value['materno'], $this->lang_item("tool_tip"), 'right' , $atrr),
									'razon_social'    => $value['razon_social'],
									'clave_corta'     => $value['cv_cliente'],
									'rfc'  			  => $value['rfc'],
									'telefonos'       => $value['telefonos'],
									'id_entidad'      => $value['entidad'],
									'id_sucursal'     => $value['sucursal'],
									'acciones'        => $acciones);
			}

			$tbl_plantilla = set_table_tpl();
		
			$this->table->set_heading(	$this->lang_item("nombre_cliente"),
										$this->lang_item("nombre_cliente"),
										$this->lang_item("razon_social"),
										$this->lang_item("clave_corta"),
										$this->lang_item("rfc_clientes"),
										$this->lang_item("telefonos"),
										$this->lang_item("entidad"),
										$this->lang_item("sucursal"),
										$this->lang_item("acciones"));
			$this->table->set_template($tbl_plantilla);
			$tabla = $this->table->generate($tbl_data);
			$buttonTPL = array( 	'text'       => array( $this->lang_item('btn_import_xlsx'), $this->lang_item("btn_xlsx")), 
									/*'id'         => array('upload_file'),
									'type'       => array('file'),*/
									'event'      => array(array('event'       => 'onclick', 
																'function'    => 'upload_file', 
																'params'      => ''
																)
													),
									'iconsweets' => array('fa fa-cloud-upload', 'fa fa-file-excel-o'),
									'href'       => array('', base_url($this->uri_modulo.$this->uri_submodulo.'export_xlsx?filtro='.base64_encode($filtro)))
							);
		}else{
			$msg   = $this->lang_item("msg_query_null");
			$tabla = alertas_tpl('', $msg ,false);
			$buttonTPL = "";
		}
		

		$data_tab_2['filtro']    = ($filtro!="") ? sprintf($this->lang_item("msg_query_search"),$total_rows , $filtro) : "";
		$data_tab_2['export']    = button_tpl($buttonTPL);
		$data_tab_2['tabla']     = $tabla;
		$data_tab_2['paginador'] = $paginador;
		$data_tab_2['item_info'] = $this->pagination_bootstrap->showing_items($limit, $offset, $total_rows);

		if($this->ajax_post(false)){
			echo json_encode($this->load_view_unique($uri_view,$data_tab_2,true));
		}else{
			return $this->load_view_unique($uri_view , $data_tab_2, true);
		}
	}
	public function insert(){
		$objData = $this->ajax_post('objData');

		if($objData['incomplete']>0){
			$msg = $this->lang_item("msg_campos_obligatorios",false);
			echo json_encode( array( 'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)));
		}else{
			if($this->validate_email($objData['email'])){
				$data_insert = array('nombre' 		  => $objData['nombre'],
									 'paterno'	  	  => $objData['paterno'],
									 'materno'	  	  => $objData['materno'],
									 'razon_social'	  => $objData['razon_social'],
									 'clave_corta'	  => $objData['clave_corta'],
									 'rfc'	          => $objData['rfc'],
									 'calle'	      => $objData['calle'],
									 'num_int'		  => $objData['num_int'],
									 'num_ext'		  => $objData['num_ext'],
									 'colonia' 		  => $objData['colonia'],
									 'municipio' 	  => $objData['municipio'],
									 'id_entidad' 	  => $objData['lts_entidades'],
									 'id_sucursal' 	  => $objData['lts_sucursales'],
									 'cp' 			  => $objData['cp'],
									 'telefonos' 	  => $objData['telefonos'],
									 'email' 		  => $objData['email'],
									 'timestamp'  	  => $this->timestamp(),
									 'id_usuario'     => $this->session->userdata('id_usuario'));

				$insert_cliente = $this->clientes_model->insert_cliente($data_insert);

				$arr_pventa  = explode(',',$objData['lts_punto_venta']);
				
				if(!empty($arr_pventa)){
					$sqlData = array();
					foreach ($arr_pventa as $key => $value){
						$data_insert = array(
							 'id_cliente'	  => $insert_cliente
							,'id_punto_venta' => $value
							,'timestamp'  	  => $this->timestamp()
							,'id_usuario'     => $this->session->userdata('id_usuario'));

						$insert = $this->clientes_model->insert_cliente_venta($data_insert);		
					}
				}

				if($insert){
					$msg = $this->lang_item("msg_insert_success",false);
					echo json_encode(array(  'success'=>'true', 'mensaje' => $msg));
				}else{
					$msg = $this->lang_item("msg_err_clv",false);
					echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('', $msg ,false)));
				}
			}else{
				$msg = $this->lang_item("invalid_email",false);
				echo json_encode( array( 'success'=>'false', 'mensaje' =>alertas_tpl('', $msg ,false)));
			}
		}
	}
	public function detalle(){
		$id_cliente       = $this->ajax_post('id_cliente');
		$detalle  = $this->clientes_model->get_cliente_unico($id_cliente);
		//print_debug($detalle);
		$uri_view   	  = $this->uri_modulo.$this->uri_submodulo.'cliente_edit';
		// Listas
		$dropArray = array(
					 'data'		 => $this->ent_model->get_entidades_default()
					 ,'selected' => $detalle[0]['id_entidad']
					,'value' 	 => 'id_administracion_entidad'
					,'text' 	 => array('clave_corta','entidad')
					,'name' 	 => "lts_entidades"
					,'class' 	 => "requerido"
				);
        $dropArray2 = array(
					 'data'		=> $this->sucur_model->db_get_data()
					 ,'selected' => $detalle[0]['id_sucursal']
					,'value' 	=> 'id_sucursal'
					,'text' 	=> array('sucursal')
					,'name' 	=> "lts_sucursales"
					,'class' 	=> "requerido"
					,'event'    => array('event'       => 'onchange', 
										 'function'    => 'load_punto_venta', 
										 'params'      => array('this.value'), 
										 'params_type' => array(0))
					);
        foreach ($detalle as $key => $value) {
        	$punto_venta[] = $value['id_sucursales_punto_venta'];
        }
		$punto_venta_array  = array(
						 'data'		=> $this->pventa->get_punto_venta_x_sucursal($detalle[0]['id_sucursal'])
						,'value' 	=> 'id_sucursales_punto_venta'
						,'text' 	=> array('clave_corta','punto_venta')
						,'name' 	=> "lts_punto_venta"
						,'class' 	=> "requerido"
						,'selected' => $punto_venta
					);
		$list_punto_venta  = multi_dropdown_tpl($punto_venta_array);		
       	$lts_entidades  = dropdown_tpl($dropArray);
       	$lts_sucursal   = dropdown_tpl($dropArray2);

		$data_tab_3['nombre_cliente']  		   = $this->lang_item("nombre_cliente");
		$data_tab_3['apellido_paterno']		   = $this->lang_item("apellido_paterno");
		$data_tab_3['apellido_materno']		   = $this->lang_item("apellido_materno");
		$data_tab_3['razon_social']    		   = $this->lang_item("razon_social");
		$data_tab_3['clave_corta'] 	   		   = $this->lang_item("clave_corta");
		$data_tab_3['rfc']             		   = $this->lang_item("rfc");
		$data_tab_3['calle'] 	       		   = $this->lang_item("calle");
		$data_tab_3['num_int']  	   		   = $this->lang_item("num_int");
		$data_tab_3['num_ext'] 	 	   		   = $this->lang_item("num_ext");
		$data_tab_3['colonia'] 		   		   = $this->lang_item("colonia");
		$data_tab_3['municipio'] 	   		   = $this->lang_item("municipio");
		$data_tab_3['entidad'] 		   		   = $this->lang_item("entidad");
		$data_tab_3['sucursal'] 	   		   = $this->lang_item("sucursal");
		$data_tab_3['lbl_punto_venta']         = $this->lang_item('lbl_punto_venta');
		$data_tab_3['cp'] 			   		   = $this->lang_item("cp");
		$data_tab_3['telefonos'] 	   		   = $this->lang_item("telefonos");
		$data_tab_3['email'] 		   		   = $this->lang_item("email");
		$data_tab_3['timestamp'] 	   		   = $this->lang_item("fecha_registro");
		$data_tab_3['lbl_ultima_modificacion'] = $this->lang_item('lbl_ultima_modificacion', false);
		$data_tab_3['button_save']     		   = form_button(array('class'=>"btn btn-primary",'name' => 'update_cliente','onclick'=>'update()' , 'content' => $this->lang_item("btn_guardar") ));
		//DATA
		$data_tab_3['list_punto_venta']        = $list_punto_venta;
		$data_tab_3['id_cliente']    		   = $detalle[0]['id_ventas_clientes'];
		$data_tab_3['cliente_value'] 		   = $detalle[0]['nombre'];
		$data_tab_3['paterno_value'] 		   = $detalle[0]['paterno'];
		$data_tab_3['materno_value'] 		   = $detalle[0]['materno'];
		$data_tab_3['rs_value']      		   = $detalle[0]['razon_social'];
		$data_tab_3['clave_value']   		   = $detalle[0]['cv_cliente'];
		$data_tab_3['rfc_value']     		   = $detalle[0]['rfc'];
		$data_tab_3['calle_value']   		   = $detalle[0]['calle'];
		$data_tab_3['num_int_value'] 		   = $detalle[0]['num_int'];
		$data_tab_3['num_ext_value'] 		   = $detalle[0]['num_ext'];
		$data_tab_3['colonia_value'] 		   = $detalle[0]['colonia'];
		$data_tab_3['municipio_value'] 		   = $detalle[0]['municipio'];
		$data_tab_3['dropdown_entidad']   	   = $lts_entidades;
		$data_tab_3['dropdown_sucursal']   	   = $lts_sucursal;
		$data_tab_3['cp_value']    	   		   = $detalle[0]['cp'];
		$data_tab_3['telefonos_value'] 		   = $detalle[0]['telefonos'];
		$data_tab_3['val_email']           	   = $detalle[0]['email'];
		$data_tab_3['timestamp_value'] 		   = $detalle[0]['timestamp'];

		$this->load_database('global_system');
        $this->load->model('users_model');
    	
    	$usuario_registro                  = $this->users_model->search_user_for_id($detalle[0]['id_usuario']);
    	$usuario_name 				       = text_format_tpl($usuario_registro[0]['name'],"u");
    	$data_tab_3['val_usuarios_registro']  = $usuario_name ;

    	if($detalle[0]['edit_id_usuario']){
        	$usuario_registro           = $this->users_model->search_user_for_id($detalle[0]['edit_id_usuario']);
        	$usuario_name 				= text_format_tpl($usuario_registro[0]['name'],"u");
        	$data_tab_3['val_ultima_modificacion']= sprintf($this->lang_item('val_ultima_modificacion', false), $this->timestamp_complete($detalle[0]['edit_timestamp']), $usuario_name);
    	}else{
    		$usuario_name = '';
    		$data_tab_3['val_ultima_modificacion']= $this->lang_item('lbl_sin_modificacion', false);
    	}
    	$data_tab_3['registro_por']    	= $this->lang_item("registro_por",false);
      	$data_tab_3['usuario_registro']	= $usuario_name;

		echo json_encode( $this->load_view_unique($uri_view ,$data_tab_3, true));
	}
	public function update(){
		$objData  	= $this->ajax_post('objData');
		if($objData['incomplete']>0){
			$msg = $this->lang_item("msg_campos_obligatorios",false);
			echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)));
		}else{
			if($this->validate_email($objData['email'])){

				$id_cliente  = $objData['id_cliente'];
				$data_insert = array('nombre'      => $objData['nombre'],
								 'paterno'         => $objData['paterno'],
								 'materno'         => $objData['materno'],
								 'razon_social'    => $objData['razon_social'],
								 'clave_corta'     => $objData['clave_corta'],
								 'rfc'             => $objData['rfc'],
								 'calle'           => $objData['calle'],
								 'num_int'         => $objData['num_int'],
								 'num_ext'         => $objData['num_ext'],
								 'colonia'         => $objData['colonia'],
								 'municipio'       => $objData['municipio'],
								 'id_entidad'      => $objData['lts_entidades'],
								 'id_sucursal'     => $objData['lts_sucursales'],
								 'cp'              => $objData['cp'],
								 'telefonos'       => $objData['telefonos'],
								 'email'           => $objData['email'],
								 'edit_timestamp'  => $this->timestamp(),
								 'edit_id_usuario' => $this->session->userdata('id_usuario'));

				$update = $this->clientes_model->update_cliente($data_insert,$id_cliente);

				$pventa_arr = explode(',',$objData['lts_punto_venta']);
				$pventa     = $this->clientes_model->delete_cliente_venta($id_cliente);
				if(!empty($pventa_arr)){
					$sqlData = array();
					foreach ($pventa_arr as $key => $value){
						$sqlData = array(
							 'id_cliente'       => $objData['id_cliente']
							,'id_punto_venta'   => $value
							,'id_usuario'       => $this->session->userdata('id_usuario')
							,'timestamp'        => $this->timestamp()
							);
						$insert = $this->clientes_model->db_update_data_cliente($sqlData);
					}
				}

				if($update){
					$msg = $this->lang_item("msg_update_success",false);
					echo json_encode(array(  'success'=>'true', 'mensaje' => $msg ));
				}else{
					$msg = $this->lang_item("msg_err_clv",false);
					echo json_encode( array( 'success'=>'false', 'mensaje' =>alertas_tpl('', $msg ,false)));
				}
			}else{
				$msg = $this->lang_item("invalid_email",false);
				echo json_encode( array( 'success'=>'false', 'mensaje' =>alertas_tpl('', $msg ,false)));
			}
		}
	}
	public function export_xlsx(){
		$filtro      = ($this->ajax_get('filtro')) ?  base64_decode($this->ajax_get('filtro') ): "";

		$lts_content = $this->clientes_model->consulta_clientes('', '', $filtro , false);
	
		if(count($lts_content)>0){
			foreach ($lts_content as $value) {
				$set_data[] = array(
									$value['id_ventas_clientes'],
									 $value['nombre'].' '.$value['paterno'].' '.$value['materno'],
									 $value['razon_social'],
									 $value['cv_cliente'],
									 $value['rfc'],
									 $value['calle'],
									 $value['num_int'],
									 $value['num_ext'],
									 $value['colonia'],
									 $value['municipio'],
									 $value['entidad'],
									 $value['sucursal'],
									 $value['cp'],
									 $value['telefonos'],
									 $value['email'],
									 $value['timestamp']);
			}
			$set_heading = array(
									$this->lang_item("ID"),
									$this->lang_item("nombre_cliente"),
									$this->lang_item("razon_social"),
									$this->lang_item("clave_corta"),
									$this->lang_item("rfc"),
									$this->lang_item("calle"),
									$this->lang_item("num_int"),
									$this->lang_item("num_ext"),
									$this->lang_item("colonia"),
									$this->lang_item("municipio"),
									$this->lang_item("entidad"),
									$this->lang_item("sucursal"),
									$this->lang_item("cp"),
									$this->lang_item("telefonos"),
									$this->lang_item("email"),
									$this->lang_item("fecha_registro"));
		}

		$params = array(	'title'  => $this->lang_item("lbl_excel"),
							'items'   => $set_data,
							'headers' => $set_heading
						);
		$this->excel->generate_xlsx($params);		
	}

	public function import_xlsx(){
		
		$config['upload_path']   = 'assets/tmp/';
		$config['allowed_types'] = 'xlsx';
		$config['max_size']	     = '100';
		
		$this->upload->initialize($config);

	
		if ( ! $this->upload->do_upload() ){

			$error = array('error' => $this->upload->display_errors());
			print_debug($error);

		}else{
			
			$data = array('upload_data' => $this->upload->data());
			print_debug($data);

		}
	}

	public function eliminar_registro(){
		$id_cliente = $this->ajax_post('id_cliente');
		$$clientes = $this->clientes_model->sucursales_cliente_venta($id_cliente);
		if($clientes[0]['num_clientes'] == 0){
			$sqlData = array(
					'id_ventas_clientes'      => $id_cliente
		            ,'activo'                 => 0
		            ,'edit_id_usuario'        => $this->session->userdata('id_usuario')
					,'edit_timestamp'         => $this->timestamp()
					);
			
			$update = $this->clientes_model->bd_delete_data($sqlData);
			if($update){
				$msg = $this->lang_item("msg_delete_success",false);
				echo json_encode(array('success'=>'true', 'mensaje' => $msg, 'id_cliente' => $id_cliente));
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