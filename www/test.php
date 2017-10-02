<?php

function test($arg)
{
	echo "Radim nešto....";
	var_dump($arg);
}

class Test {

    public function __call($name, $arguments)
	{
        if ( function_exists ( $name ) )
			$name($arguments);
		else
			return false;
    }
}

$obj = new Test();
$obj->test('vrijednost argumenta1', 'vrijednost argumenta2');

?>