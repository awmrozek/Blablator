<?php

include 'tools.php';

function secure_str ($str) {
	$str = stripslashes ($str);
	$str = mysql_real_escape_string ($str);
	
	return $str;
}

function new_urid () {
	return rand ();
}

function event ($prio, $message) {
	$message = secure_str ($message);
	$prio = secure_str ($prio);
	$ip=secure_str($_SERVER['REMOTE_ADDR']);
	//$http_user_agent=secure_str($_SERVER['HTTP_USER_AGENT']); 
	$request_uri=secure_str($_SERVER['REQUEST_URI']);
	//$phpsessid=secure_str($_SERVER['HTTP_COOKIE']);
	$user=secure_str($_SESSION['myusername']);
	mysql_query ('insert into systemlog (priority, day, hour, message, ip, user, REQUEST_URI, HTTP_USER_AGENT, PHPSESSID) values ("'.$prio.'", NOW(), NOW(), "' . $message . '", "'.$ip.'", "' . $user .'", "'.$request_uri.'", "'.$http_user_agent.'", "'.$phpsessid.'");');
}

include 'shadow_functions.php';

function root_show_systemlog () {
	root_access_validate ();
	
	echo_page_title ("Systemlog");
	$res=mysql_query ("SELECT * FROM systemlog order by ix desc;");
	echo "<TABLE>\n<TH>IX</TH><TH>Day</TH><TH>Hour</TH><TH>User</TH><TH>IP</TH><TH>Message</TH><TH>Req_uri</TH>\n";
	while ($l = mysql_fetch_array ($res)) {
		echo "<TR>";
		echo "<TD>".$l['ix']."</TD>";
		echo "<TD>".$l['day']."</TD>";
		echo "<TD>".$l['hour']."</TD>";
		echo "<TD>".$l['user']."</TD>";
		echo "<TD>".$l['ip']."</TD>";
		echo "<TD>".$l['message']."</TD>";
		echo "<TD>".$l['REQUEST_URI']."</TD>";
		echo "</TR>\n";
	}
	echo "<TABLE>";
}

function get_task_urid ($tid) {
	access_validate ();

	$tid=secure_str ($tid);
	$sql="SELECT * FROM task_texts WHERE tid=\"$tid\" and type=\"TMARK\";";
	$res=mysql_query ($sql);
	event (2, $sql." : ".mysql_error());
	$l=mysql_fetch_array ($res);
	mysql_free_result($res);
	return $l['urid'];
}

function display_by_urid ($urid) {
	access_validate ();

	$urid=secure_str ($urid);
	$r=mysql_query ("SELECT * FROM task_texts WHERE urid=\"$urid\"");

	while ($l=mysql_fetch_array($r)) {
		$content = $l['content'];
		switch ($l['type']) {
			case "STR":
				echo $content;
				break;
			case "TEXT":
				echo "<input type=\"text\" name=\"$urid\">";
				break;
		}
		$nexturid=$l['next_urid'];
	}
	return $nexturid;
}

function root_show_submissions_ex ($user, $contest) {
	root_access_validate ();
	?>
	<script type="text/javascript">
		function gotoContest () {
			var user = document.getElementById("username");
			var cont = document.getElementById("contest");

			//alert ("New Document location: ?action=show_submissions_ex&user="+user.value+"&cid="+cont.value);
			window.location = "?action=show_submissions_ex&user="+user.value+"&cid="+cont.value;
		}
	</script>
	Użytkowink:
	<select id="username" onchange="gotoContest()">
	<?php 
		$res = mysql_query ("SELECT * FROM members;");
		while ($l = mysql_fetch_array($res)) {
			$val = $l['username'];
			$ttl = $l['username'];

			echo "\t\t<option ";
			if (!strcmp ($user, $val))
				echo "selected ";
			echo "value=\"$val\">$ttl</option>\n";
		}
	?>
	</select>

	Kontest:
	<select id="contest" onchange="gotoContest()">
	<?php
		$res = mysql_query ("SELECT * FROM contest;");
		while ($l = mysql_fetch_array ($res)) {
			$val = $l['cid'];
			$ttl = $l['title'];

			echo "\t\t<option ";
			if (!strcmp($contest, $val))
				echo "selected ";
			echo "value=\"$val\">$ttl</option>\n";
		}
	?>
	</select>
	<?php
	show_task_list_for ("$user", "$contest");
}

