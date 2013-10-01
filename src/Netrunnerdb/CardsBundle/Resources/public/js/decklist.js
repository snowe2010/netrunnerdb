
var DeckList = (function () {
	var state = 0; // 0: no icon ; 1: no deck icons ; 2: deck icons
	var last_state = 0;
	
	// contains indexes to call with deck/alter
	var addQueue = [];
	var removeQueue = [];
	
	function setup() {
		$(document).bind("deckchange", DeckList.add_controls);
		
		setInterval(function() {
			var arg = "";
			while(addQueue.length) arg += "+"+addQueue.pop();
			while(removeQueue.length) arg += "-"+removeQueue.pop();
			if(arg != "") {
				ajaxHandler('/deck/alter/'+encodeURIComponent(arg));
			}
		}, 2000);
		
		DeckList.refresh();
	}
	function update_icons() {
		if($('#deck').length) {
			state = 2;
		} else {
			state = 1;
		}
		if(state == last_state) return;
		last_state = state;
		IconBar.clear();
		if($('#deck').length) {
			$(document).bind("listchange", SearchList.add_controls);
			SearchList.add_controls();
			IconBar.add_icon('reload_24x28.png', "Reload", DeckList.refresh);
			IconBar.add_icon('download_24x32.png', "Export", null);
			IconBar.add_icon('eye_32x24.png', "Display", DeckList.view);
			IconBar.add_icon('trash_stroke_32x32.png', "Trash", DeckList.trash);

		} else {
			$(document).unbind("listchange", SearchList.add_controls);
			SearchList.remove_controls();
			// no deck, add "create deck" icon
			IconBar.add_icon('layers_32x28.png', "New", DeckList.create);
			IconBar.add_icon('upload_24x32.png', "Load", null);
		}

		
	}
	function addCard(cardIndex) {
		addQueue.push(cardIndex);
	}
	function removeCard(cardIndex) {
		removeQueue.push(cardIndex);
	}

	function add_controls() {
		DeckList.update_icons();
		$('img.control-remove').click(function () {
			var cardIndex = $(this).prev().data('index');
			DeckList.removeCard(cardIndex);
			return false;
		}).hover(function() {
			$(this).attr("src", imgBaseUrl+'/iconic/16/blue/minus_alt_16x16.png')
		},function() {
			$(this).attr("src", imgBaseUrl+'/iconic/16/gray_light/minus_alt_16x16.png')
		});

		$('a.ajax-control').click(function () {
			$('#deckbox').load($(this).attr('href'), function () {
				$(document).trigger("deckchange");
			});
			return false;
		});
	}
	function ajaxHandler(url) {
		$('#deckbox').load(url, function () {
			$(document).trigger("deckchange");
			$('meta[name=viewport]').attr('content', 'width=640');
		});
		return false;
	}
	return {
		setup: setup,
		view: function() { location.href = '/deck/view/' },
		trash: ajaxHandler.bind(DeckList, '/deck/delete/'),
		create: ajaxHandler.bind(DeckList, '/deck/new/'),
		refresh: ajaxHandler.bind(DeckList, '/deck/bar/'),
		add_controls: add_controls,
		update_icons: update_icons,
		addCard: addCard,
		removeCard: removeCard
	};
})();
