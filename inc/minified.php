<?php

/**
	We can expect the link specified in layout.htm to be routed here:-
		<link rel="stylesheet" type="text/css"
			href="/min?base=gui/css/&amp;files=demo.css"/>

	Notice that we only specified the following in our router/controller:-
		F3::route('GET /min',':minified');

	The F3::minify method combines all our comma-separated files (although
	we just have demo.css in our example) and strips them of all whitespaces
	and comments. The output is then gzipped and given a far-future expiry
	date so we get the squeeze every ounce of performance from our server.

	We could have done the same with Javascript files. The pattern should be
	the same. Here's an example:-
		<script type="text/javascript"
			src="/min?base=gui/js/&amp;files=mootools-core.js,mylib.js">
		</script>

	You can name the variables 'base' and 'files' any way you like. But make
	sure that the path pointed to by 'base' (or whatever variable you replace
	it with is relative to your Web root's index.php.

	You may want to do some validation here before using the REQUEST variable
	directly in your application. Otherwise, you'd be vulnerable to XSS
	attacks.
**/

F3::minify(F3::get('REQUEST.base'),explode(',',F3::get('REQUEST.files')));

?>