function root_show_submissions ($foruser, $onlyunrated) {
	root_access_validate ();
?>
<!-- Submission removal confirmation -->
	<script type="text/javascript">
	function confirm_delete (sid) {
		var a = confirm ("Czy na pewno usunąć rozwiązanie tego użytkownika? Tej czynności NIE DA SIĘ COFNĄĆ!");
		if (a)
			window.location = "?action=root_delete_submission&sid="+sid;
	}
	</script>
<!-- SRC -->
<?php
	$foruser = secure_str ($foruser);
	echo_page_title("Zgłoszenia użytkowników:");

	if (strcmp($foruser, "")) {
		echo "Pokazuję tylko zgłoszenia użytkownika <u>$foruser</u>.<BR />";
		$sql_user = "AND user=\"$foruser\"";
	}
	//echo "<pre>";
	echo "<TABLE><TR><TH>Submission ID</TH><TH>Index</TH><TH>Date</TH><TH>Username</TH><TH>Task ID</TH><TH>Rating</TH><TH>Action</TH>\n";
	if (strcmp($onlyunrated, "yes"))
		$r=mysql_query ("SELECT * FROM task_submission_details WHERE type=\"TASK_SUBMISSION\" $sql_user order by submit_time desc;") or die (mysql_error());
	else {
		echo "<p>Do oceny</p>";
		$r=mysql_query ("SELECT * FROM task_submission_details WHERE type=\"TASK_SUBMISSION\" AND mark IS NULL $sql_user order by submit_time desc;") or die (mysql_error());
		}
		while ($l=mysql_fetch_array($r)) {
			echo "<TR>\n";
				$user=$l['user'];
				$sid=$l['submission_id'];
				$mark_str = mark_to_string($l['mark']);
				echo "<TD>".$sid."</TD>";
				echo "<TD>".$l['ix']."</TD>";
				echo "<TD>".$l['submit_time']."</TD>";
				echo "<TD><A HREF=\"?action=show_submissions&foruser=$user\">$user</A></TD>";
				echo "<TD>".$l['tid']."</TD>";
				echo "<TD ID=$mark_str>$mark_str</TD>";
				echo "<TD><A HREF=\"?action=submission_rate&submission_id=".$l['submission_id']."\">Rate</A>";
				echo " <A HREF=\"?action=root_edit_educator_comment&sid=".$l['submission_id']."\">Comment</A>";
				echo " <A HREF=\"?action=show_submission_report&sid=$sid\">Show</A>";
				echo " <LABEL onclick=\"confirm_delete ($sid);\">Usuń</LABEL></TD>";
	//			echo "<A HREF=\"?action=task"
			echo "</TR>\n";
		}
	echo "</TABLE>\n";
	//echo "</pre>";
	mysql_free_result ($r);
}

function root_delete_submission ($sid) {
	echo_page_title ("Usuwanie rozwiązania ucznia");
	root_access_validate ();
	$sid = secure_str ($sid);
	$res = mysql_query ("DELETE from task_submission_details where submission_id=\"$sid\";");
	echo "<pre><bad>".mysql_error()."</bad></pre>";
	echo_go_back ("-1");
}

function root_update_mark ($sid) {
	root_access_validate ();
	$user = secure_str ($_SESSION['myusername']);
	$sid = secure_str ($sid);
	echo_page_title("Aktualizacja zgłoszenia dla $sid...\n");
	//echo "<pre>";
	foreach ($_POST as $urid => $new) {
		/*echo "root_update_mark: Updating $urid => $new \n";
		if (!strcmp ($new, "old")) {
			echo " -- aborted. Leaving old value.\n";
			continue;
		}*/
		$r=mysql_query ("UPDATE task_submission_details SET mark=\"$new\" WHERE urid=\"$urid\";");
		event (2, mysql_error());
		mysql_free_result ($r);

		echo "<bad>".mysql_error()."</bad>\n";
	}
	//echo "</pre>";
	//echo "Koniec raportu.";

	root_show_submissions ();
}

