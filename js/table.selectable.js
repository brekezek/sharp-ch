(function($) {
	$.fn.selectableRows = function(){
		$('main').prepend('<div class="bg-secondary text-white p-2" id="tools" style="display:none">'+
				'<div class="d-flex justify-content-between align-items-center">'+
				'<div>'+
		    		'<span id="nb-selected" class="badge badge-light px-3 mr-1" style="padding: .25rem .5rem; font-size:1.1em"><span>0</span> sélectionnés</span>'+
		    		'<button id="unselect" class="btn btn-light btn-sm" style="padding: .25rem .5rem; vertical-align:baseline; line-height:1">Désélectionner</button>'+ 
		    	'</div>'+
		    	
		    	'<div id="buttons">'+
		    		
		    	'</div>'+
			'</div>'+
		'</div>');
		
		$('#repondants_length').append('<div class="btn btn-light btn-sm ml-2" style="vertical-align:top" id="selectAll">Sélectionner tout</div>');
		
		$('body').on('click', '#repondants tbody tr', function(e){
			if($(e.target).is('td')) {
				$(this).toggleClass("active bg-dark").find('td').toggleClass('text-white');

				var nbSelected = $('#repondants tr.active').length;
				$('#tools').toggle(nbSelected > 0);
				$('#nb-selected span, [data-track-row]').text(nbSelected);
			}
		});
		
		$('#tools #unselect').click(function(){
			$('#repondants tr.active').removeClass("active bg-dark").find("td").removeClass("text-white");
			$('#tools').toggle($('#repondants tr.active').length > 0);
		});
		
		$('body').on('click', '#selectAll', function(){
			$('#repondants tbody tr').addClass("active bg-dark").find("td").addClass("text-white");
			var nbSelected = $('#repondants tr.active').length;
			$('#tools').toggle(nbSelected > 0);
			$('#nb-selected span, [data-track-row]').text(nbSelected);
		});
		
		return this;
	};
	
	$.fn.addButton = function(text, id, style, icon, event){
		$('main #tools #buttons').prepend('<a class="btn btn-'+style+' btn-sm mr-1" id="'+id+'"><span class="oi oi-'+icon+' mr-1"></span> <span class="text">'+text+'</span> <span data-track-row class="badge badge-light ml-1">0</span></a>');
		$('body').on('click', '#'+id, event);
		return this;
	};
	
	$.fn.addDropdown = function(text, id, items, style, icon, event) {
		var itemsHTML = "";
		for(i in items) {
			itemsHTML += '<div class="dropdown-item" style="cursor:pointer" data-action="'+items[i].action+'">'+items[i].text+'</div>';
		}
		$('main #tools #buttons').prepend(
			'<div class="dropdown d-inline">'+
			 	'<button class="btn btn-'+style+' btn-sm mr-1 dropdown-toggle" type="button" id="'+id+'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'+
			 		'<span class="oi oi-'+icon+' mr-1"></span> <span class="text">'+text+'</span> <span data-track-row class="badge badge-light ml-1">0</span>'+
		    	'</button>'+
				'<div class="dropdown-menu" aria-labelledby="'+id+'">'+
					itemsHTML+
				'</div>'+
			'</div>'
		);
		$('body').on('click', '.dropdown-menu[aria-labelledby="'+id+'"] .dropdown-item', event);
		return this;
	}
}(jQuery));