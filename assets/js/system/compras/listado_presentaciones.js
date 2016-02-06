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

          jQuery('#form_presentaciones_edit').remove();
          jQuery('#form_presentaciones_save').remove();
           if(id_content==1){
           		var funcion = 'buscar';
           		jQuery('#a-1').html(data+input_keypress('search-query', funcion));
           		jQuery('#search-query').val(filtro).focus();
           		tool_tips();
           }else{
              
           		jQuery('#a-'+id_content).html(data);
              var numeric_int      = 'allow_only_numeric_integer("numerico_int");';
              var numeric      = 'allow_only_numeric();'; 
           		var chosen  = 'jQuery(".chzn-select").chosen();';
           		jQuery('#a-'+id_content).html(data+include_script(chosen+numeric+numeric_int));
           }
        }
    });
}
function buscar(){
  var filtro = jQuery('#search-query').val();
  jQuery.ajax({
    type:"POST",
    url: path()+"compras/listado_presentaciones/listado",
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
function agregar(){
  var progress = progress_initialized('registro_loader');
  var btn          = jQuery("button[name='listado_presentaciones_save']");
  btn.attr('disabled','disabled');
  jQuery('#mensajes').hide();

  var incomplete   = values_requeridos('form_presentaciones_save');

  var impuesto_aplica;
  var id_embalaje;
  var listado_principal;
  if( jQuery('#impuesto_aplica').is(':checked') ){
    impuesto_aplica = 1;
  }else{
    impuesto_aplica = 0;    
  }

  if(!jQuery('#embalaje_aplica').is(':checked') ){
    id_embalaje = 0;
  }
  else{
    id_embalaje = jQuery("select[name='lts_embalaje'] option:selected").val();
  }
  if( jQuery('#listado_principal').is(':checked') ){
    listado_principal = 1;
  }else{
    listado_principal = 0;    
  }
  var id_articulo                 = jQuery("select[name='lts_articulos'] option:selected").val();
  var id_region                   = jQuery("select[name='lts_region'] option:selected").val();
  var id_proveedor                = jQuery("select[name='lts_proveedores'] option:selected").val();
  var id_marca                    = jQuery("select[name='lts_marcas'] option:selected").val();
  var id_presentacion             = jQuery("select[name='lts_presentaciones'] option:selected").val();
  var impuesto_porcentaje         = jQuery("select[name='lts_impuesto'] option:selected").val();
  var presentacion_x_embalaje     = jQuery('#presentacion_x_embalaje').val();
  var um_x_embalaje               = jQuery('#um_x_embalaje').val();
  var um_x_presentacion           = jQuery('#um_x_presentacion').val();
  var costo_sin_impuesto          = jQuery('#costo_sin_impuesto').val();
  var peso_unitario               = jQuery('#peso_unitario').val();
  var costo_unitario              = jQuery('#costo_unitario').val();
  var costo_x_um                  = jQuery('#costo_x_um').val();
  var upc                         = jQuery('#upc').val();
  var rendimiento                 = jQuery('#rendimiento').val();
  var precio_publico               = jQuery('#precio_publico').val();
  var precio_publico_con_impuesto  = jQuery('#precio_publico_con_impuesto').val();
  

  jQuery.ajax({
    type:"POST",
    url: path()+"compras/listado_presentaciones/insert",
    dataType: "json",
    data: {
        incomplete                  : incomplete,
        presentacion_x_embalaje     : presentacion_x_embalaje,
        um_x_embalaje               : um_x_embalaje,
        um_x_presentacion           : um_x_presentacion,
        costo_sin_impuesto          : costo_sin_impuesto,
        impuesto_aplica             : impuesto_aplica,
        impuesto_porcentaje         : impuesto_porcentaje,
        id_articulo                 : id_articulo,
        id_proveedor                : id_proveedor,
        id_region                   : id_region,
        id_marca                    : id_marca,
        id_presentacion             : id_presentacion,
        id_embalaje                 : id_embalaje,
        peso_unitario               : peso_unitario,
        costo_unitario              : costo_unitario,
        costo_x_um                  : costo_x_um,
        upc                         : upc,
        rendimiento                 : rendimiento,
        listado_principal           : listado_principal,
        precio_publico              : precio_publico,
        precio_publico_con_impuesto : precio_publico_con_impuesto
    },
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
function detalle(id_compras_articulo_presentacion){
  jQuery('#ui-id-2').click();
  jQuery.ajax({
        type: "POST",
        url: path()+"compras/listado_presentaciones/detalle",
        dataType: 'json',
        data: {id_compras_articulo_presentacion : id_compras_articulo_presentacion},
        success: function(data){
          var chosen = 'jQuery(".chzn-select").chosen();';
          var numeric      = 'allow_only_numeric();';
          var numeric_int      = 'allow_only_numeric_integer("numerico_int");';
          jQuery('#a-0').html('');
          jQuery('#a-2').html(data);
          jQuery('#a-2').html(data+include_script(chosen+numeric+numeric_int));
          jQuery('#ui-id-2').show('slow');
          // calcula_costos();
        }
    });
}
function update(){
  var progress = progress_initialized('update_loader');
  jQuery('#mensajes_update').hide();
  var btn          = jQuery("button[name='update']");
  btn.attr('disabled','disabled');
  //var btn_text     = btn.html();  

  var incomplete   = values_requeridos('form_presentaciones_edit');
  var impuesto_aplica;
  var id_embalaje;
  if( jQuery('#impuesto_aplica').is(':checked') ){
    impuesto_aplica = 1;
  }else{
    impuesto_aplica = 0;    
  }
  if(!jQuery('#embalaje_aplica').is(':checked') ){
    id_embalaje = 0;
  }
  else{
    id_embalaje = jQuery("select[name='lts_embalaje'] option:selected").val();
  }
  if( jQuery('#listado_principal').is(':checked') ){
    listado_principal = 1;
  }else{
    listado_principal = 0;    
  }
  var id_compras_articulo_presentacion  = jQuery('#id_compras_articulo_presentacion').val();
  var id_articulo                  = jQuery("select[name='lts_articulos'] option:selected").val();
  var id_region                    = jQuery("select[name='lts_region'] option:selected").val();
  var id_proveedor                 = jQuery("select[name='lts_proveedores'] option:selected").val();
  var id_marca                     = jQuery("select[name='lts_marcas'] option:selected").val();
  var id_presentacion              = jQuery("select[name='lts_presentaciones'] option:selected").val();
  var impuesto_porcentaje          = jQuery("select[name='lts_impuesto'] option:selected").val();
  var presentacion_x_embalaje      = jQuery('#presentacion_x_embalaje').val();
  var um_x_embalaje                = jQuery('#um_x_embalaje').val();
  var um_x_presentacion            = jQuery('#um_x_presentacion').val();
  var costo_sin_impuesto           = jQuery('#costo_sin_impuesto').val();
  var peso_unitario                = jQuery('#peso_unitario').val();
  var costo_unitario               = jQuery('#costo_unitario').val();
  var costo_x_um                   = jQuery('#costo_x_um').val();
  var upc                          = jQuery('#upc').val();
  var rendimiento                  = jQuery('#rendimiento').val();
  var precio_publico               = jQuery('#precio_publico').val();
  var precio_publico_con_impuesto  = jQuery('#precio_publico_con_impuesto').val();

  jQuery.ajax({
    type:"POST",
    url: path()+"compras/listado_presentaciones/update",
    dataType: "json",
    data: {
        incomplete                  : incomplete,
        id_compras_articulo_presentacion : id_compras_articulo_presentacion,
        presentacion_x_embalaje     : presentacion_x_embalaje,
        um_x_embalaje               : um_x_embalaje,
        um_x_presentacion           : um_x_presentacion,
        costo_sin_impuesto          : costo_sin_impuesto,
        impuesto_aplica             : impuesto_aplica,
        impuesto_porcentaje         : impuesto_porcentaje,
        id_articulo                 : id_articulo,
        id_region                   : id_region, 
        id_proveedor                : id_proveedor,
        id_marca                    : id_marca,
        id_presentacion             : id_presentacion,
        id_embalaje                 : id_embalaje,
        peso_unitario               : peso_unitario,
        costo_unitario              : costo_unitario,
        costo_x_um                  : costo_x_um,
        upc                         : upc,
        rendimiento                 : rendimiento,
        listado_principal           : listado_principal,
        precio_publico              : precio_publico,
        precio_publico_con_impuesto : precio_publico_con_impuesto
      },
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
function load_proveedor(id_region){
  jQuery.ajax({
        type: "POST",
        url: path()+"compras/listado_presentaciones/load_proveedores_x_region",
        dataType: 'json',
        data: {id_region : id_region},
        success: function(data){  
        var chosen = 'jQuery(".chzn-select").chosen();';        
          jQuery('#lts_proveedores_cargar').html(data+include_script(chosen));
        }
    });
}
function oculta_impuesto(){
  var miSelect = jQuery('[name=lts_impuesto]');
  if(jQuery('#impuesto_aplica').is(':checked') ){
    jQuery('#impuesto').show('slow');
    jQuery('[name=lts_impuesto]').addClass('requerido');
    jQuery('#desglose').show('slow'); 
    // miSelect.val('').trigger('liszt:updated');   
    calcular_precio_final();
  }else{
    var costo_final = jQuery('#costo_sin_impuesto').val();
    jQuery('#desglose_impuesto').val('');
    jQuery('#desglose').hide('slow');    
    jQuery('#costo_final').val(costo_final);
    jQuery('#impuesto').hide('slow');
    jQuery('[name=lts_impuesto]').removeClass('requerido');
    miSelect.val('').trigger('liszt:updated');
  }
  calcula_precio_publico();
}
function validar_um(id_opcion){
  if(id_opcion==1){    
    jQuery('#um_x_embalaje').attr('readonly', false);
    jQuery('#um_x_embalaje').addClass('requerido');
    jQuery('#um_x_presentacion').attr('readonly', true);
    jQuery('#um_x_presentacion').removeClass('requerido');
  }else{
    jQuery('#um_x_presentacion').attr('readonly', false);
    jQuery('#um_x_presentacion').addClass('requerido');

    jQuery('#um_x_embalaje').attr('readonly', true);
    jQuery('#um_x_embalaje').removeClass('requerido');
  }
}

function load_pre_um(id_articulo){
  jQuery.ajax({
        type: "POST",
        url: path()+"compras/listado_presentaciones/load_presentacion_um",
        dataType: 'json',
        data: {id_articulo : id_articulo},
        success: function(data){
        if(data.tipo_articulo != 3){
          // alert(dump_var(data));
          jQuery('#impuesto_p').hide('slow');
          jQuery('#impuesto_aplica_p').hide('slow');
          jQuery('#precio_publico_p').hide('slow');
          jQuery('#precio_publico').removeClass('requerido');
          jQuery('#precio_publico_con_impuesto_p').hide('slow');
          jQuery('#precio_publico_con_impuesto').removeClass('requerido');
          jQuery('#impuesto').hide('slow');
          jQuery('#impuesto_p').hide('slow');
          jQuery('[name=lts_impuesto]').removeClass('requerido');
          // jQuery('[name=lts_impuesto]').trigger('liszt:updated');
        }else{
          if(jQuery('[name=lts_impuesto]').val() != 0){
            jQuery('#impuesto_aplica').prop("checked", true);
            jQuery('#impuesto').show('slow');
          }
          
          jQuery('#impuesto_aplica_p').show('slow');
          jQuery('#precio_publico_p').show('slow');
          jQuery('#precio_publico').addClass('requerido');
          jQuery('#precio_publico_con_impuesto_p').show('slow');
          jQuery('#precio_publico_con_impuesto').addClass('requerido');
          jQuery('#impuesto_p').show('slow');
          // jQuery('#impuesto').show('slow');
          // jQuery('#impuesto_p').show('slow');
          // jQuery('[name=lts_impuesto]').addClass('requerido');
        } 
          jQuery('#pre_um').show('slow');
          jQuery('#pre_um').html(data.cv_um);
          jQuery('#pre_um2').show('slow');
          jQuery('#pre_um2').html(data.cv_um);
          jQuery('#lbl_peso').show('slow');
          jQuery('#lbl_peso').html(data.cv_um);
          jQuery('#lbl_peso_edit').html(data.cv_um);
          // jQuery('#lbl_costo_x_um').show('slow');
          // jQuery('#lbl_costo_x_um').html('1 '+data);
        }
    });
}

function calcular_precio_final(){

  
  var impuesto  = jQuery("select[name='lts_impuesto'] option:selected").text();

  var costo_sin_impuesto = jQuery('#costo_sin_impuesto').val();  
  var valor=impuesto.split("-");
  var impuesto_valor = (parseFloat(valor[1])>0)?parseFloat(valor[1]):0;
  var desglose_impuesto = (costo_sin_impuesto*impuesto_valor)/100;
  var resultado = parseFloat(costo_sin_impuesto)+parseFloat(desglose_impuesto);
  if(costo_sin_impuesto=="" || parseFloat(costo_sin_impuesto)==0){
      costo_final="";
      desglose_impuesto="";
  }
  else{
    costo_final= resultado.toFixed(3)
    desglose_impuesto=desglose_impuesto.toFixed(3);
  }
  jQuery('#costo_final').val(costo_final);
  jQuery('#desglose_impuesto').val(desglose_impuesto);
  calcula_precio_publico();
}

function eliminar(id){  
    // id = (!id)?false:id;
    // if(id)if(!confirm('Esta seguro de eliminar el registro: '+id)) return false;    
    // jQuery('#mensajes_update').hide();    
    var btn = jQuery("button[name='eliminar']");
    btn.attr('disabled','disabled');
      // Obtiene campos en formulario
      var objData = formData('#formulario');
      objData['id_compras_articulo_presentacion'] = (!objData['id_compras_articulo_presentacion'])?id:objData['id_compras_articulo_presentacion'];
      objData['msj_grid'] = (id)?1:0;
    jQuery.ajax({
      type:"POST",
      url: path()+"compras/listado_presentaciones/eliminar",
      dataType: "json",     
      data : objData,
      beforeSend : function(){
        imgLoader("#update_loader");
      },
      success : function(data){
        alert(dump_var(data));
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
    })
}

function confirm_delete(id){
  promp_delete(eliminar,id);
}
function calcula_precio_publico(){
  var impuesto  = jQuery("select[name='lts_impuesto'] option:selected").text();
  var precio_publico = jQuery('#precio_publico').val();  
  var resultado;
  if(jQuery('#impuesto_aplica').is(':checked') ){
    var valor=impuesto.split("-");
    var impuesto_valor = (parseFloat(valor[1])>0)?parseFloat(valor[1]):0;
    var desglose_impuesto = (precio_publico*impuesto_valor)/100;
   resultado = parseFloat(precio_publico)+parseFloat(desglose_impuesto);
    if(precio_publico=="" || parseFloat(precio_publico)==0){
        costo_final="";
        desglose_impuesto="";
    }
    else{
      costo_final= resultado.toFixed(3)
      desglose_impuesto=desglose_impuesto.toFixed(3);
    }
  
  }else{
    costo_final = precio_publico;

  }
  jQuery('#precio_publico_con_impuesto').val(costo_final);
}