function mark_to_string ($m) {
	switch ($m) {
		case "0":
			return "bad";
			break;
		case "1":
			return "good";
			break;
		case "-1":
			return "bad";
			break;
		default:
			return "await";
	}
}

function echo_oceniaczka ($urid) {
	echo "<SELECT NAME=$urid size=1><option value=\"old\" /><option value=1>Zdał</option><option value=0>Oblał</option><!--<option value=-1>Imponująco oblał</option>--></SELECT>";
}

function root_submission_rate ($sid) {
	root_access_validate ();
	mce_mode_ro_user ();

	$sid=secure_str ($sid);

	// czy tam w ogole jest submit?
	if (!strcmp ("0", $sid)) {
		echo_page_title ("Brak zgłoszenia");
		echo "<p>Użytkownik jeszcze nie zgłosił rozwiązania do tego zadania.</p>";
		echo_go_back ("-1");
		return;
	}

	echo_page_title ("<H2>Submission: $sid</H2>\n");
	$r=mysql_query ("SELECT * from task_submission_details where submission_id=\"$sid\" ORDER BY IX;");
	
	$l=mysql_fetch_array ($r);
	$tid = $l['tid'];
	$task_urid = $l['urid'];
	$task_mark = mark_to_string($l['mark']);

	// Nieużywane, jako że kopie treści zadań znajdują się
	// w task_submission_details (type==STR, content==frag.trści
	//preview_task ($tid);

	echo "<FORM NAME=the_task ACTION=\"?action=update_mark&submission_id=$sid\" METHOD=POST>\n";
	echo "<i>Rozwiązanie użytkownika/ucznnia</i><BR />\n<p>";
	while ($l=mysql_fetch_array($r)) {
		$content = $l['content'];
		$value = $l['value'];
		$mark = mark_to_string ($l['mark']);
		$urid = $l['urid'];
		switch ($l['type']) {
			case "STR":
				echo $content;
				break;
			case "TEXT":
				echo "<INPUT TYPE=TEXT READONLY VALUE =\"$value\" ID=\"$mark\"/>";
				echo_oceniaczka($urid);
				break;
			case "MCE":
				echo "<textarea>$value</textarea>";
				//echo_oceniaczka($urid);
		}
		/*if (!strcmp($l['type'], "STR")) {
			echo $l['content'];
		} else {
			$urid = $l['urid'];
			$value = $l['value'];
			$mark = mark_to_string($l['mark']);
			
			echo "<INPUT TYPE=TEXT READONLY VALUE=\"$value\" ID=\"$mark\"/>";
		}*/
	}
	echo "</p>\nOcena ogólna za <INPUT TYPE=TEXT READONLY VALUE=\"zadanie\" ID=$task_mark />: <SELECT NAME=\"$task_urid\" size=1><option value=\"old\" /><option value=1>Zdał</option><option value=0>Oblał</option><option value=-1>Imponująco oblał</option></SELECT><BR />\n";
	echo_submit ();	
	echo "</FORM>\n";
	mysql_free_result ($r);
}

function echo_submit () {
	echo "\n<BR /><BR />\n<small><i>Upewnij się, że to, co zostało wpisane, jest ok...</i></small><BR />\n";
	?>
<script type="text/javascript">
	function confirm_submission () {
		if (confirm("Czy jesteś absolutnie pewien, że chcesz wysłać formularz w obecnej formie?"))
			document.the_task.submit ();
	}
</script>
	<A HREF="javascript:confirm_submission()">Wyślij</A>
	<?php
}

