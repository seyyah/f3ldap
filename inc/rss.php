<?php

// Retrieve blog entries
$kul=new Axon('kul');
F3::set('entries',$kul->find());

/**
	We could have just as easily accomplished the above using the following:
		F3::sql('SELECT title,entry,time FROM blogs;');
		F3::set('entries',F3::get('DB.result'));
**/

echo F3::serve('rss.xml',FALSE);

?>
