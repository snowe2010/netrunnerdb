if (typeof NRDB != "object")
	var NRDB = { data_loaded: $.Callbacks() };

NRDB.tip = {};
(function(tip) {
	
	tip.display = function(event) {
		var code = $(this).data('index')
				|| $(this).closest('.card-container').data('index')
				|| ($(this).attr('href') && $(this).attr('href').replace(
						/.*\/card\/(\d\d\d\d\d)$/,
						"$1"));
		var card = NRDB.data.cards({
			code : code
		}).first();
		if (!card)
			return;
		var type = '<p class="card-info">' + NRDB.format.type(card) + '</p>';
		var influence = '';
		for (var i = 0; i < card.factioncost; i++)
			influence += "&bull;";
		if (card.strength != null)
			type += '<p>Strength <b>' + card.strength + '</b></p>';
		$(this).qtip(
				{
					content : {
						text : '<div class="card-image card-image-'+card.side_code+'-'+card.type_code+'"'+(card.imagesrc ? ' style="background-image:url('+card.imagesrc+')"': '')
								+ '><svg width="103px" height="90px" viewBox="0 0 677 601" xmlns="http://www.w3.org/2000/svg" xmlns:svg="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><rect x="0" y="0" width="677" height="601" fill="white" fill-opacity="1" mask="url(#mask)"/><use xlink:href="#hex" style="stroke:black; fill:none; stroke-width:15" /></svg></div><h4>'
								+ (card.uniqueness ? "&diams; " : "")
								+ card.title + '</h4>' + type
								+ '<div class="card-text">' + NRDB.format.text(card) + '</div>'
								+ '<p class="card-faction" style="text-align:right">' + influence
								+ ' ' + card.faction + '</p>'
					},
					style : {
						classes : 'qtip-bootstrap qtip-nrdb'
					},
					position : {
						my : 'left center',
						at : 'right center',
						viewport : $(window)
					},
					show : {
						event : event.type,
						ready : true,
						solo : true
					}
				}, event);
	};

})(NRDB.tip);

$(function() {

	if(typeof Modernizr == 'undefined' || !Modernizr.touch ) {
		$('body').on({
			mouseover : NRDB.tip.display,
			focus : NRDB.tip.display
		}, 'a');
	}

});