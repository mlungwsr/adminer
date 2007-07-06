<?php
// Copyright 2007 Jakub Vrana http://phpminadmin.sourceforge.net, licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in compliance with the License.

session_start();
error_reporting(E_ALL & ~E_NOTICE);
$SELF = preg_replace('~^[^?]*/([^?]*).*~', '\\1?', $_SERVER["REQUEST_URI"]) . (strlen($_GET["server"]) ? 'server=' . urlencode($_GET["server"]) . '&' : '') . (strlen($_GET["db"]) ? 'db=' . urlencode($_GET["db"]) . '&' : '');
include "./lang.inc.php";
include "./functions.inc.php";
include "./design.inc.php";
include "./auth.inc.php";
include "./connect.inc.php";

if (isset($_GET["dump"])) {
	include "./dump.inc.php";
} else {
	if (isset($_GET["table"])) {
		include "./table.inc.php";
	} elseif (isset($_GET["select"])) {
		include "./select.inc.php";
	} elseif (isset($_GET["view"])) {
		include "./view.inc.php";
	} else {
		$params = preg_replace('~.*\\?~', '', $_SERVER["REQUEST_URI"]);
		if ($_POST) {
			$error = (in_array($_POST["token"], (array) $_SESSION["tokens"][$params]) ? "" : lang('Invalid CSRF token.'));
		}
		if ($_POST && !$error) {
			$token = $_POST["token"];
		} else {
			$token = rand(1, 1e6);
			$_SESSION["tokens"][$params][] = $token;
		}
		if (isset($_GET["sql"])) {
			include "./sql.inc.php";
		} elseif (isset($_GET["edit"])) {
			include "./edit.inc.php";
		} elseif (isset($_GET["create"])) {
			include "./create.inc.php";
		} elseif (isset($_GET["indexes"])) {
			include "./indexes.inc.php";
		} elseif (isset($_GET["database"])) {
			include "./database.inc.php";
		} else {
			unset($_SESSION["tokens"][$params]);
			page_header(htmlspecialchars(lang('Database') . ": " . $_GET["db"]));
			echo '<p><a href="' . htmlspecialchars($SELF) . 'database=">' . lang('Alter database') . "</a></p>\n";
			if (mysql_get_server_info() >= 5) {
				$result = mysql_query("SELECT * FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = '" . mysql_real_escape_string($_GET["db"]) . "'");
				if (mysql_num_rows($result)) {
					echo "<h2>" . lang('Routines') . "</h2>\n";
					echo "<table border='0' cellspacing='0' cellpadding='2'>\n";
					while ($row = mysql_fetch_assoc($result)) {
						echo "<tr valign='top'>";
						echo "<th>" . htmlspecialchars($row["ROUTINE_TYPE"]) . "</th>";
						echo "<th>" . htmlspecialchars($row["ROUTINE_NAME"]) . "</th>"; //! parameters from SHOW CREATE {PROCEDURE|FUNCTION}
						echo "<td><pre>" . htmlspecialchars($row["ROUTINE_DEFINITION"]) . "</pre></td>";
						echo "</tr>\n";
					}
					echo "</table>\n";
				}
				mysql_free_result($result);
			}
		}
	}
	page_footer();
}
