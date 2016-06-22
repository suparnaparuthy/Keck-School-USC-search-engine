<?php
ini_set('display_errors', 1);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

header('Content-Type: text/html; charset=utf-8');
$limit = 20;
$query = null;
$solr=null;

require_once('/var/www/html/solr-php-client/Apache/Solr/Service.php');


$solr = new Apache_Solr_Service('localhost', 8983, '/solr/myexample2/');

if(isset($_GET['last_value']))
	$sugg_query = $_GET['value'];



if (get_magic_quotes_gpc() == 1)
{
$sugg_query = stripslashes($sugg_query);
}

try
{

$results = $solr->suggest($sugg_query, 0, $sugg_limit);
}
catch (Exception $e)
{
// in production you'd probably log or email this error to an admin
// and then show a special message to the user but for this example
// we're going to show the full exception
die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
}

$i = 0;

$suggestions = array();

foreach ($results->suggest->suggest->$sugg_query->suggestions as $doc)
{
	foreach ($doc as $field => $value)
	{


	if($field == 'term')
	{
		$suggestions[$i] = $value;
	}
            
	}

	$i++;
}


echo json_encode($suggestions);
?>