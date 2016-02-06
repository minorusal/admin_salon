jQuery(document).ready(function(){
  jQuery('#search-query').focus();
  jQuery('#search-query').keypress(function(event){
    var keycode = (event.keyCode ? event.keyCode : event.which);
    if(keycode == '13'){  
      buscar_usuario();
    } 
  });
});
function buscar_usuario(){
  var filtro = jQuery('#search-query').val();
  jQuery.ajax({
    type:"POST",
    url: path()+"administracion/usuarios/listado",
    dataType: "json",
    data: {filtro : filtro},
    beforeSend : function(){
      jQuery("#loader").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
    },
    success : function(data){
      var funcion = 'buscar_usuario';
          jQuery("#loader").html('');
          jQuery('#a-1').html(data+input_keypress('search-query', funcion));
      jQuery('#search-query').val(filtro).focus();
      tool_tips();
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
            var treeview           = 'load_treeview("treeview-modules");';
            var treeview_childrens = 'treeview_childrens();'; 
               if(id_content==1){
              		var funcion = 'buscar_usuario';
              		jQuery('#a-1').html(data+input_keypress('search-query', funcion));
              		jQuery('#search-query').val(filtro).focus();
              		tool_tips();
               }else{
                    var numeric      = 'allow_only_numeric_integer();'; 
                    var escribir = 'jQuery("#txt_nombre_usuario").on("keyup", function(){ find_string("administracion/usuarios/find_string",jQuery("#txt_nombre_usuario").val(),jQuery("#txt_nombre_usuario").attr("name"));});';
             	 	var chosen   = 'jQuery(".chzn-select").chosen();';
              		jQuery('#a-'+id_content).html(data+include_script(chosen+treeview+treeview_childrens+escribir+numeric));
                }
        }
    });
}

function asignar_perfil(id_personal,id_usuario, id_perfil){   
    jQuery('#ui-id-2').click();
    jQuery.ajax({
        type: "POST",
        url: path()+"administracion/usuarios/asignar_perfil",
        dataType: 'json',
        data: {id_personal : id_personal, id_usuario : id_usuario, id_perfil : id_perfil},
        success: function(data){
            var treeview           = 'load_treeview("treeview-modules");';
            var treeview_childrens = 'treeview_childrens();'; 
            var chosen = 'jQuery(".chzn-select").chosen();';
            jQuery('#a-2').html(data+include_script(chosen+treeview+treeview_childrens));
            jQuery('#ui-id-2').show('slow');
        }
    });
}

function find_string(uri,id,nom){
    jQuery.ajax({
        type: "POST",
        url: path()+uri,
        dataType: 'json',
        data: {item: id, nom : nom},
        success: function(data){
            if(data.existe == 0){
                jQuery('#'+data.campo).css("border", " 1px solid red");
                jQuery('#no_disponible').css({ color: "red", display: "block" });
            }else{
                jQuery('#'+data.campo).css("border", " 1px solid #339933");
                jQuery('#no_disponible').css('display','none');
            }
        }
    });
}
function load_tree_view(id_perfil){
    var treeview = [];
    jQuery.ajax({
        type: "POST",
        url : path()+'administracion/usuarios/load_tree_view_perfil',
        dataType : 'json',
        data : {id_perfil: id_perfil},
        success: function(data){
            treeview.push('load_treeview("treeview-modules");');
            treeview.push('treeview_childrens();');
            var chosen = 'jQuery(".chzn-select").chosen();';
            jQuery('#treeview_perfiles').html(data+include_script(chosen+treeview)).show('show');
        }
    });
}

function load_tree_view_perfil_usuario(id_personal,id_perfil){
    var treeview = [];
    jQuery.ajax({
        type: "POST",
        url : path()+'administracion/usuarios/load_tree_view_perfil_usuario',
        dataType : 'json',
        data : {id_personal:id_personal,id_perfil: id_perfil},
        success: function(data){
            var chosen = 'jQuery(".chzn-select").chosen();';
            jQuery('#sucursales').html(data.list_sucursales+include_script(chosen)).show('show');
            treeview.push('load_treeview("treeview-modules");');
            treeview.push('treeview_childrens();');
            jQuery('#treeview_perfiles').html(data.tree_perfiles+include_script(treeview)).show('show');
        }
    });
}
function detalle(id_personal){
  jQuery('#ui-id-2').click();
  jQuery.ajax({
        type: "POST",
        url: path()+"administracion/usuarios/detalle",
        dataType: 'json',
        data: {id_personal : id_personal},
        success: function(data){
            var numeric      = 'allow_only_numeric_integer();';
            var treeview = 'load_treeview("treeview-modules");';
            var treeview_childrens = 'treeview_childrens();'; 
            var chosen = 'jQuery(".chzn-select").chosen();';
            jQuery('#a-0').html('');
            jQuery('#a-2').html(data+include_script(chosen+treeview+treeview_childrens+numeric));
            jQuery('#ui-id-2').show('slow');
        }
    });
}


