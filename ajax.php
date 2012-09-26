<?php

require("inc/config.inc.php");
require("inc/jpdo.inc.php");

if (REQUESTED_WITH_AJAX && isset($_GET["q"]))
  {
	$pdo = new jPDO;

	switch($_GET["q"])
	  {
	  case "artists":
		send_artists();
		break;
	  case "albums":
		send_albums();
		break;
	  case "songs":
		send_songs();
		break;
	  }
  }

function send_artists()
{
  global $pdo;

  echo json_encode($pdo->get_artists());
  exit;
}

function send_albums()
{
  global $pdo;
  
  if (isset($_GET["a"]))
	echo json_encode($pdo->get_albums($_GET["a"]));
  else
	send_error("Artist ID not specified.");

  exit;
}



?>