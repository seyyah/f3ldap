<?php

// Retrieve blog entries
$kul=new Axon('kul');
F3::set('entries',$kul->find());

/*
entries degiskeni sozluk yapisinda sanirim. Emin olmak icin
print_r(F3::get('entries'));

Array ( [0] => Array ( [tc] => 123 [ad] => Nuret [soyad] => Seny ) 
	[1] => Array ( [tc] => 666 [ad] => Yusuf [soyad] => Sen  ) )

LDAP ozellinde,

entries
"cn"=>"foo", 
"sn"=>"bar",...

bicimine donusturmelisin.
*/

// Use the home.htm template
F3::set('pagetitle','ana sayfa');
F3::set('template','home');
F3::call('render');

?>
