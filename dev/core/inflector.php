<?php

/*Copyright (c) CakePHP*/
class Inflector {

	function underscore($camelCasedWord) {
		return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $camelCasedWord));
	}

}


?>
