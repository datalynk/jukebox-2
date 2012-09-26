<html>

  <head>
    <title>album scan v1.0 biatch</title>
    
    <link rel="stylesheet" type="text/css" href="rescan.css" />

  </head>
  
  <body>
    <div id="content">
<?php

require("../inc/config.inc.php");
require("../inc/rescan.inc.php");
require("../inc/jpdo.inc.php");

set_time_limit(300);

echo "Scanning " . MP3_FOLDER . "...";

$jPDO = new jPDO();
$mp3_scan = new mp3_scan($jPDO);

$albums = $mp3_scan->get_new_albums(MP3_FOLDER);


$num_albums = count($albums);

echo "<h1>Found {$num_albums} albums</h1>";

echo "<form action=\"new_albums.php\" method=\"POST\"><table>";

foreach ($albums as $a_key => $album)
  {
	$album_info = array("artist" => array(),
						"name"   => array(),
						"year"   => array());

	$t_html = "";

	foreach ($album["tracks"] as $t_key => $track)
	  {
		$r_duration = readable_duration($track["duration"]);
		$r_size = readable_size($track["file_size"]);

		$t_html .= <<<THTML
    <tr class="track">
	  <td class="track_number">
        <input type="hidden" name="songs[{$a_key}][{$t_key}][track_number]" value="{$track["track_number"]}" />
        <span>{$track["track_number"]}</span>
      </td>
	  <td class="title">
        <input type="hidden" name="songs[{$a_key}][{$t_key}][title]" value="{$track["title"]}" />
        <span>{$track["title"]}</span>
      </td>
	  <td class="artist">
        <input type="hidden" name="songs[{$a_key}][{$t_key}][artist]" value="{$track["artist"]}" />
        <span>{$track["artist"]}</span>
      </td>
	  <td class="duration">
        <input type="hidden" name="songs[{$a_key}][{$t_key}][duration]" value="{$track["duration"]}" />
		<span>{$r_duration}</span>
      </td>
	  <td class="size">
		<input type="hidden" name="songs[{$a_key}][{$t_key}][file_name]" value="{$track["file_name"]}" />
        <input type="hidden" name="songs[{$a_key}][{$t_key}][file_size]" value="{$track["file_size"]}" />
		<input type="hidden" name="songs[{$a_key}][{$t_key}][album_id]" value="0" />
		<span>{$r_size}</span>
      </td>
	</tr>
THTML;

		foreach ($album_info as $field => $values)
		  {
			if (!in_array($track[$field], $album_info[$field]))
			  $album_info[$field][] = $track[$field];
		  }
	  }
	
	$a_html = gen_album_html($a_key, $album_info);

	echo "<tr class=\"artist\">
            <td class=\"scan\">
              <input type=\"checkbox\" name=\"albums[{$a_key}][path]\" value=\"{$album["path"]}\" checked/>
            </td>
            <td class=\"name\">
              {$a_html["name"]}
            </td>
            <td class=\"artist\">
              {$a_html["artist"]}
            </td>
            <td>
              <input type=\"hidden\" name=\"albums[{$a_key}][num_tracks]\" value=\"{$album["num_tracks"]}\" />
              <input type=\"hidden\" name=\"albums[{$a_key}][size]\" value=\"{$album["size"]}\" />
              <input type=\"hidden\" name=\"albums[{$a_key}][duration]\" value=\"{$album["duration"]}\" />
              <input type=\"hidden\" name=\"albums[{$a_key}][artist_id]\" value=\"0\" />
            </td>
            <td class=\"year\">
              {$a_html["year"]}
            </td>
          </tr>" . $t_html;

  }

echo "<tr><td colspan=\"4\"><input type=\"submit\" value=\"submit\" /></td></tr>";
echo "</table></form>";

function gen_album_html($a_key, $album_info)
{
  foreach ($album_info as $field => $values)
	{
	  $key = ($field == "artist") ? "artists" : "albums";

	  if (count($album_info[$field]) > 1) // make a selection box if there's conflicting album artist data in the tracks
		{
		  $a_html[$field] = "<select name=\"{$key}[{$a_key}][{$field}]\">";
			
		  foreach ($values as $value)
			$a_html[$field] .= "<option>{$value}</option>";

		  $a_html[$field] .= "<option>(other)</option></select>";
		}
	  else
		{
		  $a_html[$field] = "<input type=\"hidden\" name=\"{$key}[{$a_key}][{$field}]\" value=\"{$values[0]}\" /><span>{$values[0]}</span>";
		}
	}
  
  return $a_html;
}

function readable_duration($seconds)
{
  return (round($seconds/60) . ":" . sprintf("%02d", round($seconds%60)));
}

function readable_size($bytes, $decimals=2)
{
  $sz = 'BKMGTP';
  $factor = floor((strlen($bytes) - 1) / 3);
  return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor] . "iB";
}

//var_dump($new_albums["new"][0]);
?>
    </div>
  </body>

</html>
