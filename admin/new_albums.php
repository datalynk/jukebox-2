<?php

require_once("../inc/config.inc.php");
require_once("../inc/jpdo.inc.php");

if (isset($_POST["albums"]) && isset($_POST["songs"]))
  {
	$pdo = new jPDO;

	//_ prepare the sql statements to add artists, albums, and songs _//
	$p_artists = $pdo->prep_array("artists", array_keys($_POST["artists"][0]));
	$p_albums = $pdo->prep_array("albums", array_keys($_POST["albums"][0]));
	$p_songs = $pdo->prep_array("songs", array_keys($_POST["songs"][0][0]));

	$db_album_paths = $pdo->get_album_paths();
	$db_alnum_artists = $pdo->get_alnum_artists();

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

		//_ loop over all the songs and add to db _//
		foreach ($_POST["songs"][$a_key] as $t_key => $song)
		  {
			$song["album_id"] = $album_id;

			$p_songs->execute($song);
		  }
	  }

	print_r($db_alnum_artists);

	if ($pdo->error)
	  $pdo->rollBack();
	else
	  $pdo->commit();
  }

function array_to_pdo($array)
{
  $new_array = array();

  foreach ($array as $field => $value)
	$new_array[":".$field] = $value;

  return $new_array;
}
?>
