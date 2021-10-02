<?php
	/**
	* InStyle
	* Embedded CSS to Inline CSS Converter Class
	* @version 0.1
	* @updated 09/18/2009
	* 
	* @author David Lim
	* @email miliak@orst.edu
	* @link http://www.davidandjennilyn.com
	* @acknowledgements Simple HTML Dom
	*/ 

	class InStyle {

		function convert($document) {

			// Extract the CSS
			preg_match('/<style[^>]+>(?<css>[^<]+)<\/style>/s', $document, $matches);
            
			// Strip out extra newlines and tabs from CSS
			$css = preg_replace("/[\n\r\t]+/s", "", $matches['css']);

			// Extract each CSS declaration
			preg_match_all('/([a-zA-Z0-9_ ,#\.]+){([^}]+)}/s', $css, $rules, PREG_SET_ORDER);

			// For each CSS declaration, explode the selector and declaration into an array
			// Array index 1 is the CSS selector
			// Array index 2 is the CSS rule(s)
			foreach ($rules as $rule) {
				$styles[trim($rule['1'])] = trim($rule['2']);
			}

			// DEBUG: Show selector and declaration
			if (isset($debug) && $debug) {
				echo '<pre>';
				foreach ($styles as $selector=>$styling) {
					echo $selector . ':<br>';
					echo $styling . '<br/><br/>';
				}
				echo '</pre><hr/>';
			}

			// Load Simple HTML DOM helper
			//require_once('simple_html_dom.php');
			$html_dom = new simple_html_dom();

			// Load in the HTML without the head and style definitions
			$html_dom->load(preg_replace('/\<head\>(.+?)\<\/head>/s', '', $document));

			// For each style declaration, find the selector in the HTML and add the inline CSS
			if (!empty($styles)) {
				foreach ($styles as $selector=>$styling) {
					foreach ($html_dom->find($selector) as $element) {
						// If there is any existing style, this will append to it
						$element->style .= $styling;
					}
				}
				$inline_css_message = $html_dom->save();
				return $inline_css_message;
			}
			return false;
		}
	}

/* End of file inline_css.php */