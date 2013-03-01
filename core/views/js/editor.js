$(function(){
	$(document).bind("ready", resizeWindow);
	$(window).bind("resize", resizeWindow);
	
	//
	// - ReOrganize Directory Tree so Dirs always preceed files
	// - Directory Click functionality/toggle
	// - Directory behavior onload if child is currently viewed file
	//
	if ($('#dir-tree-container').length) {
		$('#dir-tree-container').resizable({handles: 'e'});
		$('li.dir').each(function() {
			var t = $(this);
			t.parent().prepend(t);
		});
		$('li.dir span').click(function(){
			if ($(this).hasClass('open')) {
				$(this).removeClass('open');
				$(' > ul', $(this).parent()).css('display','none');
				$('#dir-tree-container .dir-tree').jScrollPane();
			}
			else {
				$(this).addClass('open');
				$(' > ul', $(this).parent()).css('display','block');
				$('#dir-tree-container .dir-tree').jScrollPane();
			}
		});
		$('li.dir ul').has('.current').toggle().siblings('.dirname').addClass('open');
	}
	
	function resizeWindow(e) {
		var h = $(window).height();
		$('#dir-tree-container .dir-tree').height(h-68);
		$('#dir-tree-container .dir-tree').jScrollPane();
		$('#editor-container .CodeMirror-scroll, #editor-container .CodeMirror-gutter').css('min-height', h-68 + 'px');
	}
	
	//
	// Directory Tree File Click behavior - regular click, ctrl-click, and shift-click
	//
	$('#dir-tree-container li.file a.file-btn').click(function(e){
		e.preventDefault();
		if(e.metaKey){
			if ($(this).hasClass('active')) {
				$(this).removeClass('active');
				if ($('#dir-tree-container li.file a.active').length == 1){
					$('#dir-tree-menu .one').show();
				}
				else if ($('#dir-tree-container li.file a.active').length == 0){
					
				}
			}
			else {
				$('#dir-tree-container li.file a.last-clicked').removeClass('last-clicked');
				$(this).addClass('active').addClass('last-clicked');
				if ($('#dir-tree-container li.file a.active').length > 1){
					$('#dir-tree-menu .one').hide();
				}
			}
		}
		else if(e.shiftKey) {
			var t = $(this);
			var above = false;
			var below = false;
			if (t.parent().nextAll(".file:has(.last-clicked)").length) { below = true; }
			if (t.parent().prevAll(".file:has(.last-clicked)").length) { above = true; }
			if(below) { //if last clicked is below this
				t.parent().nextUntil($('li').has('a.active')).children('a.file-btn').addClass('active');
			}
			else if(above) { //if last clicked is above this
				t.parent().prevUntil($('li').has('a.active')).children('a.file-btn').addClass('active');
			}
			$('#dir-tree-container li.file a.last-clicked').removeClass('last-clicked');
			t.addClass('active').addClass('last-clicked');
			if ($('#dir-tree-container li.file a.active').length > 1){
				$('#dir-tree-menu .one').hide();
			}
		}
		else{
			$('#dir-tree-container li.file a.active').removeClass('active');
			$('#dir-tree-container li.file a.last-clicked').removeClass('last-clicked');
			$(this).addClass('active').addClass('last-clicked');
			$('#dir-tree-menu .one').show();
		}
	});
	
	//
	// Directory Tree Menu Operations
	//
	$('#dir-tree-menu .folder').hide();
	$('#dir-menu_open').click(function(){
		var url = $('#dir-tree-container li.file a.active').attr('href');
		window.location = url; // will eventually turn this into ajax using $.load() function -- maybe /#!/ the url so user can send link
	});
	$('#dir-menu_view').click(function(){
		$('#dir-tree-container li.file a.active').each(function() {
			var name = $(this).children('.filename').text();
			var url = $(this).next('.quick-view').attr('href');
			window.open(url, name);
		});
	});
	$('#dir-menu_delete').click(function(){
		var fileList = $('<ul/>');
		$('#dir-tree-container a.active').each(function() {
			fileList.append("<li>" + $('.filename', this).text() + "</li>");
		});
		$.msgbox("Are You Sure? There is No Undo.<br /><ul>"+ fileList.html() +"</ul>", {
			type: "alert", 
			buttons: [
				{type: "submit", value: "continue delete"},
				{type: "cancel", value: "cancel"}
			]
		}, function(result) {
			if (result == 'continue delete') {
				if ($('#dir-tree-container a.active').length > 1) {
					var fileName = new Array();
					$('#dir-tree-container a.active').each(function() {
						var thisName = $(this).children('.filename').text();
						fileName.push( thisName );
					});
				}
				else {
					var fileName = $('#dir-tree-container a.active').children('.filename').text();
				}
				if ($('#dir-tree-container a.active').length > 1) {
					var file = new Array();
					$('#dir-tree-container a.active').each(function() {
						var thisFile = $(this).attr('href');
						thisFile = thisFile.replace('?file=', '')
						file.push( thisFile );
						$(this).parent('li').animate({opacity: 0}, 500, function() { $(this).remove(); });
					});
				}
				else {
					var file = $('#dir-tree-container a.active').attr('href');
					file = file.replace('?file=', '');
					$('#dir-tree-container a.active').parent('li').animate({opacity: 0}, 500, function() { $(this).remove(); });
				}
				var data = {
					  fileName: fileName,
					  file: file,
					  action: 'delete'
					};
				deleteDoc(fileName, window.location, data);
				$('#dir-tree-container .dir-tree li a.active').removeClass('active');
			}
			else {
				$('#dir-tree-container .dir-tree li a.active').removeClass('active');
			}
		});
	});
	
	//
	// Save Doc functionality
	//
	$('#save').click(liveSave);
	
	//
	// right-click menu for dir-tree
	//
	$('#dir-tree-container .dir-tree li a.file-btn').bind("contextmenu", function(event) {
		event.preventDefault();
		$('#dir-tree-overlay').animate({opacity: 'toggle'});
		$('#dir-tree-menu').css({top: event.pageY + "px", left: event.pageX + "px", display: 'block'});
		if (!$(this).hasClass('active') && !$(this).parent().hasClass('active')) {
			$('#dir-tree-container .dir-tree li a.active').removeClass('active');
			$(this).addClass('active').addClass('last-clicked');
			$('#dir-tree-menu .one').show();
		}
	});	
	$(document).bind("click", function(event) {
		if ($("#dir-tree-menu").css('display') == 'block') {
			$('#dir-tree-overlay').animate({opacity: 'toggle'});
			$("#dir-tree-menu").hide();
		}
	});
});

