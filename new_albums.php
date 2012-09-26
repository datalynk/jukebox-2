<?php

require_once("inc/config.inc.php");

if (isset($_POST["albums"]) && isset($_POST["songs"]))
  {
	$dbh = new PDO("mysql:host=" . MYSQL_HOST . ";dbname=" . MYSQL_DB, MYSQL_USER, MYSQL_PASS);

	$db_fields = array("artists" => array("artist"),
					   "albums"  => array("artist_id", "path", "name", "size", "duration", "year"),
					   "songs"   => array("album_id", "track_number", "title", "artist", "duration", "file_size", "file_name"));

	$p_artists = $dbh->prepare("INSERT INTO `artists`(`" . implode("`,`", $db_fields["artists"]) . "`) VALUES(:" . 
							   implode(",:", $db_fields["artists"]) . ")");

	$p_albums  = $dbh->prepare("INSERT INTO `albums`(`" . implode("`,`", $db_fields["albums"]) . "`) VALUES(:" . 
							   implode(",:", $db_fields["albums"]) . ")");

	$p_songs  = $dbh->prepare("INSERT INTO `songs`(`" . implode("`,`", $db_fields["songs"]) . "`) VALUES(:" . 
							   implode(",:", $db_fields["songs"]) . ")");

	$artist_keys = $album_keys = array();

	foreach ($_POST["artists"] as $a_key => $artist) // put artists in first
	  {
		// check artists against db to see if it has already been added!
		$p_artists->execute(array_to_pdo($artist));
		
		$artist_keys[$a_key] = $dbh->lastInsertId();
	  }
		
	foreach ($_POST["albums"] as $a_key => $album) // then albums
	  {
		$album["artist_id"] = $artist_keys[$a_key];

		$p_albums->execute(array_to_pdo($album));
		
		$db_a_key = $dbh->lastInsertId();

		foreach ($_POST["songs"][$a_key] as $t_key => $song)
		  {
			print_r($song);
			$song["album_id"] = $db_a_key;
			
			$p_songs->execute(array_to_pdo($song));
		  }
	  }


	$p_songs->debugDumpParams();

	print_r($p_songs->errorInfo());
  }

function array_to_pdo($array)
{
  $new_array = array();

  foreach ($array as $field => $value)
	$new_array[":".$field] = $value;

  return $new_array;
}
?>