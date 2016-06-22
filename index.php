<?php

error_reporting(E_ALL);
ini_set('display_errors',1);
ini_set('memory_limit', '-1');

include 'SpellCorrector.php';
//include 'PorterStemmer.php';
//include 'firephp-core-master';
//require_once('/lib/FirePHPCore/fb.php');
//ob_start();


// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');
$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$mquery = "";
$title_value = null;
$author_value = null;
$date_value = null;
$size_value = null;
$results = false;
$val = null;
$userspellflag = 0;

if ($query)
{
// The Apache Solr Client library should be on the include path
// which is usually most easily accomplished by placing in the
// same directory as this script ( . or current directory is a default
// php include path entry in the php.ini)
//require_once('Apache/Solr/Service.php');
require_once('/var/www/html/solr-php-client/Apache/Solr/Service.php');
// create a new solr service instance - host, port, and corename
// path (all defaults in this example)
$solr = new Apache_Solr_Service('localhost', 8983, '/solr/myexample2/');
// if magic quotes is enabled then stripslashes will be needed
if (get_magic_quotes_gpc() == 1)
{
$query = stripslashes($query);
}
// in production code you'll always want to use a try /catch for any
// possible exceptions emitted by searching (i.e. connection
// problems or a query parsing error)


$additionalParameters = array(
	'sort'=> 'pageRankFile desc'
	
);

if(isset($_GET['ranking']) && $_GET['ranking'] == "pageRankFile desc")
{
try
{


$mquery = SpellCorrector::multiWords($query);

$results = $solr->search($mquery, 0, $limit, $additionalParameters);
}
catch (Exception $e)
{
// in production you'd probably log or email this error to an admin
// and then show a special message to the user but for this example
// we're going to show the full exception
die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
}

}
else
{
try
{
$mquery = SpellCorrector::multiWords($query);

$results = $solr->search($mquery, 0, $limit);
}
catch (Exception $e)
{
// in production you'd probably log or email this error to an admin
// and then show a special message to the user but for this example
// we're going to show the full exception
die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
}
}

}
?>
<html>
<head>
<title>PHP Solr Client Example</title>
<meta charset="utf-8">
      <link href="http://code.jquery.com/ui/1.10.4/themes/ui-lightness/jquery-ui.css" rel="stylesheet">
      <script src="http://code.jquery.com/jquery-1.10.2.js"></script>
      <script src="http://code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
<script src="porter_stemming.js"></script>



<script type="text/javascript">

        var stopWordsFile = null;
	
	    var fileContent = new XMLHttpRequest();
	    fileContent.open("GET", "/stopWords.txt", false);
	    fileContent.onreadystatechange = function ()
	    {
		if(fileContent.readyState === 4)
		{
		    if(fileContent.status === 200 || fileContent.status == 0)
		    {
		        stopWordsFile = fileContent.responseText;
		        
		    }
		}
	    }
	    fileContent.send(null);
	var stopWords = stopWordsFile.split("\n");

	


 var suggestions_list = [];
function auto_Suggest(fullQuery)
{

	var queryTerms = fullQuery.split(" ");
        var last_value = queryTerms[queryTerms.length - 1];

       while(suggestions_list.length > 0) {
   	 suggestions_list.pop();
	}
	
    $.ajax({
        url:        'autoSuggest.php',
        type:       'GET',
        dataType:   'json',
        data:       { value: last_value },
        success:    function(value_suggest) {

	var query_sugg_array = [];
	var sugg_index=0;
	var flag1;
	var flag_stemmer;
	var root_form = null;
	for(var i=0;i<value_suggest.length;i++)
	{

		if(value_suggest[i].indexOf(".") == -1 && value_suggest[i].indexOf("_") == -1)
		{
		root_form = null;
 		flag1 = 0;
		var low_term = value_suggest[i].toLowerCase();
		if(query_sugg_array.length<5 )
		{
			for (var j = 0; j<stopWords.length; j++)
			{	if(stopWords[j].toLowerCase() === low_term)
				{
					flag1 = 1;
					break;

				}	
			}	
		}

		if(flag1==0)
		{	
			var flag_stemmer;
			var root_form = stemmer(low_term);
			
			if(query_sugg_array.length>0)
			{flag_stemmer = 0;
				for(var j=0;j<query_sugg_array.length;j++)
				{
					if(root_form === query_sugg_array[j])
						{
						flag_stemmer =1;
						break;
						}
				}
				if(flag_stemmer ==0)
				{
					
						query_sugg_array[sugg_index] = root_form;
	
						sugg_index++;
					

				}
			}
			if(query_sugg_array.length == 0)
			{	
					query_sugg_array[sugg_index] = root_form;

					sugg_index++;
				
				
			}
			if(query_sugg_array.length >=5)
				break;
			
	
		}

		}

		else
			continue;
	}

	var concat_query = "";
	var rest_query = "";
	if(queryTerms.length>1)
	{
		for (var i=0; i<queryTerms.length - 1; i++)
		{
			rest_query = rest_query.concat(queryTerms[i]+" ");
		}
	}

	for(var i=0;i<query_sugg_array.length ;i++)
	{
	concat_query = "";
	concat_query = rest_query.concat(query_sugg_array[i]);
	suggestions_list.push(concat_query);
	
	}
 	
	jQuery.ready();
        },
        error: function(e) {
           console.log(e.message);
        }
    });

}


         $(function() {
            $( "#q" ).autocomplete({
               source: suggestions_list
            });
         });
      </script>

