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
          jQuery('#frm_precios_proveedor_save').remove();
          jQuery('#frm_precios_proveedor_edit').remove();
           if(id_content==1){
           		var funcion = 'buscar';
           		jQuery('#a-1').html(data+input_keypress('search-query', funcion));
           		jQuery('#search-query').val(filtro).focus();
           		tool_tips();
           }else{
           		jQuery('#a-'+id_content).html(data);
              var numeric      = 'allow_only_numeric();'; 
           		var chosen  = 'jQuery(".chzn-select").chosen();';
           		jQuery('#a-'+id_content).html(data+include_script(chosen+numeric));
           }
        }
    });
}
function buscar(){
  var filtro = jQuery('#search-query').val();
  jQuery.ajax({
    type:"POST",
    url: path()+"compras/listado_precios_proveedor/listado",
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
  var btn          = jQuery("button[name='listado_precios_proveedor_save']");
  btn.attr('disabled','disabled');
  jQuery('#mensajes').hide();
  var incomplete   = values_requeridos('frm_precios_proveedor_save',false);
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
  var id_compras_articulo_presentacion   = jQuery("select[name='lts_presentaciones'] option:selected").val();
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
    url: path()+"compras/listado_precios_proveedor/insert",
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
        id_compras_articulo_presentacion        : id_compras_articulo_presentacion,
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
      if(data.success == 'true'){
        clean_formulario();
        jQuery('#presentacion_x_embalaje').val(1);
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
function detalle(id_compras_articulo_precio_proveedor){
  jQuery('#ui-id-2').click();
  jQuery.ajax({
        type: "POST",
        url: path()+"compras/listado_precios_proveedor/detalle",
        dataType: 'json',
        data: {id_compras_articulo_precio_proveedor : id_compras_articulo_precio_proveedor},
        success: function(data){
          var chosen = 'jQuery(".chzn-select").chosen();';
          var numeric      = 'allow_only_numeric();';
          jQuery('#a-0').html('');
          jQuery('#a-2').html(data);
          jQuery('#a-2').html(data+include_script(chosen+numeric));
          jQuery('#ui-id-2').show('slow');
          calcula_costos();
        }
    });
}
function update(){
  var progress = progress_initialized('update_loader');
  jQuery('#mensajes_update').hide();
  var btn          = jQuery("button[name='update']");
  btn.attr('disabled','disabled');
  //var btn_text     = btn.html();  
  var incomplete   = values_requeridos('frm_precios_proveedor_edit');
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
  var id_compras_articulo_precio_proveedor  = jQuery('#id_compras_articulo_precio_proveedor').val();
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
    url: path()+"compras/listado_precios_proveedor/update",
    dataType: "json",
    data: {
        incomplete                  : incomplete,
        id_compras_articulo_precio_proveedor : id_compras_articulo_precio_proveedor,
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
        url: path()+"compras/listado_precios_proveedor/load_proveedores_x_region",
        dataType: 'json',
        data: {id_region : id_region},
        success: function(data){  
        var chosen = 'jQuery(".chzn-select").chosen();';        
          jQuery('#lts_proveedores_cargar').html(data+include_script(chosen));
        }
    });
}
function oculta_impuesto(){
  if(jQuery('#impuesto_aplica').is(':checked') ){
    jQuery('#impuesto').show('slow');
    jQuery('select[name=lts_impuesto]').addClass('requerido');
    jQuery('#desglose').show('slow');    
    calcular_precio_final();
  }else{
    var costo_final = jQuery('#costo_sin_impuesto').val();
    jQuery('#desglose_impuesto').val('');
    jQuery('#desglose').hide('slow');    
    jQuery('#costo_final').val(costo_final);
    jQuery('#impuesto').hide('slow');
    jQuery('select[name=lts_impuesto]').removeClass('requerido');
  }
  calcula_precio_publico();
}
function oculta_embalaje(){
  
  if(jQuery('#embalaje_aplica').is(':checked') ){
    jQuery('#presentacion_x_embalaje').attr('readonly', false);
    jQuery('#radio_x_embalaje').attr('disabled', false);
    jQuery('#presentacion_x_embalaje').val(1)
    jQuery('#embajale').show('slow');
    jQuery('select[name=lts_embalaje]').addClass('requerido');
    jQuery('#radio_x_embalaje').attr('checked',true)
    validar_um(1);
    if(jQuery("select[name='lts_embalaje'] option:selected").val()>0){
      jQuery('#embalaje_cl').show('slow');
      jQuery('#pre_um2').show('slow');
      jQuery('#signo2').show('slow');
    }
  }else{
    validar_um(2);
    jQuery('#radio_x_embalaje').attr('disabled', true);
    jQuery('#radio_x_presentacion').attr('checked',true)
    jQuery('#presentacion_x_embalaje').val(1);
    jQuery('#presentacion_x_embalaje').attr('readonly',true);
    jQuery('#embajale').hide('slow');
    jQuery('select[name=lts_embalaje]').removeClass('requerido');
    jQuery('#embalaje_cl').hide('slow');
    jQuery('#pre_um2').hide('slow');
    jQuery('#signo2').hide('slow');
  }
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
function load_pre_emb(id_presentacion){
  var presentaciones  = jQuery("select[name='lts_presentaciones'] option:selected").text();  
  var clave_corta=presentaciones.split("-");
  jQuery('#pre_em').show('slow');
  jQuery('#pre_em').html(clave_corta[0]);
  jQuery('#pre_em2').show('slow');
  jQuery('#pre_em2').html(clave_corta[0]);
  jQuery('#lbl_unitario').show('slow');
  jQuery('#lbl_unitario').html('1 '+clave_corta[0]);
  jQuery('#signo').show('slow');
}
function load_pre_um(id_articulo){
  jQuery.ajax({
        type: "POST",
        url: path()+"compras/listado_precios_proveedor/load_presentacion_um",
        dataType: 'json',
        data: {id_articulo : id_articulo},
        success: function(data){          
          jQuery('#pre_um').show('slow');
          jQuery('#pre_um').html(data);
          jQuery('#pre_um2').show('slow');
          jQuery('#pre_um2').html(data);
          jQuery('#lbl_peso').show('slow');
          jQuery('#lbl_peso').html(data);
          jQuery('#lbl_peso_edit').html(data);
          jQuery('#lbl_costo_x_um').show('slow');
          jQuery('#lbl_costo_x_um').html('1 '+data);
        }
    });
}
function load_emb(id_embalaje){
  var embalaje  = jQuery("select[name='lts_embalaje'] option:selected").text(); 
  var clave_corta=embalaje.split("-");
  jQuery('#embalaje_cl').show('slow');
  jQuery('#embalaje_cl').html(clave_corta[0]);
  jQuery('#signo2').show('slow');
  jQuery('#pre_um2').show('slow');
}
function load_emb_data(){
  if(jQuery('#presentacion_x_embalaje').val()=="") clean_campos_emb();
  if(jQuery('#peso_unitario').val()!="") calcula_um_presentacion();
}
function clean_campos_emb(){
  jQuery('#um_x_presentacion').val('');
  jQuery('#um_x_embalaje').val('');
  // jQuery('#peso_unitario').val(0);
}
function calcula_um_presentacion(){
  if(jQuery('#peso_unitario').val()!=""){
    calcula_costos();
  }
  var presentacion_x_embalaje = jQuery('#presentacion_x_embalaje').val();
  var um_x_embalaje = jQuery('#um_x_embalaje').val();
  var um_x_presentacion= um_x_embalaje/presentacion_x_embalaje;
  if(presentacion_x_embalaje){
    if(isNaN(um_x_presentacion) || isNaN(um_x_embalaje)){
      um_x_presentacion=0;
    }else{
      um_x_presentacion=um_x_presentacion;
    }
    jQuery('#um_x_presentacion').val(um_x_presentacion.toFixed(2));
  }
}
function calcula_um_embalaje(){
  var presentacion_x_embalaje = jQuery('#presentacion_x_embalaje').val();
  var um_x_presentacion = jQuery('#um_x_presentacion').val();
  var um_x_embalaje= um_x_presentacion*presentacion_x_embalaje;
  jQuery('#um_x_embalaje').val(um_x_embalaje);
  if(jQuery('#peso_unitario').val()!=""){
    calcula_costos();
  }
}
function calcula_costos(){
  var presentacion_x_embalaje = jQuery('#presentacion_x_embalaje').val();
  var um_x_embalaje = jQuery('#um_x_embalaje').val();
  var costo_sin_impuesto = jQuery('#costo_sin_impuesto').val();
  var peso_unitario;
  var costo_unitario; 
  var costo_x_um;
  var costo_final = jQuery('#costo_sin_impuesto').val();
  //CALCULOS
  peso_unitario  = um_x_embalaje/presentacion_x_embalaje;

  costo_unitario = costo_sin_impuesto/presentacion_x_embalaje
  costo_x_um     = costo_sin_impuesto/um_x_embalaje
  if(um_x_embalaje >0 && presentacion_x_embalaje>0 && costo_sin_impuesto>0){
    jQuery('#peso_unitario').val(peso_unitario.toFixed(3));
    jQuery('#costo_unitario').val(costo_unitario.toFixed(3));
    jQuery('#costo_x_um').val(costo_x_um.toFixed(3));
    jQuery('#costo_final').val(costo_final);
  }  
  if(jQuery("select[name='lts_impuesto'] option:selected").val()>0){
    calcular_precio_final();
  }
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
      objData['id_compras_articulo_precios'] = (!objData['id_compras_articulo_precios'])?id:objData['id_compras_articulo_precios'];
      objData['msj_grid'] = (id)?1:0;
    jQuery.ajax({
      type:"POST",
      url: path()+"compras/listado_precios_proveedor/eliminar",
      dataType: "json",     
      data : objData,
      beforeSend : function(){
        imgLoader("#update_loader");
      },
      success : function(data){
        //alert(dump_var(data.success));
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