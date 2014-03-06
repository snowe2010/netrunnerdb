NRDB.card_modal = {};
(function( card_modal ) {
	var modal = null;
	
	card_modal.create_element = function() {
		modal = $('<div class="modal" id="cardModal" tabindex="-1" role="dialog" aria-labelledby="cardModalLabel" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h3 class="modal-title">Modal title</h3><div class="row"><div class="col-sm-12 text-center"><div class="btn-group modal-qty" data-toggle="buttons"></div></div></div></div><div class="modal-body"><div class="row"><div class="col-sm-6 modal-image"></div><div class="col-sm-6 modal-info"></div></div></div></div></div></div>');
		modal.appendTo('body');
	};
	
	card_modal.display_modal = function(event) {
		event.preventDefault();
		$(this).qtip('hide');
		var code = $(this).data('index') || $(this).closest('.card-container').data('index');
		fill_modal(code);
	};

	card_modal.display_modal = function(event) {
		event.preventDefault();
		$(this).qtip('hide');
		var code = $(this).data('index') || $(this).closest('.card-container').data('index');
		fill_modal(code);
	};

	card_modal.typeahead = function (event, data) {
		var card = CardDB({title:data.value}).first();
		fill_modal(card.code);
		$('#cardModal').modal('show');
		InputByTitle = true;
	};

	function fill_modal (code) {
		var card = CardDB({code:code}).first();
		modal.data('index', code);
		modal.find('h3.modal-title').html((card.uniqueness ? "&diams; " : "")+card.title);
		modal.find('.modal-image').html('<img class="img-responsive" src="'+card.imagesrc+'">');
		modal.find('.modal-info').html(
		  '<div class="card-info">'+get_type_line(card)+'</div>'
		  +'<div><small>' + card.faction + ' &bull; '+ card.setname + '</small></div>'
		  +'<div class="card-text"><small>'+text_format(card.text)+'</small></div>'
		);

		var qtyelt = modal.find('.modal-qty');
		if(qtyelt && typeof Filters != "undefined") {

			var qty = '';
		  	for(var i=0; i<=card.maxqty; i++) {
		  		qty += '<label class="btn btn-default"><input type="radio" name="qty" value="'+i+'">'+i+'</label>';
		  	}
		  	qtyelt.html(qty);
		   	
		  	qtyelt.find('label').each(function (index, element) {
				if(index == card.indeck) $(element).addClass('active');
				else $(element).removeClass('active');
			});
			if(card.type_code == "agenda" && card.faction_code != "neutral" && card.faction_code != Identity.faction_code) {
				qtyelt.find('label').addClass("disabled").find('input[type=radio]').attr("disabled", true);
			}
			if(card.code == Identity.code) {
				qtyelt.find('label').addClass("disabled").find('input[type=radio]').attr("disabled", true);
			}

			
		} else {
			if(qtyelt) qtyelt.closest('.row').remove();
		}
	}

	
})(NRDB.card_modal);

$(function () {
	NRDB.card_modal.create_element();
	$('body').on({click: NRDB.card_modal.display_modal}, '.card[data-target=#cardModal]');
});

