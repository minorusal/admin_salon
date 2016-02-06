jQuery(document).ready(function(){
	var Buscar = jQuery('#search-query');
	Buscar.focus();
	Buscar.keyup(function(e) { if(e.which == 13) buscar(); });
});
function buscar(){	
	var filtro = jQuery('#search-query').val();
	jQuery.ajax({
		type:"POST",
		url: path()+"almacen/aprobar_ajustes/listado",
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
	});
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
function detalle(id_almacen_ajuste){
	jQuery('#ui-id-2').click();
	jQuery.ajax({
        type: "POST",
        url: path()+"almacen/aprobar_ajustes/detalle",
        dataType: 'json',
        data: {id_almacen_ajuste : id_almacen_ajuste},
        success: function(data){
         	jQuery('#a-0').html('');
        	jQuery('#a-2').html(data);
        	jQuery('#ui-id-2').show('slow');
        }
    });
}
function calculos(id){
	var stock_total    = parseFloat(jQuery('#stock_total').val());
	var stock_um_total = parseFloat(jQuery('#stock_um_total').val());
	var stock_mov      = parseFloat(jQuery('#stock_mov').val());
	var stock_um_mov   = parseFloat(jQuery('#stock_um_mov').val());
	var mensaje=0;
	var validation = true;
	if(isNaN(stock_mov)){
		stock_mov=0;
	}
	if(isNaN(stock_um_mov)){
		stock_um_mov=0;
	}
	
	if(id==1){
		if(parseFloat(stock_total)>=parseFloat(stock_mov)){
			var um = regla_tres(stock_total,stock_um_total,stock_mov);
			jQuery('#stock_um_mov').val(um);
		}else{mensaje=1;
			jQuery('#stock_um_mov').val(0);
			}

	}else{
		if(parseFloat(stock_um_total)>=parseFloat(stock_um_mov)){
			var um = regla_tres(stock_um_total,stock_total,stock_um_mov);
			jQuery('#stock_mov').val(um);
		}else{
			mensaje=1;
			jQuery('#stock_mov').val(0)
		}
	}
	if(mensaje){
		validation=false;
		alert('no puede ser mayor a la cantidad registrada');
	}
	return validation;
}
function agregar(id_almacen_ajuste){
	var cal = calculos(1);
	if(cal){
		var progress = progress_initialized('update_loader');
		jQuery("#mensajes_update").html('').hide('slow');
		jQuery('#mensajes').hide();
		var btn  = jQuery("button[name='ajuste_save']");
		var btn2 = jQuery("button[name='ajuste_delete']");
		
		btn.attr('disabled','disabled');
		btn2.attr('disabled','disabled');
		var id_articulo    				= jQuery('#id_articulo').val();
		var id_almacen 	   				= jQuery('#id_almacen').val();
		var id_pasillo 	   				= jQuery('#id_pasillo').val();
		var id_gaveta 	   				= jQuery('#id_gaveta').val();
		var id_compras_articulo_precios = jQuery('#id_compras_articulo_precios').val();
		var stock_mov      				= parseFloat(jQuery('#stock_mov').val());
		var stock_um_mov   				= parseFloat(jQuery('#stock_um_mov').val());

		jQuery.ajax({
			type:"POST",
			url: path()+"almacen/aprobar_ajustes/agregar",
			dataType: "json",			
			data : {
					id_articulo	 	  			:  id_articulo,
					id_almacen	 	  			:  id_almacen,
					id_pasillo	 	  			:  id_pasillo,
					id_gaveta	 	  			:  id_gaveta,
					stock_mov 	 	  			:  stock_mov,
					stock_um_mov 	  			:  stock_um_mov,
					id_almacen_ajuste 			:  id_almacen_ajuste,
					id_compras_articulo_precios : id_compras_articulo_precios
			},
			beforeSend : function(){
				btn.attr('disabled',true);
				btn2.attr('disabled','disabled');
			},
			success : function(data){
				if(data.success == 'true' ){
					jgrowl(data.mensaje);
					jQuery("#listaod_afectado").html(data.table).show('slow').delay(3000).fadeIn("slow");	
					btn.hide();
					btn2.hide();
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
		           btn2.attr('disabled','disabled');
		        }).done(function(){
			        progress.progressTimer('complete');
			        btn.attr('disabled',false);
			        btn2.attr('disabled','disabled');
		  });
	}else{
		return false;
	}

}
function rechazar(id_almacen_ajuste){
	var progress = progress_initialized('update_loader');
	jQuery("#mensajes_update").html('').hide('slow');
	jQuery('#mensajes').hide();
	var btn  = jQuery("button[name='ajuste_delete']");
	var btn2 = jQuery("button[name='ajuste_save']");

	btn.attr('disabled','disabled');
	btn2.attr('disabled','disabled');
	jQuery.ajax({
		type:"POST",
		url: path()+"almacen/aprobar_ajustes/rechazar",
		dataType: "json",			
		data : {
				id_almacen_ajuste :  id_almacen_ajuste
		},
		beforeSend : function(){
			btn.attr('disabled',true);
			btn2.attr('disabled',true);
		},
		success : function(data){
			if(data.success == 'true' ){
				jgrowl(data.mensaje);
				jQuery("#listaod_afectado").html(data.table).show('slow').delay(3000).fadeIn("slow");	
				btn.hide();
				btn2.hide();
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
	           btn2.attr('disabled',false);
	        }).done(function(){
		        progress.progressTimer('complete');
		        btn.attr('disabled',false);
		        btn2.attr('disabled',false);
	  });
}