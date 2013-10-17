<?php
	### Security Management moved to index.php
	### Zarządzanie Bezpieczeństwem przeniesione do index.php

	function root_add_task () {
		$tname = secure_str ($_POST['new_task_name']);
		$cid = secure_str ($_POST['contest_name']);

		if (strcmp($cid, "")) {
			// nazwa kontestu niepusta - trzeba dodać zadanie
			echo_page_title ("Nowe zadanie w konteście");
			$username = $_SESSION['myusername'];
			$res = mysql_query ("INSERT INTO task (cid, tid, title, added, added_by) values (\"$cid\", NOW(), \"$tname\", NOW(), \"$username\");");
			echo "<p><bad>".mysql_error()."</bad></p>\n";
			echo "Dodano nowe zadanie do kontestu, jeśli brak błędu powyżej.";
			echo_go_back ("-1");
		} else {
			echo_page_title ("Nowy kontest");
			$username = $_SESSION['myusername'];
			$res = mysql_query ("INSERT INTO contest (cid, title, added, added_by) values (NOW(), \"$tname\", NOW(), \"$username\");");
			echo "<p><bad>".mysql_error()."</bad></p>";
			echo "Dodano nowy kontest jeśli brak błędu powyżej.";
			echo_go_back ("-1");
		}
	}

	function root_show_editor_update_task ($tid) {
		echo "<pre>";
		$task_ml=mysql_real_escape_string($_POST['elm1']);
		//$task=preg_split ('/(<[^>]*[^\/]>)/i', $task_ml, -1, PREG_SPLIT_NO_EMPTY);
		$task=preg_split ('(<label.*?>|<\/label.*?>)', $task_ml, -1, PREG_SPLIT_NO_EMPTY);
		print_r ($task);

		mysql_query("DELETE from task_texts where tid=\"$tid\";");
		echo "Removing previous version... <bad>".mysql_error()."</bad>\n";
		foreach ($task as $x) {
			$type="STR";
			$urid=new_urid();
			if (strstr($x, "[EDIT]"))
				$type = "TEXT";
			if (strstr($x, "[MCE]"))
				$type = "MCE";
			mysql_query("INSERT INTO task_texts (type, tid, urid, content) values (\"$type\", \"$tid\", \"$urid\", \"$x\");");
			echo ("<bad>".mysql_error()."</bad><br />\n");
		}
		echo "</pre>";
	}

	function root_show_editor ($tid, $mode) {
		if (!strcmp($mode, "update_task")) {
			root_show_editor_update_task ($tid);
			return;
		}
		### Echo Editor Frames
		include 'tinymce_editor.html';

		echo "<form name=\"the_task\" method=\"post\" action=\"?action=root_show_editor&tid=$tid&mode=update_task\">\n";
		echo "<textarea id=\"elm1\" name=\"elm1\" rows=\"15\" cols=\"80\" style=\"width: 100%\">\n";
		### Echo Editor Content
		$res = mysql_query ("SELECT * from task_texts where tid=\"$tid\" ORDER BY IX;");
		while ($l = mysql_fetch_array($res)) {
			switch ( $l['type'] ) {
				case "STR":
					echo $l['content'];
					echo "\n";
					break;
				case "TEXT":
					echo "<label class=\"editor_textbox\" style=\"border: 1px solid gray; padding: 2px;\">[EDIT] Pole edycyjne</label>\n";
					break;
				case "MCE":
					echo "<label class=\"editor_mce\" style=\"border: 1px solid gray; padding: 5px;\">[MCE] Edytor Tekstu WYSIWYG</label>\n";
					break;
				default:
					echo "Undefinied seq.\n";
			}
		}
		echo "\n</textarea>\n";
		echo_submit();
		echo "</form>";
	}

	function root_adder_query ( $type, $title, $cid, $isTmLmt, $sDy, $sMt, $sYr, $sHr, $sMn, $eDy, $eMt, $eYr, $eHr, $eMn, $eDy ) {
		if (!strcmp($type, "contest")) {
			$id = time();
			$username = $_SESSION['myusername'];

			if (!strcmp($isTmLmt, "1"))
				# Dodawanie kontestu z limitem czasowym
				$sql = "INSERT INTO contest (cid, title, begin, end, added, added_by) values (\"$id\", \"$title\", \"$sYr-$sMt-$sDy $sHr:$sMt:00\", \"$eYr-$eMt-$eDy $eHr:$eMt:00\", NOW(), \"$username\";";
			else
				# Dodawanie kontestu bez ograniczen czasowych
				$sql = "INSERT INTO contest (cid, title, added, added_by) values (\"$id\", \"$title\", NOW(), \"$username\");";
		} else {
			if (!strcmp($isTmLmt, "1"))
				echo "<bad>SORRY: Obsługa limitów czasowych w zadaniach nie jest niestety aktualnie zaimplementowana.</bad>";
			else
				$sql = "INSERT INTO task (cid, tid, added, added_by, title) values (\"$cid\", \"$id\", NOW(), \"$username\", \"$title\");";
		}

		
		echo "Wykonam Query $sql<BR />\n";
		return;
		mysql_query($sql);
		echo "<p>Jeśli nie zgłoszono błędu operacja wykonana poprawnie.</p>";
		echo "<p><bad>" . mysql_error() . "</bad></p>";
	}
?>