function auth_user ($user, $pwd) {
	//if (session_is_registered(myusername)) {
	if (!strcmp($_SESSION["auth"], "1")) {
		echo "\t<bad>Session already registered: Access denied!</bad></BR>\n";
		event (12, "Double session registration");
	} else {
		$myusername=strtolower($user);
		$mypassword=$pwd;
		
		$myusername = secure_str ($myusername);
		$mypassword = secure_str ($mypassword);
		
		$mypassword=md5($mypassword);
		
		$res = mysql_query("select * from members where username='$myusername' and pwd='$mypassword';") or die (mysql_error());
		
	   if (mysql_num_rows($res) > 0) {
			//session_register("myusername");
			//session_register("mypassword");
	
			//get name+surname
			$l = mysql_fetch_array ($res);
			$_SESSION['realname'] = ucwords($l['realname']);

			$_SESSION['myusername'] = ucwords($myusername);
			$_SESSION['auth'] = "1";
			
			$_SESSION['priv'] = $l['privileges'];
	
			event (1, "Logging in");
			reload_page ();
	   	} else {
			page_start();
			echo "<bad>Sorry: Login incorrect!</bad><BR />\n";
			event (10, "Login Failed for ".$myusername);
		}
	}
}

function show_puzzle ($tid, $dynamic) {
	access_validate ();
	
	//max pytań w 1 teście
	$lent = 8;
	if ($dynamic)
		$lent=7;

	$tid = secure_str ($tid);

	$username=$_SESSION['myusername'];
	if ($dynamic) {
		echo "<h2>Dopasowanka dynamiczna:</h2>\n\n";
		$conts = mysql_query ('SELECT ix, drag FROM qmatch_scores where user="'.$username.'" order by score;') or die (mysql_error());
	} else {
		echo "<h2>Dopasowanka:</h2>\n\n";
		$conts = mysql_query ('SELECT ix, drag FROM q_match where tid="' . $tid . '";') or die (mysql_error());
	}

	echo "<div id=\"dragScriptContainer\">\n\t<div id=\"questionDiv\">\n";

	$i = 0;
	while (($ln = mysql_fetch_array ($conts)) && ($i++ < $lent)) {
		echo "\t<div class=\"dragDropSmallBox\" id=\"" . $ln['ix'] . "\">" . $ln['drag'] . "</div>\n";
		echo "\t<div class=\"destinationBox\"></div>\n";
	}

	echo "</div>\n\n<div id=\"answerDiv\">\n";

	if ($dynamic)
		$conts = mysql_query ('SELECT ix, onto FROM qmatch_scores where user="'.$username.'" order by score;') or die (mysql_error());
	else
		$conts = mysql_query ('SELECT ix, onto FROM q_match where tid="' . $tid . '";') or die (mysql_error());

	$i=0;
	while (($ln = mysql_fetch_array ($conts)) && ($i++ < $lent)) {
		echo "<div class=\"dragDropSmallBox\" id=\"" . $ln['ix'] . "\">" . $ln['onto'] . "</div>\n";
	}

	echo "</div>\n";
	echo "<div id=\"dragContent\"></div>\n";
	//echo "<input type=\"button\" onclick=\"dragDropResetForm();return false\" value=\"Reset\">\n";

	echo "</div>\n";
	mysql_free_result ($conts);
}

function update_puzzle_score ($user, $qmtid, $drag, $by) {
	access_validate();
	$by = secure_str($by);
	$user = secure_str($user);
	$drag = secure_str($drag);
	$qmtid = secure_str($qmtid);

	if ($by == 1)
		$by="+1";
	else
		$by="-10";

	//echo "Update score on ".$qmtid." for ".$user." by ".$by;
	//event (1, "Dragdrop: Update score on ".$qmtid." for ".$user." by ".$by);

	$text=mysql_fetch_array(mysql_query('SELECT drag, onto from q_match where ix = '.$qmtid.';'));
	$drag=$text['drag'];
	$onto=$text['onto'];
	if (mysql_num_rows(mysql_query("select score from qmatch_scores where ix = \"".$qmtid."\";"))) {
		$sql = "update qmatch_scores set score=score".$by." where ix=\"" . $qmtid . "\" and user=\"" . $_SESSION['myusername'] . "\";";
		//event (0, "Updating existing entry:");
		//event (0, $sql);
		mysql_query ($sql);
	} else {
		$sql = 'insert into qmatch_scores(user, score, ix, drag, onto) values ("'.$user.'", 1, '.$qmtid.',"'.$drag.'", "'.$onto.'");';
		//event(0, "Creating new entry:");
		//event(0, $sql);
		mysql_query ($sql);
	}
}

