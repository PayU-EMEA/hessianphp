<?php
include_once 'base_test.php';

echo '<h4>HessianPHP 2 Unit tests (protocol v2)</h4>';

echo "Url ".baseURL().'php_test_server.php';

//ini_set('mbstring.internal_encoding','UTF-8');

class Hessian2Tests extends BaseHessianTests {
	var $version = 2;
}

