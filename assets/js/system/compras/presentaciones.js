jQuery(document).ready(function(){
	jQuery('#search-query').focus();
	jQuery('#search-query').keypress(function(event){
		var keycode = (event.keyCode ? event.keyCode : event.which);
		if(keycode == '13'){  
			buscar_presentacion();
		} 
	});
})
function load_content(uri, id_content){
	jQuery('#ui-id-2').hide('slow');
	var filtro = jQuery('#search-query').val();
    jQuery.ajax({
        type: "POST",
        url: uri,
        dataType: 'json',
        data: {filtro : filtro, tabs:1},
        success: function(data){
        	jQuery('#form_presentacion_edit').remove();
        	jQuery('#form_presentacion_save').remove();
           if(id_content==1){
           		var funcion = 'buscar_presentacion';
           		jQuery('#a-1').html(data+input_keypress('search-query', funcion));
           		jQuery('#search-query').val(filtro).focus();
           		tool_tips();
           }else{
           		jQuery('#a-'+id_content).html(data);
           }
        }
    });
}
function buscar_presentacion(){
	var filtro = jQuery('#search-query').val();
	jQuery.ajax({
		type:"POST",
		url: path()+"compras/presentaciones/listado_presentaciones",
		dataType: "json",
		data: {filtro : filtro},
		beforeSend : function(){
			jQuery("#loader").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
		},
		success : function(data){
			var funcion = 'buscar_presentacion';
        	jQuery("#loader").html('');
        	jQuery('#a-1').html(data+input_keypress('search-query', funcion));
			jQuery('#search-query').val(filtro).focus();
			tool_tips();
		}
	})
}
function detalle_presentacion(id_presentacion){
	jQuery('#ui-id-2').click();
	jQuery.ajax({
        type: "POST",
        url: path()+"compras/presentaciones/detalle_presentacion",
        dataType: 'json',
        data: {id_presentacion : id_presentacion},
        success: function(data){
        	jQuery('#a-0').html('');
        	jQuery('#a-2').html(data);
        	jQuery('#ui-id-2').show('slow');
        }
    });
}
function update_presentacion(){
	var progress = progress_initialized('update_loader');
	jQuery('#mensajes_update').hide();
	var btn          = jQuery("button[name='update_presentacion']");
	btn.attr('disabled','disabled');
	var btn_text     = btn.html();	
	var incomplete       = values_requeridos('form_presentacion_edit');
	var id_presentacion  = jQuery('#id_presentacion').val();
    var presentacion     = jQuery('#presentaciones').val();
    var clave_corta      = jQuery('#clave_corta').val();
    var descripcion      = jQuery('#descripcion').val();
  
	jQuery.ajax({
		type:"POST",
		url: path()+"compras/presentaciones/update_presentacion",
		dataType: "json",
		data: {incomplete :incomplete,id_presentacion:id_presentacion, presentacion:presentacion, clave_corta:clave_corta, descripcion:descripcion },
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

function insert_presentacion(){
	var progress = progress_initialized('registro_loader');
	var btn          = jQuery("button[name='save_presentacion']");
	btn.attr('disabled','disabled');
	jQuery('#mensajes').hide();
	var incomplete   = values_requeridos('form_presentacion_save');
    var presentacion = jQuery('#presentaciones').val();
    var clave_corta  = jQuery('#clave_corta').val();
    var descripcion  = jQuery('#descripcion').val();
  
	jQuery.ajax({
		type:"POST",
		url: path()+"compras/presentaciones/insert_presentacion",
		dataType: "json",
		data: {incomplete :incomplete, presentacion:presentacion, clave_corta:clave_corta, descripcion:descripcion },
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

function eliminar(id_presentacion){
	var progress = progress_initialized('loader_global');
    jQuery.ajax({
        type: "POST",
        url: path()+"compras/presentaciones/eliminar_registro",
        dataType: 'json',
        data: {id_presentacion : id_presentacion},
        beforeSend: function(){
        },
        success: function(data){
        	if(data.success == 'true'){
        		jQuery('#ico-eliminar_'+data.id_presentacion).parent().parent().parent().remove();
        		jgrowl(data.mensaje);
        	}else{
        		jQuery("#mensajes_grid").html(data.mensaje).show('slow');
        	}
        }
    }).error(function(){
   		progress.progressTimer('error', {
            errorText:'ERROR!',
            onFinish:function(){
            }
        });
    }).done(function(){
	        progress.progressTimer('complete');
  		});
}

function confirm_delete(id_presentacion){
	promp_delete(eliminar,id_presentacion);
}


