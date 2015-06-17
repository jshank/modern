<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);
require_once '../includes/config.php';
$query = "select Name from Monitors order by Name";
$result = mysql_query($query) or die('Error, selecting monitors failed.');
while ($row = mysql_fetch_array($result)){
	echo $row['Name'] . ',';
}
?>
