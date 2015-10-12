<?php

require_once('geoplugin.class.php');

$geoplugin = new geoPlugin();

//locate the IP
$geoplugin->locate();

$country_code = $geoplugin->countryCode;

echo $country_code;

if($country_code == "NO") {
	header("Location: http://www.wuergh.com/oslo/experimental/norsk.html");
} else {
	header("Location: http://www.wuergh.com/oslo/experimental/english.html");
}

?>
