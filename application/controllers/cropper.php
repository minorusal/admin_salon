<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class cropper extends Base_Controller {

	public function __construct(){
		parent::__construct();
	}

	public function avatar_user(){
		$this->load->model('users_model','db_model');
		$avatar_folder = 'assets/avatar/users/';
		$avatar_image  =  $this->item_session('user').'_'.date('YmdHis');
		$response = $this->jcrop->initialize_crop( 
				isset($_POST['avatar_src']) ? $_POST['avatar_src'] : null,
				$avatar_folder.$avatar_image,
				isset($_POST['avatar_data']) ? $_POST['avatar_data'] : null,
				isset($_FILES['avatar_file']) ? $_FILES['avatar_file'] : null
		);
		$this->db_model->update_avatar_user($this->item_session('id_personal'), $avatar_image.'.png');
		if(file_exists($avatar_folder.$this->item_session('avatar_user'))){
			unlink( $avatar_folder.$this->item_session('avatar_user') );
		}
		
		$this->session->set_userdata('avatar_user',$avatar_image.'.png');
		$this->removeCache();
		echo json_encode($response);
	}

	public function avatar_articulo(){

		$hidden_cropper = explode('|', $this->ajax_post('hidden_cropper') );
		$this->load->model('compras/articulos_model','db_model');
		$avatar_folder = 'assets/avatar/articulos/';
		$avatar_image  =  $hidden_cropper[0].'_'.date('YmdHis');

		$response = $this->jcrop->initialize_crop( 
				isset($_POST['avatar_src']) ? $_POST['avatar_src'] : null,
				$avatar_folder.$avatar_image,
				isset($_POST['avatar_data']) ? $_POST['avatar_data'] : null,
				isset($_FILES['avatar_file']) ? $_FILES['avatar_file'] : null
		);
		$this->db_model->update_avatar_articulo($hidden_cropper[0], $avatar_image.'.png');
		
		if($hidden_cropper[1] !=''){
			if(file_exists($avatar_folder.$hidden_cropper[1])){
				unlink($avatar_folder.$hidden_cropper[1]);
			}
		}
		
		$this->removeCache();
		$response['hidden_cropper'] = $hidden_cropper[0].'|'.$avatar_image.'.png';
		echo json_encode($response);
	}

	public function avatar_recetario(){

		$hidden_cropper = explode('|', $this->ajax_post('hidden_cropper') );
		$this->load->model('nutricion/recetario_model','db_model');
		$avatar_folder = 'assets/avatar/recetario/';
		$avatar_image  =  $hidden_cropper[0].'_'.date('YmdHis');

		$response = $this->jcrop->initialize_crop( 
				isset($_POST['avatar_src']) ? $_POST['avatar_src'] : null,
				$avatar_folder.$avatar_image,
				isset($_POST['avatar_data']) ? $_POST['avatar_data'] : null,
				isset($_FILES['avatar_file']) ? $_FILES['avatar_file'] : null
		);
		$this->db_model->update_avatar_receta($hidden_cropper[0], $avatar_image.'.png');
		
		if($hidden_cropper[1] !=''){
			if(file_exists($avatar_folder.$hidden_cropper[1])){
				unlink($avatar_folder.$hidden_cropper[1]);
			}
		}
		
		$this->removeCache();
		$response['hidden_cropper'] = $hidden_cropper[0].'|'.$avatar_image.'.png';
		echo json_encode($response);
	}
}