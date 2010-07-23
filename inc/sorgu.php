<?php
F3::call(':ldap_login');
$cn = "*";
$data = myldap_search("cn=$cn");

unset($data["count"]);
F3::set('entries', $data);

/*
print_r(F3::get('entries'));

Array ( 
	[0] => Array ( [cn] => creators [sn] => m [telephonenumber] => 12356 [postalcode] => 55050 [userpassword] => {MD5}ofDWJ4N+OTCFftj5hhitMA== ) 
	
	[1] => Array ( [cn] => moodle [sn] => User [telephonenumber] =>
[postalcode] => [userpassword] => {MD5}laBi5pYA91KuyVLgyoCiIA== ) 

	[2] => Array ( [cn] => test [sn] => test [telephonenumber] =>[postalcode] => [userpassword] => {MD5}ofDWJ4N+OTCFftj5hhitMA== ) 

	[3] => Array ( [cn] => ldap [sn] => test [telephonenumber] => [postalcode] => [userpassword]=> {MD5}ofDWJ4N+OTCFftj5hhitMA== ) 

	[4] => Array ( [cn] => harun [sn] => dama [telephonenumber] => 12345 [postalcode] => 21112 [userpassword] => ) 

	[5] => Array ( [cn] => mahruki [sn] => dama [telephonenumber] => 12345 [postalcode] =>21112 [userpassword] => {MD5}Xr4ilOzQ4PCOq3aQ0qbuaQ== ) 

	[6] => Array ( [cn] => murat [sn] => ggg [telephonenumber] => 1235 [postalcode] => 33321 [userpassword] => {SSHA}pb1w8iNvfR0aT7uhXvGn25cH0lIPbzwQ ) 

	[7] => Array ( [cn]=> mustafa [sn] => kuru [telephonenumber] => 1235 [postalcode] => 33321 [userpassword] => {SSHA}/6FnViYzjvUoP52AOhHoM9fV7x4OPsO2 ) )
*/

// Use the home.htm template
F3::set('pagetitle','sorgu sonuçları');
F3::set('template','sorgu');
F3::call('render');

?>
