<?php
	function includemce () {
?>
<!-- TinyMCE -->
<script type="text/javascript" src="jscripts/tiny_mce/tiny_mce.js"></script>
<!-- /TinyMCE -->
<?php
	}

	function mce_mode_rw_user () { 
	includemce (); ?>
<script type="text/javascript">
	tinyMCE.init({
		mode : "textareas",
		theme : "advanced",
		plugins : "fullscreen",
		theme_advanced_buttons1 : "bold, italic, underline",
		theme_advanced_buttons2 : "cut, copy",
		theme_advanced_buttons3 : ""
	})
</script>
<?php
	}

	function mce_mode_ro_user () { 
	includemce (); ?>
<script type="text/javascript">
	tinyMCE.init({
		mode : "textareas",
		theme : "advanced",
		plugins : "fullscreen",
		readonly : 1,
		theme_advanced_buttons1 : "",
		theme_advanced_buttons2 : "",
		theme_advanced_butotns3 : ""
	})
</script>

<?php 
	}
?>
