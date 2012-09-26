<?php

require_once ("getid3/getid3.php");

class mp3_scan
{
  var $error = false;

  public function __construct($folder)
  {
    $this->folder = $folder;
    $this->id3 = new getID3;
  }

  public function get_new_albums()
  {
    $db_album_folders = array();
	$db_artists = array();
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->folder), RecursiveIteratorIterator::SELF_FIRST);
    $new_artist = $old_artists = array();

    while ($it->valid()) // loop thru subfolders
      {
		if ($it->hasChildren()) // ignores . and .. folder names
		  {
			$child_it = $it->getChildren();
			$folder = $it->getSubPathName();

			while ($child_it->valid()) // loop thru folder contents
			  {
				$file = $child_it->getSubPathName();

				if (strrchr($file, ".") == ".mp3") // if file has .mp3 extension
				  {
					if (!in_array($folder, $db_album_folders)) // and the folder hasn't already been put into the db
					  {
						if (!$mp3_info = $this->_get_id3($file) ) // get the id3 tag of a random mp3 file
						  break;

						$uni_artist = strtolower(preg_replace("/[^A-Za-z0-9]/", "", $mp3_info["artist"])); // remove non-alpha and convert to lowercase

						$new_album = array("folder" => $folder,
										   "artist" => $mp3_info["artist"],
										   "album" => $mp3_info["album"],
										   "year" => $mp3_info["year"]);

						if (!$artist_key = array_search($uni_artist, $db_artists)) // artist is not already present in database
						  {
							if (!isset($new_artists[$uni_artist]))
							  $new_artists[$uni_artist] = array();

							$new_artists[$uni_artist][] = $new_album;
						  }
						else // artist is present in database
						  {
							if (!isset($old_artists[$artist_key]))
							  $old_artists[$artist_key] = array();

							$old_artists[$artist_key][] = $new_album;
						  }

					  }

					break;
				  }
				$child_it->next();
			  }
		  }
	
		$it->next();
      }

    ksort($new_artists);

	return array("new" => $new_artists,
				 "old" => $old_artists);
  }

  private function _get_id3($mp3)
  {
    $tags = $this->id3->Analyze($this->folder.$mp3);

    getid3_lib::CopyTagsToComments($tags);

    $fields = array("artist", "album", "year");
    $track = array();

    if (!isset($tags["comments"]))
	  return false;

    foreach ($fields as $field)
      {
		if (isset($tags["comments"][$field][0]))
		  {
			if ($field == "year")
			  $track[$field] = is_numeric($tags["comments"][$field][0]) ? $tags["comments"][$field][0] : "0";
			else
			  $track[$field] = utf8_encode($tags["comments"][$field][0]);
		  }
		else
		  {
			if ($field == "year")
			  $track[$field] = "0";
			else
			  $track[$field] = "?";
		  }
	  }

	if ($track["artist"] != "")
	  return $track;
	else
	  return false;
  }
}

function sort_by_artist($a, $b)
{
  return strcasecmp($a["artist"], $b["artist"]);
}
?>