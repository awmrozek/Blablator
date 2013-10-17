<?php

function access_validate () {
   //if (session_is_registered(myusername)) {
   if (strcmp($_SESSION['auth'], "1")) {
	   echo "<bad>Access denied.</bad><BR />\n";
	   echo "<bad>Brak uprawnień.</bad>\n";
	   echo "<bad><p>In order to use the requested resource please log in.</p><p>Aby skorzystać z żądanego zasobu proszę się zalogować.</p></bad>";
	   event (12, "Request denied");
	   exit;
	}
}

function root_access_validate () {
	access_validate ();
	if (strcmp($_SESSION['priv'], "2")) {
		echo "<p><bad>Admin privilege escalation. This incident will be reported.</bad></p><p>Próba naruszenia bezpieczeństwa. Ten incydent będzie raportowany.</p>";
		event (13, "Admin privilege escalation");
		exit;
	}
}

function is_root () {
	if (strcmp($_SESSION['priv'], "2"))
		return false;
	return true;
}
?>
