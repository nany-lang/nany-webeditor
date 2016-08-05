<!DOCTYPE html>
<html>
  <head>
	<title>Nany Web Editor</title>
	<meta charset="utf-8">
	<!-- Stylesheets -->
	<link href="lib/jquery-ui/jquery-ui.min.css" rel="stylesheet">
	<link href="lib/jstree-themes/default/style.min.css" rel="stylesheet">
	<link href="lib/dropzone.min.css" rel="stylesheet">
	<link href="lib/codemirror/lib/codemirror.css" rel="stylesheet">
	<link href="lib/codemirror/theme/nany-light.css" rel="stylesheet">
	<link href="lib/codemirror/addon/fold/foldgutter.css" rel="stylesheet">
	<link href="style.css" rel="stylesheet">
	<!-- UI libraries -->
	<script type="text/javascript" src="lib/jquery-2.1.4.min.js"></script>
	<script type="text/javascript" src="lib/jquery-ui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="lib/jstree.min.js"></script>
	<script type="text/javascript" src="lib/dropzone.min.js"></script>
	<script type="text/javascript" src="lib/spin.min.js"></script>
	<script type="text/javascript" src="lib/jquery.spin.js"></script>
	<script type="text/javascript" src="lib/codemirror/lib/codemirror.js"></script>
	<script type="text/javascript" src="lib/codemirror/addon/edit/matchbrackets.js"></script>
	<script type="text/javascript" src="lib/codemirror/addon/edit/trailingspace.js"></script>
	<script type="text/javascript" src="lib/codemirror/addon/fold/foldcode.js"></script>
	<script type="text/javascript" src="lib/codemirror/addon/fold/foldgutter.js"></script>
	<script type="text/javascript" src="lib/codemirror/addon/fold/brace-fold.js"></script>
	<script type="text/javascript" src="lib/codemirror/addon/fold/comment-fold.js"></script>
	<script type="text/javascript" src="lib/codemirror/mode/nany/nany.js"></script>
  </head>

  <body>
	<div id="maintitle" class="title"><h1>Nany Web Editor</h1></div>
	<div id="subtitle" class="title"><h1>Discover Nany now !</h1></div>

	<div id="sample-list" class="ui-widget ui-widget-content ui-corner-all"><ul></ul></div>

	<div id="editors">
	  <button id="new-file">+</button>
	  <div id="editor-tabs">
		<ul>
		  <li class="editor-tab"><a href="#tab1">New</a></li>
		</ul>

		<form id="tab1">
		  <textarea id="editor1"></textarea>
		</form>
	  </div>
	</div>

	<!-- UI -->
	<script>
	  // Create a CodeMirror editor with given ID
	  function createEditor(id) {
		var textArea = document.getElementById(id);
		var cmEditor = CodeMirror.fromTextArea(textArea, {
			lineNumbers: true,
			mode: "nany",
			theme : "nany-light",
			extraKeys: {"Ctrl-Q": function(cm){ cm.foldCode(cm.getCursor()); }},
			matchBrackets: true,
			showTrailingSpace: true,
			foldGutter: true,
			gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"]
		});
		textArea.style.display = "none";
		return cmEditor;
	  }

	  var editors = new Array;
	  editors[1] = createEditor("editor1");

	  $(function() {
		var tabTitle = "New";
		var tabContent = "<form id='tab#{index}'><textarea id='editor#{index}'></textarea></form>";
		var tabTemplate = "<li><a href='#{href}'>#{label}</a> <span class='ui-icon ui-icon-close' role='presentation'>Remove Tab</span></li>";
		var tabCounter = 2;

		// Declare jQuery-UI tabs
		var tabs = $("#editor-tabs").tabs({
			// On select :
			activate: function(event, ui) {
				/*var oldEditor = document.getElementById("editor" + (ui.oldTab.index() + 1));
				oldEditor.nextSibling.style.display = "none";
				var newEditor = document.getElementById("editor" + (ui.newTab.index() + 1));
				newEditor.nextSibling.style.display = "block";*/
			}
		});

		// Add a new tab
		function addTab() {
			var label = "New (" + tabCounter + ")";
			var li = $(tabTemplate.replace(/#\{href\}/g, "#tab" + tabCounter).replace(/#\{label\}/g, label));
			tabContentHtml = tabContent.replace(/#\{index\}/g, tabCounter);

			tabs.find(".ui-tabs-nav").append(li);
			tabs.append(tabContentHtml);
			//tabs.select(tabCounter - 1);
			tabs.tabs("refresh");
			editors[tabCounter] = createEditor("editor" + tabCounter);
			tabCounter++;
		}

		// + button: create a new tab
		$("#new-file").button().on("click", function() {
			addTab();
		});

		// Close icon: removing the tab on click
		tabs.on("click", "span.ui-icon-close", function() {
			var panelId = $(this).closest("li").remove().attr("aria-controls");
			$("#" + panelId).remove();
			tabs.tabs("refresh");
		});

		// Alt-BACK also closes the current tab
		tabs.on("keyup", function(event) {
			if (event.altKey && event.keyCode === $.ui.keyCode.BACKSPACE) {
				var panelId = tabs.find(".ui-tabs-active").remove().attr("aria-controls");
				$("#" + panelId).remove();
				tabs.tabs("refresh");
			}
		});
	  });

	  <?php
		 $sampleDir = "./samples";
		 $files = scandir($sampleDir);
	  ?>

	  // Sample list is a jsTree
	  // Fill the sample list using the contents of the "samples/" folder retrieved by PHP
	  $(function() {
		var sampleIndex = 1;
		//var entryTemplate = "<option value='samples/#{href}'>#{label}</option>";
		var entryTemplate = "<li id='#{href}'><a href='#{href}'>#{label}</a></li>";
		var fileList = <?php echo '["' . implode('", "', $files) . '"]' ?>;
		$.each(fileList, function(i, item) {
			if (item !== "." && item !== "..") {
				var entry = $(entryTemplate.replace(/#\{href\}/g, item).replace(/#\{label\}/g, item.replace(/.ny/, "")));
				$("#sample-list").find("ul").append(entry);
			}
		});
	  // Register the sample-list as a jsTree and add a selection changed listener
	  $("#sample-list").jstree({
		"core": {
			"themes": {
				"name": "default"
			},
			"multiple": false
		}
	  }).bind("select_node.jstree", function(e, data) {
			loadFileInEditor("samples/" + data.node.id);
			$("ul > .ui-tabs-active > a").text(data.node.id);
		});
	  });

	  function activeEditor() {
		return editors[activeTabIndex() + 1];
	  }
	  
	  function activeTabIndex() {
		return $("#editor-tabs").tabs("option", "active");
	  }
	  
	  function activeTabID() {
		return $("ul > .ui-tabs-active").attr("aria-controls");
	  }
	  
	  function loadTextInEditor(text) {
		  activeEditor().getDoc().setValue(text);
	  }

	  function loadFileInEditor(filePath) {
		$.ajax({
			url : filePath,
			dataType: "text",
			success : function (data) {
				loadTextInEditor(data);
			}
		});
	  }
	</script>

  </body>
</html>
