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
           }
        }
    });
}

function buscar(){
	var filtro = jQuery('#search-query').val();
	jQuery.ajax({
		type:"POST",
		url: path()+"nutricion/familias/listado",
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
function detalle(id_familia){
  jQuery('#ui-id-2').click();
  jQuery.ajax({
        type: "POST",
        url: path()+"nutricion/familias/detalle",
        dataType: 'json',
        data: {id_familia : id_familia},
        success: function(data){
          jQuery('#a-0').html('');
          jQuery('#a-2').html(data);
          jQuery('#ui-id-2').show('slow');
        }
    });
}

function actualizar(){
  var progress = progress_initialized('update_loader');
 jQuery('#mensajes_update').hide();
  var btn             = jQuery("button[name='actualizar']");
  btn.attr('disabled','disabled');
  var btn_text        = btn.html()

  var objData = formData('#formulario');
  objData['incomplete'] = values_requeridos();

  jQuery.ajax({
    type:"POST",
    url: path()+"nutricion/familias/actualizar",
    dataType: "json",
    data: {objData : objData },
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
  var btn          = jQuery("button[name='save_familia']");
  btn.attr('disabled','disabled');
  jQuery('#mensajes').hide();

  var objData = formData('#formulario');
  objData['incomplete']   = values_requeridos();

  jQuery.ajax({
    type:"POST",
    url: path()+"nutricion/familias/insert_familia",
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