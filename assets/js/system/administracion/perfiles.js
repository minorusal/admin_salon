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
        	jQuery('#form_perfil_edit').remove();
        	jQuery('#form_perfil_save').remove();
        	var treeview           = 'load_treeview("treeview-modules");';
        	var treeview_childrens = 'treeview_childrens();'; 
           if(id_content==1){
           		var funcion = 'buscar';
           		jQuery('#a-1').html(data+input_keypress('search-query', funcion));

           		jQuery('#search-query').val(filtro).focus();
           		tool_tips();
           }else{
           		jQuery('#a-'+id_content).html(data+include_script(treeview+treeview_childrens));
           }
        }
    });
}
function buscar(){
	var filtro = jQuery('#search-query').val();
	jQuery.ajax({
		type:"POST",
		url: path()+"administracion/perfiles/listado",
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
function detalle(id_perfil){
	jQuery('#ui-id-2').click();
	jQuery.ajax({
        type: "POST",
        url: path()+"administracion/perfiles/detalle",
        dataType: 'json',
        data: {id_perfil : id_perfil},
        success: function(data){
        	var treeview = 'load_treeview("treeview-modules");';
        	var treeview_childrens = 'treeview_childrens();'; 
        	jQuery('#a-0').html('');
        	jQuery('#a-2').html(data+include_script(treeview+treeview_childrens));
        	jQuery('#ui-id-2').show('slow');
        }
    });
}
function actualizar(){
	var progress = progress_initialized('update_loader');
	jQuery('#mensajes_update').hide();
	var btn             = jQuery("button[name='actualizar']");
	btn.attr('disabled','disabled');
	var btn_text        = btn.html();	
	var nivel_1 = [];
	var nivel_2 = [];
	var nivel_3 = [];
	var objData = formData('#form_perfil_edit');
	objData['incomplete']  = values_requeridos('form_perfil_edit');

	jQuery("input[name='nivel_1']:checked").each(function(){
	  nivel_1.push(jQuery(this).val());
	});
	
	jQuery("input[name='nivel_2']:checked").each(function(){
	  nivel_2.push(jQuery(this).val());
	});
	
	jQuery("input[name='nivel_3']:checked").each(function(){
	  nivel_3.push(jQuery(this).val());
	});

	objData['nivel_1']     = (nivel_1.length>0) ? nivel_1.join(',') : nivel_1;
	objData['nivel_2']     = (nivel_2.length>0) ? nivel_2.join(',') : nivel_2;
	objData['nivel_3']     = (nivel_3.length>0) ? nivel_3.join(',') : nivel_3;
	
	jQuery.ajax({
		type:"POST",
		url: path()+"administracion/perfiles/actualizar",
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
	var btn     = jQuery("button[name='save_perfil']");
	btn.attr('disabled','disabled');
	jQuery('#mensajes').hide();
	var nivel_1 = [];
	var nivel_2 = [];
	var nivel_3 = [];
	var objData = formData('#form_perfil_save');
	objData['incomplete']  = values_requeridos('form_perfil_save');

	jQuery("input[name='nivel_1']:checked").each(function(){
	  nivel_1.push(jQuery(this).val());
	});
	
	jQuery("input[name='nivel_2']:checked").each(function(){
	  nivel_2.push(jQuery(this).val());
	});
	
	jQuery("input[name='nivel_3']:checked").each(function(){
	  nivel_3.push(jQuery(this).val());
	});

	objData['nivel_1']     = (nivel_1.length>0) ? nivel_1.join(',') : nivel_1;
	objData['nivel_2']     = (nivel_2.length>0) ? nivel_2.join(',') : nivel_2;
	objData['nivel_3']     = (nivel_3.length>0) ? nivel_3.join(',') : nivel_3;
	jQuery.ajax({
		type:"POST",
		url: path()+"administracion/perfiles/insert_perfil",
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

function eliminar(id_perfil){
	var progress = progress_initialized('loader_global');
    jQuery.ajax({
        type: "POST",
        url: path()+"administracion/perfiles/eliminar_registro",
        dataType: 'json',
        data: {id_perfil : id_perfil},
        beforeSend: function(){
        },
        success: function(data){
        	if(data.success == 'true'){
        		jQuery('#ico-eliminar_'+id_perfil).parent().parent().parent().remove();
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

function confirm_delete(id_perfil){
	promp_delete(eliminar,id_perfil);
}





