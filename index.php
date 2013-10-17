<?php
	session_start ();
	echo "<!-- SYSTEM INITIALIZATION -->\n";
	echo "<!--  > modules: loading... \n";
	include 'incl.inc';
	include 'shadow.inc';
	include 'contest_handling.php';
	include 'editor.php';
	include 'messaging.php';
	include 'db_handler.php';
	echo "<!--  > database: connecting -->\n";
	$lnk = rybka_connect ();
	
	echo "<!-- INITIALIZATION OK. WE ARE ON AIR NOW! -->\n";
	event (1, "Index request");

	// Query processing
	//echo "\n<div id=\"page\">\n";
	switch ($_GET['action']) {
		// Session management related options
		case "test_latex":
			page_start ();
			echo "<IMG SRC=\"latex/?q=f(x)=2x\" />";
			break;
		case "test_edit_table":
			page_start ();
			tbedit_simple_edit_table ();
			break;
		case "auth":
			// Authenticate user
			auth_user ($_POST['username'], $_POST['password']);
			break;
		case "logout":
			event (1, "Logout");
			session_destroy ();
			reload_page ();
			break;
		case "news":
			page_start ();
			show_news ();
			break;
		case "rank":
			page_start ();
			show_rank ();
			break;
		case "add_news":
			page_start ();
			root_add_news_form ();
			break;
		case "dbase_add_news":
			page_start ();
			root_add_news ();
			break;
		
		// Messaging (Message Box)
		case "messagebox":
			page_start ();
			show_inbox ();
			break;
		case "show_message":
			page_start ();
			show_message ($_GET['msgid']);
			break;
		case "root_show_systemlog":
			page_start ();
			root_show_systemlog ();
			break;
		// Task comments
		case "root_update_educator_comment":
			page_start ();
			root_update_educator_comment ($_GET['sid']);
			break;
		case "root_edit_educator_comment":
			page_start ();
			root_edit_educator_comment ($_GET['sid']);
			break;
		// Task editing
		case "root_display_task_list":
			page_start ();
			root_display_task_list ();
			break;
		case "root_show_editor":
			page_start ();
			root_access_validate ();
			root_show_editor (secure_str($_GET['tid']), secure_str($_GET['mode']));
			break;
		case "root_pilot_req":
			page_start ();
			root_pilot_req ($_GET['urid'], $_GET['mode']);
			break;
		case "root_add_task":
			page_start ();
			root_access_validate ();
			root_add_task ();
			break;
		// Contest viewing related
		case "show_submission_report":
			page_start ();
			show_submission_report ($_GET['sid']);
			break;
		case "contest_list":
			page_start ();
			show_contest_list ();
			break;
		case "contest":
			page_start ();
			show_task_list ($_GET['contest']);
			break;
		case "verify":
			page_start ();
			validate_task ($_GET['contest']);
			break;
		case "task":
			page_start ();
			show_task ($_GET['tid']);
			break;
		case "submit":
			page_start ();
			submit_task ($_GET['tid'], $_SESSION['myusername']);
			break;
		case "show_submissions_todo":
			page_start ();
			root_show_submissions ($_GET['foruser'], "yes");
			break;
		case "show_submissions":
			page_start ();
			root_show_submissions ($_GET['foruser'], "no");
			break;
		case "show_submissions_ex":
			page_start ();
			root_access_validate ();
			root_show_submissions_ex (secure_str ($_GET['user']), secure_str ($_GET['cid']));
			break;
		case "root_delete_submission":
			page_start ();
			root_access_validate ();
			root_delete_submission (secure_str($_GET['sid']));
			break;
		case "submission_rate":
			page_start();
			root_submission_rate ($_GET['submission_id']);
			break;
		case "update_mark":
			page_start ();
			root_update_mark ($_GET['submission_id']);
			break;
		case "puzzle_dynamic":
			page_start ();
			show_puzzle ($_GET['puzzle'], 1);
			break;
		case "puzzle":
			page_start ();
			show_puzzle ($_GET['puzzle'], 0);
			break;
		case "updatePuzzleScore":
			update_puzzle_score($_SESSION['myusername'], $_GET['qmtid'], $_GET['drag'], $_GET['by']); // "drag" not used
			break;
		default:
			page_start ();
			show_news ();
	}
	echo "</div>\n<BR />\n";

	echo "<div id=\"systemLog\">\n";
	echo "</div>\n";

	//echo "</div>\n";
	// Database - close connection
	mysql_close ($lnk);
	
	page_stop ();
?>
