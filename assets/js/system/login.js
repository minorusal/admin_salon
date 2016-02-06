jQuery(document).ready(function(){
	jQuery('#user').focus();
	jQuery( "#button_login" ).click(function() {
		authentication();
	});
	jQuery( "#forgot_pwd" ).click(function() {
		window.location.href =  path()+"forgot_pwd";
	});
	jQuery('#user').keypress(function(event){
	    var keycode = (event.keyCode ? event.keyCode : event.which);
	    if(keycode == '13'){
	        authentication();
	    }
	});
	jQuery('#pwd').keypress(function(event){
	    var keycode = (event.keyCode ? event.keyCode : event.which);
	    if(keycode == '13'){
	       authentication();
	    }
	});
	jQuery('a[data-rel]').each(function() {
        jQuery(this).attr('rel', jQuery(this).data('rel'));
    });

});

function authentication(){
	var progress = progress_initialized('progress');
	var user     = jQuery('#user').val();
	var pwd      = jQuery('#pwd').val();
	var id_user  = jQuery('#id_user').val();
	jQuery.ajax({
		type: "POST",
		url: "login/authentication",
		dataType: 'json',
		data: {id_user: id_user,user: user, pwd: pwd},
		success: function(data){
			switch (data){
				case 0:
					jQuery(location).attr('href','login');
					break
				case 1:
					jQuery(location).attr('href','inicio');
					break
				default:
					var promp_content = {
									content_01:{
										html:data,
										buttons:{ }
									}
								};
					jQuery.prompt(promp_content);
					setTimeout("clean_form_login()",60000);
					break
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

function authentication_perfil(id_user){
	var id_usuario = id_user;
	jQuery('#id_user').val(id_usuario);
	authentication();
}

function clean_form_login(){
	jQuery('#user').val('').focus();
	jQuery('#pwd').val('');
	jQuery.prompt.close();
}

function timerIncrement() {
    var idleTime = idleTime + 1;
    if (idleTime > 10) { // 20 minutes
        clean_form_login();
    }
}