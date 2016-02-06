<?php /*O3M*/
/**
* Descripción:	Parsea archivo con datos de configuración incial y los
*				pone disponibles en variables globales.
* Creación:		2015-05-29
* @author		Oscar Maldonado - O3M
* @param 		$filename
* @return 		$cfg[], $db[], $var[]
*/
class config_vars {
	public $cfg, $db, $var;

	public function lista_archivos($directorio){
	// Devuelve arrreglo con los nombres de los archivos contenidos en un directorio
	   if (is_dir($directorio)) {
	      if ($dh = opendir($directorio)) {
	         while (($file = readdir($dh)) !== false) {
				#if($file!='.' && $file!='..'){
				if(filetype($directorio . $file)!='dir'){
	            	$files[]=$file;
				}            
	         }
	      closedir($dh);
	      }
	      return $files;
	   }else
	      return false;
	}  

	public function load_vars($cfg_path='assets/cfg/') {
	#Lee directorio y cargar infomación de archivos contenidos [*.cfg]
		$val = '';
		// Deteccion de directorio o archivo
		if(is_dir($cfg_path)){
			$filename = $this->lista_archivos($cfg_path);
			$pathfile = $cfg_path;
		}else{
			$filename = array($cfg_path);
			$pathfile = '';
			$module[0]= basename($filename[0], '.cfg');
		}
		if($filename){
			// Recorrido de archivo encontrados
			for($i=0; $i<count($filename); $i++){
				$file = $pathfile.$filename[$i];
				$module = (!isset($module))?explode('.',$filename[$i]):$module;
				try {			
					if (file_exists($file)) {
				        if ($handle = fopen($file, 'r')) {				        	
				        	$varsList = array('cfg','var','db');				        	
				            while (!feof($handle)) {
					        // Establece valores encontrados en las variables
				            	$contenido = fgets($handle);
				            	if($contenido[0]!='#' && trim($contenido[0])!=''){
					                $linea = explode('|',$contenido);
					                $campovalor = explode('=',$linea [1]);
					                $type = $linea [0];
					                $name = $campovalor[0];
					                $value = (!empty($campovalor[1]))?$campovalor[1]:false;

									$value = utf8_encode($value);
									if (trim($type)=='var') { 
									#VAR vars
										$this->var[$module[0]][trim($name)] = trim($value);
										$m = ($module[0])?$module[0].' | ':'';
									}else{$m='';}
									if (trim($type)=='cfg') { 
									#CFG vars
										$this->cfg[trim($name)] = trim($value);
									}
									if (trim($type)=='db') { 
									#DB vars
										$this->db[trim($name)] = trim($value);					
									}				
									if (in_array(trim($type),$varsList)) { 
									#Print for Debug									
									 	$val.=$filename[$i].' | '.$type.' | '.$m.$name.' = '.$value."<br/>\n\r";
									}
								}
				            }            
				        }	        						
					}else{
						$msj = utf8_decode("¡ERROR CRÍTICO!<br/> No se ha logrado cargar el archivo de configuración <".$file.">, por favor, contacte al administrador del sistema.<br/>");
				    	throw new Exception($msj, 1);    	
				    	return false;
				    }	
				} catch (Exception $e) {		
					print($e->getMessage());
					return false;
				}
			}
			return $val;
		}	   
	}
}

/*O3M*/
?>