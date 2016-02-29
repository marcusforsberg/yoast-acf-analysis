(function($) {
	$(document).ready(function() {
		/**
		 * Set up the Yoast ACF Analysis plugin
		 */
		YoastACFAnalysis = function() {
			// Register with YoastSEO
			YoastSEO.app.registerPlugin('yoastACFAnalysis', {status: 'ready'});
			YoastSEO.app.registerModification('content', this.addAcfFieldsToContent, 'yoastACFAnalysis', 5);

			this.analysisTimeout = 0;

			// Re-analyse SEO score each time the content of an ACF field is updated
			$('#post-body').find('input[type=text][id^=acf], textarea[id^=acf-]').on('keyup paste cut blur', function() {
				if ( YoastACFAnalysis.analysisTimeout ) {
					window.clearTimeout(YoastACFAnalysis.analysisTimeout);
				}

				YoastACFAnalysis.analysisTimeout = window.setTimeout( function() { YoastSEO.app.pluginReloaded('yoastACFAnalysis'); }, 200 );
			});

		};

		/**
		 * Combine the content of all ACF fields on the page and add it to Yoast content analysis
		 *
		 * @param data Current page content
		 */
		YoastACFAnalysis.prototype.addAcfFieldsToContent = function(data) {
			var acf_content = ' ';
			
			$('#post-body').find('input[type=text][id^=acf], textarea[id^=acf-]').each(function() {
				acf_content += ' ' + $(this).val();
			});

			data = data + acf_content;

			return data.trim();
		};

		new YoastACFAnalysis();
	});
}(jQuery));