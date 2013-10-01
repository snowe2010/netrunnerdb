
var SearchList = (function () {
	var deck_controls_set = false;
	function setup() {
		var target = $('#list'),
			form = $('#search-form');
		if(!target.length || !history.pushState || !form) return;
		history.replaceState(location.pathname+location.search, document.title, null);

		var handler = ajaxHandler.bind(target, true);

		// things to do when a list of cards is loaded via ajax
		$(document).bind("listchange", function () {
			$('#list a').each(function () {
				var href = $(this).attr('href');
				if(href && (href.substring(0, 1) == "/" || href.substring(0, location.origin.length) == location.origin)) {
					$(this).click( function() {
						$.ajax( {
							url: href + (href.indexOf('?') != -1 ? '&' : '?') + "mode=fragment",
							success: handler
						} );
						return false;
					} );
				}
			});
		});
		$(document).bind("listchange", function () {
			$('.forum-links input').click(function () { $(this).select(); });
		});

		// replace normal submit with ajax submit
		$('#mode-input').attr('value', 'fragment').removeAttr('disabled');
		form.submit( function() {
			$.ajax( {
				type: form.attr( 'method' ),
				url: form.attr( 'action' ),
				data: form.serialize(),
				success: handler
			} );
			return false;
		} );

		window.onpopstate = function(event) {
		    if(event.state) {
				$.ajax( {
					url: event.state + (event.state.indexOf('?') != -1 ? '&' : '?') + "mode=fragment",
					success: ajaxHandler.bind(target, false)
				} );
		    }
		}
	}
	function ajaxHandler( recordState, response, status, jqXHR ) {
		deck_controls_set = false;
        $(this).html(response);
		$(document).trigger("listchange");
		var newUri = jqXHR.getResponseHeader('RequestUri');
		var newTitle = decodeURIComponent(jqXHR.getResponseHeader('PageTitle'));
		var newSearchString = decodeURIComponent(jqXHR.getResponseHeader('SearchString'));
		if(recordState) history.pushState(newUri, newTitle, newUri);
		document.title = newTitle;
		$('#search-input').val(newSearchString);
		_gaq.push(['_trackPageview', newUri]);
		document.getElementById('viewport').setAttribute('content', 'width=640');
		alert(document.getElementById('viewport').setAttribute('content'));
	}
	function add_controls() {
		if(deck_controls_set) return;
		deck_controls_set = true;
		$('a.card-title').after(
			$('<img src="'+imgBaseUrl+'/iconic/16/gray_light/plus_alt_16x16.png" class="glyph control control-add">').hover(function() {
				$(this).attr("src", imgBaseUrl+'/iconic/16/blue/plus_alt_16x16.png')
			},function() {
				$(this).attr("src", imgBaseUrl+'/iconic/16/gray_light/plus_alt_16x16.png')
			})
		);
		$('img.control-add').click(function () {
			var cardIndex = $(this).prev().attr('data-index');//TODO
			DeckList.addCard(cardIndex);
			return false;
		})
	}
	function remove_controls() {
		if(!deck_controls_set) return;
		deck_controls_set = false;
		$('img.control-add').remove();
	}
	return {
		setup: setup.bind(SearchList),
		add_controls: add_controls.bind(SearchList),
		remove_controls: remove_controls
	};
})();