/*function preview_task ($tid) {
	root_access_validate ();

	echo "<p>\n";
	$tid=secure_str ($tid);
	$res=mysql_query ("SELECT * from task_texts where tid=\"$tid\";");
	
	while ($l=mysql_fetch_array($res)) {
		$urid=$l['urid'];
		switch ($l['type']) {
			case "STR":
				echo $l['content'];
				break;
			case "TEXT":
				echo "<B>[$urid]</B> ";
				break;
			default:
				echo "<bad>$urid</bad>";
		}
	}
	mysql_free_result ($res);
	echo "</p>\n";
}*/

//Zwraca Submission Id?
function is_submitted ($tid, $user) {
	access_validate ();
	$tid = secure_str ($tid);
	$user = secure_str ($user);

	$r=mysql_query ("SELECT * FROM task_submission_details WHERE tid=\"$tid\" and user=\"$user\" and type=\"TASK_SUBMISSION\";");
	if (mysql_num_rows ($r) > 0) {
		$l=mysql_fetch_array ($r);
		$ret=$l['submission_id'];
	} else
		$ret=0;
	mysql_free_result ($r);
	return $ret;
}

function get_task_score ($sid) {
	access_validate ();
	$sid=secure_str ($sid);

	if ($sid == 0)
		return "To zadanie czeka, abyś je rozwiązał ^^.";
	$r=mysql_query ("SELECT * FROM task_submission_details WHERE TYPE=\"TASK_SUBMISSION\" and submission_id=\"$sid\";");
	while ($l=mysql_fetch_array ($r)) {
		$ret=$l['mark'];
	}
	mysql_free_result ($r);
	switch ($ret) {
		case "1":
			return "<good>Zaliczone</good>";
		case "0":
			return "<bad>Błędne</bad>";
		case "-1":
			return "<bad>Błędne</bad><!--ojojoj, i to jak... -->";
		default:
			return "<await>Oczekuje</await>";
	}
	return "WTF?";
}

function show_task_content ($tid) {
	access_validate ();

	//Allows using TinyMCE, function from tools.php
	mce_mode_rw_user ();

	$tid=secure_str ($tid);
	$res=mysql_query ("SELECT * from task_texts where tid=\"$tid\" ORDER BY IX;");
	/*$urid=get_task_urid ($tid, 0);
	echo "Task urid: $urid";
	while ($urid=display_by_urid($urid));*/
	while ($l=mysql_fetch_array($res)) {
		$urid=$l['urid'];
		$content=$l['content'];
		switch ($l['type']) {
			case "STR":
				echo $content;
				break;
			case "TEXT":
				echo "<input autocomplete=\"off\" type=\"text\" name=\"$urid\">";
				break;
			case "MCE":
				echo "<textarea id=\"elm1\" class=\"elm1\" rows=\"15\" cols=\"80\" name=\"$urid\"></textarea>";
				break;
			default:
				echo "<bad>$urid</bad>";
		}
		echo " ";
	}
	
	mysql_free_result ($res);

}

function show_task ($tid) {
	access_validate ();

	echo "\n<div id=\"task_contents\">\n";
	$sid=is_submitted ($tid, $_SESSION['myusername']);
	if ($sid != 0) {
		show_submission_report ($sid);
	} else {
		echo "<form name =\"the_task\" action=\"?action=submit&tid=$tid\" method=\"POST\">\n";
		show_task_content ($tid);
		echo_submit ();
		echo "</form>\n";
	}
	echo "\n</div>\n";
}

