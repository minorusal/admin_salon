jQuery(document).ready(function(){
	jQuery('#search-query').focus();
	jQuery('#search-query').keypress(function(event){
		var keycode = (event.keyCode ? event.keyCode : event.which);
		if(keycode == '13'){  
			buscar_um();
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
          jQuery('#form_um_save').remove();
          jQuery('#form_um_edit').remove();
           if(id_content==1){
           		var funcion = 'buscar_um';
           		jQuery('#a-1').html(data+input_keypress('search-query', funcion));
           		jQuery('#search-query').val(filtro).focus();
           		tool_tips();
           }else{
           		jQuery('#a-'+id_content).html(data);
           }
        }
    });
}
function buscar_um(){
  var filtro = jQuery('#search-query').val();
  jQuery.ajax({
    type:"POST",
    url: path()+"compras/um/listado_um",
    dataType: "json",
    data: {filtro : filtro},
    beforeSend : function(){
      jQuery("#loader").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
    },
    success : function(data){
      var funcion = 'buscar_um';
          jQuery("#loader").html('');
          jQuery('#a-1').html(data+input_keypress('search-query', funcion));
      jQuery('#search-query').val(filtro).focus();
      tool_tips();
    }
  })
}
function detalle_um(id_um){
  jQuery('#ui-id-2').click();
  jQuery.ajax({
        type: "POST",
        url: path()+"compras/um/detalle_um",
        dataType: 'json',
        data: {id_um : id_um},
        success: function(data){
          jQuery('#a-0').html('');
          jQuery('#a-2').html(data);
          jQuery('#ui-id-2').show('slow');
        }
    });
}
function update_um(){
  var progress = progress_initialized('update_loader');
  jQuery('#mensajes_update').hide();
  var btn          = jQuery("button[name='update_um']");
  btn.attr('disabled','disabled');
  var btn_text     = btn.html();  
  var incomplete       = values_requeridos('form_um_edit');
  var id_um            = jQuery('#id_um').val();
  var um               = jQuery('#um').val();
  var clave_corta      = jQuery('#clave_corta').val();
  var descripcion      = jQuery('#descripcion').val();
  
  jQuery.ajax({
    type:"POST",
    url: path()+"compras/um/update_um",
    dataType: "json",
    data: {incomplete :incomplete,id_um:id_um, um:um, clave_corta:clave_corta, descripcion:descripcion },
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
function insert_um(){
  var progress = progress_initialized('registro_loader');
  var btn          = jQuery("button[name='save_um']");
  btn.attr('disabled','disabled');
  jQuery('#mensajes').hide();
  var incomplete     = values_requeridos('form_um_save');
    var um           = jQuery('#um').val();
    var clave_corta  = jQuery('#clave_corta').val();
    var descripcion  = jQuery('#descripcion').val();
  
  jQuery.ajax({
    type:"POST",
    url: path()+"compras/um/insert_um",
    dataType: "json",
    data: {incomplete :incomplete, um:um, clave_corta:clave_corta, descripcion:descripcion },
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

function eliminar(id_um){
  var progress = progress_initialized('loader_global');
    jQuery.ajax({
        type: "POST",
        url: path()+"compras/um/eliminar_registro",
        dataType: 'json',
        data: {id_um : id_um},
        beforeSend: function(){
        },
        success: function(data){
          if(data.success == 'true'){
            jQuery('#ico-eliminar_'+data.id_um).parent().parent().parent().remove();
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

function confirm_delete(id_um){
  promp_delete(eliminar,id_um);
}