function agregar_perfil(){
    var progress = progress_initialized('registro_loader');
    jQuery("#mensajes_update").html('').hide('slow');
    jQuery('#mensajes').hide();
    var btn             = jQuery("button[name='agregar_perfil']");
    btn.attr('disabled','disabled');
    var btn_text        = btn.html();

    var nivel_1    = [];
    var nivel_2    = [];
    var nivel_3    = [];
    var objData    = formData('#formulario');
    var sucursales = objData['lts_sucursales'];
   
    jQuery("input[name='nivel_1']:enabled:checked").each(function(){
        nivel_1.push(jQuery(this).val());
    });
    
    jQuery("input[name='nivel_2']:enabled:checked").each(function(){
      nivel_2.push(jQuery(this).val());
    });
    
    jQuery("input[name='nivel_3']:enabled:checked").each(function(){
      nivel_3.push(jQuery(this).val());
    });

    objData['incomplete']  = values_requeridos();
    objData['nivel_1']     = (nivel_1.length>0) ? nivel_1.join(',') : nivel_1;
    objData['nivel_2']     = (nivel_2.length>0) ? nivel_2.join(',') : nivel_2;
    objData['nivel_3']     = (nivel_3.length>0) ? nivel_3.join(',') : nivel_3;
    
    jQuery.ajax({
        type:"POST",
        url: path()+"administracion/usuarios/insert_perfiles",
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

function insert(){
    var progress = progress_initialized('registro_loader');
    jQuery("#mensajes_update").html('').hide('slow');
    jQuery('#mensajes').hide();
    var btn             = jQuery("button[name='insert']");
    btn.attr('disabled','disabled');
    var btn_text        = btn.html();

    var nivel_1 = [];
    var nivel_2 = [];
    var nivel_3 = [];
    var objData = formData('#formulario');
    objData['numerico']      = values_numericos();
    
    jQuery("input[name='nivel_1']:enabled:checked").each(function(){
        nivel_1.push(jQuery(this).val());
    });
    
    jQuery("input[name='nivel_2']:enabled:checked").each(function(){
      nivel_2.push(jQuery(this).val());
    });
    
    jQuery("input[name='nivel_3']:enabled:checked").each(function(){
      nivel_3.push(jQuery(this).val());
    });
    
    objData['incomplete']  = values_requeridos();
    objData['nivel_1']     = (nivel_1.length>0) ? nivel_1.join(',') : nivel_1;
    objData['nivel_2']     = (nivel_2.length>0) ? nivel_2.join(',') : nivel_2;
    objData['nivel_3']     = (nivel_3.length>0) ? nivel_3.join(',') : nivel_3;

    jQuery.ajax({
        type:"POST",
        url: path()+"administracion/usuarios/insert",
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

function actualizar(){
    var progress = progress_initialized('update_loader');
    jQuery("#mensajes_update").html('').hide('slow');
    jQuery('#mensajes').hide();
    var btn             = jQuery("button[name='actualizar']");
    btn.attr('disabled','disabled');
    var btn_text        = btn.html();
    var objData = formData('#formulario');
    objData['incomplete'] = values_requeridos();

    var i = 0;
    var a = 0;
    var usuarios = objData['id_usuario'];
    usuarios = usuarios.split(",");
    var personal = objData['id_personal'];
    var perfil   = objData['lts_perfiles'];
    perfil = perfil.split(",")
    var num_perfil = perfil.length; 
    var num_usuario = usuarios.length;
    var registros = new Array();

    if(num_perfil > num_usuario){
        var res = num_perfil - num_usuario;
        for(i=0;i<=res-1;i++){
            usuarios.push(null);  
        }
    }

    if(num_perfil < num_usuario){
        var res = num_usuario-num_perfil;
        for(i=0;i<=res-1;i++){
            perfil.push(null);  
        }
    }
    
    registros = {
        id_usuario : usuarios,
        id_perfil  : perfil 
    };
   
    objData['registros'] = registros;

    jQuery.ajax({
        type:"POST",
        url: path()+"administracion/usuarios/actualizar",
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

function enviar_email(id_personal,id_usuario, id_perfil, name, mail){
    var progress = progress_initialized('loader_global');
    var mail_send= jQuery("#mail_"+id_personal);

    jQuery.ajax({
        type: "POST",
        url: path()+"administracion/usuarios/enviar_mail",
        dataType: 'json',
        data: {id_personal : id_personal, id_usuario : id_usuario, id_perfil : id_perfil, name : name, mail : mail},
        beforeSend: function(){
            mail_send.hide();
            imgLoader('#loader_'+id_personal, 'mail.gif');
        },
        success: function(data){
            jgrowl(data.msj);
            jQuery('#a-2').html(data);
            jQuery('#ui-id-2').show('slow');
        }
    }).error(function(){
            progress.progressTimer('error', {
                errorText:'ERROR!',
                onFinish:function(){
                    mail_send.show('slow');
                    imgLoader_clean('#loader_'+id_personal, 'mail.gif');
                }
        });
    }).done(function(){
        progress.progressTimer('complete');
        mail_send.show('slow');
        imgLoader_clean('#loader_'+id_personal, 'mail.gif');
    });
}
function confirm_delete(id_usuario){
    promp_delete(eliminar,id_usuario);
}

function eliminar(id_usuario){
    var progress = progress_initialized('loader_global');
    jQuery.ajax({
        type: "POST",
        url: path()+"administracion/usuarios/eliminar_registro",
        dataType: 'json',
        data: {id_usuario : id_usuario},
        beforeSend: function(){
        },
        success: function(data){
            if(data.success){
                jQuery('#ico-eliminar_'+id_usuario).parent().parent().parent().remove();
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