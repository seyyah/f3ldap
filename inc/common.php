<?php

// Execute validation rules
F3::call('cn|sn|telephonenumber|postalcode|userpassword');

/**
	Functions below are used by both saveblog.php and updateblog.php import
	files for validation of form fields in the blog entry page
**/

function cn() {
	// Validate cn
	F3::input('cn',
		function($value) {
			if (!F3::exists('message')) {
				if (empty($value))
					F3::set('message','Ad should not be blank');
				elseif (strlen($value)>127)
					F3::set('message','Ad is too long');
				elseif (strlen($value)<3)
					F3::set('message','Ad is too short');
			}
			// Do post-processing of cn here
			F3::set('REQUEST.cn',ucfirst($value));
		}
	);
}
function sn() {
	// Validate sn
	F3::input('sn',
		function($value) {
			if (!F3::exists('message')) {
				if (empty($value))
					F3::set('message','Soyad should not be blank');
				elseif (strlen($value)>127)
					F3::set('message','Soyad is too long');
				elseif (strlen($value)<3)
					F3::set('message','Soyad is too short');
			}
			// Do post-processing of sn here
			F3::set('REQUEST.sn',ucfirst($value));
		}
	);
}
function telephonenumber() {
	// Validate telephonenumber
	F3::input('telephonenumber',
		function($value) {
			if (!F3::exists('message')) {
				if (empty($value))
					F3::set('message','Telefon should not be blank');
				elseif (strlen($value)>127)
					F3::set('message','Telefon is too long');
				elseif (strlen($value)<3)
					F3::set('message','Telefon is too short');
			}
			// Do post-processing of telephonenumber here
			F3::set('REQUEST.telephonenumber',ucfirst($value));
		}
	);
}
function postalcode() {
	// Validate postalcode
	F3::input('postalcode',
		function($value) {
			if (!F3::exists('message')) {
				if (empty($value))
					F3::set('message','Posta kodu should not be blank');
				elseif (strlen($value)>127)
					F3::set('message','Posta kodu is too long');
				elseif (strlen($value)<3)
					F3::set('message','Posta kodu is too short');
			}
			// Do post-processing of postalcode here
			F3::set('REQUEST.postalcode',ucfirst($value));
		}
	);
}
function userpassword() {
	// Validate userpassword
	F3::input('userpassword',
		function($value) {
			if (!F3::exists('message')) {
				if (empty($value))
					F3::set('message','Parola should not be blank');
				elseif (strlen($value)>127)
					F3::set('message','Parola is too long');
				elseif (strlen($value)<3)
					F3::set('message','Parola is too short');
			}
			// Do post-processing of userpassword here
			F3::set('REQUEST.userpassword',ucfirst($value));
		}
	);
}

?>
