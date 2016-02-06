<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class pagination_bootstrap extends CI_Pagination{
	
	private $ci;
	
	public function __construct(){
		$this->ci =& get_instance();
	}

	var $full_tag_open		= '<div class="pagination" style="float:right;"><ul>';
	var $full_tag_close		= '</ul></div>';
	var $first_tag_open		= '<li class="prev  paginate_button page">';
	var $first_tag_close	= '</li>';
	var $last_tag_open		= '<li class="next  paginate_button page">';
	var $last_tag_close		= '</li>';
	var $cur_tag_open		= '<li class="active"><a>';
	var $cur_tag_close		= '</a></li>';
	var $next_tag_open		= '<li class="next  paginate_button  page">';
	var $next_tag_close		= '</li>';
	var $prev_tag_open		= '<li class="prev  paginate_button page">';
	var $prev_tag_close		= '</li>';
	var $num_tag_open		= '<li class="page">';
	var $num_tag_close		= '</li>';
	var $anchor_class		= 'style=" cursor:pointer;"';
	var $evento_link		= 'href';
	var $function_js        = '';
	var $params_js          = '';
	
	/**
	* Funcion encargada de Generar los links del paginador
	* @param int $total_rows
	* @param string $url
	* @param int $per_page
	* @param int $uri_segment
	* @param array $config
	*/

	public function paginator_generate($total_rows = 5 , $url = '' , $per_page = 2 , $uri_segment = 4, $arr_config = array()){
		
		if(!empty($arr_config)){
			foreach ($arr_config as $key => $value) {
				$config[$key] = $value;
			}
		}
		$config['first_link']  = $this->ci->lang_item("lang_pag_first_link",false);
		$config['next_link']   = $this->ci->lang_item("lang_pag_next_link",false);
		$config['prev_link']   = $this->ci->lang_item("lang_pag_prev_link",false);
		$config['last_link']   = $this->ci->lang_item("lang_pag_last_link",false);
		$config['total_rows']  = $total_rows;
		$config['base_url']    = $url;
		$config['per_page']    = $per_page;
		$config['uri_segment'] = $uri_segment;

		$this->initialize($config);
		
		
		$links = $this->create_links();

		return $links;
	}

	/**
	* Funcion encargada de Generar info acerca del paginador
	* @param int $limit
	* @param int $offset
	* @param int $total_items
	*/

	public function showing_items($limit, $offset, $total_items){

		$item_firts = $offset + 1;
		$item_last  = ($total_items < $limit)?$total_items :($offset + $limit);

		if($total_items==0){
			$item_firts = 0;
			$item_last  = 0;
		}
		$showing    = '<div style="float:left;"><p class="text-info">'.$this->ci->lang_item("pag_resultado").$item_firts.' - '.$item_last.$this->ci->lang_item("pag_de",false).$total_items.$this->ci->lang_item("pag_registros").'</p></div>';

		return $showing;
	}
}