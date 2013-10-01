
var IconBar = (function () {
	
	var icon_color_normal = "brown_dark";
	var icon_color_hover = "gray_dark";

	function clear() {
		$('#deck-control-menu').html('');
	}
	function add(icon, text, handler) {
		$('#deck-control-menu').append(
			$('<li>').append(
				$("<a>").attr('href', '#').attr('title', text).click(handler).append(
					$("<img>")
						.attr('src', imgBaseUrl+'/iconic/32/'+icon_color_normal+'/'+icon)
						.attr('alt', "Create a Deck")
				).append(
					$("<span>").attr('class', 'label').html(text)
				)
			)
			
		);
	}
	function setup() {
		$('#main-menu')
		.on("mouseenter", "li", function () {
			$(this).find('img').each(function () {
				$(this).attr('src', $(this).attr('src').replace(icon_color_normal, icon_color_hover)); 
			});
		})
		.on("mouseleave", "li", function () {
			$(this).find('img').each(function () {
				$(this).attr('src', $(this).attr('src').replace(icon_color_hover, icon_color_normal)); 
			});
		})
	}
	return {
		clear: clear,
		add_icon: add.bind(IconBar),
		setup: setup
	};
})();
