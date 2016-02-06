jQuery(document).ready(function(){
	jQuery('#search-query').focus();
	jQuery('#search-query').keypress(function(event){
		var keycode = (event.keyCode ? event.keyCode : event.which);
		if(keycode == '13'){  
			buscar_articulo();
		} 
	});
	//jQuery('#ico-eliminar_1').parent().parent().parent().css("background","green");
})

function buscar_articulo(){
	var filtro = jQuery('#search-query').val();
	jQuery.ajax({
		type:"POST",
		url: path()+"compras/articulos/listado_articulos",
		dataType: "json",
		data: {filtro : filtro},
		beforeSend : function(){
			jQuery("#loader").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
		},
		success : function(data){
			var funcion = 'buscar_articulo';
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
    jQuery.ajax({
        type: "POST",
        url: uri,
        dataType: 'json',
        data: {filtro : filtro, tabs:1},
        success: function(data){
        	jQuery('#formulario_articulo_edit').remove();
        	jQuery('#formulario_articulo_insert').remove();
           if(id_content==1){
           		var funcion = 'buscar_articulo';
           		jQuery('#a-1').html(data+input_keypress('search-query', funcion));
           		jQuery('#search-query').val(filtro).focus();
           		tool_tips();
           }else{
          	 	var chosen  = 'jQuery(".chzn-select").chosen();';
           		jQuery('#a-'+id_content).html(data+include_script(chosen));
           }
        }
    });
}

function detalle_articulo(id_articulo){
	jQuery('#ui-id-2').click();
	jQuery.ajax({
        type: "POST",
        url: path()+"compras/articulos/detalle_articulo",
        dataType: 'json',
        data: {id_articulo : id_articulo},
        success: function(data){
        	var chosen = 'jQuery(".chzn-select").chosen();';
        	jQuery('#a-0').html('');
        	jQuery('#a-2').html(data+include_script(chosen));
        	jQuery('#ui-id-2').show('slow');
        }
    });
}

function update_articulo(){
	var progress = progress_initialized('update_loader');
	jQuery('#mensajes_update').hide();
	var btn    = jQuery("button[name='update_articulo']");
	btn.attr('disabled','disabled');
	var objData = formData('#formulario_articulo_edit');
		objData['incomplete'] = values_requeridos('formulario_articulo_edit');    
	jQuery.ajax({
		type:"POST",
		url: path()+"compras/articulos/update_articulo",
		dataType: "json",
		data: objData,
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

function insert_articulo(){
	var progress = progress_initialized('registro_loader');
	var btn          = jQuery("button[name='save_articulo']");
	btn.attr('disabled','disabled');
	jQuery('#mensajes').hide();
	
	var objData = formData('#formulario_articulo_insert');
  	objData['incomplete'] = values_requeridos('formulario_articulo_insert');

	jQuery.ajax({
		type:"POST",
		url: path()+"compras/articulos/insert_articulo",
		dataType: "json",
		data: objData,
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

function confirm_delete(id_articulo){
	promp_delete(eliminar,id_articulo);
}
function eliminar(id_articulo){
	var progress = progress_initialized('loader_global');
    jQuery.ajax({
        type: "POST",
        url: path()+"compras/articulos/eliminar_articulo",
        dataType: 'json',
        data: {id_articulo : id_articulo},
        beforeSend: function(){
        },
        success: function(data){
        	if(data.success == 'true'){
        		jQuery('#ico-eliminar_'+data.id_articulo).parent().parent().parent().remove();
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