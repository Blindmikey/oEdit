<?php
/*
 *	Editor View
 */
if ($_SERVER["PHP_SELF"] == $_SERVER['REQUEST_URI']) die ("Tsk Tsk. Nice Try.");
//get $this props
	$dirTree = $this->displayFileTree($tree);
	$curFileName = $this->curFileName;
	$curFileType = $this->curFileType;
	if (!$curFileType) {
		$curFileType = 'php';
	}	
	$curFile = $this->curFile;
	$untitledFile = str_replace(OEDITDIR.'/../','',(str_replace('\\', '/', EDITPATH).'untitled.php'));
	
	if ($curFile) {
		$thisFileContents = $this->getFileContents($curFile);
	}
	elseif (file_exists($untitledFile)) {
		$thisFileContents = $this->getFileContents($untitledFile);
	}
	else { 
		$thisFileContents = false;
	}
	
// vars to auto set in future - temp for now	
	
	if(isset($_COOKIE['username'])) {
		$myUser = $_COOKIE['username'];
	}
	else {
		$myUser = $data['username'];
	}
	if(isset($_COOKIE['username'])) {
		$mySwatch = $_COOKIE['swatch'];
	}
	else {
		$mySwatch = $data['swatch'];
	}
	
?><!DOCTYPE html>
<!--
   ____  ______     ___ __ 
  / __ \/ ____/____/ (_) /_* B E T A 
 / /_/ / __/  / __  / / __/
 \____/ /___ / /_/ / / /_  
     /_____/ \__,_/_/\__/  
~~~~~~~~~~~~~~~~~~~~~~~~~~~
Created by Michael Niles
blindmikey.com
~~~~~~~~~~~~~~~~~~~~~~~~~~~
Many thanks to:
codemirror.net
easywebsocket.org
jquery.com
jqueryui.com
jscrollpane.kelvinluck.com
necolas.github.com/normalize.css/
p.yusukekamiyamane.com

-->
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>oEdit Editor</title>
	<link rel="stylesheet" href="<?php echo STYLESPATH; ?>normalize.css" />
	<link rel="stylesheet" href="<?php echo STYLESPATH; ?>jquery.msgbox.css" />
	<link rel="stylesheet" href="<?php echo CODEMIRRORLIB; ?>codemirror.css">
    <link rel="stylesheet" href="<?php echo CODEMIRRORTHEME; ?>eclipse.css">
	<link rel="stylesheet" href="<?php echo STYLE; ?>" />
	<style>
		.activeline {
			background-color:rgba(<?php echo $mySwatch; ?>, .25) !important;
			}
		.activeline:after {
			background-color: rgb(<?php echo $mySwatch; ?>);
			}
		.activeline:hover:after {
			content: '<?php echo $myUser;  ?>';
			}
		#Users .<?php echo str_replace(' ','-',strtolower($myUser));  ?> .swatch {
			background-color:rgb(<?php echo $mySwatch; ?>);
			}
	</style>
