<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING 
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

/*DEFAULT*/
$route['default_controller']       = 'login/index';
$route['sign_up']                  = 'sign_up/index';
$route['forgot_pwd']               = 'sign_up/forgot_pwd';

$route['sync/SyncCatalogs']         = 'sync/ws_sync/SyncCatalogs';
$route['sync/SyncSales']            = 'sync/ws_sync/SyncSales';


$route['404_override']             = 'error404';
$route['inicio']                   = 'inicio/index';
/********/

/*HISTORIAL DE CICLOS*/
$route['historial']				   = 'historial/index';

/*LOGIN*/
$route['login']                    = 'login/index';
$route['login/authentication']     = 'login/authentication';
$route['login/valindando']         = 'login/redireccion';
$route['logout']                   = 'login/logout';
/*******/

/*NUTRICION*/

/*Valores nutricionales*/
$route['nutricion/valores_nutricionales']    	 = 'nutricion/valores_nutricionales/index';
/*Cotizador de recetas*/
$route['nutricion/cotizador_recetas']    	     = 'nutricion/cotizador_recetas/index';
/*Familias*/
$route['nutricion/catalogos/familias']    	     = 'nutricion/familias/index';
/*tiempos*/
$route['nutricion/catalogos/tiempos']    	     = 'nutricion/tiempos/index';
/*tiempos*/
$route['nutricion/catalogos/grupos']    	     = 'nutricion/grupos/index';
/*Recetario*/
$route['nutricion/recetario']                    = 'nutricion/recetario/index';
/*Programacion*/
$route['nutricion/programacion']                 = 'nutricion/programacion/index';
/*Conformacion de Ciclos*/
$route['nutricion/conformacion_de_ciclos']       = 'nutricion/ciclos/index';
/*Conformacion de menus*/
$route['nutricion/conformacion_de_menus']        = 'nutricion/menus/index';


/*COMPRAS*/

/*Listado de precios*/
$route['compras/listado_precios']    			= 'compras/listado_precios/index';

/*Listado Presentaciones*/
$route['compras/listado_presentaciones']    	= 'compras/listado_presentaciones/index';
/*Listado articulos precios proveedores*/
$route['compras/listado_precios_proveedores']   = 'compras/listado_precios_proveedores/index';

/*Catalogo Articulos*/
$route['compras/catalogos/articulos']   = 'compras/articulos/index';

/*Catalogo Presentaciones*/
$route['compras/catalogos/presentaciones']                               = 'compras/presentaciones/index';
$route['compras/catalogos/presentaciones/listado_presentaciones']        = 'compras/presentaciones/listado_presentaciones';
$route['compras/catalogos/presentaciones/listado_presentaciones/(:num)'] = 'compras/presentaciones/listado_presentaciones/$1';

/*Catalogo Lineas*/
$route['compras/catalogos/lineas']                       = 'compras/lineas/index';
$route['compras/catalogos/lineas/listado_lineas']        = 'compras/lineas/listado_lineas';
$route['compras/catalogos/lineas/listado_lineas/(:num)'] = 'compras/lineas/listado_lineas/$1';

/*Catalogo de Marcas*/
$route['compras/catalogos/marcas']                       = 'compras/marcas/index';
$route['compras/catalogos/marcas/listado_marcas']        = 'compras/marcas/listado_marcas';
$route['compras/catalogos/marcas/listado_marcas/(:num)'] = 'compras/marcas/listado_marcas/$1';

/*Catalogo de U.M.*/
$route['compras/catalogos/unidad_de_medida']                   = 'compras/um/index';
$route['compras/catalogos/unidad_de_medida/listado_um']        = 'compras/um/listado_um';
$route['compras/catalogos/unidad_de_medida/listado_um/(:num)'] = 'compras/um/listado_um/$1';

/*Catalogo de Embalaje*/
$route['compras/catalogos/embalaje']    = 'compras/embalaje/index';

/*Catalogo de Ordenes*/
$route['compras/ordenes/ordenes']                    = 'compras/ordenes/index';
$route['compras/ordenes/aprobar_ordenes']            = 'compras/aprobar_ordenes/index';
$route['compras/ordenes/historial_ordenes']          = 'compras/historial_ordenes/index';

/*ALMACEN*/

