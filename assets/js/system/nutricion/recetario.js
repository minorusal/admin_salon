jQuery(document).ready(function(){
	jQuery('#search-query').focus();
	jQuery('#search-query').keypress(function(event){
		var keycode = (event.keyCode ? event.keyCode : event.which);
		if(keycode == '13'){  
			buscar();
		} 
	});
});
function buscar(){
	var filtro = jQuery('#search-query').val();
	jQuery.ajax({
		type:"POST",
		url: path()+"nutricion/recetario/listado",
		dataType: "json",
		data: {filtro : filtro},
		beforeSend : function(){
			imgLoader("#loader");
		},
		success : function(data){
			var funcion = 'buscar';
        	imgLoader_clean("#loader");
        	jQuery('#a-1').html(data+input_keypress('search-query', funcion));
			jQuery('#search-query').val(filtro).focus();
			tool_tips();
		}
	});
}
function load_content(uri, id_content){
	jQuery('#ui-id-2').hide('slow');
	var filtro = jQuery('#search-query').val();
    jQuery.ajax({
        type: "POST",
        url: uri,
        dataType: 'json',
        data: {filtro : filtro, tabs:1},
        beforeSend : function(){
        	//alert(id_content);
        	if(id_content!=1){
        		imgLoader('#a-'+id_content);
        	}else{
        		imgLoader('#loader');
        	}
		},
        success: function(data){
        	jQuery('#form_recetario_agregar').remove();
        	jQuery('#form_recetario_editar').remove();
        	jQuery('#form_recetario_duplicador').remove();
			if(id_content==1){
			  var funcion = 'buscar';
			  jQuery('#a-1').html(data+input_keypress('search-query', funcion));
			  jQuery('#search-query').val(filtro).focus();
			  tool_tips();
			}else if(id_content==3){
        		//alert(dump_var(id_content));
        		imgLoader_clean('#a-'+id_content);
        		var select_receta_familia = 'select_receta_familia()';
        		var chosen       = 'jQuery(".chzn-select").chosen();';
        		jQuery('#a-'+id_content).html(data+include_script(chosen+select_receta_familia));
			}else{
				imgLoader_clean('#a-'+id_content);
				var numeric      = 'allow_only_numeric();';
				var chosen       = 'jQuery(".chzn-select").chosen();';
				var multi_chosen = "jQuery('select[name=\"lts_insumos_insert\"]').on('change', function(evt, params){edit_porciones(evt, params);});";
				jQuery('#a-'+id_content).html(data+include_script(chosen+multi_chosen+numeric));
			}
        }
    });
}

function select_receta_familia(){
	jQuery('.familia select[name=lts_sucursales_familia]').removeClass('requerido');
	jQuery('.receta').show();
	jQuery('.familia').hide();
	jQuery('input[name=tipo]').click(function(){
   		var valor = jQuery(this).val();
   		if(valor == 'receta'){
   			jQuery('.familia').hide();
   			jQuery('.familia select[name=lts_sucursales_familia]').removeClass('requerido');
   			jQuery('.familia .chzn-select').val('').trigger('liszt:updated');
   			jQuery('select[name=lts_familias]').find('option').each(function(){
   				jQuery(this).remove();
   			});
   			jQuery('select[name=lts_familias]').trigger("liszt:updated");

   			jQuery('.receta select[name=lts_sucursales_receta]').addClass('requerido');
   			jQuery('.receta').show();

   		}else if(valor == 'familia'){
   			jQuery('.receta').hide();
   			jQuery('.receta select[name=lts_sucursales_receta]').removeClass('requerido');
   			jQuery('.receta .chzn-select').val('').trigger('liszt:updated');
   			jQuery('select[name=lts_recetas]').find('option').each(function(){
   				jQuery(this).remove();
   			});
   			jQuery('select[name=lts_recetas]').trigger("liszt:updated");

   			jQuery('.familia select[name=lts_sucursales_familia]').addClass('requerido');
   			jQuery('.familia').show();
   		}
	});
}

