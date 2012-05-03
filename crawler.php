<?php
include("mysql.ssi.php");
#Naming conventions: http://support.eve-online.com/Pages/KB/Article.aspx?id=37
#Sample page format: http://www.eve-kill.net/?a=kill_detail&kll_id=1000000

$first_item = file_get_contents("cache.txt");
$last_item = 3300000;

for ($i = $first_item; $i <= $last_item; $i++) {
	eve_crawl_url("http://www.eve-kill.net/?a=kill_detail&kll_id=$i", $i);
}


function eve_crawl_url($url, $iteration) {
	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL,$url);
	curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"); #pretend we're IE
	curl_setopt ($ch, CURLOPT_TIMEOUT, 20);
	curl_setopt ($ch, CURLOPT_FOLLOWLOCATION,1);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	$page = curl_exec ($ch);
	
	if (strpos($page, "That kill doesn't exist.")) {
		echo "ERROR: Kill Doesn't Exist.<br />\n";
		write_cache($iteration);
		return false;
	}
	
	$location_format = "#system_detail&amp;sys_id=([0-9]+)\">([a-zA-Z0-9- ']+)</a></b>#";
	preg_match($location_format, $page, $matches);
	$location = $matches[2];
	if (empty($location)) {
		echo "ERROR: #$iteration, LOCATION.<br />\n";
		write_cache($iteration);
		return false;
	}
	$location_id = item_to_id($location, "wt_systems");
	
	$date_format = "#<td class=kb-table-cell>([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})</td>#";
	preg_match($date_format, $page, $matches);
	$date = $matches[1];
	if (empty($date)) {
		echo "ERROR: #$iteration, DATE.<br />\n";
		write_cache($iteration);
		return false;
	}
	
	
	$loss_isk_format = "#<td class=kb-table-cell>([0-9,]+\.[0-9]{2})</td>#";
	preg_match($loss_isk_format, $page, $matches);
	$loss_isk = round(ereg_replace("[^0-9.]", "", $matches[1]));
	if (empty($loss_isk)) {
		echo "ERROR: #$iteration, LOSS ISK. Continuing...<br />\n";
		write_cache($iteration);
		$loss_isk = 0;
	}
	
	$victim_format = '#<td class=kb-table-cell><b><a href="\?a=pilot_detail&plt_id=([0-9]+)">([a-zA-Z0-9- \']+)</a></b></td>#';
	preg_match($victim_format, $page, $matches);
	$victim_name = $matches[2];
	if (empty($victim_name)) {
		echo "ERROR: #$iteration, VICTIM NAME.<br />\n";
		write_cache($iteration);
		return false;
	}
	$victim_id = item_to_id($victim_name, "wt_player");
	
	$victim_corp_format = '#<td class=kb-table-cell><b><a href="\?a=corp_detail&crp_id=([0-9]+)">([a-zA-Z0-9 \'\-.]+)</a></b></td>#';
	preg_match($victim_corp_format, $page, $matches);
	$victim_corp_name = $matches[2];
	if (empty($victim_corp_name)) {
		echo "ERROR: #$iteration, VICTIM CORP NAME.<br />\n";
		write_cache($iteration);
		return false;
	}
	$victim_corp_id = item_to_id($victim_corp_name, "wt_corporation");
	
	$victim_alliance_format = '#<b><a href="\?a=alliance_detail&all_id=[0-9]+">([a-zA-Z0-9 \'\-.]+)[</a>]*</b></td>#';
	preg_match($victim_alliance_format, $page, $matches);
	$victim_alliance_name = $matches[1];
	if (empty($victim_alliance_name)) {
		echo "ERROR: #$iteration, VICTIM ALLIANCE NAME.<br />\n";
		write_cache($iteration);
		return false;
	}
	$victim_alliance_id = item_to_id($victim_alliance_name, "wt_alliance");
	
	$victim_ship_format = '#<td class=kb-table-cell><b><a href="\?a=invtype&id=([0-9]+)">([a-zA-Z0-9 \'\-.]+)</a></b></td>#';
	preg_match($victim_ship_format, $page, $matches);
	$victim_ship_name = $matches[2];
	if (empty($victim_ship_name)) {
		echo "ERROR: #$iteration, VICTIM SHIP NAME.<br />\n";
		write_cache($iteration);
		return false;
	}
	$victim_ship_id = item_to_id($victim_ship_name, "wt_ships");
	
	$killer_name_format = '#<a href="\?a=pilot_detail&plt_id=[0-9]+"><b>([0-9a-zA-Z \']+)[ \(Final Blow\)]*</b></a></td>#';
	preg_match_all($killer_name_format, $page, $matches);
	#print_r($matches[1]);
	$killer_names = $matches[1];
	
	$killer_corp_format = '#<a href="\?a=corp_detail&crp_id=[0-9]+">([0-9a-zA-Z -.\']+)</a></td>#';
	preg_match_all($killer_corp_format, $page, $matches);
	#print_r($matches[1]);
	$killer_corps = $matches[1];
	
	$killer_ship_format = '#1px;"><b><a href="\?a=invtype&id=[0-9]+">([0-9a-zA-Z ]+)</a></b></td>#';
	preg_match_all($killer_ship_format, $page, $matches);
	#print_r($matches[1]);
	$killer_ships = $matches[1];
	
	$killer_alliance_format = '#style="padding-top: 1px; padding-bottom: 1px;"><a href="\?a=alliance_detail&all_id=[0-9]+">([0-9a-zA-Z-. \']+)</a></td>#';
	preg_match_all($killer_alliance_format, $page, $matches);
	#print_r($matches[1]);
	$killer_alliances = $matches[1];
	
	$size_killer_names = sizeof($killer_names);
	$size_killer_corps = sizeof($killer_corps);
	$size_killer_ships = sizeof($killer_ships);
	$size_killer_alliances = sizeof($killer_alliances);
	
	if ($size_killer_names != $size_killer_corps || $size_killer_ships != $size_killer_alliances || $size_killer_names != $size_killer_ships) {
		echo "ERROR: #$iteration, KILLER ARRAY MISMATCH [n:$size_killer_names, c:$size_killer_corps, s:$size_killer_ships, a:$size_killer_alliances].<br />\n";
		write_cache($iteration);
		return false;
	}
	
	$victim_fit_format = '#                    <td class="kb-table-cell">([0-9a-zA-Z -\'/.]+)</td>#';
	preg_match_all($victim_fit_format, $page, $matches);
	#print_r($matches[1]);
	$victim_fits = $matches[1];
	
	runQuery("INSERT INTO wt_player_sighting SET player_id = '$victim_id', corp_id = '$victim_corp_id', alliance_id = '$victim_alliance_id', ship_id = '$victim_ship_id', time='$date', system_id = '$location_id'");
	$player_sighting_id = mysql_insert_id();
	runQuery("INSERT INTO wt_ship_loss SET cost = '$loss_isk', player_sighting_id = '$player_sighting_id'");
	$ship_loss_id = mysql_insert_id();
	
	for ($i = 0; $i < sizeof($killer_names); $i++) {
		$killer_player_id = item_to_id($killer_names[$i], "wt_player");
		$killer_corp_id = item_to_id($killer_corps[$i], "wt_corporation");
		$killer_ship_id = item_to_id($killer_ships[$i], "wt_ships");
		$killer_alliance_id = item_to_id($killer_alliances[$i], "wt_alliance");
		runQuery("INSERT INTO wt_player_sighting SET player_id = '$killer_player_id', corp_id = '$killer_corp_id', alliance_id = '$killer_alliance_id', ship_id = '$killer_ship_id', time='$date', system_id = '$location_id'");
		$killer_sighting_id = mysql_insert_id();
		runQuery("INSERT INTO wt_ship_kill SET player_instance_id = '$killer_sighting_id', ship_loss_id = '$ship_loss_id'");
	}
	
	for($i = 0; $i < sizeof($victim_fits); $i++) {
		$item_id = item_to_id($victim_fits[$i], "wt_items");
		runQuery("INSERT INTO wt_item_to_ship_loss SET ship_loss_id = '$ship_loss_id', item_id = '$item_id'");
	}
	write_cache($iteration);
	
	set_time_limit(10);

	return true;
}

function item_to_id($value, $table, $column = 'name') {
	# We are given a string to add, and the table to add it to.
	# If the value already exists, we return it's ID.
	# Otherwise, we make it and return its ID.
	$value = addslashes($value);
	$sql = "SELECT id FROM $table WHERE $column = '$value' LIMIT 1";
	$result = runQuery($sql);
	if (mysql_num_rows($result)) {
		$row = mysql_fetch_assoc($result);
		return $row['id'];
	} else {
		$sql = "INSERT INTO $table SET $column = '$value'";
		runQuery($sql);
		return mysql_insert_id();
	}
}

function write_cache($iteration) {
	$fp = fopen("cache.txt", 'w');
	fwrite($fp, $iteration);
	fclose($fp);
}

