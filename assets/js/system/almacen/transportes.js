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
		url: path()+"almacen/transportes/listado",
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

function detalle(id_transporte){
	jQuery('#ui-id-2').click();
	jQuery.ajax({
        type: "POST",
        url: path()+"almacen/transportes/detalle",
        dataType: 'json',
        data: {id_transporte : id_transporte},
        success: function(data){
        	var chosen = 'jQuery(".chzn-select").chosen();';
        	jQuery('#a-0').html('');
        	jQuery('#a-2').html(data);
        	jQuery('#a-2').html(data+include_script(chosen));
        	jQuery('#ui-id-2').show('slow');
        }
    });
}

function actualizar(){
	var progress = progress_initialized('update_loader');
	jQuery('#mensajes_update').hide();
	var btn          = jQuery("button[name='actualizar']");
	btn.attr('disabled','disabled');
	var btn_text     = btn.html();	
	var incomplete   = values_requeridos();
	var id_transporte   = jQuery('#id_transporte').val();
    var empresa      = jQuery('#txt_empresa').val();
    var conductor      = jQuery('#txt_nombre_conductor').val();
    var licencia      = jQuery('#txt_num_licencia').val();
    var marca      = jQuery('#txt_marca').val();
    var modelo      = jQuery('#txt_modelo').val();
    var placas      = jQuery('#txt_placas').val();
    var clave_corta  = jQuery('#clave_corta').val();
    var descripcion  = jQuery('#descripcion').val();
	jQuery.ajax({
		type:"POST",
		url: path()+"almacen/transportes/actualizar",
		dataType: "json",
		data: {incomplete :incomplete,id_transporte:id_transporte, empresa:empresa, conductor:conductor, licencia:licencia, marca:marca, modelo:modelo, placas:placas, clave_corta:clave_corta, descripcion:descripcion},
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
	var btn          = jQuery("button[name='save_transporte']");
	btn.attr('disabled','disabled');
	jQuery('#mensajes').hide();
	var incomplete   = values_requeridos();
    var empresa      = jQuery('#txt_empresa').val();
    var conductor    = jQuery('#txt_nombre_conductor').val();
    var licencia     = jQuery('#txt_num_licencia').val();
    var marca        = jQuery('#txt_marca').val();
    var modelo       = jQuery('#txt_modelo').val();
    var placas      = jQuery('#txt_placas').val();
    var clave_corta  = jQuery('#clave_corta').val();
    var descripcion  = jQuery('#descripcion').val();
	jQuery.ajax({
		type:"POST",
		url: path()+"almacen/transportes/insert_transporte",
		dataType: "json",
		data: {incomplete :incomplete, empresa:empresa, conductor:conductor, licencia:licencia, marca:marca, modelo:modelo, placas:placas, clave_corta:clave_corta, descripcion:descripcion},
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

function eliminar(id){	
	// id = (!id)?false:id;
	// if(id)if(!confirm('Esta seguro de eliminar el registro: '+id)) return false; 
	// jQuery('#mensajes_update').hide();		
	var btn = jQuery("button[name='eliminar']");
	btn.attr('disabled','disabled');
		// Obtiene campos en formulario
		var objData = formData('#formulario');
		objData['id_almacen_transportes'] = (!objData['id_almacen_transportes'])?id:objData['id_almacen_transportes'];
		objData['msj_grid'] = (id)?1:0;
	jQuery.ajax({
		type:"POST",
		url: path()+"almacen/transportes/eliminar",
		dataType: "json",			
		data : objData,
		beforeSend : function(){
			imgLoader("#update_loader");
		},
		success : function(data){
			if(data.msj_grid==1){
		    	jQuery("#mensajes_grid").html(data.contenido).show('slow');
		    	jQuery('#ico-eliminar_'+id).closest('tr').fadeOut(function(){
					jQuery(this).remove();
				});
			}else{
				jQuery("#update_loader").html('');				
			    jQuery("#mensajes_update").html(data.contenido).show('slow');
			}

		}
	});
}

function confirm_delete(id){
	promp_delete(eliminar,id);
}