<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	if(!function_exists('array_2_string_format')){
		function array_2_string_format($input = array(), $separator = "=", $glue = " "){
			if(!empty($input)){
				$output = implode($glue, array_map(function ($v, $k) { return sprintf("%s?'%s'" , $k, $v); }, $input, array_keys($input)));
				$output = str_replace('?',$separator,$output);
			}else{
				$output = '';
			}
			return $output ;
		}
	}
    /**
	* imprime un arreglo formateado para debug
	* y detiene la ejecucion del script
	* @return array $array
	*/
	if(!function_exists('print_debug')){
		function print_debug($array, $die = true){
			echo '<pre>';
			print_r($array);
			echo '</pre>';
			if($die){
				die();	
			}
		}
	}


	if(!function_exists('time_to_decimal')){
		function time_to_decimal($time){
			$horaArray = split(":",$time);
			$horaDecimal = $horaArray[0]+($horaArray[1]/60);
			return $horaDecimal;
		}
	}

	if(!function_exists('dump_var')){
		function dump_var($variable,$tipo=0){
			echo "<pre>";
			if(!$tipo){ print_r($variable); }else{var_dump($variable);}
			echo "</pre>";
			die();
		}
	}

	if(!function_exists('imagebmp')){
		function imagebmp($tipo='jpg',$imagesource='',$imagebmp='new.bmp'){
	    // Conviete imagen de JPG a BMP
			switch ($tipo) {
				case 'jpg': $im = imagecreatefromjpeg($imagesource); break;	
				case 'png': $im = imagecreatefrompng($imagesource); break;
				case 'gif': $im = imagecreatefromgif($imagesource); break;
			}
	        if (!$im) return false;
	        $w = imagesx($im);
	        $h = imagesy($im);
	        $result = '';
	        if (!imageistruecolor($im)) {
	            $tmp = imagecreatetruecolor($w, $h);
	            imagecopy($tmp, $im, 0, 0, 0, 0, $w, $h);
	            imagedestroy($im);
	            $im = & $tmp;
	        }
	        $biBPLine = $w * 3;
	        $biStride = ($biBPLine + 3) & ~3;
	        $biSizeImage = $biStride * $h;
	        $bfOffBits = 54;
	        $bfSize = $bfOffBits + $biSizeImage;
	        $result .= substr('BM', 0, 2);
	        $result .=  pack ('VvvV', $bfSize, 0, 0, $bfOffBits);
	        $result .= pack ('VVVvvVVVVVV', 40, $w, $h, 1, 24, 0, $biSizeImage, 0, 0, 0, 0);
	        $numpad = $biStride - $biBPLine;
	        for ($y = $h - 1; $y >= 0; --$y) {
	            for ($x = 0; $x < $w; ++$x) {
	                $col = imagecolorat ($im, $x, $y);
	                $result .=  substr(pack ('V', $col), 0, 3);
	            }
	            for ($i = 0; $i < $numpad; ++$i)
	                $result .= pack ('C', 0);
	        }
	        if($imagebmp==""){
	            echo $result;
	        }else{
	            $file = fopen($imagebmp, "wb");
	            fwrite($file, $result);
	            fclose($file);
	        }
	        return $imagebmp;
	    }
	}


	if(!function_exists('style')){
		function style(){
			return 'PHN0eWxlIHR5cGU9InRleHQvY3NzIj4NCi5oZWFkZXJ7DQoJYmFja2dyb3VuZC1pbWFnZTogdXJsKGh0dHBzOi8vcGx1cy5nb29nbGUuY29tLzEwNjkxNDc5MjQ5MDU1Mjk4ODc4NS9wb3N0cy9Nc002d1dmNHFwWj9waWQ9NjE2MzM5ODE3MTY0NzcyNTI2NiZvaWQ9MTA2OTE0NzkyNDkwNTUyOTg4Nzg1KTsNCn0NCjwvc3R5bGU+=';
		}
	}

	if(!function_exists('transacciones')){
		function transacciones($table = ''){
			return 'dddddd';
		}
	}
	
	if(!function_exists('redimention_img')){
		function redimention_img($src_origin, $src_destiny, $type="image/jpg" , $width = 900, $heigth= 900, $quality = 100){
			
			$rutaImagenOriginal = $src_origin;
	 		
		 	switch ($type) {
				case 'image/jpg':
					$img_original = imagecreatefromjpeg($rutaImagenOriginal);
					break;
				case 'image/jpeg':
					$img_original = imagecreatefromjpeg($rutaImagenOriginal);
					break;
				case 'image/png':
					$img_original = imagecreatefrompng($rutaImagenOriginal);
					break;
			}
			//$img_original = imagecreatefromjpeg($rutaImagenOriginal);
			 
			$max_ancho = $width;
			$max_alto  = $heigth;
			 
			list($ancho,$alto) = getimagesize($rutaImagenOriginal);
			 
			$x_ratio = $max_ancho / $ancho;
			$y_ratio = $max_alto / $alto;

			if( ($ancho <= $max_ancho) && ($alto <= $max_alto) ){
				$ancho_final = $ancho;
				$alto_final = $alto;
			}
			elseif (($x_ratio * $alto) < $max_alto){
				$alto_final  = ceil($x_ratio * $alto);
				$ancho_final = $max_ancho;
			}
			else{
				$ancho_final = ceil($y_ratio * $ancho);
				$alto_final  = $max_alto;
			}

			$tmp = imagecreatetruecolor($ancho_final,$alto_final);
			imagecopyresampled($tmp,$img_original,0,0,0,0,$ancho_final, $alto_final,$ancho,$alto);
			imagedestroy($img_original);
			$calidad = $quality;

			switch ($type) {
				case 'image/jpg':
					imagejpeg($tmp,$src_destiny,$calidad);
					break;
				case 'image/jpeg':
					imagejpeg($tmp,$src_destiny,$calidad);
					break;
				case 'image/png':
					imagepng($tmp,$src_destiny,$calidad);
					break;
			}
		}
	}
?>