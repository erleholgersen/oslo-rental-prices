<?php 

$configdata = parse_ini_file("../../../config.ini");
    
function quartile($n, $victim) {
	// sort($victim);
	$s = 1;
	$tempk = ($n/4.0)*(count($victim)-1) + 1;
	$k = floor($tempk);
	$f = $tempk - $k;
	$output = $victim[$k-1]+($f*($victim[$k] - $victim[$k-1]));
	return $output;
}

ini_set('default_charset', 'utf-8');
header("Content-Type: text/html;charset=UTF-8");
// ini_set('display_errors', 'On');
// error_reporting(-1);

mysql_set_charset('utf8');

$link = mysql_connect('localhost', $configdata['username'], $configdata['password']) or die('Could not connect: ' . mysql_error());


mysql_select_db('oslo') or die('Could not select database');

$places = array(
	"su" => utf8_decode("'St.Hanshaugen - Ullevål'"),
	"bf" => utf8_decode("'Bygdøy - Frogner'"),
	"um" => utf8_decode("'Uranienborg - Majorstuen'"),
	"gs" => utf8_decode("'Grünerløkka - Sofienberg'"),
	"go" => utf8_decode("'Gamle Oslo'"),
	"st" => utf8_decode("'Sagene - Torshov'"),
	"vinderen" => utf8_decode("'Vinderen'"),
	"eb" => utf8_decode("'Ekeberg - Bekkelaget'"),
	"ullern" => utf8_decode("'Ullern'"),
	"bjerke" => utf8_decode("'Bjerke'"),
	"gk" => utf8_decode("'Grefsen - Kjelsås'"),
	"roa" => utf8_decode("'Røa'"),
	"sn" => utf8_decode("'Søndre Nordstrand'"),
	"hs" => utf8_decode("'Helsfyr - Sinsen'"),
	"sogn" => utf8_decode("'Sogn'"),
	"nordstand" => utf8_decode("'Nordstrand'"),
	"sentrum" => utf8_decode("'Sentrum'"),
	"grorud" => utf8_decode("'Grorud'"),
	"hellerud" => utf8_decode("'Hellerud'"),
	"furuset" => utf8_decode("'Furuset'"),
	"stovner" => utf8_decode("'Stovner'"),
	"romsas" => utf8_decode("'Romsås'"),
	"lambertseter" => utf8_decode("'Lambertseter'"),
	"boler" => utf8_decode("'Bøler'"),
	"manglerud" => utf8_decode("'Manglerud'"),
	"ostensjo" => utf8_decode("' '")
);

$futurejson = array();
$data = array();

foreach($places as $p) {
	$a = utf8_encode(str_replace("'", "", $p));
	$futurejson[$a] = array();
}


// Get dates

$query = "SELECT DISTINCT date FROM rawdata WHERE DATEDIFF(curdate(), date) <= 365;";

$result = mysql_query($query) or die('Query failed: ' . mysql_error());

while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$data[ $line['date'] ] = array();
}

mysql_free_result($result);


// Get data


foreach($_GET as $key => $value) {
	if(strpos($key, "graf_") === 0) {
				
		$query = "SELECT date, AVG(price) AS avg_price, AVG(sqm) AS avg_sqm, COUNT(*) AS count FROM rawdata WHERE DATEDIFF(curdate(), date) <= 365 AND (probable=1)"; 

		if( isset( $_GET["type"] ) ) {
			if($_GET["type"] == "h") {
				$query = $query . " AND category_guess='hybel'";
			}

			if($_GET["type"] == "r") {
				$query = $query .  " AND category_guess='rom'";
			}
		}
		
		
		$areas = explode("-", $value);
		
		$areaquery = "";
			
		foreach ($areas as $a) {
			if (array_key_exists($a, $places)) {
				if ($areaquery == "") {
					$areaquery = " AND (area=".$places[$a];
				}
				else {
					$areaquery = $areaquery . " OR area=".$places[$a];
				}
			}
		}
		
		
		$areaquery = $areaquery . ")";
	
		$query = $query . $areaquery . " GROUP BY date;";	
				
		$result = mysql_query($query) or die('Query failed: ' . mysql_error());

		while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$subjson = array();
			$subjson['avg_price'] = floatval( $line['avg_price'] );
			$subjson['count'] = $line['count'];
			$data[ $line['date'] ][ $key ] = $subjson;
		}
		
		
	}
}


$new_data = array();

foreach($data as $key => $value) {
	$value['date'] = $key;
	array_push($new_data, $value);
}


mysql_free_result($result);

mysql_close($link);

echo json_encode($new_data);
 
?>