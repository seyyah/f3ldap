<?php

// Retrieve matching record
F3::call(':ldap_login');
$data = myldap_search('cn=' . F3::get('PARAMS.cn'));

if ($data['count'] > 0) {
	$data = data4f3($data);
	
	// Populate REQUEST global with retrieved values
	F3::set('REQUEST', array_merge(
		(array) F3::get('REQUEST'),
		$data ));	
	// Render blog.htm template
	F3::set('pagetitle','Kullanıcıyı güncelle');
	F3::set('template','kul');
	F3::call('render');
}
else
	// Invalid blog entry; display our 404 page
	F3::http404();
?>
