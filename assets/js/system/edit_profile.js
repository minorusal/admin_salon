jQuery(document).ready(function(){
	allow_only_numeric();
	jQuery( "#set_profile" ).click(function() {
		set_perfil();
	});
	
	jQuery(".modal-wide").on("show.bs.modal", function() {
	  var height = jQuery(window).height() - 200;
	  jQuery(this).find(".modal-body").css("max-height", height);
	});

	
	
});

function set_perfil(){
	var progress    = progress_initialized('set_infor_perfil');
	var requeridos  = values_requeridos();
	var objData     = formData('#profile');
	var username    = jQuery('#username').val();
	var pwd         = jQuery('#pwd').val();

	jQuery('#msg_perfil').html('').hide('slow');
	jQuery.ajax({
        type: "POST",
        url: path()+"edit_profile/update_info_user",
        dataType: 'json',
        data: {requeridos: requeridos , objData :objData, username:username, pwd:pwd},
        success: function(data){
        	if(data.success){
        		jgrowl(data.msg);
        	}else{
        		jQuery('#msg_perfil').html(data.msg).show('slow');
           }
        }
    }).error(function(){
       		progress.progressTimer('error', {
	            errorText:'ERROR!',
	            onFinish:function(){
	            }
            });
        }).done(function(data){
	        progress.progressTimer('complete');  
    });
}