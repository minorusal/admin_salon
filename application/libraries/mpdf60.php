<?php
/**
* Descripción: 	Crea PDF con contenido HTML usando libreria TCPDF
* @author: 		Oscar Madonado
* Creación:		2015-06-18
* Modificación:	2015-06-19_OM
*/
// Clases requeridas
// require_once (APPPATH.'third_party/tcpdf/tcpdf.php'); 
require_once(APPPATH.'third_party/mpdf60/mpdf.php');

class mpdf60 extends config_vars{

	protected $path_img, $path_tmp; #Paths
	private $pdf; 
	public $respuesta;

	public function __construct($orientation=false, $unit=false, $format=false, $unicode=false, $encoding=false, $diskcache=false, $pdfa=false){
		// Variables
		$this->load_vars('assets/cfg/general.cfg');
		$this->path_img 	 	= $this->cfg['path_img'];
		$this->path_tmp 	 	= $this->cfg['path_tmp'];
		$this->load_vars('assets/cfg/codeigniter.cfg');
		$this->load_vars('assets/cfg/pdf.cfg');
		$this->respuesta 		= false;
		// Instancia TCPDF
		$this->orientation 		= (isset($orientation))?$this->var['pdf']['pdf_orientation']:$orientation;
		$this->unit 			= (isset($unit))?$this->var['pdf']['pdf_unit']:$unit;
		$this->format 			= (isset($format))?$this->var['pdf']['pdf_format']:$format;
		$this->unicode 			= (isset($unicode))?$this->var['pdf']['pdf_unicode']:$unicode;
		$this->encoding 		= (isset($encoding))?$this->var['pdf']['pdf_encoding']:$encoding;
		$this->diskcache 		= (isset($diskcache))?$this->var['pdf']['pdf_diskcache']:$diskcache;
		$this->pdfa 			= (isset($pdfa))?$this->var['pdf']['pdf_pdfa']:$pdfa;
		$this->pdf = new mPDF($this->orientation, $this->unit, $this->format, $this->unicode, $this->encoding, $this->diskcache, $this->pdfa);		
		// Atributos
		$this->creador 			= (isset($this->var['pdf']['pdf_creador']))?$this->var['pdf']['pdf_creador']:'';
		$this->autor 			= (isset($this->var['pdf']['pdf_autor']))?$this->var['pdf']['pdf_autor']:'';
		$this->titulo			= (isset($this->var['pdf']['pdf_title']))?$this->var['pdf']['pdf_title']:'';
		$this->subject 			= (isset($this->var['pdf']['pdf_subject']))?$this->var['pdf']['pdf_subject']:'';
		$this->keywords 		= (isset($this->var['pdf']['pdf_keywords']))?$this->var['pdf']['pdf_keywords']:'';
		// Headers
	}

	// public function crear($html=false){
	// 	$this->pdf->WriteHTML($html);
	// 	$respuesta = $this->pdf->Output();
	// 	return $respuesta;
	// }

	public function crear($data=array()){
	/**
	* Crea un documento PDF con contenido HTML recibido
	* $data [] : 
	*	html 	=> Contenido del documento
	*	output 	=> Tipo de salida al crear el documento (I, D, F, FD, S, E)
	*	archivo => Nombre de archivo PDF (sin extensión .pdf)
	* @return $respuesta [] :
	*	pdf 	=> Contenido del pdf (para output: E & S)
	*	path 	=> Ruta local en donde se creó el archivo pdf
	*	uri 	=> Ruta URL para accesar al documento creado
	*	success	=> true / false
	*/
		#Contenido HTML
		// dump_var($this->cfg['base_url'].$this->cfg['path_tmp']);
		$html 	= (empty($data['html']))?'Sin contenido.':$data['html'];
		#Output | I => Standar output ; D => Download ; F => Save to localfile ; FD => Save to localfile and download ; S => Return as string ; E => base64 mime multi-part email attachment
		$output = (empty($data['output']))?'I':strtoupper($data['output']);
		#Nombre de archivo
		$tmp_path = realpath($this->cfg['path_tmp']).'/';
		$tmp_dir = ($output=='FD' || $output=='F')?$tmp_path:'';
		$archivo = (empty($data['archivo']))?date('Ymd-His').'.pdf':$data['archivo'].'.pdf';
		#Atributos
		$this->pdf->SetCreator($this->creador);
		$this->pdf->SetAuthor($this->autor);
		$this->pdf->SetTitle($this->titulo);
		$this->pdf->SetSubject($this->subject);
		$this->pdf->SetKeywords($this->keywords);
		#Imprimir header y footer
		// $this->pdf->SetHeader($this->var['pdf']['pdf_print_header']);
		// $this->pdf->SetHeader($this->var['pdf']['pdf_print_footer']);
		#Margenes
		$this->pdf->SetMargins($this->var['pdf']['pdf_margin_left'], $this->var['pdf']['pdf_margin_top'], $this->var['pdf']['pdf_margin_right']); #left,top,right,keepmargins=false
		#Salto de pagina automatico
		$this->pdf->SetAutoPageBreak($this->var['pdf']['pdf_autopagebreak'], $this->var['pdf']['pdf_margin_bottom']);
		#Set default font subsetting mode
		if($this->var['pdf']['pdf_font_subsetting']){
			$this->pdf->setFontSubsetting($this->var['pdf']['pdf_font_subsetting']);
		}
		#Establecer fuente
		$this->pdf->SetFont($this->var['pdf']['pdf_font_name'], '', $this->var['pdf']['pdf_font_size'], '', true);
		#Agrega pagina
		$this->pdf->AddPage();
		#Efecto de sombra en el texto
		if($this->var['pdf']['pdf_efecto_sombra']){
			$this->pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));
		}
		#Imprime contenido
		$this->pdf->WriteHTML($html);
		#Cierra y crea documento
		if($PDFFile=$this->pdf->Output($tmp_dir.$archivo, $output)){$this->respuesta = true;}
		$respuesta ['pdf'] 		= $PDFFile;
		$respuesta ['path'] 	= $tmp_dir.$archivo;
		$respuesta ['uri'] 		= $this->cfg['base_url'].$this->cfg['path_tmp'].$archivo;
		$respuesta ['success'] 	= $this->respuesta;
		return $respuesta;
	}
}
?>