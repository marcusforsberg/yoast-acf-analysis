# Yoast ACF Analysis
## Please see the up-to-date version maintained by Yoast: https://github.com/Yoast/yoast-acf-analysis/

---

WordPress plugin that adds the content of all ACF fields to the Yoast SEO score analysis.

##Description
[Yoasts SEO's](https://yoast.com/wordpress/plugins/) score analysis does not take in to account the content of a post's [Advanced Custom Fields](http://www.advancedcustomfields.com/). This plugin uses Yoast 3.0's plugin system to hook into the analyser in order to add ACF content to the SEO analysis.

This had previously been done by the [WordPress SEO ACF Content Analysis](https://wordpress.org/plugins/wp-seo-acf-content-analysis/) plugin but that no longer works with Yoast 3.0. Kudos to [ryuheixys](https://profiles.wordpress.org/ryuheixys/), the author of that plugin, for the original idea.

Please note that this does not work with the "Recalculate SEO scores" tool in Yoast SEO's admin area. I am [looking into that](https://github.com/Yoast/wordpress-seo/issues/3323#issuecomment-160114155).
