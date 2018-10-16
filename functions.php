<?php 

use \Hcode\Model\User;

function post($key)
{
	return str_replace("'", "", $_POST[$key]);
}
function get($key)
{
	return str_replace("'", "", $_GET[$key]);
}


function formatPrice($vlprice)
{
	return number_format($vlprice, 2, ",", ".");
}

function checkLogin($inadmin = true)
{
	return User::checkLogin($inadmin);
}

function getUserName()
{
	$user = user::getFromSession();
	return $user->getdesperson();
}


 ?>