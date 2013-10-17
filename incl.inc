<?php

/*$host='127.0.0.1';
$user='rybka_user';
$pwd='uiklopiia';*/

$SYS_TEST=0;
$SYS_NORM=1;
$SYS_CRIT=12;

function berror ($error) {
	echo "<p>Blablator</p>";
	echo "<h1>Internal system error</h1>";
	echo "<i>Details:</i><p>";
	die ($error);
}

function reload_page () {
   echo "<META HTTP-EQUIV=\"refresh\" CONTENT=\"0; url=index.php\">";
}

function echo_page_title ($title) {
	echo "<H2>$title</H2>\n<HR />\n";
}

function page_start () {
	echo "<HTML>\n";
	echo "<link rel=StyleSheet HREF=\"style.css\" TYPE=\"text/css\" MEDIA=screen>\n";
	echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"/>\n";
	
	echo "<BODY>\n";
	echo "<div id=\"header\">\n";
	
	echo "<div id=logo><img src=\"images/logo3.png\"></div>\n";
#	echo "<div id=logo><img src=\"images/bleblyblator.png\"></div>\n";
#	echo "Blablator";
	echo "<div id=login>";
	
	//if (session_is_registered(myusername)) {
	if (!strcmp($_SESSION["auth"], "1")) {
		$username = $_SESSION['myusename'];
		$realname = $_SESSION['realname'];

		$username=$_SESSION['myusername'];
		echo "\t<SMALL> Zalogowano jako: $realname (<u>$username</u>)</SMALL>\n";
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
	
	//echo "<center><bad>Я — 143. Не получаю генератор. Идёт такая работа от аппаратной.</bad></center>\n";
	print_menu ();   // menu
	
	echo "\n<div id=\"content\">\n";
};

function page_stop () {
	echo "</BODY>\n";
	echo "</HTML>\n";
};

function get_mnu_style ($i) {
	if (strcmp($i, $_GET['action']))
		$mnu = "mnu_clicked";
	else
		$mnu = "mnu";
	return $mnu;
}

function print_menu () {
	$mainmenu = array('index.php?action=news' => 'Wydarzenia');
	//$usermenu = array('contest_list' => 'Pokaż konkursy', 'messagebox' => 'Skrzynka wiadomości');
	$usermenu = array('contest_list' => 'Pokaż konkursy');
	$rootmenu = array('?action=show_submissions' => 'Zgłoszenia', '?action=show_submissions_todo' => 'Do oceny', '?action=show_submissions_ex' => 'Przegląd');

	echo "<div id=menu>\n<ul>\n";
	foreach ($mainmenu as $i => $v) {
		//$mnu=get_mnu_style ($i);
		echo "<li class=\"menuitem\"><a class=\"menuitem\" href=\"$i\">$v</a></li>\n";
	}
	//echo "echo tu idzie user";
	if (!strcmp($_SESSION['auth'], "1"))
		foreach ($usermenu as $i => $v) {
			//$mnu = get_mnu_style ($i);
			echo "<li class=\"menuitem\"><a class=\"menuitem\" href=\"?action=$i\">$v</a></li>\n";
		}
	//echo "tu idzie admin";
	if (!strcmp($_SESSION['priv'], "2")) {
		echo "|";
		foreach ($rootmenu as $i => $v) {
			//$mnu = get_mnu_style ($i);
			echo "<li class=\"menuitem\"><a class=\"menuitem\" href=\"$i\">$v</a></li>\n";
		}
	}
	echo "</ul></div>";
	//echo "tu przeszla krowa";
}

function echo_go_back () {
	echo "<p><A HREF=\"javascript:history.back()\">Powrót do poprzedniej strony</A></p>";
}

?>