function root_update_educator_comment ($sid) {
	root_access_validate ();
	$sid = secure_str ($sid);
	$comment = secure_str ($_POST['task_comment']);

	echo_page_title ("Aktualizacja komentarza");
	echo "<pre>";
	echo "Updating comment...\n";
	echo "New comment: $comment\n";

	$r=mysql_query ("SELECT * from submission_comments where sid=\"$sid\";");
	echo "Locating sid returned ";
	echo mysql_error ();
	if (mysql_num_rows($r) > 0)
		$r2=mysql_query("UPDATE submission_comments set comment=\"$comment\" where sid=\"$sid\";");
	else
		$r2=mysql_query("insert into submission_comments (sid, comment) values (\"$sid\", \"$comment\");");
	echo "\nMySQL update returned <bad>";
	echo mysql_error ();
	echo "</bad>\n";
	echo "</pre>";
	mysql_free_result ($r);
	mysql_free_result ($r2);
}

function root_edit_educator_comment ($sid) {
	root_access_validate ();
	$sid = secure_str ($sid);

	show_submission_report ($sid);

	$res=mysql_query ("SELECT comment from submission_comments where sid=\"$sid\"i;");
	while ($l=mysql_fetch_array($res)) {
		$comment=$l['comment'];
	}
	echo "<form name=\"the_task\" action=\"?action=root_update_educator_comment&sid=$sid\" method=post>";
	echo "<HR/> Aktualizuj komentarz do zadania: <input name=\"task_comment\" style=\"width: 100%\" value=\"$comment\">";
	echo_submit ();
	echo "</form>";

	mysql_free_result ($res);
}

function show_educator_comment ($sid) {
	//SORRY: FOR NOW DISABLED
//	echo "<p><bad>Komentarze do zadań aktualnie wyłączone</bad></p>";
//	return;
	access_validate ();
	$sid=secure_str ($sid);

	$res=mysql_query ("SELECT comment from submission_comments where sid=\"$sid\";");
	while ($l=mysql_fetch_array ($res)) {
		echo "<div id=\"submission_comment\">\n<div id=\"submission_comment_title\">Komentarz do rozwiązania:</div>";
		echo $l['comment'];
		echo "</div>\n";
	}
	mysql_free_result ($res);
}

// Pokazuje raport dla zgloszenia
// Zwraca ocene dla danego submission-idu
function show_submission_report ($sid) {
	echo_page_title ("Poniżej znajduje się zapis Twojego rozwiązania");
	mce_mode_ro_user ();
	$sid = secure_str ($sid);
	$r = mysql_query("SELECT * FROM task_submission_details WHERE submission_id=\"$sid\" ORDER BY IX;");
	while ($l=mysql_fetch_array ($r)) {
		switch ($l['type']) {
			case "STR":
				echo $l['content'];
			break;
			case "TEXT":
				echo "<INPUT READONLY TYPE=TEXT ID=\"";
				switch ($l['mark']) {
					case "1":
						echo "good";
						break;
					case "0":
						echo "bad";
						break;
					default:
						echo "await";
				}
				echo "\" VALUE=\"";
				echo $l['value'];
				echo "\" />";
				break;
			case "MCE":
				echo "<textarea READONLY>";
				echo $l['value'];
				echo "</textarea>";
				break;
			case "TASK_SUBMISSION":
				$ret=$l['mark'];
			default:
				//echo "<bad>Error @306: Co jest ?</bad>";
				// A to chyba nieważne...
		}
	}
	//mysql_free_result ($r);
	show_educator_comment ($sid);
	return $ret;
}

