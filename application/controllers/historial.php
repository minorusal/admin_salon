<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class historial extends Base_Controller {
	public function __construct(){
		parent::__construct();
		$this->load->model('nutricion/programacion_model','db_model');
	}

	public function index(){

		$especiales = array();
		$fecha_hoy = strtotime(date('Y/m/d'));
		// $fecha_hoy = strtotime('2016-02-01');

		$sucursales = $this->db_model->db_get_sucursales();
		// print_debug($sucursales);
		$count = 0;
		$index = 0;
		$descartados          = array();
		$festivos             = array();
		$especiales           = array();
		foreach ($sucursales as $key => $value) {
			// print_debug($value,false);
			$params_ciclo         = $this->db_model->get_params_ciclos($value['id_sucursal']);
			$dias_descartados     = $this->db_model->get_dias_descartados($value['id_sucursal']);
			$dias_festivos        = $this->db_model->get_dias_festivos($value['id_sucursal']);
			$dias_especiales      = $this->db_model->get_dias_especiales_contenido_ciclo_insumos($value['id_sucursal']);
			$ciclos_programados   = $this->db_model->get_programacion_contenido_ciclo_insumos($value['id_sucursal']);
			
			$params_inicio        = strtotime(str_replace('/', '-', $params_ciclo[0]['fecha_inicio']));
			$params_termino       = strtotime(str_replace('/', '-', $params_ciclo[0]['fecha_termino']));

			if ($params_inicio <= $fecha_hoy && $params_termino >= $fecha_hoy) {

				if(is_array($dias_festivos)){
					foreach ($dias_festivos as $key => $items) {
						$festivos[] = strtotime(str_replace('/', '-', $items['fecha'])); 
					}
				}
				
				if(is_array($dias_descartados)){
					foreach ($dias_descartados as $key => $items) {
						$descartados[] = $items['dia_index'];
					}
				}

				if(is_array($dias_especiales)){
					foreach ($dias_especiales as $key => $items) {
						$especiales[strtotime(str_replace('/', '-', $items['fecha']))][] =  $items;
					}
				}
				if(is_array($ciclos_programados)){
					foreach ($ciclos_programados as $key => $items) {
						$ciclos[$items['orden']][] = $items;
					}
					// print_debug($ciclos);
					for($i=$params_inicio; $i<=$fecha_hoy; $i = strtotime("+1 day", $i)){
						if($i == $fecha_hoy){
							$indexar= true;
						}

						if(array_key_exists($i, $especiales)){
							if($indexar){
								$rows_format[$i]= $especiales[$i];
								// print_debug($rows_format);
							}
							$index++;
							continue;
						}
						if(!array_key_exists($index, $ciclos)){
							$index = 0;
						}
						if(!in_array($i, $festivos)){
							$day = (date('N', $i) == 7) ? 0 : date('N', $i);
							
							if(!in_array($day, $descartados)){
								
								if($indexar){
									$rows_format[$i] = $ciclos[$index];
								}
								$index++;
							}
						}else{
							$index++;
						}
					}
					// print_debug($rows_format);
					foreach ($rows_format as $key => $value_ciclo) {
						$size = count($value_ciclo);
						for($j=0;$j<$size;$j++){
							$value_ciclo[$j]['id_sucursal'] = $value['id_sucursal'];
							$value_ciclo[$j]['timestamp']   = $this->timestamp();
							// $value_ciclo[$j]['timestamp']   = '2016-02-01 15:29:12';
						}   
					}
							// print_debug($value_ciclo);
							$insert = $this->db_model->insert_ciclo_hoy($value_ciclo);
				}
			}else{
				// echo 'ERROR!';
			}
		}

		if($insert){
			echo 'EXITO!';
		}
	}
}
