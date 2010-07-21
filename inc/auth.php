<?php

// Reset previous error message, if any
F3::clear('message');

// Form field validation
F3::call('checkID|password|captcha');

if (!F3::exists('message')) {
	// No input error; check values
	if (preg_match('/^admin$/i',F3::get('REQUEST.userID')) &&
		preg_match('/^admin$/i',F3::get('REQUEST.password'))) {
		// User ID is admin, password is admin - set session variable
		F3::set('SESSION.user','{@REQUEST.userID}');
		// Return to home page; but now user is logged in
		F3::reroute('/');
	}
	else
		F3::set('message','Invalid user ID or password');
}
// Display the login page again
F3::call(':login');

/**
	Functions below are used by this sandboxed import file for validation
	of form fields in the login page
**/

function checkID() {
	// Validate user ID
	F3::input('userID',
		function($value) {
			if (!F3::exists('message')) {
				if (empty($value))
					F3::set('message','User ID should not be blank');
				elseif (strlen($value)>24)
					F3::set('message','User ID is too long');
				elseif (strlen($value)<3)
					F3::set('message','User ID is too short');
			}
			/**
				Convert form field to lowercase; Notice we use F3::set
				to assign a value to the sanitized version of
				$_REQUEST[userID]; this allows us to use the variable in
				our template
			**/
			F3::set('REQUEST.userID',strtolower($value));
		}
	);
}

function password() {
	// Validate password
	F3::input('password',
		function($value) {
			if (!F3::exists('message')) {
				if (empty($value))
					F3::set('message','Password must be specified');
				elseif (strlen($value)>24)
					F3::set('message','Invalid password');
			}
		}
	);
}

function captcha() {
	// Validate CAPTCHA verification code; if any
	F3::input('captcha',
		function($value) {
			if (!F3::exists('message') && F3::exists('SESSION.captcha')) {
				$captcha=F3::get('SESSION.captcha');
				if (empty($value))
					F3::set('message','Verification code required');
				elseif (strlen($value)>strlen($captcha))
					F3::set('message','Verification code is too long');
				elseif (strtolower($value)!=$captcha)
					F3::set('message','Invalid verification code');
			}
		}
	);
}

?>
