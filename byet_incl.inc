<?php

$host='sql207.byethost10.com';
$user='b10_6245279';
$pwd='uiklopii';

$SYS_TEST=0;
$SYS_NORM=1;
$SYS_CRIT=12;

function reload_page () {
   echo "<META HTTP-EQUIV=\"refresh\" CONTENT=\"0; url=index.php\">";
}

function page_start () {
	echo "<HTML>\n";
	echo "<link rel=StyleSheet HREF=\"style.css\" TYPE=\"text/css\" MEDIA=screen>\n";
	echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"/>\n";
	
	echo "<BODY>\n";
	echo "<div id=\"header\">\n";
	
	echo "<div id=logo><img src=\"images/logo1.png\"></div>\n";
	echo "<div id=login>";
	
	if (session_is_registered(myusername)) {
		echo "\t<SMALL> Zalogowano jako: " . $_SESSION['myusername'] . "</SMALL>\n";
		echo "\t<A HREF=\"?action=logout\">Wyloguj</A>";
	} else {
		echo "\t<form name=\"login\" method=\"post\" action=\"?action=auth\">
		<input type=\"text\" name=\"username\">
		<input type=\"password\" name=\"password\">
		<input type=\"submit\" name=\"submit\" value=\"Login\">
		</form>";
	}
	
	echo "</div>";
	echo "</div>\n";
	
	print_menu ();   // menu
	
	echo "\n<div id=\"content\">\n";
};

function page_stop () {
	echo "</BODY>\n";
	echo "</HTML>\n";
};

function print_menu () {
	echo "<div id=\"menu\">\n";

	echo "\t<ul>\n";

	//echo "\t\t<li><A HREF=\"javascript:history.go(-1);\">Wstecz</A></li>\n";
	echo "\t\t<li id=mnu><A HREF=\"?action=news\">Njusy</A></li>\n";

	if (session_is_registered(myusername)) {
		echo "\t\t<li id=\"mnu_contest\"><A HREF=\"?action=contest_list\">Kontesty</A></li>\n";
		//echo "\t\t<li id=mnu><A HREF=\"?action=rank\">Ranking</A></li>\n";
		//echo "\t\t<li><A HREF=\"?action=puzzle_dynamic\">Dynamiczna Dopasowanka</A></li>\n";
		
		if (is_root()) {
			echo "|";
			echo "\t\t<li ID=mnu><A HREF=\"?action=show_submissions\">Pokaż zgłoszenia</A></li>\n";
			echo "\t\t<li ID=mnu><A HREF=\"?action=add_news\">Dodaj newsa</A></li>\n";
			echo "\t\t<li ID=mnu><A HREF=\"?action=root_display_task_list\">Zmień zadania</A></li>\n";
		}
	}

	echo "\t</ul>\n";
	echo "</div>\n";
}

?>
