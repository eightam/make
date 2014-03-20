/* global Backbone, jQuery, _ */
var oneApp = oneApp || {};

(function (window, Backbone, $, _, oneApp) {
	'use strict';

	var Sections = Backbone.Collection.extend({
		model: oneApp.Section,

		$stage: $('#ttf-one-stage'),

		toggleStageClass: function() {
			var sections = $('.ttf-one-section', this.$stage).length;

			if (sections > 0) {
				this.$stage.removeClass('ttf-one-stage-closed');
			} else {
				this.$stage.addClass('ttf-one-stage-closed');
			}
		}
	});

	oneApp.sections = new Sections();
})(window, Backbone, jQuery, _, oneApp);