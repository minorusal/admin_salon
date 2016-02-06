<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class stock extends Base_Controller{
	/**
	* Nombre:		Control de movimientos de invetario - STOCK
	* Ubicación:	almace/stock
	* Descripción:	Control de movimientos de invetario - STOCK
	* @author:		Oscar Maldonado - O3M
	* Creación: 	2015-07-10
	* Modificación: 
	*/

	public function stock_insert($data){
	// Inserta registro en tabla de stock
		$success = false;
		$id_accion 						= (isset($data['id_accion']))?$data['id_accion']:'';
		$id_stock 						= (isset($data['id_stock']))?$data['id_stock']:'';
		$id_compras_orden_articulo 		= (isset($data['id_compras_orden_articulo']))?$data['id_compras_orden_articulo']:'';
		$id_almacen_entradas_recepcion 	= (isset($data['id_almacen_entradas_recepcion']))?$data['id_almacen_entradas_recepcion']:'';
		$id_articulo_tipo 				= (isset($data['id_articulo_tipo']))?$data['id_articulo_tipo']:'';
		$id_almacen_origen				= (isset($data['id_almacen_origen']))?$data['id_almacen_origen']:''; 
		$id_pasillo_origen 				= (isset($data['id_pasillo_origen']) && $data['id_pasillo_origen']==0)?null:$data['id_pasillo_origen'];
		$id_gaveta_origen				= (isset($data['id_gaveta_origen']))?$data['id_gaveta_origen']:''; 
		$stock_origen 					= (isset($data['stock_origen']))?$data['stock_origen']:''; 
		$stock_um_origen 				= (isset($data['stock_um_origen']))?$data['stock_um_origen']:''; 
		$id_almacen_destino				= (isset($data['id_almacen_destino']))?$data['id_almacen_destino']:''; 
		$id_pasillo_destino 			= (isset($data['id_pasillo_destino']) && $data['id_pasillo_destino']==0)?null:$data['id_pasillo_destino'];
		$id_gaveta_destino				= (isset($data['id_gaveta_destino']))?$data['id_gaveta_destino']:''; 
		$stock_destino					= (isset($data['stock_destino']))?$data['stock_destino']:''; 
		$stock_um_destino				= (isset($data['stock_um_destino']))?$data['stock_um_destino']:''; 		
		$lote 							= (isset($data['lote']))?$data['lote']:'';
		$caducidad 						= (isset($data['caducidad']))?$data['caducidad']:'';
		if($id_accion){
			// Update Stock
			$sqlData= array(
								'id_almacen'		   	   	   => $id_almacen_destino,
								'id_pasillo'				   => $id_pasillo_destino,
								'id_gaveta'		   	   	   	   => $id_gaveta_destino,
								'id_almacen_entradas_recepcion'=> $id_almacen_entradas_recepcion,
								'id_compras_orden_articulo'    => $id_compras_orden_articulo,
								'id_articulo_tipo'			   => $id_articulo_tipo,
								'stock'		   	   			   => $stock_destino,									
								'stock_um'		   	   		   => $stock_um_destino,
								'lote'					   	   => $lote,
								'caducidad'			   	   	   => $caducidad,
								'id_estatus'			  	   => 1, #1 => STOCK
								'timestamp'  	 		       => $this->timestamp(),
								'id_usuario'   		   		   => $this->session->userdata('id_usuario')
							);
			if($traspaso = $this->stock_model->insert_data_stock($sqlData)){
			// Log Stock
				$sqldatalog_stock= array(
								'id_accion'			  		   => $id_accion,
								'id_stock'			   		   => $traspaso,
								'id_almacen_entradas_recepcion'=> $id_almacen_entradas_recepcion,
								'id_compras_orden_articulo'    => $id_compras_orden_articulo,
								'log_id_almacen_origen'		   => $id_almacen_origen,
								'log_id_pasillo_origen'		   => $id_pasillo_origen,
								'log_id_gaveta_origen'		   => $id_gaveta_origen,
								'log_stock_origen'    	 	   => $stock_origen,
								'log_stock_um_origen'  	 	   => $stock_um_origen,
								'log_id_almacen_destino'	   => $id_almacen_destino,
								'log_id_pasillo_destino'	   => $id_pasillo_destino,
								'log_id_gaveta_destino'		   => $id_gaveta_destino,
								'log_stock_destino'    	 	   => $stock_destino,
								'log_stock_um_destino'  	   => $stock_um_destino,
								'log_lote'					   => $lote,
								'log_caducidad'			   	   => $caducidad,
								'timestamp'  	 		       => $this->timestamp(),
								'id_usuario'   		   		   => $this->session->userdata('id_usuario')
							);
				$insertlog = $this->stock_model->insert_stock_log($sqldatalog_stock);
				$success = true;
			}
		}

		if($id_accion == $this->vars->cfg['id_accion_almacen_traspaso']){
			$stock_recalculo 			= $data['stock_origen'] - $data['stock_destino'];
			$stock_um_recalculo			= $data['stock_um_origen'] - $data['stock_um_destino'];
			$data['id_almacen_destino']	= $id_almacen_origen;
			$data['id_pasillo_destino'] = $id_pasillo_origen;
			$data['id_gaveta_destino'] 	= $id_gaveta_origen;
			$data['stock_destino'] 		= $stock_recalculo;
			$data['stock_um_destino'] 	= $stock_um_recalculo;
			$data['id_accion'] 			= $this->vars->cfg['id_accion_almacen_actualizacion']; #6 => ACTUALIZACION
			$success = $this->stock_update($data);
		}

		return $success;
	}

	public function stock_update($data){
	// Actualiza registro en tabla de stock
		$success = false;
		$id_accion 						= (isset($data['id_accion']))?$data['id_accion']:'';
		$id_stock 						= (isset($data['id_stock']))?$data['id_stock']:'';
		$id_compras_orden_articulo 		= (isset($data['id_compras_orden_articulo']))?$data['id_compras_orden_articulo']:'';
		$id_almacen_entradas_recepcion 	= (isset($data['id_almacen_entradas_recepcion']))?$data['id_almacen_entradas_recepcion']:'';
		$id_articulo_tipo 				= (isset($data['id_articulo_tipo']))?$data['id_articulo_tipo']:'';
		$id_almacen_origen				= (isset($data['id_almacen_origen']))?$data['id_almacen_origen']:''; 
		$id_pasillo_origen 				= (isset($data['id_pasillo_origen']) && $data['id_pasillo_origen']==0)?null:$data['id_pasillo_origen'];
		$id_gaveta_origen				= (isset($data['id_gaveta_origen']))?$data['id_gaveta_origen']:''; 
		$stock_origen 					= (isset($data['stock_origen']))?$data['stock_origen']:''; 
		$stock_um_origen 				= (isset($data['stock_um_origen']))?$data['stock_um_origen']:''; 
		$id_almacen_destino				= (isset($data['id_almacen_destino']))?$data['id_almacen_destino']:''; 
		$id_pasillo_destino 			= (isset($data['id_pasillo_destino']) && $data['id_pasillo_destino']==0)?null:$data['id_pasillo_destino'];
		$id_gaveta_destino				= (isset($data['id_gaveta_destino']))?$data['id_gaveta_destino']:''; 
		$stock_destino					= (isset($data['stock_destino']))?$data['stock_destino']:''; 
		$stock_um_destino				= (isset($data['stock_um_destino']))?$data['stock_um_destino']:''; 		
		$lote 							= (isset($data['lote']))?$data['lote']:'';
		$caducidad 						= (isset($data['caducidad']))?$data['caducidad']:'';		

		if($id_accion){
			// Update Stock
			$sqlData  = array(
							'id_stock'     		=> $id_stock,
							'id_almacen'   		=> $id_almacen_destino,
							'id_pasillo'  		=> $id_pasillo_destino,
							'id_gaveta'  		=> $id_gaveta_destino,
							'stock'				=> $stock_destino,
							'stock_um'			=> $stock_um_destino,
							'edit_timestamp'  	=> $this->timestamp(),
							'edit_id_usuario'  	=> $this->session->userdata('id_usuario')
						);
			if($stock_destino<=0){$sqlData['activo']=0;}
			if($traspaso = $this->stock_model->update_data_stock($sqlData)){
			// Log Stock
				if($id_accion == $this->vars->cfg['id_accion_almacen_actualizacion']){
					$id_almacen_destino = $id_almacen_origen;
					$id_pasillo_destino = $id_pasillo_origen;
					$id_gaveta_destino 	= $id_gaveta_origen;
				}
				$sqldatalog_stock= array(
								'id_accion'			  		   => $id_accion,
								'id_stock'			   		   => $id_stock,
								'id_almacen_entradas_recepcion'=> $id_almacen_entradas_recepcion,
								'id_compras_orden_articulo'    => $id_compras_orden_articulo,
								'log_id_almacen_origen'		   => $id_almacen_origen,
								'log_id_pasillo_origen'		   => $id_pasillo_origen,
								'log_id_gaveta_origen'		   => $id_gaveta_origen,
								'log_stock_origen'    	 	   => $stock_origen,
								'log_stock_um_origen'    	   => $stock_um_origen,
								'log_id_almacen_destino'	   => $id_almacen_destino,
								'log_id_pasillo_destino'	   => $id_pasillo_destino,
								'log_id_gaveta_destino'		   => $id_gaveta_destino,
								'log_stock_destino'    	 	   => $stock_destino,
								'log_stock_um_destino' 	 	   => $stock_um_destino,
								'log_lote'					   => $lote,
								'log_caducidad'			   	   => $caducidad,
								'timestamp'  	 		       => $this->timestamp(),
								'id_usuario'   		   		   => $this->session->userdata('id_usuario')
							);
				$insertlog = $this->stock_model->insert_stock_log($sqldatalog_stock);
				$success = true;
			// Stock vacío => ceros
				if($stock_destino<=0){
					$delArray = array('fisico'=>true, 'id_stock'=>$id_stock);
					$success = $this->eliminar_stock_en_cero($delArray);
				}
			}
		}
		return $success;
	}

	public function eliminar_stock_en_cero($data=array()){
	// Copia y elimina registros con stock = 0 y activo = 0
		$borrado_fisico = (isset($data['fisico']))?true:false;
		$id_stock 		= (isset($data['id_stock']))?$data['id_stock']:false;
		$success = $this->stock_model->copy_stock_to_deleted(array('id_stock'=>$id_stock));
		if($borrado_fisico){
			$success = $this->stock_model->delete_stock_en_cero(array('id_stock'=>$id_stock)); #Eliminación física
		}
		return $success;
	}
}
?>