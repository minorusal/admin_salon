<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class usuarios extends Base_Controller { 

	private $modulo;
	private $submodulo;
	private $view_content;
	private $path;
	private $icon;
	private $offset, $limit_max;
	private $tab = array(), $tab_indice = array();

	public function __construct(){
		parent::__construct();
		$this->modulo 			= 'administracion';
		$this->submodulo		= 'control_de_usuarios';
		$this->seccion          = 'usuarios';
		$this->icon 			= 'fa fa-user'; 
		$this->path 			= $this->modulo.'/'.$this->seccion.'/';
		$this->view_agregar     = $this->modulo.'/'.$this->seccion.'/'.$this->seccion.'_agregar';
		$this->view_detalle     = $this->modulo.'/'.$this->seccion.'/'.$this->seccion.'_detalle';
		$this->view_content 	= 'content';
		$this->limit_max		= 10;
		$this->offset			= 0;

		$this->tab_indice 		= array(
									 'agregar'
									,'listado'
									,'detalle'
								);
		for($i=0; $i<=count($this->tab_indice)-1; $i++){
			$this->tab[$this->tab_indice[$i]] = $this->tab_indice[$i];
		}
		$this->load->model('users_model','db_model');
		$this->load->model('sucursales/listado_sucursales_model','sucursales');
		$this->load->model($this->modulo.'/perfiles_model','perfiles');
		$this->load->model($this->modulo.'/areas_model','areas');
		$this->load->model($this->modulo.'/puestos_model','puestos');
		$this->lang->load($this->modulo.'/'.$this->seccion,"es_ES");
	}
	public function config_tabs(){
		for($i=1; $i<=count($this->tab); $i++){
			${'tab_'.$i} = $this->tab [$this->tab_indice[$i-1]];
		}
		$path  	= $this->path;
		$pagina =(is_numeric($this->uri_segment_end()) ? $this->uri_segment_end() : "");
		
		$config_tab['names']    = array(
										 $this->lang_item($tab_1)
										,$this->lang_item($tab_2)
										,$this->lang_item($tab_3)
								); 
		$config_tab['links']    = array(
										 $path.$tab_1 
										,$path.$tab_2.'/'.$pagina
										,$tab_3
								); 
		$config_tab['action']   = array(
										 'load_content'
										,'load_content'
										,''
								);
		$config_tab['attr']     = array('','', array('style' => 'display:none'));
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
		$data['titulo_seccion']   = $this->lang_item("seccion");
		$data['titulo_submodulo'] = $this->lang_item("submodulo");
		$data['icon']             = $this->icon;
		$data['tabs']             = tabbed_tpl($this->config_tabs(),base_url(),$tabl_inicial,$contenidos_tab);	
		$js['js'][]               = array('name' => $this->seccion, 'dirname' => $this->modulo);
		$this->load_view($this->uri_view_principal(), $data, $js);
	}
	public function listado($offset = 0){
		$seccion 		= '';
		$filtro         = ($this->ajax_post('filtro')) ? $this->ajax_post('filtro') : "";
		$accion 		= $this->tab['listado'];
		$tab_detalle	= $this->tab['detalle'];
		$limit 			= $this->limit_max;
		$uri_view 		= $this->modulo.'/'.$accion;
		$url_link 		= $this->path.$seccion.$accion;		
		$sqlData = array(
						'user'          => $this->session->userdata('id_usuario')
						,'buscar'      	=> $filtro
						,'offset' 		=> $offset
						,'limit'      	=>  $limit
						,'aplicar_limit'=> true
					);
		$uri_segment  = $this->uri_segment(); 
		$total_rows	  = count($this->db_model->get_users($sqlData));
		$list_content = $this->db_model->get_users($sqlData);
		
		$url          = base_url($url_link);
		$paginador    = $this->pagination_bootstrap->paginator_generate($total_rows, $url, $limit, $uri_segment, array('evento_link' => 'onclick', 'function_js' => 'load_content', 'params_js'=>'1'));
		if($total_rows>0){
			foreach ($list_content as $value) {
				// Evento de enlace
				$atrr = array(
								'href'    => '#',
							  	'onclick' => $tab_detalle.'('.$value['id_personal'].')'
						);
				$nombre = "'".$value['name']."'";
				$mail   = "'".$value['mail']."'";
				$btn_acciones['ficha'] 	= '<span style="color:blue;"   class="ico_acciones ico_articulos fa fa-user" onclick="asignar_perfil('.$value['id_personal'].','.$value['id_usuario'].','.$value['id_perfil'].')" title="'.$this->lang_item("lbl_asignar_perfil").'"></span>';
				$btn_acciones['email'] 	= '<span id="mail_'.$value['id_personal'].'" 
				                           style="color:green;"  
				                           class="ico_acciones ico_articulos fa fa-envelope" 
				                           onclick="enviar_email('.$value['id_personal'].','.$value['id_usuario'].','.$value['id_perfil'].','.$nombre.','.$mail.')" 
				                           title="'.$this->lang_item("lbl_enviar_email").'"></span>';
				$btn_acciones['delete']	= '<span style="color:red;" id="ico-eliminar_'.$value['id_usuario'].'" class="ico_eliminar fa fa-times" onclick="confirm_delete('.$value['id_usuario'].')" title="Eliminar"></span>';
				$btn_acciones['loader']	= '<span id="loader_'.$value['id_personal'].'"></span>';
				//,'.$value['name'].','.$value['mail'].'
				$acciones = implode('&nbsp;&nbsp;&nbsp;&nbsp;',$btn_acciones);
				// Datos para tabla
				$tbl_data[] = array('id'               => $value['id_usuario'],
									'avatar'           => tool_tips_tpl(($value['avatar'] == '') ? return_avatar('users') : return_avatar('users', $value['avatar']), $this->lang_item("tool_tip"), 'right' , $atrr),
									'nombre'           => tool_tips_tpl($value['name'], $this->lang_item("tool_tip"), 'right' , $atrr),
									'nombre_de_usuario'=> $value['user'],
									'area'             => $value['area'],
									'puesto'           => $value['puesto'],
									'acciones'		   => $acciones
									);
			}

			$tbl_plantilla = set_table_tpl();
			
			$this->table->set_heading(	$this->lang_item("id"),
										$this->lang_item("foto"),
										$this->lang_item("lbl_nombre"),
										$this->lang_item("lbl_user"),
										$this->lang_item("lbl_area"),
										$this->lang_item("lbl_puesto"),
										$this->lang_item("lbl_acciones")
									);

			$buttonTPL = array( 'text'       => $this->lang_item("btn_xlsx"), 
								'iconsweets' => 'fa fa-file-excel-o',
								'href'       => base_url($this->path.'export_xlsx?filtro='.base64_encode($filtro))
								);

			$this->table->set_template($tbl_plantilla);
			$tabla = $this->table->generate($tbl_data);
		}else{
			$buttonTPL = "";
			$msg       = $this->lang_item("msg_query_null");
			$tabla     = alertas_tpl('', $msg ,false);
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
	public function asignar_perfil(){
		$id_perfil   = $this->ajax_post('id_perfil');
		$id_personal = $this->ajax_post('id_personal');
		$id_usuario  = $this->ajax_post('id_usuario');
		$perfiles    = $this->db_model->search_data_perfil($id_personal);
		$detalle     = $this->db_model->get_user($id_usuario);
		
		$tbl_plantilla = set_table_tpl();
		$info_user = array(	
								'lbl_nombre_usuario' => $this->lang_item("lbl_nombre")
								,'txt_nombre_usuario' => ($detalle[0]['name'])?$detalle[0]['name']:$this->lang_item("lbl_x_asignar")
								,'lbl_email'          => $this->lang_item("lbl_email")
								,'txt_email'          => $detalle[0]['mail']
								,'lbl_telefono'		  => $this->lang_item("lbl_telefono")
								,'txt_telefono'		  => $detalle[0]['telefono']
								,'lbl_user'           => $this->lang_item("lbl_user")
								,'txt_user'			  => $detalle[0]['user']
								,'lbl_area'		      => $this->lang_item("lbl_area")
								,'txt_area'			  => $detalle[0]['area']
								,'lbl_puesto'		  => $this->lang_item("lbl_puesto")
								,'txt_puesto'		  => $detalle[0]['puesto']
								
							);
		
		$plantilla = array ( 'table_open'  => '<table   class="table table-bordered table-invoice">' );
		$this->table->set_template($plantilla);
		$info_user = $this->table->make_columns($info_user, 2);
		$tabla = $this->table->generate($info_user);

		if($perfiles){
			$boton         = array(
						'class'   => 'btn btn-primary'
					   ,'name'    => 'agregar_perfil'
					   ,'onclick' => 'agregar_perfil()'
					   ,'content' => $this->lang_item("btn_guardar"));
			$btn_save      = form_button($boton);
			$perfil_array  = array(
								 'data'		=> $perfiles
								,'value' 	=> 'id_perfil'
								,'text' 	=> array('clave_corta','perfil')
								,'name'  	=> "lts_perfiles"
								,'class' 	=> "requerido"
								,'selected' => $id_perfil
								,'event'    => array('event'       => 'onchange',
							   						 'function'    => 'load_tree_view_perfil_usuario',
							   						 'params'      => array($id_personal,'this.value'),
							   						 'params_type' => array(0,0)
			   										));
		    $list_perfiles =  dropdown_tpl($perfil_array);
		    $arr_sucursales = explode(',',$detalle[0]['id_sucursales']);
			foreach ($arr_sucursales as $key => $value) {
				$sucursales[] = $value;
			}
		    $sucursales_array  = array(
						 'data'		=> $this->sucursales->get_sucursales_usuarios()
						,'value' 	=> 'id_sucursal'
						,'text' 	=> array('clave_corta','sucursal')
						,'name' 	=> "lts_sucursales"
						//,'class' 	=> "requerido"
						,'selected' => $sucursales
				);
			$sucursales        = multi_dropdown_tpl($sucursales_array);
			$tabData['list_sucursales'] = $sucursales;
			$tabData['id_usuario']     = $detalle[0]['id_usuario'];
		    $tabData['tabla']          = $tabla;
		    $tabData['lbl_perfiles']   = $this->lang_item("lbl_perfiles");
		    $tabData['lbl_sucursales'] = $this->lang_item("lbl_sucursales");
		    $tabData['id_personal']    = $id_personal;
		    $tabData['list_perfiles']  = $list_perfiles;
		    $tabData['tree_view']      = $this->treeview_perfiles_usuarios($id_personal,$id_perfil);
		    $tabData['button_save']    = $btn_save;
			$uri_view = $this->modulo.'/'.$this->seccion.'/ficha_asignar_perfiles';
			echo json_encode( $this->load_view_unique($uri_view ,$tabData, true));
		}
	}
	public function insert_perfiles(){
		$objData  	= $this->ajax_post('objData');
		if($objData['incomplete']>0){
			$msg = $this->lang_item("msg_campos_obligatorios",false);
			echo json_encode( array( 'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)));
		}else{
			$sqlData = array(
					 'id_personal'      => $objData['id_personal']
					,'id_sucursales'    => $objData['lts_sucursales']
					,'edit_id_usuario'  => $this->session->userdata('id_usuario')
					,'edit_timestamp'   => $this->timestamp()
				);
			$insert = $this->db_model->insert_sucursales_usuario($sqlData);
			$sqlData = array(
							 'id_personal'      => $objData['id_personal']
							,'id_perfil'        => $objData['lts_perfiles']
							,'id_sucursales'    => $objData['lts_sucursales']
							,'id_menu_n1'       => (isset($objData['nivel_1'])) ? $objData['nivel_1']:''
							,'id_menu_n2'       => (isset($objData['nivel_2'])) ? $objData['nivel_2']:''
							,'id_menu_n3'       => (isset($objData['nivel_3'])) ? $objData['nivel_3']:''
							,'edit_id_usuario'  => $this->session->userdata('id_usuario')
							,'edit_timestamp'   => $this->timestamp()
							);
			$insert = $this->db_model->insert_perfiles_usuario($sqlData);
			if($insert){
				$msg = $this->lang_item("msg_update_success",false);
				echo json_encode(array(  'success'=>'true', 'mensaje' => $msg ));
			}else{
				$msg = $this->lang_item("msg_err_clv",false);
				echo json_encode( array( 'success'=>'false', 'mensaje' =>alertas_tpl('', $msg ,false)));
			}
		}
	}
	public function detalle(){
		$id_personal = $this->ajax_post('id_personal');
		
		$boton = array(
						'class'   => 'btn btn-primary'
					   ,'name'    => 'actualizar'
					   ,'onclick' => 'actualizar()'
					   ,'content' => $this->lang_item("btn_guardar"));
		$btn_save   = form_button($boton);
		$tabData['id_personal']  = $id_personal;
		$tabData['lbl_nombre']   = $this->lang_item("lbl_nombre");
		$tabData['lbl_usuario']  = $this->lang_item("lbl_usuario");
		$tabData['lbl_paterno']  = $this->lang_item("lbl_paterno");
		$tabData['lbl_materno']  = $this->lang_item("lbl_materno");
		$tabData['lbl_telefono'] = $this->lang_item("lbl_telefono");
		$tabData['lbl_email']    = $this->lang_item("lbl_email");
		$tabData['lbl_sucursal'] = $this->lang_item("lbl_sucursal");
		$tabData['lbl_area']     = $this->lang_item("lbl_area");
		$tabData['lbl_puesto']   = $this->lang_item("lbl_puesto");
		$tabData['lbl_perfil']   = $this->lang_item("lbl_perfil");

		$detalle = $this->db_model->get_user_detalle($id_personal);
		
		foreach ($detalle as $value){
			$id_perfil[]  = $value['id_perfil'];
			$id_usuario[] = $value['id_usuario'];
		}
		
		$tabData['txt_nombre']    = $detalle[0]['nombre'];
		$tabData['txt_paterno']   = $detalle[0]['paterno'];
		$tabData['txt_materno']   = $detalle[0]['materno'];
		$tabData['txt_telefono']  = $detalle[0]['telefono'];
		$tabData['txt_email']     = $detalle[0]['mail'];
		$sqlData = array(
			 'buscar'      	 => ''
			,'offset' 		 => 0
			,'limit'      	 => 0
		);
		$sucursales_array     = array(
					 'data'     => $this->sucursales->db_get_data($sqlData)
					,'value' 	=> 'id_sucursal'
					,'text' 	=> array('sucursal')
					,'name' 	=> "lts_sucursales"
					,'class' 	=> "requerido"
					,'selected' => $detalle[0]['id_sucursal']
					);
		$sucursales            = dropdown_tpl($sucursales_array);
		$areas_array    = array(
								 'data'		=> $this->areas->db_get_data()
								,'value' 	=> 'id_administracion_areas'
								,'text' 	=> array('area')
								,'name' 	=> "lts_areas"
								,'class' 	=> "requerido"
								,'selected' => $detalle[0]['id_area']);
		$list_area      =  dropdown_tpl($areas_array);
		$tabData['dropdown_area']   = $list_area;
		$puestos_array      = array(
								 'data'		=> $this->puestos->db_get_data()
								,'value' 	=> 'id_administracion_puestos'
								,'text' 	=> array('puesto')
								,'name' 	=> "lts_puestos"
								,'class' 	=> "requerido"
								,'selected' => $detalle[0]['id_puesto']);
		$list_puesto            =  dropdown_tpl($puestos_array);
		$tabData['dropdown_puesto'] = $list_puesto;
		$perfiles_array  = array(
						 'data'		=> $this->perfiles->db_get_data()
						,'value' 	=> 'id_perfil'
						,'text' 	=> array('clave_corta','perfil')
						,'name' 	=> "lts_perfiles"
						,'class' 	=> "requerido"
						,'selected' => $id_perfil);
		$perfiles  = multi_dropdown_tpl($perfiles_array);
		$tabData['dropdown_perfil'] = $perfiles;
		$tabData['dropdown_sucursal'] = $sucursales;
		$tabData['lbl_ultima_modificacion'] = $this->lang_item('lbl_ultima_modificacion');
        $tabData['val_fecha_registro']      = $detalle[0]['timestamp'];
        $tabData['foto_img']          = ($detalle[0]['avatar']=='') ? return_avatar('users','', false, 100, 100,true) : return_avatar('users', $detalle[0]['avatar'], false,  100, 100,true);
		$tabData['lbl_fecha_registro']      = $this->lang_item('lbl_fecha_registro');
		$tabData['lbl_usuario_registro']    = $this->lang_item('lbl_usuario_registro');

		$usuario_registro                  = $this->users_model->search_user_for_id($id_personal);
	    $usuario_name	                   = text_format_tpl($usuario_registro[0]['name'],"u");
	    $tabData['val_usuarios_registro']  = $usuario_name;

	    if($detalle[0]['edit_id_usuario']){
	    	$usuario_registro = $this->users_model->search_user_for_id($detalle[0]['edit_id_usuario']);
			$usuario_name     = text_format_tpl($usuario_registro[0]['name'],"u");
			$tabData['val_ultima_modificacion'] = sprintf($this->lang_item('val_ultima_modificacion',false), $this->timestamp_complete($detalle[0]['edit_timestamp']), $usuario_name);
	    }else{
	    	$usuario_name = '';
			$tabData['val_ultima_modificacion'] = $this->lang_item('lbl_sin_modificacion', false);
	    }
	    $tabData['id_usuario']       = implode(',',$id_usuario);
	    $tabData['button_save']      = $btn_save;
		$tabData['registro_por']     = $this->lang_item('registro_por', false);
		$tabData['usuario_registro'] = $usuario_name;

		$uri_view = $this->view_detalle;
		echo json_encode($this->load_view_unique($uri_view,$tabData,true));
	}
	public function actualizar(){
		$objData  	= $this->ajax_post('objData');
		if($objData['incomplete']>0){
			$msg = $this->lang_item("msg_campos_obligatorios",false);
			echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)));
		}else{
			$search_email = $this->db_model->search_email_edit($objData['txt_email'],$objData['id_personal']);
			if($search_email){
				$msg = $this->lang_item("msg_mail_repetido",false);
				echo json_encode( array( 'success'=>'false', 'mensaje' => alertas_tpl('', $msg ,false)));

			}else{
				if($this->validate_email($objData['txt_email'])){
					$detalle      = $this->db_model->get_user_detalle($objData['id_personal']);
					$perfil_bd    = $this->db_model->get_user_perfil($objData['id_personal']);
				    $perfil_form  = $objData['registros']['id_perfil'];
				    $usuario_form = $objData['registros']['id_usuario'];
					
					$sqlData = array('id_personal'      => $objData['id_personal']
						            ,'activo'           => 0
						            ,'edit_id_usuario'  => $this->session->userdata('id_usuario')
									,'edit_timestamp'   => $this->timestamp());
					$desactiva = $this->db_model->desactiva_usuarios($sqlData);
					
					foreach($perfil_form as $key => $value){
						$perfil = array(
								'id_perfil' => $value
							);
						
						if(in_array($perfil,$perfil_bd)){

							$sqlData = array(
								     'id_perfil'       => $value
								    ,'id_personal'     => $objData['id_personal']
						            ,'activo'          => 1
						            ,'edit_id_usuario' => $this->session->userdata('id_usuario')
									,'edit_timestamp'  => $this->timestamp());
							$activa = $this->db_model->activa_usuarios($sqlData);

						}else{
							
							if($value){
								
								$sqlData = array(
									 'id_personal'     => $objData['id_personal']
									,'id_clave'        => $detalle[0]['id_clave']
									,'id_perfil'       => $value
									,'id_pais'	       => $this->session->userdata('id_pais')
									,'id_empresa'      => $this->session->userdata('id_empresa')
									,'id_sucursal'     => $objData['lts_sucursales']
									,'id_puesto'       => $detalle[0]['id_puesto']
									,'id_area'         => $detalle[0]['id_area']
									,'id_menu_n1'      => ''
									,'id_menu_n2'      => ''
									,'id_menu_n3'      => ''
									,'id_usuario_reg'  => $this->session->userdata('id_usuario')
									,'timestamp'       => $this->timestamp()
									);
								$insert_usuarios = $this->db_model->db_insert_usuarios($sqlData);
							}
						}
					}
					
					$sqlData = array(
						 'id_personal'      => $objData['id_personal']
						,'nombre'           => $objData['txt_nombre']
						,'paterno'          => $objData['txt_paterno']
						,'materno'          => $objData['txt_materno']
						,'telefono'         => $objData['txt_telefono']
						,'mail'		        => $objData['txt_email']
						,'edit_id_usuario'  => $this->session->userdata('id_usuario')
						,'edit_timestamp'   => $this->timestamp()
						);
					$insert = $this->db_model->db_update_personal($sqlData);

					$sqlData = array(
							 'id_personal'      => $objData['id_personal']
							,'id_area'          => $objData['lts_areas']
							,'id_puesto'        => $objData['lts_puestos']
							,'edit_id_usuario'  => $this->session->userdata('id_usuario')
							,'edit_timestamp'   => $this->timestamp()
							);
					$insert = $this->db_model->db_update_usuarios($sqlData);

					if($insert){
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
	}
	public function agregar(){
		$seccion 		= '';
		$uri_view   	= $this->view_agregar;
		$btn_save       = form_button(array('class'=>"btn btn-primary",'name' => 'save_usuario','onclick'=>'insert()' , 'content' => $this->lang_item("btn_guardar") ));
		$btn_reset      = form_button(array('class'=>"btn btn-primary",'name' => 'reset','value' => 'reset','onclick'=>'clean_formulario()','content' => $this->lang_item("btn_limpiar")));
		
		$areas_array      = array(
								 'data'		=> $this->areas->db_get_data()
								,'value' 	=> 'id_administracion_areas'
								,'text' 	=> array('area')
								,'name' 	=> "lts_areas"
								,'class' 	=> "requerido");
		$areas            =  dropdown_tpl($areas_array);
		$puestos_array      = array(
								 'data'		=> $this->puestos->db_get_data()
								,'value' 	=> 'id_administracion_puestos'
								,'text' 	=> array('puesto')
								,'name' 	=> "lts_puestos"
								,'class' 	=> "requerido");
		$puestos            =  dropdown_tpl($puestos_array);

		$perfiles_array  = array(
						 'data'		=> $this->perfiles->db_get_data()
						,'value' 	=> 'id_perfil'
						,'text' 	=> array('clave_corta','perfil')
						,'name' 	=> "lts_perfiles"
						,'class' 	=> "requerido"
						
					);
		$perfiles  = multi_dropdown_tpl($perfiles_array);
		$sqlData = array(
			 'buscar'      	 => ''
			,'offset' 		 => 0
			,'limit'      	 => 0
		);
		$sucursales_array     = array(
					 'data'     => $this->sucursales->db_get_data($sqlData)
					,'value' 	=> 'id_sucursal'
					,'text' 	=> array('sucursal')
					,'name' 	=> "lts_sucursales"
					,'class' 	=> "requerido"
					);
		$sucursales            = dropdown_tpl($sucursales_array);
		$tabData['base_url']            =  base_url();
		$tabData['lbl_nombre_usuario']  = $this->lang_item("lbl_nombre_usuario");
		$tabData['lbl_no_disponible']   = $this->lang_item("lbl_no_disponible");
		$tabData['lbl_nombre']          = $this->lang_item('lbl_nombre', false);
		$tabData['lbl_paterno']         = $this->lang_item('lbl_paterno', false);
		$tabData['lbl_materno']         = $this->lang_item('lbl_materno', false);
		$tabData['lbl_telefono']        = $this->lang_item('lbl_telefono', false);
		$tabData['lbl_email']           = $this->lang_item('lbl_email', false);
		$tabData['lbl_sucursal']        = $this->lang_item("lbl_sucursal");
		$tabData['lbl_area']            = $this->lang_item('lbl_area', false);
		$tabData['lbl_puesto']          = $this->lang_item('lbl_puesto', false);
		$tabData['lbl_perfil']          = $this->lang_item('lbl_perfil', false);
		$tabData['dropdown_sucursal']   = $sucursales;
		$tabData['dropdown_area']       = $areas;
		$tabData['dropdown_puesto']     = $puestos;
		$tabData['dropdown_perfil']     = $perfiles;
		$tabData['button_save']         = $btn_save;
		$tabData['button_reset']        = $btn_reset;
		$tabData['tree_view']           = '';
		
		

        if($this->ajax_post(false)){
				echo json_encode($this->load_view_unique($uri_view , $tabData, true));
		}else{
			return $this->load_view_unique($uri_view , $tabData, true);
		}
	}
	public function load_tree_view_perfil(){
		$id_perfil         = $this->ajax_post('id_perfil');
		$treeview_perfiles = $this->treeview_perfiles($id_perfil, true);
		echo json_encode($treeview_perfiles);
	}
	public function load_tree_view_perfil_usuario(){
		$id_perfil      = $this->ajax_post('id_perfil');
		$id_personal    = $this->ajax_post('id_personal');
		$treeview_perfiles = $this->treeview_perfiles_usuarios($id_personal,$id_perfil);
		$tabData['tree_perfiles']   = $treeview_perfiles;
		echo json_encode($tabData);
	}
	public function insert(){
		$objData  	= $this->ajax_post('objData');
		if($objData['incomplete']>0){
			$msg = $this->lang_item("msg_campos_obligatorios",false);
			echo json_encode( array( 'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)));
		}else{
			$search_email = $this->db_model->search_email($objData['txt_mail']);
			if($search_email){
				$msg = $this->lang_item("msg_mail_repetido",false);
				echo json_encode( array( 'success'=>'false', 'mensaje' => alertas_tpl('', $msg ,false)));
			}else{
				if($this->validate_email($objData['txt_mail'])){

					$sqlData = array(
						'nombre'       => $objData['txt_nombre']
						,'paterno'     => $objData['txt_paterno']
						,'materno'     => $objData['txt_materno']
						,'telefono'    => $objData['txt_telefono']
						,'mail'        => $objData['txt_mail']
						,'id_usuario'  => $this->session->userdata('id_usuario')
						,'timestamp'   => $this->timestamp());
					$insert_personal = $this->db_model->db_insert_personal($sqlData);
					$sqlData = array(
						 'user'       => ''
						,'pwd'        => ''
						,'token'      => $this->generate_token()
						,'id_usuario' => $this->session->userdata('id_usuario')
						,'timestamp'  => $this->timestamp());
					$insert_claves = $this->db_model->db_insert_claves($sqlData);

					$arr_perfil    = explode(',',$objData['lts_perfiles']);
					if(!empty($arr_perfil)){
						$sqlData = array();
						foreach ($arr_perfil as $key => $value){
							$sqlData = array(
								 'id_personal'     => $insert_personal
								,'id_clave'        => $insert_claves
								,'id_perfil'       => $value
								,'id_pais'	       => $this->session->userdata('id_pais')
								,'id_empresa'      => $this->session->userdata('id_empresa')
								,'id_sucursal'     => $objData['lts_sucursales']
								,'id_puesto'       => $objData['lts_puestos']
								,'id_area'         => $objData['lts_areas']
								,'id_menu_n1'      => ''
								,'id_menu_n2'      => ''
								,'id_menu_n3'      => ''
								,'id_usuario_reg'  => $this->session->userdata('id_usuario')
								,'timestamp'       => $this->timestamp()
								);
							$insert_usuarios = $this->db_model->db_insert_usuarios($sqlData);
						}
					}
					if($insert_claves){
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
	}
	public function export_xlsx($offset=0){
		$filtro      = ($this->ajax_get('filtro')) ?  base64_decode($this->ajax_get('filtro') ): "";
		$limit 		 = $this->limit_max;
		$sqlData     = array(
			 'buscar'      	=> $filtro
			,'offset' 		=> $offset
			,'limit'      	=> $limit
		);
		$lts_content = $this->db_model->get_users($sqlData);
		if(count($lts_content)>0){
			foreach ($lts_content as $value) {
				$set_data[] = array(
									 $value['name'],
									 $value['user'],
									 $value['perfil'],
									 $value['area'],
									 $value['puesto']
									 );
			}
			
			$set_heading = array(
									$this->lang_item("lbl_nombre"),
									$this->lang_item("lbl_user"),
									$this->lang_item("lbl_perfil"),
									$this->lang_item("lbl_area"),
									$this->lang_item("lbl_puesto")
									);
	
		}

		$params = array(	'title'   => $this->lang_item("lbl_excel"),
							'items'   => $set_data,
							'headers' => $set_heading
						);
		
		$this->excel->generate_xlsx($params);
	}
	public function find_string(){
		$string = $this->ajax_post('item');
		$campo = $this->ajax_post('nom');
		$detalle = $this->db_model->get_user_by_userName($string);
		if($detalle){
			echo json_encode(array('existe' => 0, 'string' => $string, 'campo' => $campo));
		}else{
			echo json_encode(array('existe' => 1, 'string' => '', 'campo' => $campo));
		}
	}
	public function enviar_mail(){
		$id_usuario = $this->ajax_post('id_usuario');
		$id_personal = $this->ajax_post('id_personal');
		$mail        = $this->ajax_post('mail');
		$nombre      = utf8_decode($this->ajax_post('name'));
		$token       = $this->generate_token();
		$asunto      = utf8_decode($this->lang_item("lbl_asunto"));

		$sqlData = array(
			 'id_usuario'      => $id_usuario
			,'user'            => ''
			,'pwd'             => ''
			,'token'           => $token
			,'edit_id_usuario' => $this->session->userdata('id_usuario')
			,'edit_timestamp'  => $this->timestamp());
		$insert_claves = $this->db_model->db_update_claves($sqlData);
		
		$url_image = base_url().'assets/images/';
		$detalle = $this->db_model->get_user($id_personal);
		
		$destinatarios[] = array(
					 'email'	=> $mail
					,'nombre'	=> $nombre
				);
		// Template
		$htmlData = array(

			 'titulo'    => 'Bienvenido: '
			,'nombre'    => $nombre
			,'link' 	 => base_url().'sign_up?token='
			,'key' 		 => $token
			,'url_image' => $url_image
			,'token'     => base_url().'sign_up?token='.$token);
		$htmlTPL = $this->load_view_unique('mail/mail_userkey' , $htmlData, true);
		// Imagenes del correo
		$imagenes[] = array('ruta' => 'assets/images', 'alias' => 'logo', 'file' => 'logo.png', 'encode' => 'base64', 'mime' => 'image/png' );
		$imagenes[] = array('ruta' => 'assets/images', 'alias' => 'banner', 'file' => 'banner_azul.png', 'encode' => 'base64', 'mime' => 'image/png' );
		$imagenes[] = array('ruta' => 'assets/images', 'alias' => 'footer', 'file' => 'mail_footer.png', 'encode' => 'base64', 'mime' => 'image/png' );
		// Create ArrayData
		$tplData = array(
			 'body' 				=> $htmlTPL
			,'tipo' 				=> 'html' #html | text
			,'destinatarios' 		=> $destinatarios
			,'asunto' 				=> $asunto
			,'imagenes'  			=> $imagenes
		);

		if($resultado = $this->mailsmtp->send($tplData)){
			echo json_encode($resultado);
		}else{
			echo json_encode( array('success' => false, 'msj' => "err") ) ;
		}
	}
	
	public function eliminar_registro(){
		$id_usuario = $this->ajax_post('id_usuario');
		
		
		
		$update = $this->db_model->bd_delete_user($id_usuario);
		if($update){
			$msg = $this->lang_item("msg_delete_success",false);
			echo json_encode(array('success'=>'true', 'mensaje' => $msg, 'id_perfil' => $id_perfil));
		}else{
			$msg = $this->lang_item("msg_err_delete",false);
			echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('', $msg ,false)));
		}
		
	}
}