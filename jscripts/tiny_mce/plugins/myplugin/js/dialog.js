tinyMCEPopup.requireLangPack();
var ExampleDialog = {
	init : function() {
		var f = document.forms[0];

		// Get the selected contents as text and place it in the input
		f.someval.value = tinyMCEPopup.editor.selection.getContent({format : 'text'});
		f.somearg.value = tinyMCEPopup.getWindowArg('some_custom_arg');
	},

	insert : function() {
		// Insert the contents from the input into the document
		// A to sie zmieni, bo jest brzydkie... pizza byla pyszna (4 dec 2010)
		tinyMCEPopup.editor.execCommand('mceInsertContent', false, "<label READONLY class=\"task_editor_textbox\" style=\"padding: 2px; background-color: #DDD; border : 1px solid gray; width: 200px;\" value=\""+document.forms[0].someval.value+"\">[EDIT] Pole edycyjne</label>");
		tinyMCEPopup.close();
	},
	
	insert_editor : function() {
		tinyMCEPopup.editor.execCommand('mceInsertContent', false, "<p><label READONLY class=\"task_editor_textbox\" style=\"padding: 2px; background-color: #DDD; border : 1px solid gray; width: 200px;\" value=\""+document.forms[0].someval.value+"\">[MCE] Edytor tekstu</label></p>");
		tinyMCEPopup.close();
	}
};

tinyMCEPopup.onInit.add(ExampleDialog.init, ExampleDialog);
