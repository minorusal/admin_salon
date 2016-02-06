jQuery(document).ready(function(){
  var Buscar = jQuery('#search-query');
  Buscar.focus();
  Buscar.keyup(function(e) { if(e.which == 13) buscar(); });
});

function buscar(){
  var filtro = jQuery('#search-query').val();
  jQuery.ajax({
    type:"POST",
    url: path()+"compras/historial_ordenes/listado",
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
           }else{}
        }
    });
}
function articulos(id_compras_orden){ 
  var functions=[];
  jQuery.ajax({
        type: "POST",
        url: path()+"compras/historial_ordenes/articulos_listado",
        dataType: 'json',
        data: {id_compras_orden : id_compras_orden},
        success: function(data){
          jQuery('#a-0').html('');
          functions.push('jQuery(".chzn-select").chosen();');
            functions.push('calendar_dual_detalle("orden_fecha","entrega_fecha")');
          jQuery('#a-3').html(data+include_script(functions));
          jQuery('#ui-id-3').show('slow');
          jQuery('#ui-id-3').click();
        }
    });
}