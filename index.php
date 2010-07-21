<?php

// Use the Fat-Free Framework
require_once 'lib/F3.php';

F3::set('RELEASE',FALSE);

// Use custom 404 page
F3::set('E404','layout.htm');

// Path to our Fat-Free import files
F3::set('IMPORTS','inc/');

// Path to our CAPTCHA font file
F3::set('FONTS','fonts/');

// Path to our templates
F3::set('GUI','gui/');

// Another way of assigning values to framework variables
F3::mset(
	array(
		'site'=>'Kullanıcı Veritabanı',
		'data'=>'db/demo.db'
	)
);

// Common inline Javascript
F3::set('extlink','window.open(this.href); return false;');

F3::set('DB',array('dsn'=>'sqlite:{@data}'));
if (!file_exists(F3::get('data')))
	// SQLite database doesn't exist; create it programmatically
	// Call db.php inside the inc/ folder
	F3::call(':db');

// Define our main menu; this appears in all our pages
F3::set('menu',
	array_merge(
		array(
			'Ana sayfa'=>'/'
		),
		// Toggle login/logout menu option
		F3::get('SESSION.user')?
			array(
				'Hakkında'=>'/about',
				'Çıkış'=>'/logout'
			):
			array(
				'Giriş'=>'/login'
			)
	)
);

/**
	Let's define our routes (HTTP method and URI) and route handlers;
	Unlike other frameworks, Fat-Free's code elegance makes it easy for
	novices and experts alike to understand what these lines do!
**/
F3::route('GET /',':showkul');

// Minify CSS; and cache page for 60 minutes
F3::route('GET /min',':minified',3600);

// Cache the "about" page for 60 minutes; read the full documentation to
// understand the possible unwanted side-effects of the cache at the
// client-side if your application is not designed properly
F3::route('GET /about',':about',3600);

// This is where we display the login page
F3::route('GET /login',':login',3600);
	// This route is called when user submits login credentials
	F3::route('POST /login',':auth');

// New blog entry
F3::route('GET /create',':createkul');
	// Submission of blog entry
	F3::route('POST /create',':savekul');

// Edit blog entry
F3::route('GET /edit/@tc',':editkul');
	// Update blog entry
	F3::route('POST /edit/@tc',':updatekul');

// Delete blog entry
F3::route('GET /delete/@tc',':erasekul');

// Logout
F3::route('GET /logout',':logout');

// RSS feed
F3::route('GET /rss',':rss');

// Generate CAPTCHA image
F3::route('GET /captcha',':captcha');

// Execute application
F3::run();

/**
	The function below could have been saved as an import file (render.php)
	loaded by the F3::route method like the other route handlers; but let's
	embed it here so you can see how you can mix and match MVC functions
	and import files.

	Although allowed by Fat-Free, functions like these are not recommended
	because they pollute the global namespace, specially when it's defined
	in the main controller. In addition, the separation of the controller
	component and the business logic becomes blurred when we do this - not
	good MVC practice.

	It's all right to define the function here if you're still figuring out
	the structural layout of your application, but don't trade off coding
	convenience for good programming habits.
**/
function render() {
	// layout.htm is located in the directory pointed to by the Fat-Free
	// GUI global variable
	echo F3::serve('layout.htm');
}

?>
