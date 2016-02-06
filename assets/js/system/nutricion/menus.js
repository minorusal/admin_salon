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
		url: path()+"nutricion/menus/listado",
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

function detalle(id_menu){
	jQuery('#ui-id-2').click();
	jQuery.ajax({
        type: "POST",
        url: path()+"nutricion/menus/detalle",
        dataType: 'json',
        data: {id_menu : id_menu},
        success: function(data){
        	jQuery('#a-0').html('');
        	jQuery('#a-2').html(data);
        	jQuery('#ui-id-2').show('slow');

        	jQuery('#a-2').html(data);
           	var chosen  = 'jQuery(".chzn-select").chosen();';
           	jQuery('#a-2').html(data+include_script(chosen));
        }
    });
}

function load_dropdowns(id_sucursal){
	var id_sucursal     = id_sucursal; 
	var multi_recetas   = jQuery('#recetas_selected');
	var multi_articulos = jQuery('#articulos_selected');
	
	multi_articulos.find('option').empty();
	multi_recetas.find('option').empty();
	
	jQuery.ajax({
		type: 'POST',
		url : path()+'nutricion/menus/load_dropdowns',
		dataType : 'json',
		data : {id_sucursal :id_sucursal},
		beforeSend : function(){

		},
		success: function(data){
			if(data.recetas!=''){
				jQuery.each(data.recetas, function(){
					multi_recetas.append(jQuery('<option></option>').attr('value', this.key).text(this.item));
				});
				multi_recetas.trigger('liszt:updated');
			}
				
			if(data.articulos!=''){
				jQuery.each(data.articulos, function(){
					multi_articulos.append(jQuery('<option></option>').attr('value', this.key).text(this.item));
				});
				multi_articulos.trigger('liszt:updated');
			}
		}

	});
}

function conformar_menu(){
var progress = progress_initialized('loader_formatos');
	var btn = jQuery("button[name='save_formato']");
	btn.attr('disabled','disabled');
	jQuery('#mensajes_formatos').hide();

	var objData = formData('#formatos');
  	objData['incomplete'] = values_requeridos();
  	
	jQuery.ajax({
		type:"POST",
		url: path()+"nutricion/menus/conformar_menu",
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
				jQuery("#mensajes_formatos").html(data.mensaje).show('slow');	
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

function modificar_menu(){
	var progress = progress_initialized('loader_formatos');
	jQuery("#mensajes_formatos").html('').hide('slow');
	var btn             = jQuery("button[name='actualizar']");
	btn.attr('disabled','disabled');
	var btn_text        = btn.html();

	var objData = formData('#formatos');
  	objData['incomplete'] = values_requeridos();
  	
	jQuery.ajax({
		type:"POST",
		url: path()+"nutricion/menus/modificar_menu",
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