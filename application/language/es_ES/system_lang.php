<?php
	$lang['avatar'] = 'Avatar';
	$lang['id'] = 'ID';
	$lang['foto']   = 'Foto';
	$lang['admincontrol']  = 'Admincontrol';
	$lang['close_session']       	= 'Cerrar Sesión';	
	$lang['fecha_actual']			= 'de %s del %s';
	$lang['timestamp_string'] 		= 'de %s del %s a las %s hrs.';
	$lang['lbl_ultima_modificacion']= 'Ultima modificacion';
	$lang['lbl_sin_modificacion']   = 'Sin moficiaciones desde su creacion';
	$lang['val_ultima_modificacion']= 'Realizada el %s por %s';
// Días y meses
	$lang['lunes']      = 'Lunes';
	$lang['martes']     = 'Martes';
	$lang['miercoles']  = 'Miércoles';
	$lang['jueves']     = 'Jueves';
	$lang['viernes']    = 'Viernes';
	$lang['sabado']     = 'Sábado';
	
	$lang['domingo']    = 'Domingo';
	$lang['enero']      = 'Enero';
	$lang['febrero']    = 'Febrero';
	$lang['marzo']      = 'Marzo';
	$lang['abril']      = 'Abril';
	$lang['mayo']       = 'Mayo';
	$lang['junio']      = 'Junio';
	$lang['julio']      = 'Julio';
	$lang['agosto']     = 'Agosto';
	$lang['septiembre'] = 'Septiembre';
	$lang['octubre']    = 'Octubre';
	$lang['noviembre']  = 'Noviembre';
	$lang['diciembre']  = 'Diciembre';
	$lang['calendar_month'] = 'Mes';
	$lang['calendar_week']  = 'Semana';
	$lang['calendar_day']   = 'Dia';
	$lang['calendar_today'] = 'Hoy';

// Mensajes
	$lang['msg_err_exist'] 					   = '<strong>Atención!</strong> No existen clientes relacionados con este punto de venta';
	$lang['msg_err_insert']					   = '<strong>Atención! ocurrio un error y no se inserto el registro</strong>';
	$lang['msg_err_cantidad']				   = '<strong>Atención!</strong><br> la cantidad registrada no puede ser mayor a la existente, gracias.';
	$lang['msg_atencion']					   = '<strong>Atención!</strong>';
	$lang['msg_eliminar']                      = '¿Desea eliminar el registro de la base de datos?';
	$lang['msg_err_send_mail']                 = '<strong>Atencion!</strong><br>ERROR al enviar.';
	$lang['msg_err_delete']                    = '<strong>Atencion!</strong><br>No se puede eliminar este registro por esta siendo utilizado.';
	$lang['msg_succes_send_mail']              = '<strong>Atencion!</strong><br>Correo enviado!';
	$lang['msg_mail_repetido']                 = '<strong>Atencion!</strong><br>El email que tratas de ingresar ya esta ocupado, intenta otro, gracias.';
	$lang['msg_user_repetido']                 = '<strong>Atencion!</strong><br>El usuario que tratas de ingresar ya esta ocupado, intenta otro, gracias.';
	$lang['msg_sin_recetas']                   = '<strong>Atencion!</strong><br>No se encontraron recetas asociadas con este ciclo.';
	$lang['msg_con_recetas']                   = '<strong>Atencion!</strong><br>No se puede eliminar el registro ya que esta asignado a una receta.';
	$lang['msg_searh_fail']          		   = '<strong>Atencion!</strong><br>No se encontraron coincidencias.';
	$lang['msg_query_null']          		   = '<strong>Atencion!</strong><br>No se encontraron registros.';
	$lang['msg_insert_success']      		   = '<strong>Exito!</strong><br>El registro se dio de alta correctamente';
	$lang['msg_update_success']      		   = '<strong>Exito!</strong><br>Los cambios se guardaron correctamente';
	$lang['msg_delete_success']      		   = '<strong>Exito!</strong><br>El registro se ha elimiminado correctamente';
	$lang['msg_seesion_destroy']     		   = '<strong>Error!</strong><br>Su sesion ha finalizado por favor inicie sesion nuevamente, gracias.';
	$lang['msg_campos_obligatorios']           = '<strong>Atencion!</strong><br>Los campos marcado con (*) son obligatorios, gracias';
	$lang['msg_hora_igual']                    = '<strong>Atencion!</strong><br>la hora de inicio y de termino no pueden ser iguales, gracias';
	$lang['msg_horainicio_mayor']              = '<strong>Atencion!</strong><br>La hora de inicio no puede ser mayor o igual a la hora final, gracias';
	$lang['msg_horario_empalmado']             = '<strong>Atencion!</strong><br>El rango de horario interfiere con el horario de otro servicio, gracias';
	$lang['msg_campos_numericos']              = '<strong>Atencion!</strong><br>Los campos deben contener numeros, gracias';
	$lang['msg_query_search']        		   = '<div style="float:left;"><p class="text-info">Se han encontrado %d registros para "%s".</p></div>'; 
	$lang['msg_err_clv']             		   = '<strong>Advertencia!</strong><br>La clave asignada ya se ha proporcionado a otro registro, porfavor intente con una clave diferente';
	$lang['msg_query_insert']             	   = '<strong>Advertencia!</strong><br>No se puedo realizar el registro';
	$lang['msg_insert_orden_success']          = '<strong>Exito!</strong><br>El registro se dio de alta correctamente. Con el numero de orden %s';
	$lang['msg_existencia_listado']            = '<strong>Atencion!</strong><br>El articulo ya se encuentra registrado, por favor seleccione otro, gracias.';
	$lang['msg_err_coincide_sucursal']         = '<strong>Atencion!</strong><br>La sucursal de origen no puede ser la misma que la sucursal de destino, gracias.';

