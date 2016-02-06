Nuevo sistema de AdminControl v.2.0

Contenido:
    
    Compras
    Ventas
    Almacenes (inventario)
    Nutrición (Hostess)
    Administración 
        Usuarios
        Catalogos generales

[Archivo con parametros]

\assets\js\system\inc.config.js
	if(!Folder){Folder='[nombre_de_folder]';}
		i.e: if(!Folder){Folder='admincontrol';}

\assets\cfg\codeigniter.cfg
	cfg|base_url=[ruta_URI]
		i.e: cfg|base_url=http://192.168.230.28/sandbox/admincontrol/