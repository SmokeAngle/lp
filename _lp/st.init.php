<?php

define( 'ROOT' , dirname( __FILE__ ) . DS );
define( 'TROOT' , ROOT . 'simpletest' . DS  );

require_once( TROOT . 'autorun.php');
require_once( TROOT . 'web_tester.php' );
require_once (FRAEWORK_ROOT . DS . 'core' . DS . 'function' . DS . 'Core.function.php');
$test = new TestSuite('Test Center');

foreach( glob( WEB_ROOT . DS . 'test'. DS .'phptest' . DS . '*.test.php' ) as $f )
	$test->addFile( $f );
unset( $test );