function submit_task ($tid, $who) {
	// Funkcja wolana z :287 -> ?action=submit
	access_validate ();
	$tid = secure_str ($tid);
	$who = secure_str ($_SESSION['myusername']);
	$submit_id=rand();

	if (is_submitted ($tid, $who)) {
		echo "Już submitowałeś. Dziękujemy.<BR />\n";
		event (10, "function submit_task: attempting to submit task even it has already been submitted");
	} else {
		event (1, "Task submission");
		// Info, że był submit
		$nw_urid = "SUBMIT".new_urid ();
		$res = mysql_query ("INSERT INTO task_submission_details (type, submit_time, user, tid, value, submission_id, urid) values (\"TASK_SUBMISSION\", NOW(), \"$who\", \"$tid\", \"Submission\", \"$submit_id\", \"$nw_urid\");");
		//mysql_free_result($res);

		$res = mysql_query ("SELECT * FROM task_texts where tid=\"$tid\" ORDER BY IX;");
		while ($l=mysql_fetch_array($res)) {
			//echo "jestem w petli i co mi zrobisz?";
			$nexturid=$l['urid'];
			$nextval=mysql_real_escape_string($_POST["$nexturid"]);

			$content=secure_str ($l['content']);
			
//			echo "Submitting: <pre>";
//			echo $nextval;
//			echo "</pre>";
			/*$submit=true;
			switch ($l['type']) {
					case "STR":
						$type="STR";
						break;
					case "TEXT":
						$type="TEXT";
						break;
					default:
						type="UNK";
						$submit=false;
			}

			if (!$submit)
					continue;*/
			$type=$l['type'];
			$add = mysql_query("INSERT INTO task_submission_details (submit_time, user, tid, value, submission_id, urid, type, content) values (NOW(), \"$who\", \"$tid\", \"$nextval\", \"$submit_id\", \"$nexturid\", \"$type\", \"$content\");") or die (mysql_error());
			//mysql_free_result ($add);
		}
		echo_page_title("Dziękujemy!");
		echo "Rozwiązanie zadania zostało wysłane. Teraz oczekuje na sprawdzenie...<BR /> <A HREF=\"javascript:history.go(-2);\">Powrót do listy zadań</A>";
		mysql_free_result ($res);
	}
}

function root_display_task_list (){
	root_access_validate ();

	$r=mysql_query ("SELECT * FROM task_list;");

	event (2, mysql_error ());
	echo_page_title("Task list:");
	echo "<TABLE>\n<TR><TH>TID</TH><TH>Title</TH></TR>\n";
	while ($l=mysql_fetch_array ($r)) {
		$tid = $l['tid'];
		$title = $l['title'];
		echo "<TR><TD><A HREF=\"?action=root_show_editor&tid=$tid\">$tid</A></TD><TD>$title</TD></TR>\n";
	}
	echo "</TABLE>\n";
	mysql_free_result ($r);

}

function validate_task ($tid) {
	access_validate ();
	$cnt = secure_str ($tid);

	$tuid = 'pkgs/' . $cnt . '.pkg.q.txt';
	$auid = 'pkgs/' . $cnt . '.pkg.a.txt';

	$f = file ($tuid);
	$c = file ($auid);

	$f = implode ("<BR />", $f);

	echo "<h2>Raport:</h2>\n";
	echo "<TABLE>\n";
	echo "\t<TR>\n";
	echo "\t\t<TH>Twoja odpowiedź</TH>\n";
	echo "\t\t<TH>Odpowiedź wzorcowa</TH>\n";
	echo "\t\t<TH>Status</TH>\n";
	echo "\t</TR>\n";

	foreach ($c as $i => $ans) {
		echo "\t<TR>\n";
		echo "\t\t<TD> $_POST[$i] </TD>\n";
		echo "\t\t<TD> $ans </TD>\n";
		echo "\t\t<TD> ";
		if (strcmp($ans, $_POST[$i] . "\n")) {
			echo "<bad>SIO DO KSIĄŻEK!!!!!!!</bad>";
		} else {
			echo "<good>OK ;)</good>";
		}
		echo " </TD>\n";
		echo "\t</TR>\n";
	}

	echo "\t</TABLE>";
}

function show_contest_list () {
// chleb z pasztetem
// UWAGA: Zachodzi przy skręcie 1.5m **Schmelzzwagen**
	access_validate ();

	//event (1, "Show contest list for ".$_SESSION['myusername']);
	$conts = mysql_query ('SELECT cid, title FROM contest;') or die (mysql_error());

	echo_page_title("Wybierz kontest:");

	echo "<div class='list'>\n";
	while ($ln = mysql_fetch_array ($conts)) {
		$cid=$ln['cid'];
		echo "\t<p><a href=\"?action=contest&contest=" . $cid . "\">" . $ln['title'] . " </a>";
		echo "</p>\n<hr>\n";
	}
	echo "</div>\n";

	mysql_free_result ($conts);

	if (is_root()) {
		include 'add_contest_form1.html';
		echo_submit ();
	}
}

