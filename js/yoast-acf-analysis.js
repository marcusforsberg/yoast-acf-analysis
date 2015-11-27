(function($) {
	$(document).ready(function() {
		/**
		 * Set up the Yoast ACF Analysis plugin
		 */
		YoastACFAnalysis = function() {
			// Register with YoastSEO
			YoastSEO.app.registerPlugin('yoastACFAnalysis', {status: 'ready'});
			YoastSEO.app.registerModification('content', this.addAcfFieldsToContent, 'yoastACFAnalysis', 5);

			// Re-analyse SEO score each time the content of an ACF field is updated
			$('#post-body').find('input[type=text][name^=acf], textarea[name^=acf]').on('keyup paste cut', function() {
				YoastSEO.app.pluginReloaded('yoastACFAnalysis');
			});
		}

		/**
		 * Combine the  content of all ACF fields on the page and add it to Yoast content analysis 
		 *
		 * @param data Current page content
		 */
		YoastACFAnalysis.prototype.addAcfFieldsToContent = function(data) {
			var acf_content = '';
			
			$('#post-body').find('input[type=text][name^=acf], textarea[name^=acf]').each(function() {
				acf_content += ' ' + $(this).val();
			});

			return data + acf_content;
		};

		new YoastACFAnalysis();
	});
}(jQuery));