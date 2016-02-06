jQuery(document).ready(function(){
  
});

function load_content(uri, id_content){
    jQuery.ajax({
        type: "POST",
        url: uri,
        dataType: 'json',
        data: {tabs:1},
        success: function(data){
        	jQuery('#form_cotizador_familia').remove();
        	jQuery('#form_cotizador_receta').remove();
        	jQuery('#form_cotizador_insumo').remove();
        	var chosen = 'jQuery(".chzn-select").chosen();';
        	if(id_content == 0){
        		jQuery('#a-'+id_content).html(data+include_script(chosen)).show();
        	}else if(id_content == 1){
        		jQuery('#a-'+id_content).html(data+include_script(chosen)).show();
        	}else if(id_content == 2){
        		jQuery('#a-'+id_content).html(data+include_script(chosen)).show();
        	}
        }
    });
}

function load_familias(id_sucursal){
	jQuery.ajax({
		type:"POST",
		url: path()+"nutricion/cotizador_recetas/load_familias",
		dataType: "json",
		data: {id_sucursal:id_sucursal},
		beforeSend : function(){
			jQuery("#familias_list").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
		},
		success : function(data){
			jQuery('#respuesta').html('');
			var chosen  = 'jQuery(".chzn-select").chosen();';
			var lbl_familia = "<label>Familia: (*)</label>";
			var spanIni = "<span class='field'>";
			var spanEnd = '</span>';
		    jQuery('#familias_list').html(lbl_familia+spanIni+data.familia_list+include_script(chosen)+spanEnd).show();
		    jQuery('#btn_cotizar').html(data.btn_cotizar);
		}
	});
}

function guarda_cotizacion_by_familia(){
	var objData = formData('#form_cotizador_familia');
  	objData['incomplete'] = values_requeridos('form_cotizador_familia');
  	
	jQuery.ajax({
		type:"POST",
		url: path()+"nutricion/cotizador_recetas/insert_cotizacion_familia",
		dataType: "json",
		data: {objData:objData},
		beforeSend : function(){
			jQuery("#respuesta").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
		},
		success : function(data){
			jQuery('#respuesta').show();
			if(data.success){
				jQuery('#respuesta').html(data.contenido);
			}else if(!data.success){
				jQuery('#respuesta').html(data.contenido);
			}else if(data.success == 'vacio'){
				jQuery('#respuesta').html(data.contenido);
			}	
		}
	});
}

function load_grupos(id_sucursal){
	jQuery.ajax({
		type:"POST",
		url: path()+"nutricion/cotizador_recetas/load_grupos",
		dataType: "json",
		data: {id_sucursal:id_sucursal},
		beforeSend : function(){
			jQuery("#grupos_list").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
		},
		success : function(data){
			jQuery('#respuesta').html('');
			var chosen  = 'jQuery(".chzn-select").chosen();';
			var lbl_grupo = "<label>Grupos: (*)</label>";
			var spanIni = "<span class='field'>";
			var spanEnd = '</span>';
		    jQuery('#grupos_list').html(lbl_grupo+spanIni+data.grupos_list+include_script(chosen)+spanEnd);
		    jQuery('#recetas_list').html('');
		}
	});
}

function load_recetas(id_grupo,id_sucursal){
	jQuery.ajax({
		type:"POST",
		url: path()+"nutricion/cotizador_recetas/load_recetas",
		dataType: "json",
		data: {id_sucursal:id_sucursal,id_grupo:id_grupo},
		beforeSend : function(){
			jQuery("#recetas_list").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
		},
		success : function(data){
			jQuery('#respuesta').html('');
			var chosen  = 'jQuery(".chzn-select").chosen();';
			var lbl_grupo = "<label>Recetas: (*)</label>";
			var spanIni = "<span class='field'>";
			var spanEnd = '</span>';
		    jQuery('#recetas_list').html(lbl_grupo+spanIni+data.recetas_list+include_script(chosen)+spanEnd);
		    jQuery('#btn_cotizar').html(data.btn_cotizar);
		}
	});
}

function guarda_cotizacion_by_receta(){
	var objData = formData('#form_cotizador_receta');
  	objData['incomplete'] = values_requeridos('form_cotizador_receta');
	jQuery.ajax({
		type:"POST",
		url: path()+"nutricion/cotizador_recetas/insert_cotizacion_receta",
		dataType: "json",
		data: {objData:objData},
		beforeSend : function(){
			jQuery("#respuesta").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
		},
		success : function(data){
			jQuery('#respuesta').show();
			if(data.success){
				jQuery('#respuesta').html(data.contenido);
			}else if(!data.success){
				jQuery('#respuesta').html(data.contenido);
			}else if(data.success == 'vacio'){
				jQuery('#respuesta').html(data.contenido);
			}	
		}
	});
}

function load_insumos(id_sucursal){
	jQuery.ajax({
		type:"POST",
		url: path()+"nutricion/cotizador_recetas/load_insumos",
		dataType: "json",
		data: {id_sucursal:id_sucursal},
		beforeSend : function(){
			jQuery("#insumos_list").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
		},
		success : function(data){
			jQuery('#respuesta').html('');
			var chosen  = 'jQuery(".chzn-select").chosen();';
			var lbl_grupo = "<label>Insumos: (*)</label>";
			var spanIni = "<span class='field'>";
			var spanEnd = '</span>';
		    jQuery('#insumos_list').html(lbl_grupo+spanIni+data.insumos_list+include_script(chosen)+spanEnd);
		    jQuery('#btn_cotizar').html(data.btn_cotizar);
		}
	});
}

function guarda_cotizacion_by_insumo(){
	var objData = formData('#form_cotizador_insumo');
  	objData['incomplete'] = values_requeridos('form_cotizador_insumo');
	jQuery.ajax({
		type:"POST",
		url: path()+"nutricion/cotizador_recetas/guarda_cotizacion_by_insumo",
		dataType: "json",
		data: {objData:objData},
		beforeSend : function(){
			jQuery("#respuesta").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
		},
		success : function(data){
			jQuery('#respuesta').show();
			if(data.success){
				jQuery('#respuesta').html(data.contenido);
			}else if(!data.success){
				jQuery('#respuesta').html(data.contenido);
			}else if(data.success == 'vacio'){
				jQuery('#respuesta').html(data.contenido);
			}	
		}
	});
}