</head>
<body class="editor" spellcheck="false">
	<header>
		<a href="<?php echo LOCPATH; ?>" id="logo"><span>oEdit</span></a>
		<span id="curFile" contenteditable="true"><?php if($curFileName){?><?php echo $curFileName; ?><?php } else { ?>untitled.php<?php } ?></span>
		<button id="save">save</button>
		<!--<div id="fullscreen"></div>-->
		<ul id="Users">
			<li class="<?php echo str_replace(' ','-',strtolower($myUser));  ?>">
				<em class="swatch"></em><?php echo $myUser;  ?>
			</li>
		</ul>
	</header>
	<div id="container">
		<nav id="dir-tree-container">
			<?php echo $dirTree; ?>
			<div id="dir-tree-overlay"></div>
		</nav>
		<div id="editor-container">
			<div id="editor-msg"></div>
			<form action="<?php echo LOCPATH; ?>" method="post">
				<input type="hidden" id="file" name="file" value="<?php if($curFile) {echo $curFile;} else {echo ABSPATH.'../untitled.php';}?>" />
				<textarea name="editor" id="editor" cols="30" rows="10"><?php 
				
					if($thisFileContents) {
						echo htmlspecialchars($thisFileContents);
					}
				
				?></textarea>
			</form>
		</div>
	</div>
	<div id="dir-tree-menu">
		<ul>
			<li><button value="open" id="dir-menu_open" class="one">Edit</button></li>
			<li><button value="view" id="dir-menu_view" class="multi">View</button></li>
			<li><button value="delete" id="dir-menu_delete" class="multi">Delete</button></li>
			<!--<li><button value="cut" id="dir-menu_cut" class="multi">Cut</button></li>
			<li><button value="copy" id="dir-menu_copy" class="multi">Copy</button></li>
			<li><button value="paste" id="dir-menu_paste" class="folder">Paste</button></li>-->
		</ul>
	</div>
	<script src="<?php echo JSPATH; ?>jquery.js"></script>
	<script src="<?php echo JSPATH; ?>jquery-ui.js"></script>
	<script src="<?php echo JSPATH; ?>mousewheel.js"></script>
	<script src="<?php echo JSPATH; ?>jscrollpane.js"></script>
	<script src="<?php echo JSPATH; ?>jquery.msgbox.min.js"></script>
    <script src="<?php echo CODEMIRRORLIB; ?>codemirror.js"></script>
    <!--<script src="<?php echo CODEMIRRORLIB; ?>zen.js"></script>-->
    <script src="<?php echo CODEMIRRORMODE; ?>xml/xml.js"></script>
    <script src="<?php echo CODEMIRRORMODE; ?>javascript/javascript.js"></script>
    <script src="<?php echo CODEMIRRORMODE; ?>css/css.js"></script>
    <script src="<?php echo CODEMIRRORMODE; ?>clike/clike.js"></script>
    <script src="<?php echo CODEMIRRORMODE; ?>php/php.js"></script>
    <script src="<?php echo CODEMIRRORMODE; ?>htmlmixed/htmlmixed.js"></script>
	<script src="<?php echo JSPATH; ?>socket.js"></script>
	<script src="<?php echo JSPATH; ?>editor.js"></script>
	<script>	
		var fileName;
		var oldFileName;
		var newContent;
		var data;
		var file;
		var delay;
		var lastSaveTime = 0;
		var thisSaveTime;
		var saveDelay = 10000;
		var badExp = /[^A-Za-z0-9_\-.]/;
		//connect("<?php echo $this->baseurl('ws') . LOCPATH . 'index.php'; ?>"); // general connection for chat -- need another for specifically viewed file.
		var editor = CodeMirror.fromTextArea(document.getElementById("editor"), {
			lineNumbers: true,
			matchBrackets: true,
			mode: '<?php 
			if ($curFileType == 'php') { echo 'application/x-httpd-php';}
			elseif ($curFileType == 'js') { echo 'text/javascript'; }
			elseif ($curFileType == 'htm' || $curFileType == 'html') { echo 'text/html'; }
			elseif ($curFileType == 'css') {echo 'text/css';}
			?>',
			theme: 'eclipse',
			tabSize: 4,
			indentUnit: 4,
			indentWithTabs: true,
			onChange: function(editor) {
				clearTimeout(delay);
				delay = setTimeout(liveSave, saveDelay);
			},
			extraKeys: {
				"Ctrl-S": liveSave,
				"Cmd-S": liveSave,
			},
			onGutterClick: function(cm, n) {
				var info = cm.lineInfo(n);
				if (info.markerText)
					cm.clearMarker(n);
				else
					cm.setMarker(n, "<span class='marker'></span> %N%");
			},
			onCursorActivity: function() {
				editor.setLineClass(hlLine, null);
				hlLine = editor.setLineClass(editor.getCursor().line, "activeline");
			}<?php if ($curFileType == 'php' || $curFileType == 'htm' || $curFileType == 'html' || $curFileType == 'css') { ?>,
			// Zen Coding stuff
			<?php if ($curFileType == 'css') {?>
			//syntax: 'css',
			<?php } else { ?>
			//syntax: 'html',
			<?php } ?>
			//onLoad: zen_editor.bind(editor)
			<?php } ?>
		});
		var hlLine = editor.setLineClass(0, "activeline");
	
		$(function() {
			$('#curFile').bind('blur keypress', function(e){
				if (e.keyCode == '13') {
					e.preventDefault();
					e.cancelBubble = true;
					e.returnValue = false;
					if(badExp.test($('#curFile').text())) {
						$(this).designMode = 'off';
						if($(this).hasClass('editing')) {
							$(this).text(oldFileName);
						}
						$(this).removeClass('editing').blur();
						$.msgbox("Not a valid filename...", {type: "error"}); // add this same security on the backend
					}
					else {						
						$(this).designMode = 'off';
						$(this).removeClass('editing').blur();
						renameFile();
					}
				}
				if (e.type == 'blur') {
					$(this).designMode = 'off';
					if($(this).hasClass('editing')) {
						$(this).text(oldFileName);
					}
					$(this).removeClass('editing');
				}
			});
			$('#curFile').focus(function(){
				if (!$(this).hasClass('editing')) {
					$(this).designMode = 'on';
					$(this).addClass('editing');
					oldFileName = $(this).text();
				}
			});
		});
			
		function liveSave(){
			thisSaveTime = new Date().getTime();
			if (thisSaveTime > (lastSaveTime + saveDelay + 1000)) {
				lastSaveTime = thisSaveTime;
				fileName = $('#curFile').text();
				file = $('#file').val();
				newContent = editor.getValue();
				data = {
					fileName: fileName,
					file: file,
					action: 'save',
					content: newContent
				};
				saveDoc(fileName, window.location, data)
			}
		}
		function renameFile(){
			fileName = $('#curFile').text();
			oldFile = $('#file').val();
			file = oldFile.replace(oldFileName, fileName);
			newContent = editor.getValue();
			data = {
				fileName: fileName,
				oldFileName: oldFileName,
				oldFile: oldFile,
				file: file,
				action: 'rename',
				content: newContent
			};
			renameDoc(oldFileName, fileName, file, window.location, data)
		}
	</script>
</body>
</html>