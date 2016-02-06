jQuery(document).ready(function(){

	jQuery('#username').focus();

	jQuery('#user_mail').focus();
	
	jQuery('#sign_registro').click(function(){
		signUp_register();
	});
	jQuery('#sign_forgot_pwd').click(function(){
		signUp_reset_pwd();
	});
	
	jQuery('#user_mail').keypress(function(event){
	    var keycode = (event.keyCode ? event.keyCode : event.which);
	    if(keycode == '13'){
	        signUp_reset_pwd();
	    }
	});

	jQuery('#username').keypress(function(event){
	    var keycode = (event.keyCode ? event.keyCode : event.which);
	    if(keycode == '13'){
	        signUp_register();
	    }
	});
	jQuery('#password_new').keypress(function(event){
	    var keycode = (event.keyCode ? event.keyCode : event.which);
	    if(keycode == '13'){
	       signUp_register();
	    }
	});
	jQuery('#password_repeat').keypress(function(event){
	    var keycode = (event.keyCode ? event.keyCode : event.which);
	    if(keycode == '13'){
	       signUp_register();
	    }
	});
	 
});
function signUp_reset_pwd(){
	var progress    = progress_initialized('loader');
	var user_mail      = jQuery('#user_mail').val();
	var requeridos  = values_requeridos();
	
	jQuery('#msg').html('').hide('slow');
	jQuery.ajax({
        type: "POST",
        url: path()+"sign_up/reset_pwd",
        dataType: 'json',
        data: {requeridos: requeridos ,user_mail: user_mail },
        success: function(data){
           
        	jQuery('#msg').html(data.msg).show('slow');
           
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

function signUp_register(){
	var progress    = progress_initialized('loader');
	var iduser      = jQuery('#iduser').val();
	var user        = jQuery('#username').val();
	var pwd_new     = jQuery('#password_new').val();
	var pwd_repeat  = jQuery('#password_repeat').val();
	var requeridos  = values_requeridos();
	
	jQuery('#msg').html('').hide('slow');
	jQuery.ajax({
        type: "POST",
        url: path()+"sign_up/register",
        dataType: 'json',
        data: {requeridos: requeridos ,iduser: iduser ,user: user, pwd_new:pwd_new,pwd_repeat:pwd_repeat},
        success: function(data){
           if(data.success){

        	}else{
        		jQuery('#msg').html(data.err).show('slow');
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
	        if(data.success){
           		window.location.href = path()+"login";
        	}
    });
}