/*ENtradas Recepcion*/
$route['almacen/entradas/entradas_recepcion']		 = 'almacen/entradas_recepcion/index';
$route['almacen/entradas/entradas_almacen']		     = 'almacen/entradas_almacen/index';
/*Traspasos de alamcen*/
$route['almacen/traspasos']			     			 = 'almacen/traspasos';
/*Catalogo de Ajustes de alamcen*/
$route['almacen/ajustes/agregar_ajustes']			 = 'almacen/agregar_ajustes/index';
$route['almacen/ajustes/aprobar_ajustes']			 = 'almacen/aprobar_ajustes/index';
$route['almacen/ajustes/historial_ajuste']			 = 'almacen/historial_ajuste/index';
/*Catalogo de almacenes*/
$route['almacen/catalogos/almacenes']			     = 'almacen/almacenes/index';
/*Catalogo de pasillos*/
$route['almacen/catalogos/pasillos']			     = 'almacen/pasillos/index';
/*Catalogo de gavetas*/
$route['almacen/catalogos/gavetas']			         = 'almacen/gavetas/index';
/*Catalogo de transportes*/
$route['almacen/catalogos/transportes']			     = 'almacen/transportes/index';
/*Reportes de almacenes*/
$route['almacen/catalogos/reportes']			     = 'almacen/reportes/index';


/*ADMINISTRACION*/

/*
 * CONTROL DE USUARIOS
 */
/*Catalogo de Usuarios*/
$route['administracion/control_de_usuarios/usuarios']        = 'administracion/usuarios/index';
/*Catalogo de Usuarios*/
$route['administracion/control_de_usuarios/perfiles']        = 'administracion/perfiles/index';
/*Catalogo de Puestos*/
$route['administracion/control_de_usuarios/puestos']         = 'administracion/puestos/index';
/*Catalogo de Areas*/
$route['administracion/control_de_usuarios/areas']           = 'administracion/areas/index';
/*Catalogo de Perfiles*/
$route['administracion/control_de_usuarios/perfiles']        = 'administracion/perfiles/index';
/*Catalogo de Perfiles*/
$route['administracion/control_de_usuarios/empresa']         = 'administracion/empresa/index';
/*
 * CATALOGOS GENERALES
 */
/*Catalogo de Sucursales*/
$route['administracion/sucursales/sucursales']               = 'administracion/sucursales/index';
/*Catalogo de Impuestos*/
$route['administracion/catalogos_generales/impuestos']       = 'administracion/impuestos/index';
/*Catalogo de Descuentos*/
$route['administracion/catalogos_generales/descuentos']      = 'administracion/descuentos/index';
/*Catalogo de Formas de pago*/
$route['administracion/catalogos_generales/formas_de_pago']  = 'administracion/formas_de_pago/index';
/*Catalogo de Creditos*/
$route['administracion/catalogos_generales/creditos']        = 'administracion/creditos/index';
/*Catálogo de Entidades*/
$route['administracion/catalogos_generales/entidades']       = 'administracion/entidades/index';
/*Catálogo de Regiones*/ 
$route['administracion/catalogos_generales/regiones']        = 'administracion/regiones/index'; 
/*Catálogo de subrogacion*/ 
$route['administracion/catalogos_generales/subrogacion']     = 'administracion/subrogacion/index'; 
/*Catálogo de Servicios*/ 
$route['administracion/sucursales/servicios']                = 'administracion/servicios/index';

/*VENTAS*/
/*Ventas por punto de venta*/
$routr['ventas/por_punto_venta']                = 'ventas/ventas_punto_venta/index';
/*Catalogo de clientes*/
$route['ventas/catalogos/clientes']				= 'ventas/clientes/index';
/*Catalogo de vendedores*/
$route['ventas/catalogos/vendedores']			= 'ventas/vendedores/index';

$route['ventas/ventas_punto_venta/paginar']            = 'ventas/ventas_punto_venta/buscar_ventas';
$route['ventas/ventas_punto_venta/paginar/(:num)']     = 'ventas/ventas_punto_venta/buscar_ventas/$1';

/*SUCURSALES*/
$route['sucursales/listado_sucursales']				    =	'sucursales/listado_sucursales/index';
$route['sucursales/horarios_servicio']				    =	'sucursales/horarios_servicio/index';
$route['sucursales/punto_venta']				        =	'sucursales/punto_venta/index';
$route['sucursales/clientes']							=	'sucursales/clientes/index';
$route['sucursales/operadores']							=	'sucursales/operadores/index';
$route['sucursales/horarios_atencion']				    =	'sucursales/horarios_atencion/index';
$route['sucursales/parametrizacion/tickets']			=	'sucursales/parametrizacion/tickets/index';
$route['sucursales/parametrizacion/tipo_de_compra']		=	'sucursales/parametrizacion/tipo_de_compra/index';
$route['sucursales/parametrizacion/forma_de_pago']		=	'sucursales/parametrizacion/forma_de_pago/index';
$route['sucursales/sincronizacion/carga_de_datos']		=	'sucursales/sincronizacion/carga_de_datos/index';
$route['sucursales/sincronizacion/descarga_de_datos']	= 	'sucursales/sincronizacion/descarga_de_datos/index';
/* End of file routes.php */
/* Location: ./application/config/routes.php */