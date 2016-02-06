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
    url: path()+"sucursales/operadores/listado",
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
  var btn          = jQuery("button[name='save_vendedor']");
  btn.attr('disabled','disabled');
  jQuery('#mensajes').hide();
  
  var incomplete    = values_requeridos('form_vendedores_insert');    

  var  nombre       = jQuery('#nombre').val();
  var  paterno      = jQuery('#paterno').val();
  var  materno      = jQuery('#materno').val();
  var  clave_corta  = jQuery('#clave_corta').val();
  var  rfc          = jQuery('#rfc').val();
  var  calle        = jQuery('#calle').val();
  var  num_int      = jQuery('#num_int').val();
  var  num_ext      = jQuery('#num_ext').val();
  var  colonia      = jQuery('#colonia').val();
  var  municipio    = jQuery('#municipio').val(); 
  var  cp           = jQuery('#cp').val();
  var  telefonos    = jQuery('#telefonos').val();
  var  email        = jQuery('#email').val();
  var id_entidad    = jQuery("select[name='lts_entidades'] option:selected").val();
  var id_sucursal   = jQuery("select[name='lts_sucursales'] option:selected").val();
  
  jQuery.ajax({
    type:"POST",
    url: path()+"sucursales/operadores/insert",
    dataType: "json",
    data: {
            incomplete :incomplete,nombre:nombre,paterno:paterno,materno: materno, clave_corta:clave_corta,rfc:rfc,calle:calle,num_int:num_int,num_ext:num_ext,colonia:colonia,municipio:municipio,id_entidad:id_entidad,id_sucursal:id_sucursal,cp:cp,telefonos:telefonos,email:email},
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
function detalle(id_vendedor){
  jQuery('#ui-id-2').click();
  jQuery.ajax({
        type: "POST",
        url: path()+"sucursales/operadores/detalle",
        dataType: 'json',
        data: {id_vendedor : id_vendedor},
        success: function(data){
          var numeric = 'allow_only_numeric_integer();';
          var chosen = 'jQuery(".chzn-select").chosen();';
          jQuery('#a-0').html('');
          jQuery('#a-2').html(data+include_script(chosen+numeric));
          jQuery('#ui-id-2').show('slow');
        }
    });
}
function update(){
  var progress = progress_initialized('update_loader');
  var btn          = jQuery("button[name='update_vendedor']");
  btn.attr('disabled','disabled');
  jQuery('#mensajes').hide();
  
  var incomplete    = values_requeridos('form_vendedores_edit');    
  var  id_vendedor   = jQuery('#id_vendedor').val();
  var  nombre       = jQuery('#nombre').val();
  var  paterno      = jQuery('#paterno').val();
  var  materno      = jQuery('#materno').val();
  var  clave_corta  = jQuery('#clave_corta').val();
  var  rfc          = jQuery('#rfc').val();
  var  calle        = jQuery('#calle').val();
  var  num_int      = jQuery('#num_int').val();
  var  num_ext      = jQuery('#num_ext').val();
  var  colonia      = jQuery('#colonia').val();
  var  municipio    = jQuery('#municipio').val();  
  var  cp           = jQuery('#cp').val();
  var  telefonos    = jQuery('#telefonos').val();
  var  email        = jQuery('#email').val();
  var id_entidad       = jQuery("select[name='lts_entidades'] option:selected").val();
  var id_sucursal      = jQuery("select[name='lts_sucursales'] option:selected").val();


  jQuery.ajax({
    type:"POST",
    url: path()+"sucursales/operadores/update",
    dataType: "json",
    data: {
            incomplete :incomplete,id_vendedor:id_vendedor,nombre:nombre,paterno:paterno,materno: materno,clave_corta:clave_corta,rfc:rfc,calle:calle,num_int:num_int,num_ext:num_ext,colonia:colonia,municipio:municipio,id_entidad:id_entidad,id_sucursal:id_sucursal,cp:cp,telefonos:telefonos,email:email},
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

function eliminar(id_operador){
  var progress = progress_initialized('loader_global');
    jQuery.ajax({
        type: "POST",
        url: path()+"sucursales/operadores/eliminar",
        dataType: 'json',
        data: {id_operador : id_operador},
        beforeSend: function(){
        },
        success: function(data){
          if(data.success == 'true'){
            jQuery('#ico-eliminar_'+data.id_operador).parent().parent().parent().remove();
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

function confirm_delete(id_operador){
  promp_delete(eliminar,id_operador);
}