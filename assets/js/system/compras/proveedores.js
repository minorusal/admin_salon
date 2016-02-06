jQuery(document).ready(function(){
	jQuery('#search-query').focus();
	jQuery('#search-query').keypress(function(event){
		var keycode = (event.keyCode ? event.keyCode : event.which);
		if(keycode == '13'){  
			buscar();
		} 
	});
})


function buscar(){
	var filtro = jQuery('#search-query').val();
	jQuery.ajax({
		type:"POST",
		url: path()+"compras/proveedores/listado",
		dataType: "json",
		data: {filtro : filtro},
		beforeSend : function(){
			imgLoader("#loader");
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
	var filtro = jQuery('#search-query').val();
    jQuery.ajax({
        type: "POST",
        url: uri,
        dataType: 'json',
        data: {filtro : filtro, tabs:1},
        success: function(data){
        	jQuery('#form_proveedores_save').remove();
        	jQuery('#form_proveedores_edit').remove();
           if(id_content==1){
           		var funcion = 'buscar';
           		jQuery('#a-1').html(data+input_keypress('search-query', funcion));
           		jQuery('#search-query').val(filtro).focus();
           		tool_tips();
           }else{
           		var numeric = 'allow_only_numeric_integer();';
          	 	var chosen  = 'jQuery(".chzn-select").chosen();';
           		jQuery('#a-'+id_content).html(data+include_script(chosen+numeric));
           }
        }
    });
}

function detalle(id_compras_proveedor){	
	jQuery.ajax({
        type: "POST",
        url: path()+"compras/proveedores/detalle",
        dataType: 'json',
        data: {id_compras_proveedor : id_compras_proveedor},
        success: function(data){
        	var numeric  = 'allow_only_numeric_integer();';
        	var chosen = 'jQuery(".chzn-select").chosen();';
        	jQuery('#a-0').html('');
        	jQuery('#a-2').html(data+include_script(chosen+numeric));
        	jQuery('#ui-id-2').show('slow');
        	jQuery('#ui-id-2').click();
        }
    });
}

function insert(){	
var progress = progress_initialized('loader');	
	var btn          = jQuery("button[name='save']");
	btn.attr('disabled','disabled');
	jQuery('#mensajes').hide();	
	

  	var objData = formData('#form_proveedores_save');
  	objData['incomplete'] = values_requeridos('form_proveedores_save');

	jQuery.ajax({
		type:"POST",
		url: path()+"compras/proveedores/insert",
		dataType: "json",
		data : {objData:objData},
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

function actualizar(){	
	var progress = progress_initialized('update_loader');
		jQuery('#mensajes_update').hide();		
		var btn          = jQuery("button[name='update']");
		btn.attr('disabled','disabled');
  		

  		var objData = formData('#form_proveedores_edit');
  		objData['incomplete'] = values_requeridos('form_proveedores_edit');

  		
		jQuery.ajax({
			type:"POST",
			url: path()+"compras/proveedores/actualizar",
			dataType: "json",			
			data : {objData:objData},
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

function eliminar(id){	
		id = (!id)?false:id;
		if(id)if(!confirm('Esta seguro de eliminar el registro: '+id)) return false; 
		jQuery('#mensajes_update').hide();		
		var btn = jQuery("button[name='eliminar']");
		btn.attr('disabled','disabled');
  		// Obtiene campos en formulario
  		var objData = formData('#form_update');
  		objData['id_compras_proveedor'] = (!objData['id_compras_proveedor'])?id:objData['id_compras_proveedor'];
  		objData['msj_grid'] = (id)?1:0;
		jQuery.ajax({
			type:"POST",
			url: path()+"compras/proveedores/eliminar",
			dataType: "json",			
			data : objData,
			beforeSend : function(){
				imgLoader("#update_loader");
			},
			success : function(data){
				if(data.msj_grid==1){
			    	jQuery("#mensajes_grid").html(data.contenido).show('slow');
			    	// location.reload();
				}else{
					jQuery("#update_loader").html('');				
				    jQuery("#mensajes_update").html(data.contenido).show('slow');
				}

			}
		})
}