jQuery(document).ready(function(){
	var Buscar = jQuery('#search-query');
	Buscar.focus();
	Buscar.keyup(function(e) { if(e.which == 13) buscar(); });
});
function buscar(){
	var filtro = jQuery('#search-query').val();
	jQuery.ajax({
		type:"POST",
		url: path()+"almacen/entradas_almacen/listado",
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
function detalle(id_compras_orden_articulo){
	jQuery.ajax({
        type: "POST",
        url: path()+"almacen/entradas_almacen/detalle",
        dataType: 'json',
        data: {id_compras_orden_articulo : id_compras_orden_articulo},
        success: function(data){
        	var chosen = 'jQuery(".chzn-select").chosen();';
        	jQuery('#a-0').html('');
        	jQuery('#a-2').html(data+include_script(chosen));
        	jQuery('#ui-id-2').show('slow');
        	jQuery('#ui-id-2').click();
        }
    });
}
function load_gaveta_pas(id_almacen){
	jQuery('#ui-id-2').click();
	  jQuery.ajax({
	        type: "POST",
	        url: path()+"almacen/entradas_almacen/load_gaveta_pas",
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
	jQuery('#ui-id-2').click();
	id_almacen = jQuery('select[name=lts_almacen] option:selected').val();
	  jQuery.ajax({
	        type: "POST",
	        url: path()+"almacen/entradas_almacen/load_gaveta",
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

	jQuery.ajax({
		type:"POST",
		url: path()+"almacen/entradas_almacen/update_almacen",
		dataType: "json",			
		data : objData,
		beforeSend : function(){
			imgLoader("#update_loader");
		},
		success : function(data){
			btn.removeAttr('disabled');   
	      var data = data.split('|');
	        if(data[0]==1){
	        }
	      	jQuery("#update_loader").html('');
	        jQuery("#mensajes_update").html(data[1]).show('slow');
	    }
	});
}