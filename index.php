<html>

  <head>
    <title>album scan v1.0 biatch</title>
    
    <link rel="stylesheet" type="text/css" href="rescan.css" />

  </head>
  
  <body>
    <div id="content">
<?php

include "config.inc.php";
include "rescan.inc.php";

echo "Scanning " . MP3_FOLDER . "...";

$mp3_scan = new mp3_scan(MP3_FOLDER);
$new_albums = $mp3_scan->get_new_albums();

$num_new = count($new_albums["new"]);
$num_old = count($new_albums["old"]);

echo "found " . ($num_new+$num_old) . " artists ({$num_new} new artists, {$num_old} existing artists)";

$artist_key = $album_key = 0;

echo "<form action=\"new_albums.php\" method=\"POST\"><table>";

foreach ($new_albums["new"] as $key => $artist)
  {
	$album_key = 0;

	echo "<tr class=\"artist\"><td colspan=\"3\"><input type=\"hidden\" name=\"new[{$artist_key}][{$album_key}][artist]\" value=\"{$artist[0]["artist"]}\" />{$artist[0]["artist"]}:</td></tr>";

	foreach ($artist as $key2 => $album)
	  {
		echo "<tr class=\"album\">";
		echo "<td><input type=\"checkbox\" name=\"scan[{$artist_key}][{$album_key}]\" value=\"{$album["folder"]}\" checked /></td>";
		echo "<td><input type=\"hidden\" name=\"{$artist_key}[{$album_key}][album]\" value=\"{$album["album"]}\" /><span>{$album["album"]}</span></td>";
		echo "<td><input type=\"hidden\" name=\"{$artist_key}[{$album_key}][year]\" value=\"{$album["year"]}\" /><span>{$album["year"]}</span></td>";
		echo "</tr>";
		$album_key++;
	  }

	$artist_key++;
  }

echo "<tr><td colspan=\"4\"><input type=\"submit\" value=\"submit\" /></td></tr>";
echo "</table></form>";


//var_dump($new_albums["new"][0]);
?>
    </div>
  </body>

</html>