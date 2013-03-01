Current Feature List:

0. basic operations work!
1. option to grant only cetain email addresses access at login
2. ability to change oEdit folder name to improve security
3. ctrl-s/cmd-s: save
4. fully functioning directory tree and contextual menu
5. drop-in install
6. smart auto save with manual override
7. gutter markers
8. fully functioning editor (numbered lines, gutters, gutter markers, indent lines, syntax/bracket/error highlighting, etc)
9. rename files by simply editing the filename (html5 contenteditable!)

///////////////////////////////////////

To Do (no priority):

4.  line name is hidden if text editor window is too wide - think of solution --- might get rid of name alltogether - rely on color swatch only

7.  make your active line show accurately on another browser (tag: collaboration)

8.  find good way to update textarea live to websocket without destroying text added after the contribution (specific chars on specific line?)
	this is most likely going to be our method: http://codemirror.net/demo/preview.html (watch the content update as you type!!!)
	only requirement I can see is that we can't be listening to our own signal - only that of others.
	though one issue is - you add new, neighbor sends his new before he gets yours. Your new work is deleted... how can we avoid this?...
		This can be avoided for the most part if we don't allow two people to edit the same line - and only update by line...
			What if user copy/pastes? or deletes a bunch of lines - or simply carage returns... ???

12. add chat functionality -- this should be easy.

15. prevent user from selecting an ".active.other" line in the editor
16. add the ability to save the 'untitled' file (prompt for filename & ext?) 
	(prevent auto-save for untitled docs ?)

19. change order and color of 'are you sure to delete' dialogBox options. Cancel should be first - and should be grey.

20. prevent current file from being deleted by contextual menu ???
	
22. allow user to change swatch color (just use simple color picker - on change alter cookie value)

23. check if PHP has ability to add/modify files - if no, immediately alert user to change permissions.

26. add to file-directory context menu:
	- Ability to right-click and copy file-name or file-url
	- Make file/dir
	
27. ability to share gutter markers (could be circle of user-color) 

28. add zen-coding when plugin comes out

29. add last-save date/time to the left of the save button
30. adjust auto-save timer - maybe 5 secons instead of 3?

31. add search/replace functionality
32. add ability to logout (destroy cookie, and reload page)