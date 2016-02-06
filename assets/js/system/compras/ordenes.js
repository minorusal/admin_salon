jQuery(document).ready(function(){
	var Buscar = jQuery('#search-query');
	Buscar.focus();
	Buscar.keyup(function(e) { if(e.which == 13) buscar(); });
});

function buscar(){
	var filtro = jQuery('#search-query').val();
	jQuery.ajax({
		type:"POST",
		url: path()+"compras/ordenes/listado",
		dataType: "json",
		data: {filtro : filtro},
		beforeSend : function(){
			jQuery("#loader").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
		},
		success : function(data){
			var funcion = 'buscar';
        	jQuery("#loader").html('');
        	jQuery('#a-1').html(data+input_keypress('search-query', funcion));
			jQuery('#search-query').val(filtro).focus();
			tool_tips();
		}
	})
}
function load_content(uri, id_content){
	jQuery('#ui-id-2').hide('slow');
	var filtro = jQuery('#search-query').val();
	var functions = [];
    jQuery.ajax({
        type: "POST",
        url: uri,
        dataType: 'json',
        data: {filtro : filtro, tabs:1},
        success: function(data){
        	jQuery('#formulario').remove();
           if(id_content==1){
           		jQuery('#ui-id-3').hide('slow');
           		var funcion = 'buscar';
           		jQuery('#a-1').html(data+input_keypress('search-query', funcion));
           		jQuery('#search-query').val(filtro).focus();
           		tool_tips();
           }
           else if(id_content==3){
           	jQuery('#ui-id-3').show('slow');
           }else{
           		jQuery('#ui-id-3').hide('slow');
          	 	functions.push('jQuery(".chzn-select").chosen();');
          	 	functions.push('calendar_dual("orden_fecha","entrega_fecha");');
          	 	functions.push('allow_only_numeric_integer();'); 
           		jQuery('#a-'+id_content).html(data+include_script(functions));
           }
        }
    });
}
function detalle(id_compras_orden){	
	var functions = [];
	jQuery.ajax({
        type: "POST",
        url: path()+"compras/ordenes/detalle",
        dataType: 'json',
        data: {id_compras_orden : id_compras_orden},
        success: function(data){
        	jQuery('#a-0').html('');
        	functions.push('jQuery(".chzn-select").chosen();');
          	functions.push('calendar_dual_detalle("orden_fecha","entrega_fecha")');
        	jQuery('#a-2').html(data+include_script(functions));
        	jQuery('#ui-id-2').show('slow');
        	jQuery('#ui-id-2').click();
        }
    });
}
function actualizar(){
	var progress = progress_initialized('update_loader');	
		jQuery('#mensajes_update').hide();		
		var btn          = jQuery("button[name='update']");
		btn.attr('disabled','disabled');
  		// Obtiene campos en formulario
  		var objData = formData('#formulario');

  		objData['incomplete'] = values_requeridos();
		jQuery.ajax({
			type:"POST",
			url: path()+"compras/ordenes/actualizar",
			dataType: "json",			
			data : objData,
			beforeSend : function(){
			btn.attr('disabled',true);
		},
		success : function(data){
			if(data.success == 'true' ){
				jgrowl(data.mensaje);
			}else{
				jQuery("#mensajes_update").html(data.mensaje).show('slow');	
			}
		}
	  }).error(function(){
	       		progress.progressTimer('error', {
		            errorText:'ERROR!',
		            onFinish:function(){
		            }
	            });
	           btn.attr('disabled',false);
	        }).done(function(){
		        progress.progressTimer('complete');
		        btn.attr('disabled',false);
	  });
}
function eliminar(id){	
	// id = (!id)?false:id;
	// if(id)if(!confirm('Esta seguro de eliminar el registro: '+id)) return false; 
	// jQuery('#mensajes_update').hide();		
	var btn = jQuery("button[name='eliminar']");
	btn.attr('disabled','disabled');
		// Obtiene campos en formulario
		var objData = formData('#formulario');
		objData['id_compras_orden'] = (!objData['id_compras_orden'])?id:objData['id_compras_orden'];
		objData['msj_grid'] = (id)?1:0;
	jQuery.ajax({
		type:"POST",
		url: path()+"compras/ordenes/eliminar",
		dataType: "json",			
		data : objData,
		beforeSend : function(){
			imgLoader("#update_loader");
		},
		success : function(data){
			if(data.msj_grid==1){
		    	jQuery("#mensajes_grid").html(data.contenido).show('slow');
		    	jQuery('#ico-eliminar_'+id).closest('tr').fadeOut(function(){
					jQuery(this).remove();
				});
			}else{
				jQuery("#update_loader").html('');				
			    jQuery("#mensajes_update").html(data.contenido).show('slow');
			}

		}
	});
}
function confirm_delete(id){
	promp_delete(eliminar,id);
}
function insert(){	
	var progress = progress_initialized('registro_loader');	
	var btn   = jQuery("button[name='save']");
	//btn.attr('disabled','disabled');
	jQuery('#mensajes').hide();	
	// Obtiene campos en formulario
  	var objData = formData('#formulario');
  	objData['incomplete'] = values_requeridos();
	jQuery.ajax({
		type:"POST",
		url: path()+"compras/ordenes/insert",
		dataType: "json",
		data : objData,
		beforeSend : function(){
			btn.attr('disabled',true);
		},
		success : function(data){
		    if(data.success == 'true' ){
				clean_formulario();
				jgrowl(data.mensaje);
			}else{
				jQuery("#mensajes").html(data.mensaje).show('slow');	
			} 
		}
	}).error(function(){
	       		progress.progressTimer('error', {
		            errorText:'ERROR!',
		            onFinish:function(){
		            }
	            });
	           btn.attr('disabled',false);
	        }).done(function(){
		        progress.progressTimer('complete');
		        btn.attr('disabled',false);
	  });
}
function show_proveedor(id_tipo){
	if(id_tipo==1){
		jQuery('#proveedores').show('slow');
		jQuery('#prefactura').show('slow');
		jQuery('[name=id_proveedor]').addClass('requerido');
		jQuery('#prefactura_num').addClass('requerido');
	}else{
		jQuery('#proveedores').hide('slow');
		jQuery('#prefactura').hide('slow');
		jQuery('[name=id_proveedor]').removeClass('requerido');
		jQuery('#prefactura_num').removeClass('requerido');
	}
}
function show_direccion(id_sucursal){
	jQuery.ajax({
        type: "POST",
        url: path()+"compras/ordenes/show_direccion",
        dataType: 'json',
        data: {id_sucursal : id_sucursal},
        success: function(data){
          jQuery('#entrega_direccion').html(data);
        }
    });
}
function articulos(id_compras_orden){	
	var functions=[];
	jQuery.ajax({
        type: "POST",
        url: path()+"compras/ordenes/articulos",
        dataType: 'json',
        data: {id_compras_orden : id_compras_orden},
        success: function(data){
        	jQuery('#a-0').html('');
        	functions.push('jQuery(".chzn-select").chosen();');
          	functions.push('calendar_dual_detalle("orden_fecha","entrega_fecha");');
          	functions.push('allow_only_numeric_integer();'); 
        	jQuery('#a-3').html(data+include_script(functions));
        	jQuery('#ui-id-3').show('slow');
        	jQuery('#ui-id-3').click();
        }
    });
}
function get_orden_listado_articulo(id_compras_articulo_precios){
	// Obtener el total de lineas
	arrayTable = [];
	jQuery('#dyntable2 td.consecutivo').each(function() {
	    arrayTable.push(jQuery(this).html());
	});
	var consecutivo = arrayTable.length;
	// Enviar datos
	var id_compras_orden = jQuery('#id_compras_orden').val();
	id_dentificador=jQuery('#idarticuloprecios_'+id_compras_articulo_precios).val();
	if(typeof  id_dentificador =="undefined"){
		id_dentificador=0;
	}else{
		id_dentificador=id_dentificador.split('_');
	}
	if(id_dentificador==id_compras_articulo_precios){
		//jQuery("#"+id_compras_articulo_precios).remove();
	}
	else{
		jQuery.ajax({
			type:"POST",
			url: path()+"compras/ordenes/get_data_articulo",
			dataType: "json",
			data : {id_compras_articulo_precios:id_compras_articulo_precios,id_compras_orden:id_compras_orden, consecutivo:consecutivo},
			beforeSend : function(){
				jQuery("#registro_loader").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
			},
			success : function(data){
					var validar_data = validar_exist_listado(id_compras_articulo_precios);
					if(validar_data.id==1){
						jQuery('#mensajes').hide('slow');
						jQuery('#dyntable2').show('slow');
						jQuery("#mensajes_update").html('').hide('slow');
						jQuery("#dyntable2 > tbody").append(data);
						var numeric      = 'allow_only_numeric_integer();';
						jQuery('#a-2').html(include_script(numeric));
					}else{
						jQuery('#mensajes').hide('slow');
						jQuery('#dyntable2').show('slow');
						jQuery("#mensajes_update").html('').hide('slow');
						jQuery("#dyntable2 > tbody").append(data);
						var numeric      = 'allow_only_numeric_integer();';
						jQuery('#a-2').html(include_script(numeric));
						insert_orden_listado_articulo(id_compras_articulo_precios);
					}			
					calcula_costo2(id_compras_articulo_precios);		
			}
		});
	}
}
function validar_exist_listado(id){
	var id_compras_orden = jQuery('#id_compras_orden').val();
	var validar;
	jQuery.ajax({
			type:"POST",
			url: path()+"compras/ordenes/validar_exist_listado",
			dataType: "json",
			async:false,
			data : {id_compras_articulo_precios:id,id_compras_orden:id_compras_orden},
			beforeSend : function(){
				jQuery("#registro_loader").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
			},
			success : function(data){
					 validar= data;
			}
		});
	return validar;
}
function insert_orden_listado_articulo(id_compras_articulo_precios){
	var id_compras_orden = jQuery('#id_compras_orden').val();
	// Obtiene campos en formulario
  	var objData = formData('#formulario');
	jQuery.ajax({
		type:"POST",
		url: path()+"compras/ordenes/insert_orden_listado_articulos",
		dataType: "json",
		data : {id_compras_articulo_precios:id_compras_articulo_precios,id_compras_orden:id_compras_orden},
		beforeSend : function(){
			jQuery("#registro_loader").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
		},
		success : function(data){}
	});
}
/*function validanumero(input,id){
   jQuery(input+id).keyup(function () {
    this.value = this.value.replace(/[^0-9.]/g,''); 
  }); 
}*/
function calcula_costo2(id_compras_articulo_precios){
	//validanumero('#cantidad_',id_compras_articulo_precios);
	var costo_sin_impuesto = parseFloat(jQuery('#costo_sin_impuesto_'+id_compras_articulo_precios).val());
	var cantidad = parseFloat(jQuery('#cantidad_'+id_compras_articulo_precios).val());
	var costo_x_cantidad=costo_sin_impuesto*cantidad;	
	jQuery('#costo_x_cantidad'+id_compras_articulo_precios).html(numeral(costo_x_cantidad).format('0,0.00'));
	jQuery('#costo_x_cantidad_hidden'+id_compras_articulo_precios).val(costo_x_cantidad);
	calcula_subtotal(id_compras_articulo_precios);
	update_orden_listado();
	calcula_valores_finales(id_compras_articulo_precios);
}
function calcula_valores_finales(id){
	var moneda = jQuery('#moneda').val();
	var valor=[];
	var valor_2=[];
	var valor_3=[];
	var valor_4=[];
	var result;
	var result_2;
	var result_3;
	var total=0;
	var subtotal=0;
	var descuento=0;
	var impuesto=0;
	//CALCULA EL DESCUENTO
	jQuery('[name^=descuento]').each(function(){
		valor_2.push(parseFloat(jQuery(this).val()));
	});
	jQuery('[name^=costo_x_cantidad_hidden]').each(function(){
		valor_4.push(parseFloat(jQuery(this).val()));
	});
	jQuery('[name^=subtotal__hidden]').each(function(index){
		result_2=parseFloat((parseFloat(valor_4[index])*parseFloat(valor_2[index]))/100);
		if(result_2==0){

		}else{
		descuento=descuento+result_2;
		}
	});

	//CALCULA EL SUBTOTAL
	jQuery(valor_4).each(function(index,value){
		result=parseFloat(value);
		subtotal= subtotal+result;
	});

	//CALCULA EL IMPUESTO
	jQuery('[name^=valor_hidden_impuesto]').each(function(){
		valor_3.push(parseFloat(jQuery(this).val()));
	});
	jQuery(valor_3).each(function(index,value){
		result_3=parseFloat(value);
		if(result_3==0){

		}else{
			impuesto= parseFloat(impuesto)+parseFloat(result_3);
		}
	});
	/////////////////////////////////////////////
	if(isNaN(subtotal)){
		subtotal=0;
	}
	if(isNaN(descuento)){
		descuento=0;
	}
	if(isNaN(impuesto)){
		impuesto=0;
	}
	jQuery('#subtotal').val(subtotal);
	jQuery('#value_subtotal').html('<strong>'+ moneda+' '+ numeral(subtotal).format('0,0.00') +'</strong>');

	jQuery('#descuento_total').val(descuento);
	jQuery('#value_descuento').html('<strong> - '+ moneda+' '+numeral(descuento).format('0,0.00')+'</strong>');

	jQuery('#impuesto_total').val(impuesto);
	jQuery('#value_impuesto').html('<strong>'+ moneda+' '+ numeral(impuesto).format('0,0.00')+'</strong>');

	total=(subtotal-descuento)+impuesto;
	jQuery('#total_data').val(total);
	jQuery('#value_total').html('<strong>'+ moneda+' '+ numeral(total).format('0,0.00')+'</strong>');	
}
function calcula_subtotal(id){
	//validanumero('#descuento_',id);
	var	valor_hidden_impuesto;
	var valor_1;
	var valor_2;
	var valor_impuesto;	
	var total;
	var subtotal;
	var costo_x_cantidad_hidden = parseFloat(jQuery('#costo_x_cantidad_hidden'+id).val());
	var desc = parseFloat(jQuery('#descuento_'+id).val());
	var impuesto = parseFloat(jQuery('#impuesto_'+id).val());
	if(desc>=101){
		jQuery('#descuento_'+id).val('');
		descuento=0;
	}
	else{
		//return false
		var descuento =parseFloat(desc/100);
		//SE CALCULA SUBTOTAL
		if(descuento==0 || isNaN(descuento)){
			subtotal=costo_x_cantidad_hidden;
		}else{
			descuento=costo_x_cantidad_hidden*descuento;
			subtotal=costo_x_cantidad_hidden-descuento;
		}
		jQuery('#subtotal_'+id).html(numeral(subtotal).format('0,0.00'));
		jQuery('#subtotal__hidden'+id).val(subtotal);
		// SE CALCULA EL VALOR DEL IMPUESTO
		valor_1=((subtotal*impuesto)/100);
		valor_impuesto = parseFloat(valor_1.toFixed(3));
		jQuery('#valor_hidden_impuesto_'+id).val(valor_impuesto);
		jQuery('#valor_impuesto_'+id).html(numeral(valor_impuesto).format('0,0.00'));
		// SE CALCULA EL TOTAL
		valor_2 = subtotal+valor_impuesto;
		total = parseFloat(valor_2.toFixed(3));
		jQuery('#total_hidden_'+id).val(total);
		jQuery('#total_'+id).html(numeral(total).format('0,0.00'));
		update_orden_listado();		
	}
	calcula_valores_finales(id);
}
function update_orden_listado(){
	// Obtiene campos en formulario
	var id_compras_orden = jQuery('#id_compras_orden').val();
  	var objData = formData('#formulario_listado');
  	//alert(dump_var(objData));
  	//alert(id_compras_orden);
	jQuery.ajax({
		type:"POST",
		url: path()+"compras/ordenes/update_orden_listado_precios",
		dataType: "json",
		data : objData,
		beforeSend : function(){
			jQuery("#registro_loader").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
		},
		success : function(data){}
	});
}
function deshabilitar_orden_lisatdo(id){
	var id_compras_orden = jQuery('#id_compras_orden').val();
	// Obtener el consecutivo de la linea
	var consecutivo = jQuery("#"+id).find('span[name="consecutivo"]').html();
	// Pregunta 
	id = (!id)?false:id;
	if(id)if(!confirm('Esta seguro de eliminar la linea: # '+consecutivo)) return false;
	// Ejecuta
	jQuery.ajax({
        type: "POST",
        url: path()+"compras/ordenes/deshabilitar_orden_lisatdo",
        dataType: 'json',
        data: {id_compras_orden:id_compras_orden,id_compras_articulo_precios : id},
        success: function(data){
        	if(data.id==1){
		    	jQuery("#mensajes_grid").html(data.contenido).show('slow');
		    	jQuery("#"+id).closest('tr').fadeOut(function(){
		    		jQuery("#"+id).remove();
				});
				jQuery("#mensajes").html(data.contenido).show('slow');
			}else{
				jQuery("#update_loader").html('');				
			    jQuery("#mensajes").html(data.contenido).show('slow');
			}
        }
    });
}
function cerrar_orden_listado(){
	jQuery("#cerrar").hide();
  	jQuery("#cancelar").hide();
	jQuery('#mensajes').hide();	
	// Obtiene campos en formulario
  	var objData = formData('#formulario_listado');
  	objData['incomplete'] = values_requeridos();
	jQuery.ajax({
		type:"POST",
		url: path()+"compras/ordenes/cerrar_orden_listado",
		dataType: "json",
		data : objData,
		beforeSend : function(){
			jQuery("#registro_loader").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
		},
		success : function(data){
			if(data.id==1){}
			jQuery("#registro_loader").html('');
		    jQuery("#mensajes").html(data.contenido).show('slow');
			
		}
	});
}
function cancelar_orden_listado(){
	var id_compras_orden = jQuery('#id_compras_orden').val();
	jQuery("#cancelar").hide();
	jQuery('#cerrar').hide();
	jQuery('#mensajes').hide();	
	jQuery.ajax({
		type:"POST",
		url: path()+"compras/ordenes/cancelar_orden_listado",
		dataType: "json",
		data : {id_compras_orden:id_compras_orden},
		beforeSend : function(){
			jQuery("#registro_loader").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
		},
		success : function(data){
			if(data.id==1){}
			jQuery("#registro_loader").html('');
		    jQuery("#mensajes").html(data.contenido).show('slow');
		}
	});
}
function detalle_articulos_precio(id_compras_articulo_precio){
  jQuery('#ui-id-2').click();
  jQuery.ajax({
        type: "POST",
        url: path()+"compras/listado_precios/detalle",
        dataType: 'json',
        data: {id_compras_articulo_precio : id_compras_articulo_precio},
        success: function(data){
          var chosen = 'jQuery(".chzn-select").chosen();';
          jQuery('#a-0').html('');
          jQuery('#a-2').html(data);
          jQuery('#a-2').html(data+include_script(chosen));
          jQuery('#ui-id-2').show('slow');
         // calcula_costos();
        }
    });
}