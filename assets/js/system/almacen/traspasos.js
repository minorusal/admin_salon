jQuery(document).ready(function(){
	var Buscar = jQuery('#search-query');
	Buscar.focus();
	Buscar.keyup(function(e) { if(e.which == 13) buscar(); });
});
function buscar(){
	var filtro = jQuery('#search-query').val();
	jQuery.ajax({
		type:"POST",
		url: path()+"almacen/traspasos/listado",
		dataType: "json",
		data: {filtro : filtro},
		beforeSend : function(){
			jQuery("#loader").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
		},
		success : function(data){
			var funcion = 'buscar';
        	jQuery("#loader").html('');
        	jQuery('#a-0').html(data+input_keypress('search-query', funcion));
			jQuery('#search-query').val(filtro).focus();
			tool_tips();
		}
	});
}
function load_content(uri, id_content){
	jQuery('#ui-id-1').hide('slow');
	var filtro = jQuery('#search-query').val();
	var functions = [];
    jQuery.ajax({
        type: "POST",
        url: uri,
        dataType: 'json',
        data: {filtro : filtro, tabs:1},
        success: function(data){
           if(id_content==0){
           		var funcion = 'buscar';
           		jQuery('#a-0').html(data+input_keypress('search-query', funcion));
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
function detalle(id_stock){
	var params = [];
	params.push('allow_only_numeric();');
	jQuery.ajax({
        type: "POST",
        url: path()+"almacen/traspasos/detalle",
        dataType: 'json',
        data: {id_stock : id_stock},
        success: function(data){
        	params.push('jQuery(".chzn-select").chosen();');
        	jQuery('#a-1').html(data+include_script(params));
        	jQuery('#ui-id-1').show('slow');
        	jQuery('#ui-id-1').click();
        }
    });
}
function load_gaveta_pas(id_almacen){
	jQuery('#ui-id-1').click();
	  jQuery.ajax({
	        type: "POST",
	        url: path()+"almacen/traspasos/load_gaveta_pas",
	        dataType: 'json',
	        data: {id_almacen : id_almacen},
	        success: function(data){
	         var chosen = 'jQuery(".chzn-select").chosen();';
	          jQuery('#lts_pasillo').html(data['pasillos']+include_script(chosen));
	          jQuery('#lts_gavetas').html(data['gavetas']+include_script(chosen));
	        }
	    });
}
function load_gaveta(id_pasillo){
	jQuery('#ui-id-1').click();
	id_almacen = jQuery('select[name=lts_almacen] option:selected').val();
	  jQuery.ajax({
	        type: "POST",
	        url: path()+"almacen/traspasos/load_gaveta",
	        dataType: 'json',
	        data: {id_pasillo : id_pasillo,id_almacen:id_almacen},
	        success: function(data){
	         var chosen = 'jQuery(".chzn-select").chosen();';
	          jQuery('#lts_gavetas').html(data+include_script(chosen));
	        }
	    });
}
function save(){	
	jQuery('#mensajes_update').hide();	
	var btn          = jQuery("button[name='save']");
	btn.attr('disabled','disabled');
	// Obtiene campos en formulario
	var objData = formData('#formulario');
	objData['incomplete'] = values_requeridos();
	objData['error_stock'] = (parseFloat(objData['stock'])>parseFloat(objData['stock_origen']))?1:0; 
	jQuery.ajax({
		type:"POST",
		url: path()+"almacen/traspasos/update_almacen",
		dataType: "json",			
		data : objData,
		beforeSend : function(){
			imgLoader("#update_loader");
		},
		success : function(data){ 
	    	var data = data.split('|');
	        if(data[0]==1){
				btn.hide();
	        }else{
				btn.removeAttr('disabled');
			}
	      	jQuery("#update_loader").html('');
	        jQuery("#mensajes_update").html(data[1]).show('slow');
	    }
	});
}

function calcula_stock_um(){
	var objData 	= formData('#formulario');
	var stock_input = (parseFloat(objData['stock'])>0)?parseFloat(objData['stock']):0;
	if(stock_input){
		var stock_origen 	= parseFloat(objData['stock_origen']);
		var stock_um_origen = parseFloat(objData['stock_um_origen']);
		var stock_um_final 	= (stock_input*stock_um_origen)/stock_origen;
	}else{
		var stock_um_final 	= 0;
	}
	var resultado = parseFloat(stock_um_final);
	jQuery("#stock_um_destino").val(resultado);
}

function calcula_stock_pz(){
	var objData 	= formData('#formulario');
	var stock_um_input = (parseFloat(objData['stock_um_destino'])>0)?parseFloat(objData['stock_um_destino']):0;
	if(stock_um_input){
		var stock_origen 	= parseFloat(objData['stock_origen']);
		var stock_um_origen = parseFloat(objData['stock_um_origen']);
		var stock_final 	= (stock_um_input*stock_origen)/stock_um_origen;
	}else{
		var stock_final 	= 0;
	}
	var resultado = parseFloat(stock_final);
	jQuery("#stock").val(resultado);
}