<?php

// Execute validation rules
F3::call('tc|ad|soyad');

/**
	Functions below are used by both saveblog.php and updateblog.php import
	files for validation of form fields in the blog entry page
**/

function tc() {
	// Validate tc
	F3::input('tc',
		function($value) {
			if (!F3::exists('message')) {
				if (empty($value))
					F3::set('message','TC should not be blank');
				elseif (strlen($value)>127)
					F3::set('message','TC is too long');
				elseif (strlen($value)<3)
					F3::set('message','TC is too short');
			}
			// Do post-processing of tc here
			F3::set('REQUEST.tc',ucfirst($value));
		}
	);
}

function ad() {
	// Validate blog ad
	F3::input('ad',
		function($value) {
			if (!F3::exists('message')) {
				if (empty($value))
					F3::set('message','Ad should not be blank');
				elseif (strlen($value)>127)
					F3::set('message','Ad is too long');
				elseif (strlen($value)<3)
					F3::set('message','Ad is too short');
			}
			// Do post-processing of ad here
			F3::set('REQUEST.ad',ucfirst($value));
		}
	);
}

function soyad() {
	// Validate blog soyad
	F3::input('soyad',
		function($value) {
			if (!F3::exists('message')) {
				if (empty($value))
					F3::set('message','Soyad should not be blank');
				elseif (strlen($value)>127)
					F3::set('message','Soyad is too long');
				elseif (strlen($value)<3)
					F3::set('message','Soyad is too short');
			}
			// Do post-processing of soyad here
			F3::set('REQUEST.soyad',ucfirst($value));
		}
	);
}

?>
