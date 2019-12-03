<?php

function protect_entries($array) {
	if (empty($array))
		return 0;
	$i = 0;
	$dupl = array();
	foreach ($array as $entry)
		$dupl[$i++] = htmlentities($entry);
	return $dupl;
}



?>