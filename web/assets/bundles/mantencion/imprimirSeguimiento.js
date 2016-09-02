var button_descargar_formulario = $("#button_descargar_formulario");

$(function(){

	$('#text_fecha').datepicker({
		dateFormat: "dd/mm/yy"
	});

	$("#select_dia").on('change', function(){
		
		cargarFormularios();

	});
	$("#text_fecha").on('change', function(){
		
		cargarFormularios();

	});

});

function cargarFormularios()
{
	$("#formularios").html('<div class="row"><div class="col-md-12 text-center"><i class="fa fa-cog fa-spin fa-3x fa-fw"></i></div></div>');

		var dia 	= $("#select_dia").val();
		var fecha 	= $("#text_fecha").val();

		$.ajax({
			url: Routing.generate('mantencion_ajax_cargarformularios'),
			data: {'dia': dia, 'fecha': fecha},
			dataType: 'html',
		}).success(function(json){
			$("#formularios").html(json);
		}).done(function(){

			button_descargar_formulario.on('click', function(){

				$("#form_imprimir").submit();

			});
		
		});
}