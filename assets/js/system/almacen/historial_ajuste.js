jQuery(document).ready(function(){
	var Buscar = jQuery('#search-query');
	Buscar.focus();
	Buscar.keyup(function(e) { if(e.which == 13) buscar(); });
});
function buscar(){	
	var filtro = jQuery('#search-query').val();
	jQuery.ajax({
		type:"POST",
		url: path()+"almacen/historial_ajuste/listado",
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
        url: path()+"almacen/historial_ajuste/detalle",
        dataType: 'json',
        data: {id_almacen_ajuste : id_almacen_ajuste},
        success: function(data){
         	jQuery('#a-0').html('');
        	jQuery('#a-2').html(data);
          jQuery('#ui-id-2').show('slow');
        }
    });
}