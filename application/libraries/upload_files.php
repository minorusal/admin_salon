<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class upload_files extends upload{
/**
* Nombre del archivo que se subió, incluyendo la extensión
*/
private $file_name;
/*
* Tipo Mime del archivo
*/
private $file_type;
/*
* Ruta absoluta del servidor al archivo
*/
private $file_path;
/*
* Ruta absoluta del servidor incluyendo el nombre de archivo
*/
private $full_path;
/*
* Nombre del archivo sin la extensión
*/
private $raw_name;
/*
* Nombre original del archivo. Solamente es útil si usa la opción de nombre encriptado
*/
private $orig_name;
/*
* Nombre de archivo como lo proporciona el agente de usuario, antes de cualquier preparación o incremento 
* del nombre de archivo
*/

private $client_name;
/*
* Extensión de archivo con el punto
*/
private $file_ext;
/*
* Tamaño del archivo en kilobytes
*/
private $file_size;
/*
* Si el archivo es o no una imagen. 1 = imagen. 0 = no lo es
*/
private $is_image;
/*
* Ancho de la imagen
*/
private $image_width;
/*
* Altura de la imagen
*/
private $image_height;
/*
* Tipo de imagen. Normalmente la extensión sin el punto
*/
private $image_type;
/*
* Una cadena que contiene ancho y alto. Útil para ponerlo en una etiqueta de imagen
*/
private $image_size_str;

	private $ci;
	
	public function __construct(){
		$this->ci =& get_instance();
	}

	function Upload()
	{
		parent::Controller();
	}
	
	function index()
	{	
		$this->load->view('formulario_carga', array('error' => ' ' ));
	}
	function do_upload()
	{
		$config['upload_path']  = './uploads/';
		$config['allowed_types'] = 'gif|jpg|png';
		$config['max_size']	     = '100';
		$config['max_width']  = '1024';
		$config['max_height']  = '768';
		
		$this->load->library('upload', $config);
	
		if ( ! $this->upload->do_upload())
		{
			$error = array('error' => $this->upload->display_errors());
			
			$this->load->view('formulario_carga', $error);
		}	
		else
		{
			$data = array('upload_data' => $this->upload->data());
			
			$this->load->view('upload_success', $data);
		}
	}	
}