function duplicar_recetas(){
	var btn                 = jQuery("button[name='duplicar_recetas']");
	var progress            = progress_initialized('registro_loader');
	var objData             = formData('#form_recetario_duplicador');
	objData['incomplete']   = values_requeridos('form_recetario_duplicador');
	jQuery.ajax({
		type:"POST",
		url: path()+"nutricion/recetario/duplicar_recetas",
		dataType: "json",
		data: {objData : objData},
		beforeSend : function(){
			btn.attr('disabled','disabled');
		},
		success : function(data){
			if(data.success == 'true' ){
				clean_formulario();
				jgrowl(data.mensaje);
				jQuery("#mensajes").html('').show('slow');
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

function load_receta(id){
	jQuery.ajax({
		type:"POST",
		url: path()+"nutricion/recetario/load_receta",
		dataType: "json",
		data: {id : id},
		beforeSend : function(){
			//imgLoader("#loader");
		},
		success : function(data){
			var chosen   = 'jQuery(".chzn-select").chosen();';
			var label    = '<label>Recetas (*):</label>';
			var iniSpan  = '<span class="field">';
			var endSpan  = '</span>';
			jQuery('#recetas_list').html(label+iniSpan+data+endSpan+include_script(chosen));
		}
	});
}

function load_familia(id){
	jQuery.ajax({
		type:"POST",
		url: path()+"nutricion/recetario/load_familia",
		dataType: "json",
		data: {id : id},
		beforeSend : function(){
			//imgLoader("#loader");
		},
		success : function(data){
			var chosen   = 'jQuery(".chzn-select").chosen();';
			var label    = '<label>Familias (*):</label>';
			var iniSpan  = '<span class="field">';
			var endSpan  = '</span>';
			jQuery('#familias_list').html(label+iniSpan+data+endSpan+include_script(chosen));
		}
	});
}

function edit_porciones(evt,params){
	if(params.selected){
		jQuery('#content_porciones_insert').show();
		jQuery.ajax({
	        type: "POST",
	        url: path()+"nutricion/recetario/detalle_articulo", 
	        dataType: 'json',
	        data: {id_articulo : params.selected},
	        beforeSend : function(){
	        	imgLoader('#loader_editar_porciones_insert');
			},
			success:function(data){
				imgLoader_clean('#loader_editar_porciones_insert');
				var numeric      = 'allow_only_numeric();';
				jQuery('#content_porciones_insert').append(data+include_script(numeric));
			}
		});
	}else{
		jQuery('#articulo_'+params.deselected).remove();
	}
}
function agregar(){
	var progress            = progress_initialized('registro_loader');
	var btn                 = jQuery("button[name='save_receta']");
	var objData             = formData('#form_recetario_agregar');
	objData['incomplete']   = values_requeridos('form_recetario_agregar');
	//alert(dump_var(objData));
	jQuery('#a-2').html('');
	jQuery('#mensajes').hide();
	jQuery.ajax({
		type:"POST",
		url: path()+"nutricion/recetario/insert",
		dataType: "json",
		data: {objData: objData},
		beforeSend : function(){
			btn.attr('disabled','disabled');
		},
		success : function(data){
			if(data.success == 'true' ){
				clean_formulario_recetas();
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
function actualizar(){
	var progress            = progress_initialized('update_loader');
	var btn                 = jQuery("button[name='update_receta']");
	var objData             = formData('#form_recetario_editar');
	objData['incomplete']   = values_requeridos('form_recetario_editar');
	jQuery("#mensajes_update").html('').hide('slow');
	jQuery('#mensajes').hide();
	jQuery.ajax({
		type:"POST",
		url: path()+"nutricion/recetario/update",
		dataType: "json",
		data: {objData: objData},
		beforeSend : function(){
			btn.attr('disabled','disabled');
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
function detalle(id_receta){ 
  	jQuery('#a-0').html('');
  	jQuery('#ui-id-2').click();
  	jQuery.ajax({
        type: "POST",
        url: path()+"nutricion/recetario/detalle",
        dataType: 'json',
        data: {id_receta : id_receta},
        success: function(data){
        	var numeric      = 'allow_only_numeric();';
			var chosen       = 'jQuery(".chzn-select").chosen();';
			var multi_chosen = "jQuery('select[name=\"lts_insumos_update\"]').on('change', function(evt, params){edit_porciones(evt, params);});";
          jQuery('#a-2').html(data+include_script(chosen+multi_chosen+numeric));
          jQuery('#ui-id-2').show('slow');
          jQuery('#test').modal()
        }
    });
}
function clean_formulario_recetas(){
	clean_formulario();
	jQuery('#content_porciones_insert').html('').hide();
}

function eliminar(id){
	var progress = progress_initialized('loader_global');
    jQuery.ajax({
        type: "POST",
        url: path()+"nutricion/recetario/eliminar_registro",
        dataType: 'json',
        data: {id : id},
        beforeSend: function(){
        },
        success: function(data){
        	if(data.success == 'true'){
        		jQuery('#ico-eliminar_'+data.id).parent().parent().parent().remove();
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

function confirm_delete(id){
	promp_delete(eliminar,id);
}

function load_region(id_sucursal){
	jQuery.ajax({
        type: "POST",
        url: path()+"nutricion/recetario/load_region",
        dataType: 'json',
        data: {id_sucursal : id_sucursal},
        beforeSend: function(){
        },
        success: function(data){
        	jQuery('#region').html(data.region);
        }
    })
}