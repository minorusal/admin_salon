function agregar(){
	var progress = progress_initialized('registro_loader');
	var btn          = jQuery("button[name='save_area']");
	btn.attr('disabled','disabled');

	var objData = formData('#formulario');
	objData['incomplete']   = values_requeridos();
	
	jQuery.ajax({
		type:"POST",
		url: path()+"administracion/empresa/insert",
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