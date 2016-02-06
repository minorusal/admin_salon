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
    url: path()+"sucursales/clientes/listado",
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
function load_content(uri, id_content){
  jQuery('#ui-id-2').hide('slow');
	var filtro = jQuery('#search-query').val();
    jQuery.ajax({
        type: "POST",
        url: uri,
        dataType: 'json',
        data: {filtro : filtro, tabs:1},
        success: function(data){
          jQuery('#form_clientes_edit').remove();
          jQuery('#form_clientes_save').remove();
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
function insert(){
  var progress = progress_initialized('registro_loader');
  var btn          = jQuery("button[name='save_cliente']");
  btn.attr('disabled','disabled');
  jQuery('#mensajes').hide();
  
  var objData = formData('#form_clientes_save');
  objData['incomplete'] = values_requeridos('form_clientes_save');

  jQuery.ajax({
    type:"POST",
    url: path()+"sucursales/clientes/insert",
    dataType: "json",
    data: {objData:objData},
    beforeSend : function(){
      btn.attr('disabled',true);
    },
    success : function(data){
        if(data.success == 'true'){
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
function detalle(id_cliente){
  jQuery('#ui-id-2').click();
  jQuery.ajax({
        type: "POST",
        url: path()+"sucursales/clientes/detalle",
        dataType: 'json',
        data: {id_cliente : id_cliente},
        success: function(data){
          var numeric  = 'allow_only_numeric_integer();';
          var chosen = 'jQuery(".chzn-select").chosen();';
          jQuery('#a-0').html('');
          jQuery('#a-2').html(data+include_script(chosen+numeric));
          jQuery('#ui-id-2').show('slow');
        }
    });
}

function update(){
  var progress = progress_initialized('update_loader');
  var btn          = jQuery("button[name='update_cliente']");
  btn.attr('disabled','disabled');
  jQuery('#mensajes').hide();

  var objData = formData('#form_clientes_edit');
  objData['incomplete'] = values_requeridos('form_clientes_edit');

  jQuery.ajax({
    type:"POST",
    url: path()+"sucursales/clientes/update",
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


function load_punto_venta(id_sucursal){
  jQuery.ajax({
        type: "POST",
        url: path()+"sucursales/clientes/load_punto_venta",
        dataType: 'json',
        data: {id_sucursal : id_sucursal},
        success: function(data){
          var chosen = 'jQuery(".chzn-select").chosen();';
          jQuery('#list_punto_venta').html(data+include_script(chosen));
        }
    });
}

function upload_file(){
    if(jQuery('#upload_file').length){
        jQuery('input[type="file"]').click();
    }else{
        jQuery("#dyntable_wrapper").append('<div id ="upload_file"></div>');

        var button = jQuery('#upload_file'), interval;
        new AjaxUpload('#upload_file', {
            action: path()+"sucursales/clientes/import_xlsx",
            onSubmit : function(file , ext){
                if (! (ext && /^(xlsx)$/.test(ext))){
                    alert('Error: Solo se permiten imagenes');
                    return false;
                } else {
                    this.disable();
                }
            },
            onComplete: function(file, response){                 
                this.enable();          
            }   
        }); 
        upload_file();
    } 
}

function eliminar(id_cliente){
  var progress = progress_initialized('loader_global');
    jQuery.ajax({
        type: "POST",
        url: path()+"sucursales/clientes/eliminar_registro",
        dataType: 'json',
        data: {id_cliente : id_cliente},
        beforeSend: function(){
        },
        success: function(data){
          if(data.success == 'true'){
            jQuery('#ico-eliminar_'+data.id_cliente).parent().parent().parent().remove();
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

function confirm_delete(id_cliente){
  promp_delete(eliminar,id_cliente);
}

