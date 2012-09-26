<?php

require_once("inc/config.inc.php");
require_once("inc/juke_db.inc.php");

if (isset($_POST["albums"]) && isset($_POST["songs"]))
  {
	$pdo = new jPDO;

	$db_fields = array("artists" => array("artist"),
					   "albums"  => array("artist_id", "path", "name", "size", "duration", "year"),
					   "songs"   => array("album_id", "track_number", "title", "artist", "duration", "file_size", "file_name"));

	$p_artists = $pdo->prep_assoc("artists", $db_fields["artists"]);
	$p_albums = $pdo->prep_assoc("albums", $db_fields["albums"]);
	$p_songs = $pdo->prep_assoc("songs", $db_fields["songs"]);

	$db_album_paths = $pdo->get_album_paths();
	$db_alnum_artists = $pdo->get_alnum_artists();
	$album_keys = array();

	$pdo->beginTransaction();
	
	//_ loop through artists, adding them to db if necessary. if not, getting the db artist id's _//
	foreach ($_POST["artists"] as $a_key => $artist)
	  {
		$alnum_artist = alnum($artist["artist"]); // remove non-alpha chars and compare to db non-alpha
		$db_key = array_search($alnum_artist, $db_alnum_artists);

		//_ if artist hasn't been added, add it to db and append db key to artists array _//
		if ($db_key === FALSE)
		  {
			$p_artists->execute($artist);
			$db_alnum_artists[$pdo->lastInsertId()] = $alnum_artist;
		  }

		$_POST["albums"][$a_key]["artist_id"] = ($db_key === FALSE) ? $pdo->lastInsertId() : $db_key;
	  }

	//_ now loop through all albums and add them to db and their tracks
	foreach ($_POST["albums"] as $a_key => $album)
	  {
		if (in_array($album["path"], $db_album_paths)) //- ignore preexisting albums
		  break;

		$p_albums->execute($album); //- add album record to db

		$album_id = $pdo->lastInsertId();

		//_ loop over all the songs and add to db 
		foreach ($_POST["songs"][$a_key] as $t_key => $song)
		  {
			$song["album_id"] = $album_id;

			$p_songs->execute($song);
		  }
	  }

	print_r($db_alnum_artists);

	if (!$pdo->error)
	  $pdo->commit();

	//$p_songs->debugDumpParams();

	//print_r($p_songs->errorInfo());
  }

function array_to_pdo($array)
{
  $new_array = array();

  foreach ($array as $field => $value)
	$new_array[":".$field] = $value;

  return $new_array;
}
?>