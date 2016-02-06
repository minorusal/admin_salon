<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class test extends Base_Controller { 
/**
* Nombre:		test
* Ubicación:	General
* Descripción:	Controlador de pruebas
* @author:		Oscar Maldonado - OM
* Creación: 	2015-05-25
* Modificación:	
*/
	
	public function __construct(){
		parent::__construct();

	}

	public function imprimir_ticket(){
	// Prueba de impresión de ticket directamente a impresora
		// Crea codigo de barras
		$barras_txt = '0123456790123';		$this->barcode->barcode_img_tipo='png';
		$barras 	= ($barrasImg = $this->barcode->create($barras_txt))?true:false; #Crea jpg
		$barrasBmp 	= ($barras)?imagebmp('png',$barrasImg,'assets\tmp\barcode.bmp'):false; #Convierte jpg->bmp
		// Crea código QR
		$qr_txt 	= 'http://www.isolution.mx';
		$qr 		= ($qrImg = $this->codeqr->create($qr_txt))?true:false;
		$qrBmp 		= ($qr)?imagebmp('png',$qrImg,'assets\tmp\qrcode.bmp'):false; #Convierte jpg->bmp
		// Envía datos
		$impData = array(
			 'contenido' 	=> 'assets\tmp\ticket.txt' #Contenido de ticket
			,'logo' 		=> 'assets/images/logo.bmp' #Logo de empresa en BMP
			,'impresora' 	=> 'tickets' #Nombre de impresora local
			,'formato' 		=> true #Formato de texto
			,'codebar' 		=> $barrasBmp #Envia BMP
			,'codeqr' 		=> $qrBmp #Envía BMP
			);		
		// Imprime ticket
		if($this->impresion->enviar_a_impresora($impData)){
			echo "Impresi&oacute;n enviada: ".date('Y-m-d H:i:s');
		}
		// Elimina imagenes generadas
		if($barrasImg) unlink($barrasImg);
		if($barrasBmp) unlink($barrasBmp);
		if($qrImg) unlink($qrImg);
		if($qrBmp) unlink($qrBmp);
	}

	public function barcode(){
	// Prueba de generacion de código de barras
		$txt = "iSolution.mx";
		$this->barcode->barcode_img_tipo='jpg';
		if($this->barcode->create($txt)){
			echo "Codigo de barras creado: ".date('Y-m-d H:i:s');
		}else{
			echo "No se creo";
		}
	}

	public function codeqr(){
	// Prueba de generacion de código QR
		$txt = "iSolution.mx";
		if($this->codeqr->create($txt)){
			echo "Codigo QR creado: ".date('Y-m-d H:i:s');
		}else{
			echo "No se creo";
		}
	}

	public function load_vars(){
	// Prueba carga de archivo config.ini
		echo $this->config_vars->load_vars();
	}

	public function directorio(){
	// Lee contenido de directorio
		print_r($this->config_vars->lee_directorio('assets/cfg/'));
	}

	public function html2pdf(){
	// Lee contenido de directorio
		// $html = file_get_contents("assets/tmp/t.html");
		$html = '<table border="1" cellspacing="3" width="100%"><tbody>';
		for($i=0; $i<=50; $i++){
			$html .= '<tr>
						<td>'.$i.'</td>
						<td>'.rand($i,$i*date('s')).'</td>
						<td>'.date('Y-m-d H:i:s').'</td>
						<td>'.md5(str_shuffle('wefFDSFeiwihñygyuuyGu97t')).'</td>
						<td>'.str_shuffle('pi8ybDdtfhjhUg5dtiLJ8FrvIUgRxc').'</td>
					</tr>';
		}
		$html .= '</tbody></table>';
		// dump_var($html);
		$arrayPDF = array(
						 'html' 	=> $html
						,'output'	=> 'F'
						,'archivo' 	=> false
					);
		echo 'Proceso iniciado a las: '.date('Y-m-d H:i:s').'<hr/>';
		ob_start();
		if(!$pdfFile=$this->html2pdf->crear($arrayPDF)){
			echo "Error al crear documento PDF.";
		}else{echo "Archivo Creado a las ".date('Y-m-d H:i:s').' -> '.'<a href="'.$pdfFile['uri'].'">'.$pdfFile['uri'].'</a>';}
		$respuesta = ob_get_contents();
		ob_end_clean();
		echo $respuesta;
		echo '<hr/>Proceso terminado a las: '.date('Y-m-d H:i:s');		
	}


	public function mailing(){
		echo 'Envio de mail: '.date('Y-m-d H:i:s').'<hr/>';
		$destinatarios[] = array(
					 'email'	=> 'oscar.maldonado@isolution.mx'
					,'nombre'	=> 'Oscar Maldonado'
				);
		$destinatariosCC[] = array(
					 'email'	=> 'oscar.maldonado@isolution.mx'
					,'nombre'	=> 'Oscar Maldonado'
				);
		$destinatariosBCC[] = array(
					 'email'	=> 'oscar.maldonado@isolution.mx'
					,'nombre'	=> 'Oscar Maldonado'
				);
		$adjuntos[] = false;
		// Template
		$htmlData = array('titulo' => 'Prueba','subtitulo' => date('H:i:s'), 'contenido'=>utf8_decode('Aquí va el contenido.'));
		// $htmlTPL = $this->load_view_unique('mail/test_tpl' , $htmlData, true);
		$htmlTPL = $this->load_view_unique('mail/template' , $htmlData, true);
		// Imagenes embedded
		$url_image = 'assets/images/';		
		$imagenes[] = array(
					 'ruta'		=> $url_image
					,'alias'	=> 'logo'
					,'file' 	=> 'logo.png'
					,'encode' 	=> 'base64'
					,'mime' 	=> 'image/png'
				);
		$imagenes[] = array(
					 'ruta'		=> $url_image
					,'alias'	=> 'banner'
					,'file' 	=> 'banner_azul.png'
					,'encode' 	=> 'base64'
					,'mime' 	=> 'image/png'
				);
		$imagenes[] = array(
					 'ruta'		=> $url_image
					,'alias'	=> 'footer'
					,'file' 	=> 'mail_footer.png'
					,'encode' 	=> 'base64'
					,'mime' 	=> 'image/png'
				);
		
		// Create ArrayData
		$tplData = array(
			 'body' 				=> $htmlTPL
			,'tipo' 				=> 'html' #html | text
			,'destinatarios' 		=> $destinatarios
			// ,'destinatariosCC' 		=> $destinatariosBCC
			// ,'destinatariosBCC' 	=> $destinatariosBCC
			,'asunto' 				=> 'iSolution - Prueba de envio de correo: AdminControl.2.0 - '.date('Y-m-d H:i:s')
			,'imagenes' 			=> $imagenes
			,'adjuntos' 			=> $adjuntos
		);		
		// Send email
		if($resultado = $this->mailsmtp->send($tplData)){
			$msj = ($resultado['success'])?"Correo enviado OK: ".date("Y-m-d H:i:s"):$resultado['msj'];
		}else{
			$msj = "ERROR: No se pudo enviar el correo: ".$resultado['msj'];
		}
		echo $msj;
	}

	public function mpdf(){
		$html = '<style>
					.estilo{
						width: 200px;
						height: 200px;

						border: 1px rgb(89,89,89) solid;

						background: rgb(253, 30, 30);

						color: rgb(25,25,25);
						font-size: inherit;
						font-weight: inherit;
						font-family: inherit;
						font-style: inherit;
						text-decoration: inherit;
						text-align: center;

						line-height: 3.8em;
						-moz-box-shadow:  8px 11px 16px 2px rgb(42, 42, 33);
						-webkit-box-shadow:  8px 11px 16px 2px rgb(42, 42, 33);
						box-shadow:  8px 11px 16px 2px rgb(42, 42, 33);
					}
					</style>
					<div class="estilo">Aqui hay un DIV</div>';

		$arrayPDF = array(
						 'html' 	=> $html
						,'output'	=> 'I'
						,'archivo' 	=> false
					);
		echo 'Proceso iniciado a las: '.date('Y-m-d H:i:s').'<hr/>';
		ob_start();
		if(!$pdfFile=$this->mpdf60->crear($arrayPDF)){
			echo "Error al crear documento PDF.";
		}else{echo "Archivo Creado a las ".date('Y-m-d H:i:s').' -> '.'<a href="'.$pdfFile['uri'].'">'.$pdfFile['uri'].'</a>';}
		$respuesta = ob_get_contents();
		ob_end_clean();
		echo $respuesta;
		echo '<hr/>Proceso terminado a las: '.date('Y-m-d H:i:s');
		
	}

	public function webservice(){
// dump_var($HTTP_RAW_POST_DATA);
		$this->webservice->ws_script = 'http://localhost/pae/admincontrol.2.0/admincontrol/test/webservice';
		#Recepción
		$this->webservice->ws->wsdl->addComplexType(
		// Estructura de la matriz que contiene los registros
		    'matriz', 
		    'complexType', 
		    'array', 
		    '',
		    'SOAP-ENC:Array', 
		    array(), 
		    array(array('ref' => 'SOAP-ENC:arrayType', 'wsdl:arrayType' => 'tns:registro[]')), 
		    'tns:registro'
		); 
		$this->webservice->ws->wsdl->addComplexType( 
		// Estructura de cada registro
		    'registro', 
		    'complexType', 
		    'array', 
		    'all', 
		    '', 
		    array(              #elements
		        'uno' =>array('name' => 'uno', 'type' => 'xsd:string'),
		        'dos' =>array('name' => 'dos', 'type' => 'xsd:string'),
		        'tres' =>array('name' => 'tres', 'type' => 'tns:registroGenerico')
		    ) 
		); 

		$this->webservice->ws->wsdl->addComplexType( 
		// Sub-Estructura de registro
		    'registroGenerico', 
		    'complexType', 
		    'array', 
		    'all', 
		    '', 
		    array(              #elements
		        'extra1' =>array('name' => 'uno', 'type' => 'xsd:string'),
		        'extra2' =>array('name' => 'uno', 'type' => 'xsd:string')
		    ) 
		);

		#Respuesta
		$this->webservice->ws->wsdl->addComplexType( 
		// Estructura de array de cada registro
		    'arrayRespuesta', 
		    'complexType', 
		    'array', 
		    'all', 
		    '', 
		    array()
		); 

		$this->webservice->ws->wsdl->addComplexType( 
		// Estructura de array de cada registro
		    'estructuraRespuesta', 
		    'complexType', 
		    'struct', 
		    'all', 
		    '', 
		    array(              #elements
		        'success' =>array('name' => 'success', 'type' => 'tns:arrayRespuesta'),
		        'recibido' =>array('name' => 'recibido', 'type' => 'tns:arrayRespuesta'),
		        'respuesta' =>array('name' => 'respuesta', 'type' => 'tns:arrayRespuesta')
		    )
		); 

		#Registro de metodos
		$this->webservice->ws->register(
		    "setData", // Nombre del método
		    array("params" => "tns:matriz"), // Parámetros de entrada
		    // array("return" => "tns:registro"),// Parámetros de salida
		    array("return" => "tns:estructuraRespuesta"),// Parámetros de salida
		     // array("return" => "tns:matriz"),// Parámetros de salida
		    "urn:setData", // Define el namespace
		    "urn:ws-server#setData", // Define la acción SOAP
		    "rpc", // Define el tipo de llamado (rpc / document)
		    "encoded", // Define el valor para USE (encoded / literal)
		    "Nos devuelve una lista de elementos" // Documentación del método
	    );

	    function setData($data) {
		    $respuesta [recibido] = $data; 
		    $recibidos = count($data);
		    $tiempo_inicio = msec_format();
		    $time_start = msec();
		    for($i=0; $i<$recibidos; $i++){
		        $respuestaArray[] = array(
		             uno    => 'Respondido - '.$i
		            ,dos    => 'Respondido - Segundo elemento'
		            ,tres   => array(
		                            fecha=>date('Y-m-d'), 
		                            hora=>msec_format(), 
		                            microtime => microtime(true)-$time_start
		                        )
		            ,cuatro => 'Respondido - Cuarto elemento'
		        );
		    }
		    $tiempo_fin = msec_format();
		    $time_transcurred = (msec()-$time_start).' segundos';
		    $respuesta [respuesta] = $respuestaArray; 
		    $respuesta [success] = array(array(
		                            ok              => true, 
		                            t_inicio        => $tiempo_inicio, 
		                            t_final         => $tiempo_fin, 
		                            segs_consumidos => $time_transcurred
		                        )); 
		    return $respuesta;
		}

		// Conversión de microsegundos a segundos
		function msec() {
		    list($usec, $sec) = explode(' ', microtime()); //split the microtime on space
		    $usec = str_replace("0.", ".", $usec);     //remove the leading '0.' from usec
		    return $usec;
		}

		function msec_format() {
		    list($usec, $sec) = explode(' ', microtime()); //split the microtime on space
		    $usec = str_replace("0.", ".", $usec);     //remove the leading '0.' from usec
		    return date('H:i:s', $sec) . $usec;       //appends the decimal portion of seconds
		}

		// Response		
		$this->webservice->ws->service($HTTP_RAW_POST_DATA);
	#--
	}


	public function prueba(){
		$this->load->model('sync_model','db_model');


		$obj = array(	
						'table'   => 'administracion_descuentos',
						'columns' => 'id_administracion_descuentos|descuento|clave_corta|valor_descuento|descripcion|activo',
						'last_id' => 5
						);

	
        $columns = explode('|', $obj['columns']);

        $condicion = array($columns[0], $obj['last_id']);


        $data  =  $this->db_model->get_data($obj['table'] , $columns, $condicion);

        $lines = '';
       
       foreach ($data as $key ) {
                    
           print_debug(implode('|',$key));
        
        }

       	

	}
}
?>