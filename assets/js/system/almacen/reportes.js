jQuery(document).ready(function(){
	
});

function carga_almacen(id_region){
	jQuery.ajax({
		type:"POST",
		url: path()+"almacen/reportes/carga_almacen",
		dataType: "json",
		data: {id_region : id_region},
		beforeSend : function(){
			jQuery("#list_almacen").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
		},
		success : function(data){
			var chosen  = 'jQuery(".chzn-select").chosen();';
        	jQuery("#list_almacen").html(data.list_almacenes+include_script(chosen)).show('slow');
        	jQuery("#btn_excel").html('');
		}
	});
}

function carga_btn_excel(id_almacen){
	jQuery.ajax({
		type:"POST",
		url: path()+"almacen/reportes/carga_btn_excel",
		dataType: "json",
		data: {id_almacen : id_almacen},
		beforeSend : function(){
			jQuery("#btn_excel").html('<img src="'+path()+'assets/images/loaders/loader13.gif"/>');
		},
		success : function(data){
        	jQuery("#btn_excel").html(data.btn_almacen+data.btn_stock_gral).show('slow');
		}
	});
}

function xls_almacen(){
	var objData = formData('#formulario');
  	objData['incomplete'] = values_requeridos();
  	jQuery.ajax({
		type:"POST",
		url: path()+"almacen/reportes/export_xlsx",
		dataType: "json",
		data: {objData : objData},
		beforeSend : function(){
			jQuery("#loader").html('<img src="'+path()+'assets/images/loaders/loader.gif"/>');
		},
		success : function(data){
			alert(data);
		}
	});
}