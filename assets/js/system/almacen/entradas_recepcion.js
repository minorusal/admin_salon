jQuery(document).ready(function(){
	var Buscar = jQuery('#search-query');
	//Buscar.focus();
	Buscar.keyup(function(e) { if(e.which == 13) buscar(); });
});

function buscar(){
	var filtro = jQuery('#search-query').val();
	jQuery.ajax({
		type:"POST",
		url: path()+"almacen/entradas_recepcion/listado",
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
	jQuery('#ui-id-3').hide('slow');
	var filtro = jQuery('#search-query').val();
	var functions = [];
    jQuery.ajax({
        type: "POST",
        url: uri,
        dataType: 'json',
        data: {filtro : filtro, tabs:1},
        success: function(data){
           if(id_content==1){
           		var funcion = 'buscar';
           		jQuery('#a-1').html(data+input_keypress('search-query', funcion));
           		jQuery('#search-query').val(filtro).focus();
           		tool_tips();
           }else{
           		functions.push('jQuery(".chzn-select").chosen();');
          	 	functions.push('calendar_actual("fecha_factura")');
          	 	jQuery('#a-'+id_content).html(data+include_script(functions));

           }
        }
    });
}
function agregar(){
	var btn          = jQuery("button[name='save_entreda']");
	btn.attr('disabled','disabled');
	jQuery('#mensajes').hide();
	var incomplete   	 = values_requeridos();
    var no_orden      	 = jQuery('#no_orden').val();
    var no_factura  	 = jQuery('#no_factura').val();
    var fecha_factura  	 = jQuery('#fecha_factura').val();
    var fecha_recepcion  = jQuery('#fecha_recepcion').val();
    
	jQuery.ajax({
		type:"POST",
		url: path()+"almacen/entradas_recepcion/get_data_orden",
		dataType: "json",
		data: {incomplete :incomplete, no_orden:no_orden, no_factura:no_factura, fecha_factura:fecha_factura, fecha_recepcion:fecha_recepcion },
		beforeSend : function(){
			jQuery("#registro_loader").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
		},
		success : function(data){
			btn.removeAttr('disabled');

			var data = data.split('|');
			if(data[0]==1){
				clean_formulario();
			}
			jQuery("#registro_loader").html('');
		    jQuery("#mensajes").html(data[1]).show('slow');
		}
	})
}
function articulos(id_compras_orden){	
	var functions=[];
	jQuery.ajax({
        type: "POST",
        url: path()+"almacen/entradas_recepcion/articulos",
        dataType: 'json',
        data: {id_compras_orden : id_compras_orden},
        success: function(data){
        	jQuery('#a-0').html('');
        	functions.push('jQuery(".chzn-select").chosen();');
          	functions.push('calendar_no_futuras("fecha_factura")');
        	jQuery('#a-3').html(data+include_script(functions));
        	jQuery('#ui-id-3').show('slow');
        	jQuery('#ui-id-3').click();
        }
    });
}
function mostrar_modal(id){
	jQuery("#mensajes_update").html('');
	var lote_modal = jQuery('#lote_modal_'+id).val();
	var cantidad_lote;
	if(lote_modal == undefined){
		cantidad_lote	= jQuery('#cantidad_lote_'+id).val();
	}else{
		cantidad_lote	= jQuery('#cantidad_resta_'+id).val();
	}
	var cantidad 		= jQuery('#cantidad_'+id).val();
	var proveedor		= jQuery('#proveedor_'+id).val();
	var articulo		= jQuery('#articulo_'+id).val();
	var presentacion    = jQuery('#presentacion_'+id).val();
	var btn_aceptar    	= 'aceptar_lote';
	var btn_cerrar    	= 'volver_lote';
	if(jQuery('#listado_'+id).is(':checked')){
		var objData = formData('#formulario');
	  	objData['incomplete'] = values_requeridos();
		jQuery.ajax({
			type:"POST",
			url: path()+"almacen/entradas_recepcion/modal_lote_caducidad",
			dataType: "json",
			data : {id:id,
					cantidad:cantidad,
				//	caducidad_val : caducidad_val,
				//	lote_val : lote_val,
					cantidad_lote : cantidad_lote,
					proveedor  : proveedor,
					articulo : articulo,
					presentacion : presentacion,
					btn_aceptar : btn_aceptar,
					btn_cerrar : btn_cerrar},
			beforeSend : function(){
				jQuery("#registro_loader").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
			},
			success : function(data){
				var promp_content = {
									content_01:{
										html:data,
										buttons:{ }
									}
								};
				jQuery.prompt(promp_content);
				calendar("caducidad");

			}
		});
	}
	jQuery('#listado_'+id).prop("checked", "");
}
function muestra_modal_actualizacion(id,id_valor){
	var cantidad 			= jQuery('#candidad_contador_'+id_valor).val();
	var caducidad_val  		= jQuery('#caducidad_contador_'+id_valor).val();
	var lote_val			= jQuery('#lote_contador_'+id_valor).val();
	var proveedor			= jQuery('#proveedor_'+id).val();
	var articulo			= jQuery('#articulo_'+id).val();
	var presentacion    	= jQuery('#presentacion_'+id).val();
	var btn_aceptar    		= 'actualizar_modal';
	var btn_cerrar    		= 'close_modal';
	//if(jQuery('#listado_'+id).is(':checked')){
		var objData = formData('#formulario');
	  	objData['incomplete'] = values_requeridos();
		jQuery.ajax({
			type:"POST",
			url: path()+"almacen/entradas_recepcion/modal_lote_caducidad",
			dataType: "json",
			data : {id:id,
					id_valor:id_valor,
					cantidad:cantidad,
					caducidad_val : caducidad_val,
					lote_val : lote_val,
					proveedor  : proveedor,
					articulo : articulo,
					presentacion : presentacion,
					btn_aceptar : btn_aceptar,
					btn_cerrar : btn_cerrar},
			beforeSend : function(){
				jQuery("#registro_loader").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
			},
			success : function(data){
				var promp_content = {
									content_01:{
										html:data,
										buttons:{ }
									}
								};
				jQuery.prompt(promp_content);
				calendar("caducidad");

			}
		});
	//}
}
function validar_cantidad(id){
	var cantidad_lote 	  = jQuery('#cantidad_lote_modal').val();
	var cantidad_resta    = jQuery('#cantidad_resta_'+id).val();
	var cantidad_total;
	cantidad_total=cantidad_resta-cantidad_lote;
	jQuery('#cantidad_resta_'+id).val(cantidad_total);
	jQuery('#cantidad_origen_'+id).html(numeral(cantidad_total).format('0,0.00')+' Pz');
}
function aceptar_lote(id){	
	validar_cantidad(id);
	var valor= 1;
	var cantidad_val    = jQuery('#cantidad_'+id).val();
	var cantidad_resta  = jQuery('#cantidad_resta_'+id).val();
	if(cantidad_resta<=0){
		if(cantidad_resta>=0){
			jQuery('#'+id).hide('slow');
			jQuery('#listado_'+id).prop("checked", "checked");
		}else{
			jQuery('#cantidad_resta_'+id).val(cantidad_val);
			jQuery('#cantidad_origen_'+id).html(numeral(cantidad_val).format('0,0.00')+' Pz');
			jQuery.ajax({
				type:"POST",
				url: path()+"almacen/entradas_recepcion/muestra_mensaje",
				dataType: "json",
				data : {valo:1},
				beforeSend : function(){
					jQuery("#registro_loader").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
				},
				success : function(data){
					if(data.id==1){
						jQuery("#registro_loader").html('');
						jQuery.prompt.close();
					    jQuery("#mensajes_update").html(data.contenido).show('slow');
					}
				}
			});
			return false;
		}
	}else{
		jQuery('#listado_'+id).prop("checked", "");
	}
	jQuery('#table_listado').show('slow');

	var tds=9;
	var columnas;
	var lote 	  		= jQuery('#lotemodal').val();
	var caducidad 		= jQuery('#caducidad').val();
	var cantidad_lote 	= jQuery('#cantidad_lote_modal').val();
	var cont=1;
	var cont_tr = jQuery('#columna_1').val();
	var nuevaFila;
	var cantidad = jQuery('#cantidad_'+id).val();
	var hiddens;
	var btn;
	var lote_hidden;
	var caducidad_hidden;
	var cantidad_hidden;
	var contador=jQuery('#lote_contador_1').val();
	var existencia = jQuery('#candidad_modal_'+id).val();
	var total_hidden = jQuery('#total_hidden_'+id).val();
	var ver_btotal;
	var total;
	ver_total = parseFloat(total_hidden)/parseFloat(cantidad);
	total= (parseFloat(ver_total)*parseFloat(cantidad_lote));
	var td_valor=new Array(
						'',
						jQuery('#proveedor_'+id).val(),
						jQuery('#articulo_'+id).val(),
						jQuery('#presentacion_'+id).val(),
						'',
						'',
						'',
						'',
						''
					);
	for(var i=0;i<tds;i++){
		if(i==4){
			if(typeof(contador) == 'undefined'){
				lote_hidden='<input type="hidden" id="lote_modal_'+id+'" name="lote_modal[]"  data-campo="lote_modal['+id+'-'+cont+']" value="'+lote+'"><input type="hidden" id="lote_actualizar_'+id+'" value="'+lote+'">';
				valor_lote='<input type="hidden" id="lote_contador_'+cont+'" name="lote_contador[]"  data-campo="lote_contador['+id+'-'+cont+']" value="'+lote+'">';
				campo_lote = '<span id="lote_lbl_'+cont+'">'+lote+'</span>';
				onlcick_actualizar ='muestra_modal_actualizacion('+id+','+cont+')';
			}else{
				jQuery('input[name="lote_modal[]"]').each(function() {
					cont++;
					lote_hidden='<input type="hidden" id="lote_modal_'+id+'" name="lote_modal[]"  data-campo="lote_modal['+id+'-'+cont+']" value="'+lote+'">';
					valor_lote='<input type="hidden" id="lote_contador_'+cont+'" name="lote_contador[]"  data-campo="lote_contador['+id+'-'+cont+']" value="'+lote+'">';
					campo_lote = '<span id="lote_lbl_'+cont+'">'+lote+'</span>';
					onlcick_actualizar ='muestra_modal_actualizacion('+id+','+cont+')';
				});
			}
		}else{
			lote_hidden='';
			valor_lote='';
			campo_lote='';
		}
		 if(i==5){
			if(typeof(contador) == 'undefined'){
				cantidad_hidden='<input type="hidden" id="candidad_modal_'+id+'" name="candidad_modal[]"  data-campo="candidad_modal['+id+'-'+cont+']" value="'+cantidad_lote+'"><input type="hidden" id="cantidad_actualizar_'+id+'" value="'+cantidad+'">';
				valor_cantidad='<input type="hidden" id="candidad_contador_'+cont+'" name="candidad_contador[]"  data-campo="candidad_contador['+id+'-'+cont+']" value="'+cantidad_lote+'">';
				campo_cantidad ='<span id="cantidad_lbl_'+cont+'">'+numeral(cantidad_lote).format('0,0.00')+' Pz</span>';
				onlcick ='remove_tr('+cont+','+id+')';
				id_tr=cont;
			}else{
				jQuery('input[name="candidad_modal[]"]').each(function() {
					//cont++;
					cantidad_hidden='<input type="hidden" id="candidad_modal_'+id+'" name="candidad_modal[]"  data-campo="candidad_modal['+id+'-'+cont+']" value="'+cantidad_lote+'">';
					valor_cantidad='<input type="hidden" id="candidad_contador_'+cont+'" name="candidad_contador[]"  data-campo="candidad_contador['+id+'-'+cont+']" value="'+cantidad_lote+'">';
					campo_cantidad ='<span id="cantidad_lbl_'+cont+'">'+numeral(cantidad_lote).format('0,0.00')+' Pz</span>';
					onlcick ='remove_tr('+cont+','+id+')';
					id_tr=cont;
				});
			}
		}else{
			cantidad_hidden='';
			valor_cantidad='';
			campo_cantidad='';
		}
	 	if(i==6){
			if(typeof(contador) == 'undefined'){
				caducidad_hidden='<input type="hidden" id="caducidad_modal_'+id+'" name="caducidad_modal[]"  data-campo="caducidad_modal['+id+'-'+cont+']" value="'+caducidad+'"><input type="hidden" id="caducidad_actualizar_'+id+'" value="'+caducidad+'">';
				valor_caducidad='<input type="hidden" id="caducidad_contador_'+cont+'" name="caducidad_contador[]"  data-campo="caducidad_contador['+id+'-'+cont+']" value="'+caducidad+'">';
				campo_caducidad ='<span id="caducidad_lbl_'+cont+'">'+caducidad+'</span>';
			}else{
				jQuery('input[name="caducidad_modal[]"]').each(function() {
					//cont++;
					caducidad_hidden='<input type="hidden" id="caducidad_modal_'+id+'" name="caducidad_modal[]"  data-campo="caducidad_modal['+id+'-'+cont+']" value="'+caducidad+'">';
					valor_caducidad='<input type="hidden" id="caducidad_contador_'+cont+'" name="caducidad_contador[]"  data-campo="caducidad_contador['+id+'-'+cont+']" value="'+caducidad+'">';
					campo_caducidad ='<span id="caducidad_lbl_'+cont+'">'+caducidad+'</span>';
				});
			}
		}else{
			caducidad_hidden='';
			valor_caducidad='';
			campo_caducidad='';
		}
		if(i==7){
			if(typeof(contador) == 'undefined'){
				campo_total ='<span id="total_lbl_'+cont+'">'+total.toFixed(2)+'</span>';
			}else{
				jQuery('input[name="caducidad_modal[]"]').each(function() {
					campo_total ='<span id="total_lbl_'+cont+'">'+total.toFixed(2)+'</span>';
				});
			}
		}else{
			campo_total='';
		}
		if(i==8){
			btn='<span id="ico-eliminar_15" class="ico_acciones ico_eliminar fa fa-times" onclick="'+onlcick+'" title="Eliminar"></span>';
			btn+='<span id="ico-articulos_18" class="ico_detalle fa fa-search-plus" onclick="'+onlcick_actualizar+'" title="Lang_agregar_articulos"></span>';
		}else{btn='';}
		hiddens=campo_lote+campo_cantidad+campo_caducidad+campo_total+lote_hidden+caducidad_hidden+cantidad_hidden+valor_lote+valor_cantidad+valor_caducidad+btn;
		columnas+='<td>'+hiddens+td_valor[i]+'</td>';
    }

	jQuery('#table_listado tr:last').after('<tr id="columna_'+id_tr+'">'+columnas+'</tr>');
	jQuery('#lote_val_'+id).val(lote);
	jQuery('#caducidad_val_'+id).val(caducidad);
	jQuery('#cantidad_lote_'+id).val(cantidad_lote);
	realiza_calculos(id)
	jQuery.prompt.close();

	var cont=0;
	jQuery(jQuery('#dyntable2 >tbody >tr')).each(function(index,value){
		if(jQuery(this).is(':visible')){
			cont++;
		}
	});
	if(cont==2){
		jQuery('#dyntable2').hide('slow');
	}
}
function remove_tr(id_tr,id){
	var cantidad_modal 	= jQuery('#candidad_contador_'+id_tr).val();
	var cantidad_origen = jQuery('#cantidad_resta_'+id).val();
	var resultado;
	if(jQuery('#'+id).is(':visible')){
		resultado = parseFloat(cantidad_origen)+parseFloat(cantidad_modal);
		jQuery('#cantidad_resta_'+id).val(resultado);
	}else{
		jQuery('#listado_'+id).prop("checked", "");
		jQuery('#'+id).show('slow');
		jQuery('#cantidad_resta_'+id).val(cantidad_modal);
	}
	jQuery('#columna_'+id_tr).remove();
}
function hide_menu(){
	var cont=0;
	jQuery(jQuery('#dyntable2 >tbody >tr')).each(function(index,value){
		if(jQuery(this).is(':visible')){
			cont++;
		}
	});
	if(cont==1){
		jQuery('#dyntable2').hide('slow');
	}
}
function actualizar_modal(id,id_valor){
	jQuery("#mensajes_update").html('');
	var cantidad_origen		= jQuery('#cantidad_'+id).val();
	var lote 				= jQuery('#lotemodal').val();
	var caducidad 			= jQuery('#caducidad').val();
	var cantidad_lote 		= jQuery('#cantidad_lote_modal').val();
	var cantidad_restante  	= jQuery('#cantidad_resta_'+id).val();
	var candidad_contador 	= jQuery('#candidad_contador_'+id_valor).val();
	var total_hidden 		= jQuery('#total_hidden_'+id).val();
	var resultado;
	var resultado1;
	var ver_total;
	var total;
	var numero;
	ver_total = parseFloat(total_hidden)/parseFloat(cantidad_origen);
	total= (parseFloat(ver_total)*parseFloat(cantidad_lote));
	if(jQuery('#'+id).is(':visible')){
		numero = parseFloat(cantidad_lote)-parseFloat(candidad_contador);
		resultado = parseFloat(cantidad_restante) - parseFloat(numero);
		if(resultado<0){
			jQuery.ajax({
				type:"POST",
				url: path()+"almacen/entradas_recepcion/muestra_mensaje",
				dataType: "json",
				data : {valo:1},
				beforeSend : function(){
					jQuery("#registro_loader").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
				},
				success : function(data){
					if(data.id==1){
						jQuery("#registro_loader").html('');
						jQuery.prompt.close();
					    jQuery("#mensajes_update").html(data.contenido).show('slow');
					}
				}
			});
			return false;
		}else if(resultado==0){
			//oculta tr de la tabla
			jQuery('#'+id).hide('slow');
			jQuery.prompt.close();
			//asigna el valor de cantidad restante
			jQuery('#cantidad_resta_'+id).val(resultado);
			jQuery('#cantidad_origen_'+id).html(numeral(resultado).format('0,0.00')+' Pz');
			//valor hiddens, valores que se guardan en la BD
			jQuery('#lote_contador_'+id_valor).val(lote);
			jQuery('#candidad_contador_'+id_valor).val(numeral(cantidad_lote).format('0,0.00')+' Pz');
			jQuery('#caducidad_contador_'+id_valor).val(caducidad);
			//valor etiquetas
			jQuery('#lote_lbl_'+id_valor).html(lote);
			jQuery('#cantidad_lbl_'+id_valor).html(numeral(cantidad_lote).format('0,0.00')+' Pz');
			jQuery('#caducidad_lbl_'+id_valor).html(caducidad);
			jQuery('#total_lbl_'+id_valor).html(total.toFixed(2));
		}else{
			resultado  = parseFloat(cantidad_restante)+parseFloat(candidad_contador);
			resultado1 = parseFloat(resultado) - parseFloat(cantidad_lote);
			jQuery.prompt.close();
			//asigna el valor de cantidad restante
			jQuery('#cantidad_resta_'+id).val(resultado1);
			jQuery('#cantidad_origen_'+id).html(numeral(resultado1).format('0,0.00')+' Pz');
			//valor hiddens, valores que se guardan en la BD
			jQuery('#candidad_contador_'+id_valor).val(numeral(cantidad_lote).format('0,0.00')+' Pz');
			jQuery('#lote_contador_'+id_valor).val(lote);
			jQuery('#caducidad_contador_'+id_valor).val(caducidad);
			//valor etiquetas
			jQuery('#lote_lbl_'+id_valor).html(lote);
			jQuery('#cantidad_lbl_'+id_valor).html(numeral(cantidad_lote).format('0,0.00')+' Pz');
			jQuery('#caducidad_lbl_'+id_valor).html(caducidad);
			jQuery('#total_lbl_'+id_valor).html(total.toFixed(2));
		}
	}else{
		numero = parseFloat(cantidad_lote)-parseFloat(candidad_contador);
		resultado = parseFloat(cantidad_restante) - parseFloat(numero);
		if(resultado<0){
			jQuery.ajax({
				type:"POST",
				url: path()+"almacen/entradas_recepcion/muestra_mensaje",
				dataType: "json",
				data : {valo:1},
				beforeSend : function(){
					jQuery("#registro_loader").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
				},
				success : function(data){
					if(data.id==1){
						jQuery("#registro_loader").html('');
						jQuery.prompt.close();
					    jQuery("#mensajes_update").html(data.contenido).show('slow');
					}
				}
			});
			return false;
		}else if(resultado==0){
			jQuery.prompt.close();
		}else{
			resultado = parseFloat(candidad_contador) - parseFloat(cantidad_lote);
			//quita popup
			jQuery.prompt.close();
			//oculta tr de la tabla
			jQuery('#'+id).show('slow');
			jQuery('#dyntable2').show('slow');
			jQuery('#listado_'+id).prop("checked", "");
			//asigna el valor de cantidad restante
			jQuery('#cantidad_resta_'+id).val(resultado);
			jQuery('#cantidad_origen_'+id).html(numeral(resultado).format('0,0.00')+' Pz');
			//valor hiddens, valores que se guardan en la BD
			jQuery('#lote_contador_'+id_valor).val(lote);
			jQuery('#candidad_contador_'+id_valor).val(cantidad_lote);
			jQuery('#caducidad_contador_'+id_valor).val(caducidad);
			//valor etiquetas
			jQuery('#lote_lbl_'+id_valor).html(lote);
			jQuery('#cantidad_lbl_'+id_valor).html(numeral(cantidad_lote).format('0,0.00')+' Pz');
			jQuery('#caducidad_lbl_'+id_valor).html(caducidad);
			jQuery('#total_lbl_'+id_valor).html(total.toFixed(2));
		}
	}
}
function realiza_calculos(id){
	var moneda 		  	= jQuery('#moneda').val();
	var subtotal 	  	= 0;
	var descuento 	  	= 0;
	var impuesto 	  	= 0;
	var total 		  	= 0;
	var cont 		  	= 0;
	var valor_2 	  	= [];
	var valor_3 	  	= [];
	var valor_4 	  	= [];
	var functions 	  	= [];
	var cantidad 		= jQuery('#cantidad_'+id).val();
	var caducidad_val  	= jQuery('#caducidad_val_'+id).val();
	var lote_val		= jQuery('#lote_val_'+id).val();
	var cantidad_lote	= jQuery('#cantidad_lote_'+id).val();
	var proveedor		= jQuery('#proveedor_'+id).val();
	var articulo		= jQuery('#articulo_'+id).val();
	var presentacion    = jQuery('#presentacion_'+id).val();
	var varl;
	var result;
	var result_2;
	var result_3;
	jQuery('input[name="aceptar[]"]:checked').each(function() {
		valor_2.push(parseFloat(jQuery('#descuento_'+jQuery(this).val()).val()));
		valor_3.push(parseFloat(jQuery('#costo_x_cantidad_hidden'+jQuery(this).val()).val()));
		valor_4.push(parseFloat(jQuery('#valor_hidden_impuesto_'+jQuery(this).val()).val()));
	});
	jQuery(valor_3).each(function(index,value){
		result=parseFloat(value);
		subtotal= subtotal+result;
	});
	//CALCULA EL DESCUENTO
	jQuery(valor_2).each(function(index,value){
		result_2=parseFloat((parseFloat(valor_3[index])*parseFloat(valor_2[index]))/100);
		descuento= descuento+result_2;
	});
	//CALCULA IMPUESTO
	jQuery(valor_4).each(function(index,value){
		result_3=parseFloat(value);
		impuesto= parseFloat(impuesto)+parseFloat(result_3);
	});
	//CALCULA SUBTOTAL
	total=(subtotal-descuento)+impuesto;
	jQuery('#subtotal').val(subtotal);
	jQuery('#value_subtotal').html('<strong>'+ moneda+' '+ numeral(subtotal).format('0,0.00') +'</strong>');
	jQuery('#descuento_total').val(descuento);
	jQuery('#value_descuento').html('<strong> - '+ moneda+' '+numeral(descuento).format('0,0.00')+'</strong>');
	jQuery('#impuesto_total').val(impuesto);
	jQuery('#value_impuesto').html('<strong>'+ moneda+' '+ numeral(impuesto).format('0,0.00')+'</strong>');
	jQuery('#total_data').val(total);
	jQuery('#value_total').html('<strong>'+ moneda+' '+ numeral(total).format('0,0.00')+'</strong>');
	jQuery('input[name="aceptar[]"]').each(function() {
		if(jQuery(this).is(':checked')){
		}else{cont++;}
	});
	if(cont>0){
		jQuery('#comentario').addClass('requerido');
	}else{
		jQuery('#comentario').removeClass('requerido');
	}
}
function recibir_orden(){
	jQuery('#mensajes').hide();	
	// Obtiene campos en formulario
  	var objData 		  = formData('#formulario');
  	//alert(dump_var(objData));
  	objData['incomplete'] = values_requeridos();
	jQuery.ajax({
		type:"POST",
		url: path()+"almacen/entradas_recepcion/insert",
		dataType: "json",
		data : objData,
		beforeSend : function(){
			jQuery("#registro_loader").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
		},
		success : function(data){
			if(data.id==1){
				clean_formulario();
				jQuery('#recibir').hide();	
				jQuery('#volver').hide();	
				jQuery("#mensajes").html(data.contenido).show('slow');
				jQuery('input[name="aceptar[]"]').prop('disabled',true);
			}else{
				jQuery("#registro_loader").html('');
			    jQuery("#mensajes").html(data.contenido).show('slow');
			}
		}
	});
}
function volver_lote(id){
	jQuery('#lote_val_'+id).val('');
	jQuery('#caducidad_val_'+id).val('');
	jQuery('#cantidad_lote_'+id).val('');
	jQuery.prompt.close();
	jQuery('#listado_'+id).prop("checked", "");
}
function close_modal(id){
	jQuery.prompt.close();
}
/*function calcula_totla_pagar(){
	var total;
	var subtotal 	= jQuery('#subtotal_final').val();
	var descuento 	= jQuery('#descuento_final').val();
	var impuesto 	= jQuery('#impuesto_final').val();
	total=parseFloat((subtotal-descuento))+parseFloat(impuesto);
	jQuery('#value_total').html(total);
}*/