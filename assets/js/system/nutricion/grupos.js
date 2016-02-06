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
        	jQuery('#form_grupos_edit').remove();
        	jQuery('#form_grupos_save').remove();
        	//alert(dump_var(data));
           if(id_content==1){
              var chosen  = 'jQuery(".chzn-select").chosen();';
           		var funcion = 'buscar';
           		jQuery('#a-1').html(data+include_script(chosen)+input_keypress('search-query', funcion));
           		jQuery('#search-query').val(filtro).focus();
           		tool_tips();
           }else{
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
		url: path()+"nutricion/grupos/listado",
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

function detalle(id_grupo){
	jQuery('#ui-id-2').click();
	jQuery.ajax({
        type: "POST",
        url: path()+"nutricion/grupos/detalle",
        dataType: 'json',
        data: {id_grupo : id_grupo},
        success: function(data){
          var chosen  = 'jQuery(".chzn-select").chosen();';
        	jQuery('#a-0').html('');
        	jQuery('#a-2').html(data+include_script(chosen));
        	jQuery('#ui-id-2').show('slow');
        }
    });
}
function actualizar(){
  var progress = progress_initialized('update_loader');
  jQuery("#mensajes_update").html('').hide('slow');
  jQuery('#mensajes').hide();
  var btn             = jQuery("button[name='actualizar']");
  btn.attr('disabled','disabled');
  var btn_text        = btn.html();

  var objData = formData('#form_grupos_edit');
    objData['incomplete'] = values_requeridos('form_grupos_edit');

  jQuery.ajax({
    type:"POST",
    url: path()+"nutricion/grupos/actualizar",
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
  var btn = jQuery("button[name='save_grupo']");
  btn.attr('disabled','disabled');
  jQuery('#mensajes').hide();

  var objData = formData('#form_grupos_save');
    objData['incomplete'] = values_requeridos('form_grupos_save');
    
  jQuery.ajax({
    type:"POST",
    url: path()+"nutricion/grupos/insert",
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

function eliminar(id_grupo){
  var progress = progress_initialized('loader_global');
    jQuery.ajax({
        type: "POST",
        url: path()+"nutricion/grupos/eliminar_registro",
        dataType: 'json',
        data: {id_grupo : id_grupo},
        beforeSend: function(){
        },
        success: function(data){
          if(data.success == 'true'){
            jQuery('#ico-eliminar_'+data.id_grupo).parent().parent().parent().remove();
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

function confirm_delete(id_grupo){
  promp_delete(eliminar,id_grupo);
}