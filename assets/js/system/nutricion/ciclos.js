jQuery(document).ready(function(){
  selec_manual_auto();
});

function selec_manual_auto(){
  var letra = allow_only_numeric_integer();
  jQuery("#txt_ciclo").attr("disabled", "disabled");
  jQuery("#txt_ciclo").removeClass("requerido");

  jQuery(".manual").hide();
  jQuery("#txt_clave_corta").removeClass("requerido");
  jQuery("#txt_clave_corta").attr("disabled", "disabled");
  jQuery('input[name=tipo]').click(function(){
    var valor = jQuery(this).val();
    if(valor == 'auto'){
      jQuery("#txt_ciclo").removeClass("requerido");
      jQuery("#txt_clave_corta").removeClass("requerido");
      jQuery("#txt_ciclo").attr('value','');
      jQuery("#txt_clave_corta").attr('value','');
      jQuery("#txt_cantidad_ciclo").removeAttr("disabled");
      jQuery("#txt_ciclo").attr("disabled", "disabled");
      jQuery(".manual").hide('slow');
      jQuery(".auto").show('slow');
      jQuery("#txt_clave_corta").attr("disabled", "disabled");
    }else{
       jQuery("#txt_cantidad_ciclo").removeClass("requerido");
       jQuery("#txt_cantidad_ciclo").attr("disabled", "disabled");
       jQuery("#txt_ciclo").removeAttr("disabled");
       jQuery("#txt_clave_corta").removeAttr("disabled");
       jQuery("#txt_cantidad_ciclo").attr('value','');
       jQuery(".manual").show('slow');
       jQuery(".auto").hide('slow');
       jQuery("#txt_ciclo").removeAttr("disabled");
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
        success: function(data){
          var chosen  = 'jQuery(".chzn-select").chosen();';
           if(id_content==1){
            jQuery('#a-0').html('');
            jQuery('#a-'+id_content).html(data+include_script(chosen+tipo_insert));
           }else{
              var treeview    = 'load_treeview("treeview_ciclos");';
              var tipo_insert  = 'selec_manual_auto();';
              jQuery('#a-1').html('');
           		jQuery('#a-'+id_content).html(data+include_script(chosen+tipo_insert));
           }
        }
    });
}

function load_ciclos(id_sucursal){
	jQuery.ajax({
        type: "POST",
        url: path()+"nutricion/ciclos/cargar_ciclos",
        dataType: 'json',
        data: {id_sucursal:id_sucursal},
        beforeSend : function(){
        	jQuery("#loader").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
        },
        success: function(data){
          //alert(dump_var(data['ciclos']));
          var chosen  = 'jQuery(".chzn-select").chosen();';
          jQuery('#ciclo').html(data['ciclos']+include_script(chosen));
          jQuery('#ciclo').show('slow');

          jQuery('#servicio').html(data['servicios']+include_script(chosen));
          jQuery('#servicio').show('slow');  
        }
    });
}

function load_contenido_ciclo(id_ciclo){
  var nombre_ciclo = jQuery('select[name=lts_ciclos] option:selected').text();
  var treeview  = 'load_treeview("treeview_ciclos");';
  if(id_ciclo!=0){
    jQuery.ajax({
          type: "POST",
          url: path()+"nutricion/ciclos/ciclo_detalle",
          dataType: 'json',
          data: {id_ciclo : id_ciclo, nombre_ciclo: nombre_ciclo},
          beforeSend : function(){
           jQuery("#loader").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
          },
          success: function(data){
            jQuery('#ciclo_detalle').html(data+include_script(treeview));
          }
      });
  }else{
    jQuery('#ciclo_detalle').html('');
  }
  jQuery('#loader').html('');
}

function load_treeview(id){  
    jQuery("#"+id).treeview({
        animated: "fast",
        control:"#sidetreecontrol",
        collapsed: false,
        unique: false
      });
}

function load_recetas(id_familia){
  var id_sucursal  = jQuery('select[name=lts_sucursales]').val();
  if(id_familia!=0){
    jQuery.ajax({
          type: "POST",
          url: path()+"nutricion/ciclos/ciclo_receta",
          dataType: 'json',
          data: {id_familia : id_familia,id_sucursal:id_sucursal},
          beforeSend : function(){
            jQuery("#loader").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
          },
          success: function(data){
            var chosen  = 'jQuery(".chzn-select").chosen();';
            jQuery('#lts_recetas').html(data+include_script(chosen));
          }
      });
  }else{
    jQuery('select[name=lts_recetas]').empty().append('whatever');
  }
  jQuery('#loader').html('');
}

function agregar(){
  var progress = progress_initialized('registro_loader');
  var btn          = jQuery("button[name='save_ciclo']");
  btn.attr('disabled','disabled');
  jQuery('#mensajes').hide();
  
  var objData = formData('#formulario');
  objData['incomplete'] = values_requeridos();
  jQuery.ajax({
    type:"POST",
    url: path()+"nutricion/ciclos/insert_ciclo",
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

function insert_config(){
  var treeview    = 'load_treeview("treeview_ciclos")';
  var btn = jQuery("button[name='save_config']");
  btn.attr('disabled','disabled');
  jQuery('#mensajes').hide();

  var objData = formData('#formularios');
  objData['incomplete'] = values_requeridos();

  jQuery.ajax({
    type:"POST",
    url: path()+"nutricion/ciclos/insert_config",
    dataType: "json",
    data: {objData: objData},
    beforeSend : function(){
      //jQuery("#registro_loader").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
    },
    success : function(data){
      //alert(data);
      jQuery('.limpio').val('').trigger('liszt:updated');
      jQuery('#ciclo_detalle').html(data+include_script(treeview));
    }
  });
}

function eliminar_servicio(id_servicio,id_ciclo){
  var treeview    = 'load_treeview("treeview_ciclos");';
  var nombre_ciclo = jQuery('select[name=lts_ciclos] option:selected').text();
  jQuery.ajax({
        type: "POST",
        url: path()+"nutricion/ciclos/eliminar_servicio",
        dataType: 'json',
        data: {id_servicio : id_servicio,id_ciclo:id_ciclo, nombre_ciclo:nombre_ciclo},
        beforeSend : function(){
         // jQuery("#registro_loader").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
        },
        success: function(data){
          jQuery('#ciclo_detalle').html(data+include_script(treeview));

        }
  });
}

function eliminar_tiempo(id_tiempo,id_ciclo){
  var treeview     = 'load_treeview("treeview_ciclos");';
  var nombre_ciclo = jQuery('select[name=lts_ciclos] option:selected').text();
  jQuery.ajax({
        type: "POST",
        url: path()+"nutricion/ciclos/eliminar_tiempo",
        dataType: 'json',
        data: {id_tiempo : id_tiempo,id_ciclo:id_ciclo, nombre_ciclo:nombre_ciclo},
        beforeSend : function(){
        },
        success: function(data){
          jQuery('#ciclo_detalle').html(data+include_script(treeview));

        }
  });
} 

function eliminar_familia(id_familia,id_ciclo){
  var treeview     = 'load_treeview("treeview_ciclos");';
  var nombre_ciclo = jQuery('select[name=lts_ciclos] option:selected').text();
  jQuery.ajax({
        type: "POST",
        url: path()+"nutricion/ciclos/eliminar_familia",
        dataType: 'json',
        data: {id_familia : id_familia,id_ciclo:id_ciclo, nombre_ciclo:nombre_ciclo},
        beforeSend : function(){
        },
        success: function(data){
          jQuery('#ciclo_detalle').html(data+include_script(treeview));

        }
  });
}

function eliminar_receta(id_vinculo,id_ciclo){
  var treeview     = 'load_treeview("treeview_ciclos");';
  var nombre_ciclo = jQuery('select[name=lts_ciclos] option:selected').text();
  jQuery.ajax({
        type: "POST",
        url: path()+"nutricion/ciclos/eliminar_receta",
        dataType: 'json',
        data: {id_vinculo : id_vinculo,id_ciclo:id_ciclo, nombre_ciclo:nombre_ciclo},
        beforeSend : function(){
        },
        success: function(data){
          jQuery('#ciclo_detalle').html(data+include_script(treeview));

        }
  });
} 

function eliminar_ciclo(id_ciclo){
  var treeview     = 'load_treeview("treeview_ciclos");';
  jQuery.ajax({
        type: "POST",
        url: path()+"nutricion/ciclos/eliminar_ciclo",
        dataType: 'json',
        data: {id_ciclo:id_ciclo},
        beforeSend : function(){
        },
        success: function(data){
          if(data == 1){
            jQuery('#ciclo_detalle').html('');
          }else{
            jQuery('#mensajes').show('slow');
            jQuery('#ciclo_detalle').html(data+include_script(treeview));
          }
        }
  });
}



