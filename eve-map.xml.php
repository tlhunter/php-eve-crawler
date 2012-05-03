<?php
define("WIDTH", 800);
define("HEIGHT", 600);
define("MARGIN", 20);
$region = 'Curse';

include("mysql.ssi.php");

$sql = "SELECT id, name, x/1e17 AS x, z/1e17 AS y
FROM wt_systems_map
WHERE region_id = (
SELECT id
FROM `wt_regions`
WHERE name = '$region')";

$result = runQuery($sql);
$mm['max_x'] = -10;
$mm['max_y'] = -10;
$mm['min_x'] = 10;
$mm['min_y'] = 10;
$rows = array();

while ($row = mysql_fetch_assoc($result)) {
	if ($row['x'] > $mm['max_x'])
		$mm['max_x'] = $row['x'];
	if ($row['y'] > $mm['max_y'])
		$mm['max_y'] = $row['y'];
	if ($row['x'] < $mm['min_x'])
		$mm['min_x'] = $row['x'];
	if ($row['y'] < $mm['min_y'])
		$mm['min_y'] = $row['y'];
	$rows[] = $row;
}

$effective_width = WIDTH - MARGIN * 2;
$effective_height = HEIGHT - MARGIN * 2;

echo "<systems count='" . count($rows) . "'>\n";
foreach($rows AS $system) {
	$system['x'] = ($system['x'] + -$mm['min_x']) / (-$mm['min_x'] + $mm['max_x']);
	$system['x'] = round($system['x'] * $effective_width + MARGIN); # may want to unload this to Flash eventually
	$system['y'] = ($system['y'] + -$mm['min_y']) / (-$mm['min_y'] + $mm['max_y']);
	$system['y'] = round($system['y'] * $effective_height + MARGIN); # may want to unload this to Flash eventually
	echo "\t<system systemId='{$system['id']}' systemName='{$system['name']}' xPixel='{$system['x']}' yPixel='{$system['y']}' />\n";
}
echo "</systems>\n";

$sql = "SELECT from_system_id AS sFrom, to_system_id AS sTo FROM wt_systems_jumps WHERE from_region_id = (
SELECT id
FROM `wt_regions`
WHERE name = '$region' )";
$result = runQuery($sql);
echo "<connections count='" . mysql_num_rows($result) . "'>\n";
while ($row = mysql_fetch_assoc($result)) {
	echo "\t<connection fromSystemId='{$row['sFrom']}' toSystemId='{$row['sTo']}' />\n";
}
echo "</connections>\n";

#print_r($rows);