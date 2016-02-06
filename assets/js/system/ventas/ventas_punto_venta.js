jQuery(document).ready(function(){
	calendar_dual("fecha_inicio","fecha_final");
});

function paginar(){
    jQuery.ajax({
        type:"POST",
        url: path()+"ventas/paginar",
        dataType: "json",
        data: {ajax:true},
        beforeSend : function(){
            jQuery("#punto_venta").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
        },
        success : function(data){
            jQuery('#table_info').show();
            jQuery("#content_list").html(data.tabla);
            jQuery('#table_info').html(data.item_info+data.paginador);
        }
    });
}

function load_content(uri, id_content){
    jQuery('#ui-id-1').hide('slow');
    var objData = formData('#form_buscar_ventas');
    jQuery.ajax({
        type: "POST",
        url: uri,
        dataType: 'json',
        data: {tabs:0},
        success: function(data){
            if(id_content==0){
                var chosen   = 'jQuery(".chzn-select").chosen();';
                var calendar = 'calendar_dual("fecha_inicio","fecha_final")';
                jQuery('#a-0').html(data+include_script(chosen+calendar));
           }else{
                var comprobante = 'ver_comprobante(id)';
                jQuery('#a-'+id_content).html(data+include_script(comprobante));
           }
        }
    });
}
function load_punto_venta(id){
	jQuery.ajax({
        type: "POST",
        url:  path()+"ventas/ventas_punto_venta/load_punto_venta",
        dataType: 'json',
        data: {id : id},
        beforeSend : function(){
			jQuery("#punto_venta").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
		},
        success: function(data){
        	var chosen  = 'jQuery(".chzn-select").chosen();';
        	jQuery('#punto_venta').html(data+include_script(chosen));
        }
    });
}

function buscar_ventas(){
    var objData = formData('#form_buscar_ventas');
    objData['incomplete'] = values_requeridos('form_buscar_ventas');
    jQuery.ajax({
        type: "POST",
        url:  path()+"ventas/ventas_punto_venta/buscar_ventas",
        dataType: 'json',
        data: {objData : objData},
        beforeSend : function(){
            jQuery("#content_list").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
        },
        success: function(data){
            jQuery('#tableData').show();
            jQuery('#btn_export').html(data.export);
            jQuery("#content_list").html(data.tabla);
            jQuery('#table_info').html(data.item_info+data.paginador);
        }
    });
}

function detalle(id){
    jQuery('#ui-id-1').click();
    jQuery.ajax({
        type: "POST",
        url:  path()+"ventas/ventas_punto_venta/detalle_venta",
        dataType: 'json',
        data: {id : id},
        beforeSend : function(){
            //jQuery("#content_list").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
        },
        success: function(data){
            jQuery('#a-0').html('');
            jQuery('#a-1').html(data);
            jQuery('#ui-id-1').show('slow');
        }
    });
}

function detalles(id){
    jQuery.ajax({
        type: "POST",
        url:  path()+"ventas/ventas_punto_venta/detalle_ventas",
        dataType: 'json',
        data: {id : id},
        beforeSend : function(){
            //jQuery("#content_list").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
        },
        success: function(data){
            var promp_content = {
                                    content_02:{
                                        html:data,
                                        buttons:{}
                                    }
                                };
            jQuery.prompt(promp_content);
            jQuery('.jqi ').css({
               "width": "1000px",
            });
        }
    });
}

function ver_comprobante(id){
     jQuery.ajax({
        type: "POST",
        url:  path()+"ventas/ventas_punto_venta/load_ticket",
        dataType: 'json',
        data: {id:id},
        success: function(data){
             var promp_content = {
                                    content_02:{
                                        html:data,
                                        buttons:{}
                                    }
                                };
            jQuery.prompt(promp_content);
            jQuery('.jqi ').css({
               "width": "550",
            });
        }
    });
}