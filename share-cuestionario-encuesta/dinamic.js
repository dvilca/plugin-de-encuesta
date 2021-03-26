$(document).ready(function(){
	var i = 1;

	$('#add').click(function () {
		var i = 1;
        var asociado = $('#cant').val();
        while (i <= asociado) {         
	   $('#row'+ i).remove();
       $('#roww'+ i).remove();
		$('#dynamic_field').append('<tr id="row'+i+'" class="form-field">' +
            '<th scope="row"><label for="last_name">Pregunta '+i+' </label></th>' +
            '<td>'+
                '<input type="text" name="pre'+i+'" required="required"  placeholder="Ingrese pregunta" class="form-control name_list" />'+                
            '</td>'+
            '</tr>'+
            '<tr id="roww'+i+'" class="form-field">' +
            '<th scope="row"></th>' +
            '<td>'+
                '<input type="text" name="alt'+i+'1" placeholder="Alternativa 1 " class="form-control name_list" />'+                          
                '<input type="text" name="alt'+i+'2" placeholder="Alternativa 2 " class="form-control name_list" />'+                          
                '<input type="text" name="alt'+i+'3" placeholder="Alternativa 3 " class="form-control name_list" />'+                          
                '<input type="text" name="alt'+i+'4" placeholder="Alternativa 4 " class="form-control name_list" />'+                          
                '<input type="text" name="alt'+i+'5" placeholder="Alternativa 5 " class="form-control name_list" />'+                          
            '</td>'+
            '</tr>');
            i++;
        }                            
	});
	
	$(document).on('click', '.btn_remove', function () {
		var id = $(this).attr('id');
	   $('#row'+ id).remove();
       $('#roww'+ id).remove();
	});
	
})