function show_task_list_for ($user, $cid) {
	if (strcasecmp ($user, $_SESSION['myusername'])) {
		root_access_validate ();
	} else
		access_validate ();
	
	$cid = secure_str ($cid);
	$user = secure_str ($user);

	$tasks = mysql_query ("SELECT tid, cid, title FROM task where cid=\"$cid\" ORDER BY IX;") or die (mysql_error());
	
	echo_page_title("Wybierz zadanie, które chciałbyś rozwiązać...");

	echo "<div class='list'>\n";
	while ($ln = mysql_fetch_array ($tasks)) {
		$tid=$ln['tid'];
		$title=$ln['title'];

		echo "\t<li><a href=\"?action=task&tid=$tid&for=$user\">$title</a>\n";
		echo "<p><i><small>";
		echo (get_task_score(is_submitted ($ln['tid'], $user)));

		if (is_root()) {
			$sid=is_submitted ($tid, $user);
			echo "<i><p><a href=\"?action=root_show_editor&tid=$tid\">[Edytuj]</a>\n";
			echo "<a href=\"?action=submission_rate&submission_id=$sid\"><big>[Oceń]</big></a>\n";
			echo "<a href=\"?action=root_edit_educator_comment&sid=$sid\">[Komentarz]</a>";
			echo "<a href=\"?action=show_submission_report&submission_id=$sid\">[Pokaż]</a></p>";
		}
		echo "</i></small></p></li>\n<hr />\n";
	}

	if (is_root()) {
	?>
	<FORM NAME="the_task" method=post action="?action=root_add_task">
	<li><input name="new_task_name" /><input type=hidden name="contest_name" value="<?php echo $cid; ?>" />
	<?php echo_submit(); ?></li>
	</form>
	<?php
	}

	echo "</div>\n";
	
	mysql_free_result ($tasks);
}

function show_task_list ($cid) {
	$user = secure_str($_SESSION['myusername']);
	show_task_list_for ($user, $cid);
}

function show_news () {
	if (is_root())
		root_add_news_form ();
	$news = mysql_query ('SELECT * from news order by added desc;');
	while ($ln = mysql_fetch_array ($news)) {
		echo "<p>\n";
		echo "\t<h3> ".$ln['title']."</h3>\n";
		echo "\t".$ln['content']."\n<BR />\n";
		echo "\t<subsmall>".$ln['author'].", ".$ln['added']."</subsmall>\n";
		echo "</p>\n<hr />\n";
	}
	mysql_free_result ($news);
}

function root_add_news_form () {
	root_access_validate ();
	echo "<div id=\"add_news_form\">\n";
	echo_page_title ("Dodaj nowy news");
	?>
<FORM NAME="the_task" ACTION="?action=dbase_add_news" METHOD="POST">
	<INPUT TYPE="TEXT" NAME="title" style="width:100%" VALUE="Tytuł newsa..." /><BR />
	<!--<INPUT TYPE="TEXT" NAME="content" style="width:100%" VALUE="Treść newsa" /><BR /> -->
	<?php  mce_mode_rw_user(); ?>
	<textarea name="content">Treść newsa</textarea>
	<?php echo_submit(); ?>
</FORM>
	<?php
	echo "</div>\n";
}

function root_add_news () {
	root_access_validate ();
	echo_page_title ("Dodawanie newsa");
	echo "Dodawanie newsa...";
	event (1, "Adding news");
	$title=secure_str ($_POST['title']);
	$content=secure_str ($_POST['content']);
	$author=secure_str ($_SESSION['myusername']);
	$r=mysql_query ("INSERT INTO news (added, title, content, author) values (NOW(), \"$title\", \"$content\", \"$author\")");
	event (2, mysql_error ());
	echo_go_back ();
}

?>