// Botones
	$lang['btn_xlsx']     		= 'Generar Excel';
	$lang['btn_import_xlsx']    = 'Importar Excel';
	$lang['btn_guardar']  		= 'Guardar cambios';
	$lang['btn_eliminar']  		= 'Eliminar registro';
	$lang['btn_limpiar']  		= 'limpiar';
	$lang['btn_search']   		= 'buscar';
	$lang['btn_cerrar']  		= 'Cerrar';
	$lang['btn_cancelar']  		= 'cancelar';
	$lang['btn_aprobar']  		= 'aprobar';
	$lang['btn_rechazar']  		= 'rechazar';
// Paginador
	$lang['pag_first_link'] = '&laquo; Primero';
	$lang['pag_next_link']  = 'Siguiente &raquo;';
	$lang['pag_prev_link']  = '&laquo; Anterior';
	$lang['pag_last_link']  = 'Último &raquo;';

	$lang['pag_registros']  = ' registros';
	$lang['pag_resultado']  = 'Resultado ';
	$lang['pag_de']         = ' de ';

// Tooltip
	$lang['tool_tip']       = 'Ver Detalle';
// Etiquetas
	$lang['fecha_registro']  = 'Fecha de Registro';
	$lang['registro_por']    = 'Registrado por';

	$lang['lbl_fecha_registro']  = 'Fecha de Registro';
	$lang['lbl_usuario_registro'] = 'Registrado por';

	$lang['catalogo']        = 'Catalogo de ';

// Acciones 
	$lang['acciones']		= 'Acciones';
	$lang['eliminar']		= 'Eliminar';
	$lang['editar']			= 'Editar';
	$lang['agregar']		= 'Agregar';
	$lang['habilitar']		= 'Habilitar';
	$lang['deshabilitar']	= 'Deshabilitar';
	$lang['imprimir']		= 'Imprimir';
	$lang['ver']			= 'Ver';
	$lang['no_editable']	= 'Este registro no puede ser editado.';

	$lang['ID']			= 'ID';
	$lang['listado']   = 'listado';
	$lang['detalle']   = 'detalle';
	$lang['collapse']            = 'Contraer lista';
	$lang['expand']              = 'Expander Lista';
	$lang['lbl_fecha_inicio']    = 'Fecha de Inicio';	
	$lang['lbl_fecha_termino']   = 'Fecha de Termino';

	$lang['sys_tittle']          = ':: iSolution.mx - AdminControl ::';
	$lang['sign_new_user']       = 'Configuración de Claves';
	$lang['sign_up']             = 'Sign Up';
	$lang['sign_info_new_user']  = 'Por Favor defina su Usuario y contraseña, para poder tener acceso al sistema, gracias.';
	$lang['sign_resgitro']		 = 'Guardar Cambios';

	$lang['edit_profile']      = 'Edición de Perfil';

	$lang['pwd_diferentes']    = 'Las contraseñas no coinciden, favor de verificar.';
	$lang['pwd_longitud']      = 'La contraseña que proporciono no es valida, asegúrese de que tenga una longitud de 6 caracteres por lo menos y no contenga espacios, ni caracteres especiales, gracias.';
	$lang['form_incompleto']   = 'Por favor llene todos los campos del formulario, gracias.';
	$lang['user_available']    = 'El usuario definido no se encuentra disponible, por favor intente con algun otro, gracias.';
	$lang['user_err']          = 'El usuario que proporcionaste no es valido, asegúrese de que no contenga espacios, ni caracteres especiales, gracias.';
	
	$lang['username']          = 'Defina su nombre de Usuario.';
	$lang['password_new']      = 'Defina su Contraseña.';
	$lang['password_repeat']   = 'Vuelva a escribir tu Contraseña.';
	$lang['introduce_mail']    = 'Introduzca su correo electronico.';
	$lang['password_reset']    = 'Reestablecer contraseña';
	$lang['registro_claves']   = 'Registrar';

	$lang['sign_forgot']       = 'Reestablecimiento de contraseña';
	$lang['sign_info_forgot']  = 'Por favor proporcione su direccion de correo electronico y en breve resivira un correo con un link dentro del cual podra resstablecer su usuario y contraseña.';

	$lang['invalid_email']     = 'La dirección de correo electronico no es valida, esto puede ser debido a que no es una direccion de correo electronico. Por favor veriquela y vuelva a intentarlo, gracias';
	$lang['email_err']         = 'La dirección de correo electronico proporcionada no es valida, esto puede ser debido a que no es una direccion de correo electronico, no se encuentra registrada en el sistema o su cuenta de usuario ha sido deshabilitada. Por favor veriquela y vuelva a intentarlo, gracias.';
	$lang['reset_pwd_success'] = 'Se ha enviado un correo electronico a la direccion proporcionada, una vez recibido, por favor siga el link que se incluye en el cuerpo del correo para que pueda reestablecer su usuario y contraseña, gracias.';
	$lang['mail_failed']       = 'Lo sentimos, pero ocurrio un error durante el proceso de reestablecimiento, por favor vuelva a intentarlo. Si el problema persiste por favor contacte con el administrador, gracias. Codigo de error : smtp failed';

	/*Crooper*/

	$lang['change_avatar']    = 'Cambiar Imagen';
	$lang['local_img_upload'] = 'Subir Imagen';
	$lang['rotate_left'] = 'Rotar a la izquierda';
	$lang['rotate_right'] = 'Rotar a la derecha';
	$lang['save_avatar'] = 'Guardar Imagen';

	/*********/
?>