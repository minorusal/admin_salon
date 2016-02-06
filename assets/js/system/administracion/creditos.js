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
        	jQuery('#form_creditos_edit').remove();
        	jQuery('#form_creditos_save').remove();
           if(id_content==1){
           		var funcion = 'buscar';
           		jQuery('#a-1').html(data+input_keypress('search-query', funcion));
           		jQuery('#search-query').val(filtro).focus();
           		tool_tips();
           }else{
           		var numeric      = 'allow_only_numeric();'; 
           		jQuery('#a-'+id_content).html(data+include_script(numeric));
           }
        }
    });
}
function buscar(){
	var filtro = jQuery('#search-query').val();
	jQuery.ajax({
		type:"POST",
		url: path()+"administracion/creditos/listado",
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

function detalle(id_credito){
	jQuery('#ui-id-2').click();
	jQuery.ajax({
        type: "POST",
        url: path()+"administracion/creditos/detalle",
        dataType: 'json',
        data: {id_credito : id_credito},
        success: function(data){
        	var numeric      = 'allow_only_numeric();'; 
        	jQuery('#a-0').html('');
        	jQuery('#a-2').html(data+include_script(numeric));
        	jQuery('#ui-id-2').show('slow');
        }
    });
}
function actualizar(){
	var progress = progress_initialized('update_loader');
	jQuery('#mensajes_update').hide();
	var btn             = jQuery("button[name='actualizar']");
	btn.attr('disabled','disabled');

	var objData = formData('#form_creditos_edit');
  	objData['incomplete'] = values_requeridos('form_creditos_edit');

	jQuery.ajax({
		type:"POST",
		url: path()+"administracion/creditos/actualizar",
		dataType: "json",
		data: {objData:objData},
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


function agregar(){
	var progress = progress_initialized('registro_loader');
	var btn          = jQuery("button[name='save_credito']");
	btn.attr('disabled','disabled');
	jQuery('#mensajes').hide();

	var objData = formData('#form_creditos_save');
  	objData['incomplete'] = values_requeridos('form_creditos_save');

	var incomplete       = values_requeridos();
	var id_credito       = jQuery('#id_credito').val();
    var credito          = jQuery('#credito').val();
    var valor_credito    = jQuery('#valor_credito').val();
    var clave_corta      = jQuery('#clave_corta').val();
    var descripcion      = jQuery('#descripcion').val();
	jQuery.ajax({
		type:"POST",
		url: path()+"administracion/creditos/insert_credito",
		dataType: "json",
		data: {objData:objData},
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

function eliminar(id_credito){
	var progress = progress_initialized('loader_global');
    jQuery.ajax({
        type: "POST",
        url: path()+"administracion/creditos/eliminar_registro",
        dataType: 'json',
        data: {id_credito : id_credito},
        beforeSend: function(){
        },
        success: function(data){
        	if(data.success == 'true'){
        		jQuery('#ico-eliminar_'+data.id_credito).parent().parent().parent().remove();
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

function confirm_delete(id_credito){
	promp_delete(eliminar,id_credito);
}