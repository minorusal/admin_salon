jQuery(document).ready(function(){
	jQuery('#search-query').focus();
	jQuery('#search-query').keypress(function(event){
		var keycode = (event.keyCode ? event.keyCode : event.which);
		if(keycode == '13'){  
			buscar();
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
        	jQuery('#form_embalaje_save').remove();
        	jQuery('#form_embalaje_edit').remove();
           if(id_content==1){
           		var funcion = 'buscar';
           		jQuery('#a-1').html(data+input_keypress('search-query', funcion));
           		jQuery('#search-query').val(filtro).focus();
           		tool_tips();
           }else{
           		jQuery('#a-'+id_content).html(data);
           		var chosen  = 'jQuery(".chzn-select").chosen();';
           		jQuery('#a-'+id_content).html(data+include_script(chosen));
           }
        }
    });
}
function buscar(){
	var filtro = jQuery('#search-query').val();
	jQuery.ajax({
		type:"POST",
		url: path()+"compras/embalaje/listado",
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
function agregar(){
	var progress = progress_initialized('registro_loader');
	var btn          = jQuery("button[name='save_almacen']");
	btn.attr('disabled','disabled');
	jQuery('#mensajes').hide();
	var incomplete   = values_requeridos('form_embalaje_save');
    var embalaje     = jQuery('#embalaje').val();
    var clave_corta  = jQuery('#clave_corta').val();
    var descripcion  = jQuery('#descripcion').val();
	jQuery.ajax({
		type:"POST",
		url: path()+"compras/embalaje/insert",
		dataType: "json",
		data: {incomplete :incomplete, embalaje:embalaje, clave_corta:clave_corta, descripcion:descripcion,},
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
function detalle(id_embalaje){
	jQuery('#ui-id-2').click();
	jQuery.ajax({
        type: "POST",
        url: path()+"compras/embalaje/detalle",
        dataType: 'json',
        data: {id_embalaje : id_embalaje},
        success: function(data){
        	var chosen = 'jQuery(".chzn-select").chosen();';
        	jQuery('#a-0').html('');
        	jQuery('#a-2').html(data);
        	jQuery('#a-2').html(data+include_script(chosen));
        	jQuery('#ui-id-2').show('slow');
        }
    });
}
function update(){
	var progress = progress_initialized('update_loader');
	jQuery('#mensajes_update').hide();
	var btn          = jQuery("button[name='update']");
	btn.attr('disabled','disabled');
	var btn_text     = btn.html();	
	var incomplete   = values_requeridos('form_embalaje_edit');
	var id_embalaje  = jQuery('#id_embalaje').val();
    var embalaje     = jQuery('#embalaje').val();
    var clave_corta  = jQuery('#clave_corta').val();
    var descripcion  = jQuery('#descripcion').val();
	jQuery.ajax({
		type:"POST",
		url: path()+"compras/embalaje/update",
		dataType: "json",
		data: {incomplete :incomplete,id_embalaje:id_embalaje, embalaje:embalaje, clave_corta:clave_corta, descripcion:descripcion},
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

function eliminar(id_embalaje){
	var progress = progress_initialized('loader_global');
    jQuery.ajax({
        type: "POST",
        url: path()+"compras/embalaje/eliminar_registro",
        dataType: 'json',
        data: {id_embalaje : id_embalaje},
        beforeSend: function(){
        },
        success: function(data){
        	if(data.success == 'true'){
        		jQuery('#ico-eliminar_'+data.id_embalaje).parent().parent().parent().remove();
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

function confirm_delete(id_embalaje){
	promp_delete(eliminar,id_embalaje);
}