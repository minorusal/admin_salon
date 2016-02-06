<?php
/**
* Descripción: 	Contruye y envía un documento a la impresora 8solo funciona en Windows)
* @author:		Oscar Maldonado - O3M
* Creación: 	2015-05-22
* Modificación: 2015-05-25_OM, 2015-05-29_OM
*/
class impresion extends config_vars{

	private $respuesta; #Respuesta
	private $impresora; #Nombre de impresora
	private $contenido, $texto, $formato; #Contenido de documento
	private $fuente; #Formato de texto
	private $codebar, $codeqr; # Imagenes
	public  $logo, $footer; #Elementos
	private $fh, $ph; #Manejadores de Impresora y archivo

	public function __construct(){
		$this->load_vars('assets/cfg/ticket.cfg');
		
		$this->impresora 	= $this->var['ticket']['path_impresora'];
		$this->logo 	 	= $this->cfg['path_img'].$this->var['ticket']['ticket_logo_img'];
		$this->footer 		= true;
		$this->respuesta 	= false;
		$this->formato 		= false;
		$this->contenido 	= '';
		$this->codebar 		= false;
		$this->codeqr 		= false;
	}

	public function enviar_a_impresora($data=array()){
	// Envia documento a impresora via printer.dll (Windows)
		$this->impresora 	= (isset($data['impresora']))?$data['impresora']:$this->impresora;
		$this->logo 	 	= (isset($data['logo']))?$data['logo']:'';
		$this->contenido 	= (isset($data['contenido']))?$data['contenido']:'';
		$this->formato 		= (isset($data['formato']))?$data['formato']:'';
		$this->codebar 		= (isset($data['codebar']))?$data['codebar']:'';
		$this->codeqr 		= (isset($data['codeqr']))?$data['codeqr']:'';

		if($this->formato){
			// Lee archivo y extrae contenido para enviarlo a la impresora
			$this->fh = fopen($this->contenido, "r");
			while(!feof($this->fh)) {
				$this->texto [] = fgets($this->fh);
			}
			fclose($this->fh);
			$isfile = true;
		}else{
			$this->texto = $this->contenido;
		}

		if($this->impresora && $this->texto){
			if($this->ph = printer_open($this->impresora)){ #Crea manejador de impresora			
				#Contrucción de documento en impresora
				// Inicio de doc y page
				printer_start_doc($this->ph);
				printer_start_page($this->ph);
				// Set print mode to RAW and send PDF to printer 
				printer_set_option($this->ph, PRINTER_MODE, "RAW"); 
				$salto_linea = 0;
				if($this->formato){
					if(file_exists($this->logo)){
						// Imagen BMP - Logo	
						printer_draw_bmp($this->ph, $this->logo, $this->var['ticket']['ticket_logo_x'], $this->var['ticket']['ticket_logo_y'], $this->var['ticket']['ticket_logo_w'], $this->var['ticket']['ticket_logo_h']);
						$salto_linea = $this->var['ticket']['ticket_logo_salto'];
					}
					// Crear formato de texto
					$estilo = printer_create_font($this->var['ticket']['ticket_formato_font'], $this->var['ticket']['ticket_formato_h'], $this->var['ticket']['ticket_formato_w'], $this->var['ticket']['ticket_formato_font_w'], $this->var['ticket']['ticket_formato_font_italic'], $this->var['ticket']['ticket_formato_font_underline'], $this->var['ticket']['ticket_formato_font_strikeout'], $this->var['ticket']['ticket_formato_font_orientation']);    
					printer_select_font($this->ph, $estilo);
					// Contenido					
					if($isfile){
						$txtArray = $this->texto;
						for($i=0; $i<count($txtArray); $i++){
							printer_draw_text($this->ph, $txtArray[$i], $this->var['ticket']['ticket_contenido_x'], $salto_linea); #Izq; Alto
							$salto_linea+=$this->var['ticket']['ticket_contenido_salto'];
						}					
					}else{						
						printer_draw_text($this->ph, $this->texto, $this->var['ticket']['ticket_contenido_x'], $salto_linea);						
					}
					// Eliminar formato de texto
					printer_delete_font($estilo);
					// Codigo de Barras
					if($this->codebar && file_exists($this->codebar)){
						printer_draw_bmp($this->ph, $this->codebar, $this->var['ticket']['ticket_barscode_x'], $salto_linea, $this->var['ticket']['ticket_barscode_w'], $this->var['ticket']['ticket_barscode_h']);
						$salto_linea+=$this->var['ticket']['ticket_barscode_salto'];
					}
					// Codigo QR
					if($this->codeqr && file_exists($this->codeqr)){
						printer_draw_bmp($this->ph, $this->codeqr, $this->var['ticket']['ticket_qrcode_x'], $salto_linea, $this->var['ticket']['ticket_qrcode_w'], $this->var['ticket']['ticket_qrcode_h']);
						$salto_linea+=$this->var['ticket']['ticket_qrcode_salto'];						
					}
					// Footer
					if($this->footer){
						$estilo = printer_create_font($this->var['ticket']['ticket_formato_footer_font'], $this->var['ticket']['ticket_formato_footer_h'], $this->var['ticket']['ticket_formato_footer_w'], $this->var['ticket']['ticket_formato_footer_font_w'], $this->var['ticket']['ticket_formato_footer_font_italic'], $this->var['ticket']['ticket_formato_footer_font_underline'], $this->var['ticket']['ticket_formato_footer_font_strikeout'], $this->var['ticket']['ticket_formato_footer_font_orientation']);    
						printer_select_font($this->ph, $estilo);
						printer_draw_text($this->ph, date('Y-m-d H:i:s'), $this->var['ticket']['ticket_footer_x'], $salto_linea);
						printer_delete_font($estilo);
					}
				}else{
					printer_write($this->ph, $this->texto); #texto sin formato
				}				
				// Fin de documento y página
				printer_end_page($this->ph);
				printer_end_doc($this->ph);
				// Cierra archivo y envía a imprimir
				printer_close($this->ph); 
				$this->respuesta = true;
			}
		}
		return $this->respuesta;
	}
}

#EJEMPLO
// $impData = array(
// 	 'contenido' 	=> 'assets\tmp\ticket.txt'
// 	// ,'impresora' 	=> 'PDFCreator'
// 	,'formato' 		=> true
// 	,'codebar' 		=> '1234567890123'
// 	);
// $p = new Impresion();
// if($p->enviar_a_impresora($impData)){
// 	echo "Impresi&oacute;n enviada: ".date('Y-m-d H:i:s');
// }
/*-O3M-*/
?>