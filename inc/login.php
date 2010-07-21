<?php

// Reset session variables in case someone else is logged in
F3::clear('SESSION.user');
F3::clear('SESSION.captcha');

// Render login.htm template
F3::set('pagetitle','Giriş');
F3::set('template','login');
F3::call('render');

/**

If you're used to object-oriented programming, you'll probably be thinking
this import file looks like procedural code. Well, if you examine the
framework code, this import file is wrapped inside the Runtime class. That's
the reason why this code fragment runs in a sandbox - without using PHP 5.3
namespaces.

You can declare a class inside Fat-Free import files like this one. PHP will
automatically place your class in the global scope once imported by the
framework.

**/

?>
