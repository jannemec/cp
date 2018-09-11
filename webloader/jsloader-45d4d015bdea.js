
(function($, undefined) {

$.nette.ext({
	before: function (xhr, settings) {
		if (!settings.nette) {
			return;
		}

		var question = settings.nette.el.data('confirm');
		if (question) {
			return confirm(question);
		}
	}
});

})(jQuery);

(function($, undefined) {

$.nette.ext('spinner', {
	init: function () {
		this.spinner = this.createSpinner();
		this.spinner.appendTo('body');
	},
	start: function () {
		this.counter++;
		if (this.counter === 1) {
			this.spinner.show(this.speed);
		}
	},
	complete: function () {
		this.counter--;
		if (this.counter <= 0) {
			this.spinner.hide(this.speed);
		}
	}
}, {
	createSpinner: function () {
		return $('<div>', {
			id: 'ajax-spinner',
			css: {
				display: 'none'
			}
		});
	},
	spinner: null,
	speed: undefined,
	counter: 0
});

})(jQuery);

(function($, undefined) {

/**
 * Depends on 'snippets' extension
 */
$.nette.ext('scrollTo', {
	init: function () {
		this.ext('snippets', true).before($.proxy(function ($el) {
			if (this.shouldTry && !$el.is('title')) {
				var offset = $el.offset();
				scrollTo(offset.left, offset.top);
				this.shouldTry = false;
			}
		}, this));
	},
	success: function (payload) {
		this.shouldTry = true;
	}
}, {
	shouldTry: true
});

})(jQuery);
