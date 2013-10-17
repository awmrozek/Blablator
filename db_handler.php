<?php
	echo "<!-- # Using independent database communication subsystem -->";
	function rybka_connect () {
		include 'shadow.inc';
		$lnk = mysql_connect ($_sqlhost, $_sqluser, $_sqlpwd) or berror (mysql_error());
		mysql_select_db ($_sqldbase) or die (mysql_error());
		mysql_query("SET NAMES 'latin1';");
		return $lnk;
	}

	function rybka_close ($lnk) {
		mysql_close ($lnk);
	}
	echo "<!-- IDCS finalized OK -->\n";
?>
