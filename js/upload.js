function addAll(categ){
	$("#CategSelect > option").each(function() {
		if ($(this).attr("v")!="false"){
			$(this).remove().clone().appendTo("#Categ"+categ+"Select"); ;
		}
		
	});
}


function addSelected(categ){
	$("#CategSelect option:selected").remove().appendTo("#Categ"+categ+"Select"); 
}

function removeAll(categ){
	$("#Categ"+categ+"Select option").remove().appendTo("#CategSelect"); 
}

function removeSelected(categ){
	$("#Categ"+categ+"Select option:selected").remove().appendTo("#CategSelect"); 
}



$(document).ready(function () {

	$('form').submit(function() {
		
		$("#Categ1Select option").prop('selected', true);
		$("#Categ2Select option").prop('selected', true);
		
	});

});