//
// Quick (no-install) Websocket functionality -- initiate in Editor View.
//
function connect(channel, user){

	var socket = new EasyWebSocket(channel);
	
	setInterval(update, 10000)
	var oldEditVal = $('#editor').val();
	var myUser = $('#username').text();
	
	socket.onopen = function(){
		//socket.send("hello world!")
	}
	socket.onmessage = function(event){
		var event = JSON.parse(event.data);
		//if (event.user != myUser) {
			$('#editor').val(event.edits);
			oldEditVal = $('#editor').val();
		//}
	}
	socket.onclose	= function(){
		//$('#editor').append("socket closed.");
	}
	
	var socketSend	= function(data){
		socket.send(JSON.stringify(data));
	}
	
	function update() {
		var thisEditVal = $('#editor').val();
		if (thisEditVal != oldEditVal) {
			alert('theres an update');
			oldEditVal = $('#editor').val();
			socketSend({
				data	: {
					user: myUser,
					edits: oldEditVal
				}
			});
		}
	}
}

function saveDoc(fileName, url, data) {
	$('#save').addClass('saving').text('Saving...');
	$.ajax({  
		type: "POST",  
		url: url,  
		data: data,  
		success: function() {  
			$('#editor-msg').append(fileName + ' is saved!').animate({opacity: 'toggle'}, 250, function() {
				$('#save').text('Saved!');
				$(this).delay(1500).animate({opacity: 'toggle'}, 500, function(){
					$(this).empty();
					$('#save').removeClass('saving').text('Save');
				});
			});
		},
		error: function() {  
			$.msgbox("File did NOT save...", {type: "error"});
			$('#save').removeClass('saving').text('Save');
		}
	}); 
}
function renameDoc(oldFileName, fileName, file, url, data) {
	$('#save').addClass('saving').text('Renaming...');
	$.ajax({  
		type: "POST",  
		url: url,  
		data: data,  
		success: function() {  
			$('#editor-msg').append(oldFileName + ' has been renamed to ' + fileName).animate({opacity: 'toggle'}, 250, function() {
				$('#save').text('Renamed!');
				$('#file').val(file);
				var thisMenuItem = $('a.file-btn.current').closest('li.file');
				
				if(thisMenuItem.length) {
					var href = $('a.file-btn.current').attr('href');
					href = href.replace(oldFileName, fileName);
					$('a.file-btn.current').attr('href', href);
					if ($('.quick-edit', thisMenuItem).length){
						$('.quick-edit', thisMenuItem).attr('href', href);
					}
					if($('.quick-view', thisMenuItem)) {
						href = $('.quick-view', thisMenuItem).attr('href');
						href = href.replace(oldFileName, fileName);
						$('.quick-view', thisMenuItem).attr('href', href);
					}
					$('a.file-btn.current .filename').text(fileName);
				}
				$(this).delay(1500).animate({opacity: 'toggle'}, 500, function(){
					$(this).empty();
					$('#save').removeClass('saving').text('Save');
				});
			});
		},
		error: function(data) {  
			$.msgbox("Could NOT rename file... <br />" + data.responseText, {type: "error"});
			$('#curFile').text(oldFileName);
			$('#save').removeClass('saving').text('Save');
		}
	}); 
}
function deleteDoc(fileName, url, data) {
	$.ajax({  
		type: "POST",  
		url: url,  
		data: data,  
		success: function() {  
			if ($.isArray(fileName)) {
				fileName = 'files';
			}
			$('#editor-msg').append(fileName + ' deleted.').animate({opacity: 'toggle'}, 250, function() {
				$(this).delay(1500).animate({opacity: 'toggle'}, 500, function(){
					$(this).empty();
				});
			});
		},
		error: function() {  
			$.msgbox("File did NOT delete...", {type: "error"});
			$('#save').removeClass('saving').text('Save');
		}
	}); 
}