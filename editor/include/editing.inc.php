<?php
function email_header($header) {
	// iconv_mime_encode requires PHP 5, imap_8bit requires IMAP extension
	return "=?UTF-8?B?" . base64_encode($header) . "?="; //! split long lines
}

function get_key_vals($query) {
	global $connection;
	$return = array();
	$result = $connection->query($query);
	while ($row = $result->fetch_row()) {
		$return[$row[0]] = $row[1];
	}
	return $return;
}
