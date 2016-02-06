jQuery(document).ready(function(){
	jQuery('#search-query').focus();
	jQuery('#search-query').keypress(function(event){
		var keycode = (event.keyCode ? event.keyCode : event.which);
		if(keycode == '13'){  
			buscar();
		} 
	});
});

function load_content(uri, id_content){
	jQuery('#ui-id-2').hide('slow');
	var filtro = jQuery('#search-query').val();
    jQuery.ajax({
        type: "POST",
        url: uri,
        dataType: 'json',
        data: {filtro : filtro, tabs:1},
        success: function(data){
        	jQuery('#form_servicios_edit').remove();
        	jQuery('#form_servicios_save').remove();
           if(id_content==1){
           		var funcion = 'buscar';
           		jQuery('#a-1').html(data+input_keypress('search-query', funcion));
           		jQuery('#search-query').val(filtro).focus();
           		tool_tips();
           }else{
           		jQuery('#a-'+id_content).html(data);
           		jQuery('#timepicker1,#timepicker2').timepicker({
					beforeShow: function(input, inst){
						var newclass = 'smart-forms'; 
						var smartpikr = inst.dpDiv.parent();
						if (!smartpikr.hasClass('smart-forms')){
							inst.dpDiv.wrap('<div class="'+newclass+'"></div>');
						}
					}					
				});

           	}
        }
    });
}

function buscar(){
	var filtro = jQuery('#search-query').val();
	jQuery.ajax({
		type:"POST",
		url: path()+"sucursales/horarios_servicio/listado",
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

function detalle(id_servicio){
	jQuery('#ui-id-2').click();
	jQuery.ajax({
        type: "POST",
        url: path()+"sucursales/horarios_servicio/detalle",
        dataType: 'json',
        data: {id_servicio : id_servicio},
        success: function(data){
        	jQuery('#a-0').html('');
        	jQuery('#a-2').html(data);
        	jQuery('#ui-id-2').show('slow');

        	jQuery('#timepicker1,#timepicker2').timepicker({
				beforeShow: function(input, inst) {
					var newclass = 'smart-forms'; 
					var smartpikr = inst.dpDiv.parent();
					if (!smartpikr.hasClass('smart-forms')){
						inst.dpDiv.wrap('<div class="'+newclass+'"></div>');
					}
				}					
			});
        }
    });
}

function actualizar(){
	var progress = progress_initialized('update_loader');
	jQuery('#mensajes_update').hide();
	var btn             = jQuery("button[name='actualizar']");
	btn.attr('disabled','disabled');
	var btn_text        = btn.html();	

	var objData = formData('#form_servicios_edit');
	objData['incomplete']  = values_requeridos('form_servicios_save');
	
	jQuery.ajax({
		type:"POST",
		url: path()+"sucursales/horarios_servicio/actualizar",
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
	var btn          = jQuery("button[name='save_servicio']");
	btn.attr('disabled','disabled');
	jQuery('#mensajes').hide();

	var objData = formData('#form_servicios_save');
  	objData['incomplete'] = values_requeridos('form_servicios_save');
	
	jQuery.ajax({
		type:"POST",
		url: path()+"sucursales/horarios_servicio/insert_servicio",
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

function eliminar(id_servicio){
	var progress = progress_initialized('loader_global');
    jQuery.ajax({
        type: "POST",
        url: path()+"sucursales/horarios_servicio/eliminar_registro",
        dataType: 'json',
        data: {id_servicio : id_servicio},
        beforeSend: function(){
        },
        success: function(data){
        	if(data.success == 'true'){
        		jQuery('#ico-eliminar_'+data.id_servicio).parent().parent().parent().remove();
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

function confirm_delete(id_servicio){
	promp_delete(eliminar,id_servicio);
}