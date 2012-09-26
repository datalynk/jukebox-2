<?php

require_once ("getid3/getid3.php");

class mp3_scan
{
  public function __construct($db)
  {
	$this->max_albums = 20;

    $this->id3 = new getID3;
	$this->db = $db;
  }

  public function get_new_albums($folder)
  { // add UnexpectedValueException handler for RDI
	
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder), RecursiveIteratorIterator::SELF_FIRST);
	$db_albums = $this->db->get_album_paths();
    $albums = array();
	$num_albums = 0;

    while ($it->valid() & $num_albums < $this->max_albums) // loop thru subfolders
      {
		if ($it->hasChildren())
		  {
			$child_it = new RegexIterator($it->getChildren(), "/\.mp3$/", RegexIterator::MATCH); // get all .mp3 files in folder

			if (iterator_count($child_it) > 0 && !in_array($it->getSubPathname(), $db_albums)) // if there are mp3s in folder and folder hsn't been added already
			  {
				$album = array("path" => $it->getSubPathname(),
							   "duration" => 0,
							   "size" => 0,
							   "num_tracks" => 0,
							   "tracks" => array());

				foreach ($child_it as $file)
				  {
					if ( !$mp3_info = $this->_get_full_id3($file->getPathname()) ) // skip mp3 file if it has bad info
					  break;

					$album["size"] += $mp3_info["file_size"]; // add onto album size
					$album["duration"] += $mp3_info["duration"]; // add onto album length in seconds
					$album["num_tracks"]++; // add to number of valid tracks
					
					$album["tracks"][] = $mp3_info; // add track to tracks array
				  }

				usort($album["tracks"], "sort_by_tracknum");

				$albums[] = $album;
				$num_albums++;
			  }
		  }

		$it->next();
	  }

	return $albums;
  }

  private function _get_full_id3($path)
  {
	$tags = $this->id3->Analyze($path);

    getid3_lib::CopyTagsToComments($tags);

	if (!isset($tags["comments"]) || !isset($tags["playtime_seconds"]))
	  return false;

    $fields = array("artist", "album", "title", "year", "track_number");
    $track = array("file_name" => $tags["filename"],
				   "file_size" => $tags["filesize"],
				   "duration" => round($tags["playtime_seconds"]));

    foreach ($fields as $field)
      {
		if (isset($tags["comments"][$field]))
		  {
			//_ sometimes the info that should be numeric in a id3 tag isn't, so let's correct for any errors, etc. _//
			if ($field == "year")
			  $track[$field] = is_numeric($tags["comments"][$field][0]) ? intval($tags["comments"][$field][0]) : 0;
			else if ($field == "track_number")
			  $track[$field] = (preg_match("/^0*(\d{1,2})/", $tags["comments"][$field][0], $track_num)) ? intval($track_num[1]) : 0; // strips leading zeros from tracknumbers USE PREG_MATCH
			else if ($field == "artist")
			  $track[$field] = utf8_encode(trim(implode(" & ", $tags["comments"][$field]))); // may have multiple artists, hence implode
			else if ($field == "album")
			  $track["name"] = utf8_encode($tags["comments"][$field][0]);
			else
			  $track[$field] = utf8_encode($tags["comments"][$field][0]);
		  }
		else
		  {
			if ($field == "title" || $field == "artist" || $field == "album") // these fields are required or else id3 is worthless
			  return false;
			else // numerical fields (year, track_number) should be zero
			  $track[$field] = 0;

			//_ log this error'd field in the array _//
			if ( !isset($track["errors"]) )
			  $track["errors"] = array($field);
			else
			  $track["errors"][] = $field;
		  }
	  }

	return $track;
  }
}

function sort_by_tracknum($a, $b)
{
  if ($a == $b) // if track num's are the same, they must have been invalid...so compare by file name
	return strcasecmp($a["file_name"], $b["file_name"]);

  return ($a["track_number"] < $b["track_number"]) ? -1 : 1;
}
?>