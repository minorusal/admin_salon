<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class valores_nutricionales extends Base_Controller{
	private $modulo;
	private $submodulo;
	private $seccion;
	private $view_content;
	private $path;
	private $icon;

	private $offset, $limit_max;
	private $tab, $tab1, $tab2;

	public function __construct(){
		parent::__construct();
		$this->modulo 			= 'nutricion';
		$this->submodulo		= 'valores_nutricionales';
		$this->icon 			= 'fa fa-sort-amount-asc'; 
		$this->path 			= $this->modulo.'/'.$this->submodulo.'/'; 
		$this->view_content 	= 'content';
		$this->limit_max		= 10;
		$this->offset			= 0;
		// Tabs
		$this->tab1 			= 'listado';
		$this->tab2 			= 'detalle';
		
		// DB Model
		$this->load->model($this->modulo.'/'.$this->submodulo.'_model','db_model');
		$this->load->model('compras/catalogos_model','db_model_articulos');
		// Diccionario
		$this->lang->load($this->modulo.'/'.$this->submodulo,"es_ES");
	}

	public function config_tabs(){
		$tab_1 	= $this->tab1;
		$tab_2 	= $this->tab2;
		
		$path  	= $this->path;
		$pagina =(is_numeric($this->uri_segment_end()) ? $this->uri_segment_end() : "");
		// Nombre de Tabs
		$config_tab['names']    = array(
										 $this->lang_item($tab_1) 
										,$this->lang_item($tab_2) 	
								); 
		// Href de tabs
		$config_tab['links']    = array(      
										$path.$tab_1.'/'.$pagina #listado
										,$tab_2                  #detalle
								); 
		// Accion de tabs
		$config_tab['action']   = array(

										 'load_content'

										,''
								);
		// Atributos 
		$config_tab['attr']     = array('', array('style' => 'display:none'));
		return $config_tab;
	}

	private function uri_view_principal(){
		return $this->modulo.'/'.$this->view_content;
	}

	public function index(){
		$tabl_inicial 			  = 1;
		$view_listado    		  = $this->listado();	
		$contenidos_tab           = $view_listado;
		$data['titulo_seccion']   = $this->lang_item($this->submodulo);
		$data['titulo_submodulo'] = $this->lang_item("titulo_submodulo");
		$data['icon']             = $this->icon;
		$data['tabs']             = tabbed_tpl($this->config_tabs(),base_url(),$tabl_inicial,$contenidos_tab);	
		
		$js['js'][]  = array('name' => $this->submodulo, 'dirname' => $this->modulo);
		$this->load_view($this->uri_view_principal(), $data, $js);
	}

	public function listado($offset=0){
		// Crea tabla con listado de elementos capturados 
		$seccion 		= '/listado';
		$tab_detalle	= $this->tab2;	
		$limit 			= $this->limit_max;
		$uri_view 		= $this->modulo.$seccion;
		$url_link 		= $this->path.'listado';
		$filtro      	= ($this->ajax_post('filtro')) ? $this->ajax_post('filtro') : "";
		$sqlData = array(
			 'buscar'      	=> $filtro
			,'offset' 		=> $offset
			,'limit'      	=> $limit
		);
		$uri_segment  = $this->uri_segment(); 
		$total_rows	  = count($this->db_model_articulos->get_articulos($sqlData['limit'],$sqlData['offset'],$sqlData['buscar'],false));
		$sqlData['aplicar_limit'] = true;
		$list_content = $this->db_model_articulos->get_articulos($sqlData['limit'],$sqlData['offset'],$sqlData['buscar'],$sqlData['aplicar_limit']);
		$url          = base_url($url_link);
		$arreglo = array($total_rows, $url, $limit, $uri_segment);
		
		$paginador = $this->pagination_bootstrap->paginator_generate($total_rows, $url, $limit, $uri_segment, array('evento_link' => 'onclick', 'function_js' => 'load_content', 'params_js'=>'0'));
		
		if($total_rows){
			foreach ($list_content as $value){
				// Evento de enlace
				$atrr = array(
								'href' => '#',
							  	'onclick' => $tab_detalle.'('.$value['id_compras_articulo'].')'
						);
				$valor_nutricional = $this->db_model->db_get_compras_articulos_valores($value['id_compras_articulo']);
				$eliminar 	= '<span style="color:red;" id="ico-eliminar_1" class="ico_eliminar fa fa-times" onclick="confirm_delete('.$value['id_compras_articulo'].')" title="'.$this->lang_item("lbl_eliminar").'"></span>';
				$btn_acciones['eliminar'] = (!$valor_nutricional[0]['id_valor_nutricional'])?$eliminar:'<span style="color:gray;" id="ico-eliminar_1" class="ico_eliminar fa fa-times" title="'.$this->lang_item("lbl_eliminar").'"></span>';
				$acciones = implode('&nbsp;&nbsp;&nbsp;',$btn_acciones);
				// Datos para tabla
				$tbl_data[] = array('id'          => $value['id_compras_articulo'],
									'avatar'         => tool_tips_tpl(($value['avatar'] == '') ? return_avatar('articulos') : return_avatar('articulos', $value['avatar']), $this->lang_item("tool_tip"), 'right' , $atrr),
									'articulo'    => tool_tips_tpl($value['articulo'], $this->lang_item("tool_tip"), 'right' , $atrr),
									'clave_corta' => $value['clave_corta'],
									'descripcion' => $value['descripcion']/*,
									'acciones'    => $acciones*/
									);
			}
			// Plantilla
			$tbl_plantilla = set_table_tpl();
			// Titulos de tabla
			$this->table->set_heading(	$this->lang_item("lbl_id"),
										$this->lang_item("foto"),
										$this->lang_item("lbl_articulo"),
										$this->lang_item("lbl_clave_corta"),
										$this->lang_item("lbl_descripcion")/*,
										$this->lang_item("lbl_acciones")*/);
			// Generar tabla
			$this->table->set_template($tbl_plantilla);
			$tabla = $this->table->generate($tbl_data);
			$buttonTPL = array( 'text'   => $this->lang_item("btn_xlsx"), 
								'iconsweets' => 'fa fa-file-excel-o',
								'href'       => base_url($this->path.'export_xlsx?filtro='.base64_encode($filtro))
								);
		}else{
			$buttonTPL = "";
			$msg   = $this->lang_item("msg_query_null");
			$tabla = alertas_tpl('', $msg ,false);
		}
		$tabData['filtro']    = (isset($filtro) && $filtro!="") ? sprintf($this->lang_item("msg_query_search",false),$total_rows , $filtro) : "";
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

	public function detalle(){
		$id_articulo         = $this->ajax_post('id_articulo');
		$detalle             = $this->db_model_articulos->get_articulo_unico($id_articulo);
		$seccion             = $this->tab2;
		$detalle_nutricional = $this->db_model->get_valores_nutricionales_unico($id_articulo);

		$btn_save                        = form_button(array('class' => 'btn btn-primary' , 'name' => 'actualizar', 'onclick' => 'actualizar()', 'content' => $this->lang_item("btn_guardar")));
		$tabData['id_articulo']          = $id_articulo;
		$tabData['lbl_articulo']         = $this->lang_item("lbl_articulo");
		$tabData['lbl_cantidad_sugerida']= $this->lang_item("lbl_cantidad_sugerida");
		$tabData['lbl_peso_bruto']       = $this->lang_item("lbl_peso_bruto");
		$tabData['lbl_peso_neto']        = $this->lang_item("lbl_peso_neto");
		$tabData['lbl_energia']          = $this->lang_item("lbl_energia");
		$tabData['lbl_proteina']         = $this->lang_item("lbl_proteina");
		$tabData['lbl_lipidos']          = $this->lang_item("lbl_lipidos");
		$tabData['lbl_hidratos_carbono'] = $this->lang_item("lbl_hidratos_carbono");
		$tabData['lbl_fibra']            = $this->lang_item("lbl_fibra");
		$tabData['lbl_vitamina_a']       = $this->lang_item("lbl_vitamina_a");
		$tabData['lbl_acido_ascorbico']  = $this->lang_item("lbl_acido_ascorbico");
		$tabData['lbl_acido_folico']     = $this->lang_item("lbl_acido_folico");
		$tabData['lbl_hierro_nohem']     = $this->lang_item("lbl_hierro_nohem");
		$tabData['lbl_potasio']          = $this->lang_item("lbl_potasio");
		$tabData['lbl_azucar']           = $this->lang_item("lbl_azucar");
		$tabData['lbl_indice_glicemico'] = $this->lang_item("lbl_indice_glicemico");
		$tabData['lbl_carga_glicemica']  = $this->lang_item("lbl_carga_glicemica");
		$tabData['lbl_calcio']           = $this->lang_item("lbl_calcio");
		$tabData['lbl_sodio']            = $this->lang_item("lbl_sodio");
		$tabData['lbl_selenio']          = $this->lang_item("lbl_selenio");
		$tabData['lbl_fosforo']          = $this->lang_item("lbl_fosforo");
		$tabData['lbl_colesterol']       = $this->lang_item("lbl_colesterol");
		$tabData['lbl_ag_saturados']     = $this->lang_item("lbl_ag_saturados");
		$tabData['lbl_ag_mono']          = $this->lang_item("lbl_ag_mono");
		$tabData['lbl_ag_poli']          = $this->lang_item("lbl_ag_poli");
		$tabData['txt_cantidad_sugerida']= ($detalle_nutricional)?$detalle_nutricional[0]['cantidad_sugerida']:'';
		$tabData['txt_um']               = ($detalle_nutricional)?$detalle_nutricional[0]['um']:'';
		$tabData['txt_articulo']         = $detalle[0]['articulo'];
		$tabData['txt_peso_bruto']       = ($detalle_nutricional)?$detalle_nutricional[0]['peso_bruto']:'';
		$tabData['txt_peso_neto']        = ($detalle_nutricional)?$detalle_nutricional[0]['peso_neto']:'';
		$tabData['txt_energia']          = ($detalle_nutricional)?$detalle_nutricional[0]['energia']:'';
		$tabData['txt_proteina']         = ($detalle_nutricional)?$detalle_nutricional[0]['proteina']:'';
		$tabData['txt_lipidos']          = ($detalle_nutricional)?$detalle_nutricional[0]['lipidos']:'';
		$tabData['txt_hidratos_carbono'] = ($detalle_nutricional)?$detalle_nutricional[0]['hidratos_carbono']:'';
		$tabData['txt_fibra']            = ($detalle_nutricional)?$detalle_nutricional[0]['fibra']:'';
		$tabData['txt_vitamina_a']       = ($detalle_nutricional)?$detalle_nutricional[0]['vitamina_a']:'';
		$tabData['txt_acido_ascorbico']  = ($detalle_nutricional)?$detalle_nutricional[0]['acido_ascorbico']:'';
		$tabData['txt_acido_folico']     = ($detalle_nutricional)?$detalle_nutricional[0]['acido_folico']:'';
		$tabData['txt_hierro_nohem']     = ($detalle_nutricional)?$detalle_nutricional[0]['hierro_nohem']:'';
		$tabData['txt_potasio']          = ($detalle_nutricional)?$detalle_nutricional[0]['potasio']:'';
		$tabData['txt_azucar']           = ($detalle_nutricional)?$detalle_nutricional[0]['azucar']:'';
		$tabData['txt_indice_glicemico'] = ($detalle_nutricional)?$detalle_nutricional[0]['indice_glicemico']:'';
		$tabData['txt_carga_glicemica']  = ($detalle_nutricional)?$detalle_nutricional[0]['carga_glicemica']:'';
		$tabData['txt_calcio']           = ($detalle_nutricional)?$detalle_nutricional[0]['calcio']:'';
		$tabData['txt_sodio']            = ($detalle_nutricional)?$detalle_nutricional[0]['sodio']:'';
		$tabData['txt_selenio']          = ($detalle_nutricional)?$detalle_nutricional[0]['selenio']:'';
		$tabData['txt_fosforo']          = ($detalle_nutricional)?$detalle_nutricional[0]['fosforo']:'';
		$tabData['txt_colesterol']       = ($detalle_nutricional)?$detalle_nutricional[0]['colesterol']:'';
		$tabData['txt_ag_saturados']     = ($detalle_nutricional)?$detalle_nutricional[0]['ag_saturados']:'';
		$tabData['txt_ag_mono']          = ($detalle_nutricional)?$detalle_nutricional[0]['ag_mono']:'';
		$tabData['txt_ag_poli']          = ($detalle_nutricional)?$detalle_nutricional[0]['ag_poli']:'';

		$tabData['foto_img']          = ($detalle_nutricional[0]['avatar']=='') ? return_avatar('articulos','', false, 100, 100,true) : return_avatar('articulos', $detalle_nutricional[0]['avatar'], false,  100, 100,true);
		$tabData['lbl_ultima_modificacion'] = $this->lang_item('lbl_ultima_modificacion');
        $tabData['val_fecha_registro']     = $detalle_nutricional[0]['timestamp'];
		$tabData['lbl_fecha_registro']     = $this->lang_item('lbl_fecha_registro');
		$tabData['lbl_usuario_registro']    = $this->lang_item('lbl_usuario_registro');

		$this->load_database('global_system');
        $this->load->model('users_model');

		$usuario_registro                  = $this->users_model->search_user_for_id($detalle[0]['id_usuario']);
	    $usuario_name	                   = text_format_tpl($usuario_registro[0]['name'],"u");
	    $tabData['val_usuarios_registro']  = $usuario_name;

		if($detalle_nutricional[0]['edit_id_usuario']){
			$usuario_registro = $this->users_model->search_user_for_id($detalle_nutricional[0]['edit_id_usuario']);
			$usuario_name = text_format_tpl($usuario_registro[0]['name'],"u");
			$tabData['val_ultima_modificacion'] = sprintf($this->lang_item('val_ultima_modificacion',false), $this->timestamp_complete($detalle_nutricional[0]['edit_timestamp']), $usuario_name);
		}else{
			$usuario_name = '';
			$tabData['val_ultima_modificacion'] = $this->lang_item('lbl_sin_modificacion', false);
		}
		$tabData['button_save'] = $btn_save;
		$tabData['registro_por'] = $this->lang_item('registro´_por', false);
		$tabData['usuario_registro'] = $usuario_name;
		$uri_view = $this->modulo.'/'.$this->submodulo.'/valores_'.$seccion;

		echo json_encode($this->load_view_unique($uri_view,$tabData,true));
	}

	public function actualizar(){
		$objData  	= $this->ajax_post('objData');
		if($objData['incomplete']>0 || $objData['numerico']>0){
			$msg = $this->lang_item("msg_campos_obligatorios",false);
			echo json_encode(array(  'success'=>'false', 'mensaje' => alertas_tpl('error', $msg ,false)));
		}else{
			$sqlData = array(
				'id_compras_articulos' => $objData['id_articulo']
				,'cantidad_sugerida'   => $objData['txt_cantidad_sugerida']
				,'peso_bruto'          => $objData['txt_peso_bruto']
				,'peso_neto'           => $objData['txt_peso_neto']
				,'energia'             => $objData['txt_energia']
				,'proteina'            => $objData['txt_proteina']
				,'lipidos'             => $objData['txt_lipidos']
				,'hidratos_carbono'    => $objData['txt_hidratos_carbono']
				,'fibra'               => $objData['txt_fibra']
				,'vitamina_a'          => $objData['txt_vitamina_a']
				,'acido_ascorbico'     => $objData['txt_acido_ascorbico']
				,'acido_folico'        => $objData['txt_acido_folico']
				,'hierro_nohem'        => $objData['txt_hierro_nohem']
				,'potasio'             => $objData['txt_potasio']
				,'azucar'              => $objData['txt_azucar']
				,'indice_glicemico'    => $objData['txt_indice_glicemico']
				,'carga_glicemica'     => $objData['txt_carga_glicemica']
				,'calcio'              => $objData['txt_calcio']
				,'sodio'               => $objData['txt_sodio']
				,'selenio'             => $objData['txt_selenio']
				,'fosforo'             => $objData['txt_fosforo']
				,'colesterol'          => $objData['txt_colesterol']
				,'ag_saturados'        => $objData['txt_ag_saturados']
				,'ag_mono'             => $objData['txt_ag_mono']
				,'ag_poli'             => $objData['txt_ag_poli']
				,'timestamp'           => $this->timestamp()
				,'id_usuario'          => $this->session->userdata('id_usuario')
				,'edit_timestamp'      => $this->timestamp()
				,'edit_id_usuario'     => $this->session->userdata('id_usuario')
				);
			$insert = $this->db_model->db_update_data($sqlData);
			if($insert){
				$msg = $this->lang_item("msg_update_success",false);
				echo json_encode(array(  'success'=>'true', 'mensaje' => $msg ));
			}else{
				$msg = $this->lang_item("msg_err_clv",false);
				echo json_encode( array( 'success'=>'false', 'mensaje' =>alertas_tpl('', $msg ,false)));
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
		$lts_content = $this->db_model->get_valores_nutricionales_default($sqlData);
		
		if(count($lts_content)>0){
			foreach ($lts_content as $value) {
				$set_data[] = array(
									 $value['articulo']
									,$value['cantidad_sugerida']
									,$value['peso_bruto']
									,$value['peso_neto']
									,$value['energia']
									,$value['proteina']
									,$value['lipidos']
									,$value['hidratos_carbono']
									,$value['fibra']
									,$value['vitamina_a']
									,$value['acido_ascorbico']
									,$value['acido_folico']
									,$value['hierro_nohem']
									,$value['potasio']
									,$value['azucar']
									,$value['indice_glicemico']
									,$value['carga_glicemica']
									,$value['calcio']
									,$value['sodio']
									,$value['selenio']
									,$value['fosforo']
									,$value['colesterol']
									,$value['ag_saturados']
									,$value['ag_mono']
									,$value['ag_poli']
									 );
			}
			$set_heading = array(
									 $this->lang_item("articulo")
									,$this->lang_item("cantidad sugerida")
									,$this->lang_item("peso bruto")
									,$this->lang_item("peso neto")
									,$this->lang_item("energia")
									,$this->lang_item("proteina")
									,$this->lang_item("lipidos")
									,$this->lang_item("hidratos de carbono")
									,$this->lang_item("fibra")
									,$this->lang_item("vitamina a")
									,$this->lang_item("acido ascorbico")
									,$this->lang_item("acido folico")
									,$this->lang_item("hierro nohem")
									,$this->lang_item("potasio")
									,$this->lang_item("azucar")
									,$this->lang_item("indice glicemico")
									,$this->lang_item("carga glicemica")
									,$this->lang_item("calcio")
									,$this->lang_item("sodio")
									,$this->lang_item("selenio")
									,$this->lang_item("fosforo")
									,$this->lang_item("colesterol")
									,$this->lang_item("ag saturados")
									,$this->lang_item("ag mono")
									,$this->lang_item("ag poli")
									);
	
		}

		$params = array(	'title'   => $this->lang_item("Valores nutricionales"),
							'items'   => $set_data,
							'headers' => $set_heading
						);
		
		$this->excel->generate_xlsx($params);
	}

	public function eliminar_registro(){
		$id_articulo = $this->ajax_post('id_articulo');
		$valor_nutricional = $this->db_model->db_get_compras_articulos_valores($id_articulo);
		//print_debug($valor_nutricional);
		if(!$valor_nutricional[0]['id_compras_articulos']){
			$sqlData = array(
					 'id_compras_articulos' => $id_articulo
		            ,'activo'               => 0
		            ,'edit_id_usuario'      => $this->session->userdata('id_usuario')
					,'edit_timestamp'       => $this->timestamp()
					);
			
			$update = $this->db_model->bd_delete_data($sqlData);
			if($update){
				$msg = $this->lang_item("msg_delete_success",false);
				echo json_encode(array('success'=>'true', 'mensaje' => $msg));
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