<script type="text/javascript">
	function div_func(){
		var query = "<?php Print($query); ?>".trim();
		var mquery = "<?php Print($mquery); ?>".trim();
		var element = document.getElementById('spell_error');
		if(query === mquery || query.length==0 || mquery.length==0)
		{
	         element.style.visibility = 'hidden';      // Hide
		}
		else 
		element.style.visibility = 'visible';     // Show

		}
</script>
</head>

<body onload="div_func();">
<div class="ui-widget">
<form accept-charset="utf-8" method="get">
<label for="q">Search:</label>
<input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>" onkeyup="auto_Suggest(this.value)"/><br>

<datalist id="json-datalist">


</datalist>

<input type="radio" name="ranking"  value="solr" <?php echo (isset($_GET['ranking']))? (($_GET['ranking'] == "solr")? "checked":"" ): "checked"; ?>  />  Solr Algorithm
<input type="radio" name="ranking"  value="pageRankFile desc" <?php echo (isset($_GET['ranking']) && $_GET['ranking'] == "pageRankFile desc") ? "checked" : "";?>  />  Networkx PageRank Algorithm
<br><br>
<input type="submit"/>
</form>
</div>

<div id="spell_error" style="visibility: hidden">
<?php
// display results
?>
<h2 style="color:blue;"><i>
<?php
    echo "Displaying results for '".$mquery."'";
?>
<br>

</i> 

</h2>
</div>
<?php
if ($results)
{
$total = (int) $results->response->numFound;

$start = min(1, $total);
$end = min($limit, $total);
?>
<div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
<ol>


<?php
// iterate result documents
foreach ($results->response->docs as $doc)
{

?>
<li>
<table style="border: 1px solid black; text-align: left" width=100%>


<?php
// iterate document fields / values
foreach ($doc as $field => $value)
{


if($field == 'id')
{
$p = stripos($value, "http");
$fileName = substr($value, $p);
$url = str_replace("$", "/", $fileName);
$url = str_replace(";", ":", $url);
$url = str_replace("#", "?", $url);
if (strrpos($url, '.html') !== false)
$url = substr_replace($url,"",-5);
else if (strrpos($url, '.pdf') !== false) 
$url = substr_replace($url,"",-4);
else if (strpos($url, '.doc') !== false) 
$url = substr_replace($url,"",-4);
else if (strrpos($url, '.docx') !== false) 
$url = substr_replace($url,"",-5);

?>
<tr>
<th><a href = "<?php echo htmlspecialchars($url, ENT_NOQUOTES, 'utf-8'); ?>">Document</a></th>
<td width=100%><?php echo htmlspecialchars($url, ENT_NOQUOTES, 'utf-8'); ?></td>
</tr>

<?php
}
if($value == "" || $value == null)
 $value = "N/A";

if($field=='title')
$title_value=$value;
else if($field=='author')
$author_value=$value;
else if($field=='date')
$date_value=$value;
else if($field=='stream_size')
{
$value = (string)$value/1024;
$size_value=$value." KB";
}

}
if($title_value=='' || $title_value==null)
{
$title_value="N/A";
}
if($author_value=='' || $author_value==null)
{
$author_value="N/A";
}
if($size_value=='' || $size_value==null)
{
$size_value="N/A";
}
if($date_value=='' || $date_value==null)
{
$date_value="N/A";
}
?>

<tr>
<th><?php echo "Title"; ?></th>
<td width=100% ><?php echo htmlspecialchars($title_value, ENT_NOQUOTES, 'utf-8'); ?></td>
</tr>
<tr>
<th><?php echo "Author"; ?></th>
<td width=100%><?php echo htmlspecialchars($author_value, ENT_NOQUOTES, 'utf-8'); ?></td>
</tr>
<tr>
<th><?php echo "Date"; ?></th>
<td width=100%><?php echo htmlspecialchars($date_value, ENT_NOQUOTES, 'utf-8'); ?></td>
</tr>
<tr>
<th><?php echo "Size"; ?></th>
<td width=100%><?php echo htmlspecialchars($size_value, ENT_NOQUOTES, 'utf-8'); ?></td>
</tr>

<?php
$title_value = null;
$author_value = null;
$date_value = null;
$size_value = null;

?>
</table>
</li>
<?php
}
?>
</ol>
<?php
}
?>
</body>
</html>