<?php
/**
 * oEdit Controller
 * 
 * @version 	1.0
 * @author 		Michael Niles (michael@blindmikey.com)
 * @link 		http://www.blindmikey.com/oEdit/
 * @copyright 	Michael Niles 2011
 */
class oEdit {
	/********************* PROPERTY ********************/
	
	private $Explorer;
	
	public $curFile;
	public $curFileName;
	public $curFileType;
	
	/******************** CONSTRUCT ********************/
	
	/**
	 * Constructor
	 * 
	 * @access 	public
	 * @return 	void
	 */
	public function __construct(){
		require_once(MODELPATH .'explorer.class.php');
		$this->Explorer = new Explorer();
	}
	
	/********************* PRIVATE *********************/
	
	/**
	 * Fetches File Tree
	 * 
	 * Retrieves Directory Structure from neighbor folders, excluding the oEdit folder.
	 *
	 * @access 	private
	 * @param	string $dir The file or directory path
	 * @return 	string
	 */
	private function getFileTree($dir) {
		$tree = $this->Explorer->SetPath($dir);
		$tree = $this->Explorer->Listing(array(), array(), array(OEDITDIR, '.git'), true);
		return $tree;
	}
	
	/**
	 * Creates Random Number
	 *
	 * @access 	private
	 * @param	int $from Lowest Number
	 * @param	int $to Heighest Number
	 * @return 	int
	 */
	private function randomNumber($from, $to) {
		return rand( $from, $to );
	}
	
	/********************* PUBLIC **********************/
	
	// TODOs :
	// add logout functionality (destroy cookies - deliver login view)
	// move $tree to become $data in 'display' method
	// fix how directories are handled (issue: if last directory ended - new directory is assumed to be only one level under - even if it isn't )
	
	/**
	 * Controls login process
	 * 
	 * @access 	public
	 * @param	array $emailFilter Whitelist of emails
	 * @return 	void
	 */
	public function isLoggedIn($emailFilter){
		$password = PASSWORD;
		if(LOCALHOST){
			$uri = false;
		}
		else {
			$uri = $_SERVER['HTTP_HOST'];
		}
		
		if(!empty($password)) {
			if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['email'])) {	
			
				$user = $_POST['username'];
				$email = $_POST['email'];	
				$pass = $_POST['password'];		
				
				if ($pass == $password) {    
					if (!empty($emailFilter)) {
						if (!in_array($email, $emailFilter)) {
							$this->display('login', array('username'=>true, 'email'=>true, 'password'=>true, 'message'=>'email not allowed: '. __LINE__));
						}
						else {
							//SETTING THE COOKIES //Cookies expires when browser closes 
							setcookie('username', $user, false, '/', $uri);
							setcookie('password', md5($pass), false, '/', $uri);
							setcookie('email', ($email), false, '/', $uri);
							$swatch = $this->randomColor();
							setcookie('swatch', $swatch, false, '/', $uri);
							
							$this->display('editor', array('username'=>$_POST['username'], 'swatch'=>$swatch));
						}
					}
					else {
						//SETTING THE COOKIES //Cookies expires when browser closes 
						setcookie('username', $user, false, '/', $uri);
						setcookie('password', md5($pass), false, '/', $uri);
						setcookie('email', ($email), false, '/', $uri);
						$swatch = $this->randomColor();
						setcookie('swatch', $swatch, false, '/', $uri);
						
						$this->display('editor', array('username'=>$_POST['username'], 'swatch'=>$swatch));
					}
				} 
				else {
					if (empty($emailFilter)) {
						$this->display('login', array('username'=>true, 'password'=>true, 'message'=>'password wrong '. __LINE__));
					}
					else {
						$this->display('login', array('username'=>true, 'email'=>true, 'password'=>true, 'message'=>'password wrong '. __LINE__));
					}
				}				
			}
			elseif (isset($_COOKIE['username']) && isset($_COOKIE['password']) && isset($_COOKIE['email'])) {
			
				$email = $_COOKIE['email'];
				
				if ($_COOKIE['password'] == md5(PASSWORD)) {    
					if (!empty($emailFilter)) {
						if (!in_array($email, $emailFilter)) {
							$this->display('login', array('username'=>true, 'email'=>true, 'password'=>true, 'message'=>'email not allowed '. __LINE__));
						}
						else {
							$this->display('editor');
						}
					}
					else {
						$this->display('editor');
					}
				} 
				else {
					$this->display('login', array('username'=>true, 'email'=>true, 'password'=>true, 'message'=>'cookie password was wrong '. __LINE__));
				}				
			} 
			else {
				if (empty($emailFilter)) {
					$this->display('login', array('username'=>true, 'password'=>true, 'message'=>'no cookie found '. __LINE__));
				}
				else {
					$this->display('login', array('username'=>true, 'email'=>true, 'password'=>true, 'message'=>'no cookie found '. __LINE__));
				}
			}
		}
		else {
			if (isset($_COOKIE['username'])) {
				$this->display('editor');
			}
			elseif (isset($_POST['username'])) {
				setcookie('username', $_POST['username'], false, '/', $uri);
				$swatch = $this->randomColor();
				setcookie('swatch', $swatch, false, '/', $uri);
				
				$this->display('editor', array('username'=>$_POST['username'], 'swatch'=>$swatch));
			}
			else {
				$this->display('login', array('username'=>true, 'message'=>'need username '. __LINE__));
			}
		}
	}
	
	/**
	 * Puts together an rgb value
	 * 
	 * @access 	public
	 * @return 	int eg) 149, 210, 198
	 */
	public function randomColor() {
		return $this->randomNumber(140, 230) .', '. $this->randomNumber(140, 230) .', '. $this->randomNumber(140, 230);
	}
	
	/**
	 * Checks if user requests to modify file - modifies accordingly
	 * 
	 * @access 	public
	 * @param	array $post_arr Posted array
	 * @return 	void
	 */
	public function didFileChange($post_arr){
		
		// continue only if file data and an action was posted
		if (isset($post_arr['file']) && isset($post_arr['action'])) {
			// SAVE
			if ($post_arr['action'] == 'save') {
				$file = $post_arr['file'];
				$this->Explorer->SetPath($file);
				if (!file_exists($file)) { //if file does NOT exist - create
					$this->Explorer->Create();
				}
				$content = $post_arr['content'];
				if ($content == '') {
					$this->Explorer->Write('//empty file'); // php throws fit if it opens a truly empty file
				}
				else {
					$this->Explorer->Write(stripslashes($content));
				}
			}
			// RENAME
			if ($post_arr['action'] == 'rename') {
				$file = $post_arr['oldFile'];
				$newFile = $post_arr['file'];
				
				if (!file_exists($newFile)) { //if file does NOT exist - create
					$this->Explorer->SetPath($newFile);
					$this->Explorer->Create();
					
					$this->Explorer->SetPath($file); // delete old file
					$this->Explorer->Delete();
				}
				else { //if file DOES exist - return error
					header('HTTP/1.1 500 Internal Server Error');
					header('Content-Type: application/json');
					die('File already exists with that name');
				}
				$this->Explorer->SetPath($newFile);
				$content = $post_arr['content'];
				if ($content == '') {
					$this->Explorer->Write('//empty file'); // php throws fit if it opens a truly empty file
				}
				else {
					$this->Explorer->Write(stripslashes($content));
				}
			}
			// DELETE
			if ($post_arr['action'] == 'delete') {
				$file = $post_arr['file'];
				if (is_array($file)) {
					foreach ($file as $thisFile) {
						$this->Explorer->SetPath($thisFile);
						$this->Explorer->Delete();
					}
				}
				else {
					$this->Explorer->SetPath($file);
					$this->Explorer->Delete();
				}
			}
		}
	}
	
	/**
	 * Returns the current base URL
	 * 
	 * @access 	public
	 * @param	string $protocol The transfer protocol, http, ftp, ws, etc.
	 * @return 	string
	 */
	public function baseurl($protocol = NULL){
		if (!$protocol) {
			$protocol = $_SERVER['HTTPS'] ? "https" : "http";
		}
		return $protocol . "://" . $_SERVER['HTTP_HOST'];
	}
	
	/**
	 * Retrieves File Contents
	 * 
	 * @param	string $filepath The file or directory path
	 * @access 	public
	 * @return 	string
	 */
	public function getFileContents($filepath) {
		$this->Explorer->SetPath($filepath);
		if (!filesize($filepath) > 0) {
			$this->Explorer->Write('//empty file'); 
			return '//empty file';
		}
		else {
			return $this->Explorer->Read();
		}
	}
	
	/**
	 * Displays the requested view
	 * 
	 * @param	string $view The view to be fetched. Editor fetched if none is specified.
	 * @param	mixed $data Any additional data to be passed onto the view
	 * @access 	public
	 * @return 	void
	 */
	public function display($view = null, $data = '') {
		if($view) {
			if (file_exists(VIEWPATH . $view . '.php')) {
				$tree = $this->getFileTree(EDITPATH); // TODO: move this to become $data in 'display' method
				include(VIEWPATH . $view . '.php');
			}
			else {
				die('The view "'.$view.'" does not exist...');
			}
		}
		else {
			$tree = $this->getFileTree(EDITPATH);
			require_once(VIEWPATH . 'editor.php'); // TODO: move this to become $data in 'display' method
		}
	}
		
	/**
	 * Parses and displays the file tree html
	 * 
	 * @param	array $tree The directory tree array
	 * @access 	public
	 * @return 	string
	 */
	public function displayFileTree($tree = array()) { // TODO: change how directories are handled - breaks when nested > 3
		$get_file = $_GET;
		$root = str_replace('\\', '/', EDITPATH);
		$lastDirPath = $root;
		$nestRootCount = substr_count($root, '/');
		$nestCount = $nestRootCount;
		$result = '<ul class="dir-tree">';
		
		date_default_timezone_set('US/Central');
		foreach ($tree as $branch) {
			if (!isset($dirName)) {
				$dirName = '/';
			}
			
			// get directories - then get files in those directories
			if ($branch['type'] == 'dir') {
				$dirName = basename($branch['fullpath']);
				//if item is out of last dir prepend with '</ul>' (taking into consideration nested dirs)
				if (($branch['fullpath'] . '/' != $lastDirPath) && ($branch['fullpath'] . '/' != $lastDirPath . $dirName . '/') && ($lastDirPath != $root)) {
					$result .= '</ul>';
					$lastDirPath = $branch['fullpath'] . '/';
					$thisNestLevel = substr_count($branch['fullpath'], '/');
					if ($nestCount > $thisNestLevel) {
						while ($nestCount > $thisNestLevel + 1) { 
							$result .= '</ul>';
							$nestCount = $nestCount - 1;
						}
					}
				}
				else {
					$nestCount = $nestCount + 1;
					$lastDirPath = $lastDirPath . $dirName .'/';
				}
				$result .= '<li class="dir"><span class="dirname">' . $dirName . '</span><ul>';
				foreach($tree as $item) {
					$aClass = '';
					if ($item['type'] == 'file' && $item['path'] == $branch['fullpath'] . '/') {
						$quickView = str_replace( OEDITDIR . '/','',LOCPATH) . str_replace('../','', str_replace(str_replace('\\', '/', ABSPATH),'',$item['fullpath']));
						$fullpath = str_replace(OEDITDIR.'/../','',$item['fullpath']);
						if (isset($get_file['file'])) {
							if ($fullpath == $get_file['file']) {
								$aClass = ' current';
								$this->curFile = $fullpath;
								$this->curFileName = $item['filename'];
								$this->curFileType = $item['extension'];
							}
						}
						$result .= '<li class="file ' . $item['extension'] . '"><a class="file-btn'. $aClass .'" href="?file='. $fullpath .'"><em class="ext">' . $item['extension'] . '</em><span class="filename">' . $item['filename'] . '</span></a><a href="'. $quickView .'" target="_blank" class="quick-view"></a><a href="?file='. $fullpath .'" class="quick-edit"></a></li>';
					}
				}
			}
		}
		
		// seal up the ULs
		if ($nestCount > $nestRootCount) {
			while ($nestCount > $nestRootCount) { 
				$result .= '</ul>';
				$nestCount = $nestCount - 1;
			}
		}
		
		// now get all files in root dir
		foreach ($tree as $branch) {
			$aClass = '';
			if (!isset($dirName)) {
				$dirName = '/';
			}
			if ($branch['type'] == 'file' && $branch['path'] == $root) {
				$quickView = str_replace( OEDITDIR . '/','',LOCPATH) . str_replace('../','', str_replace(str_replace('\\', '/', ABSPATH),'',$branch['fullpath']));
				$fullpath = str_replace(OEDITDIR.'/../','',$branch['fullpath']);
				$untitledFile = str_replace(OEDITDIR.'/../','',(str_replace('\\', '/', EDITPATH).'untitled.php'));
				if (isset($get_file['file'])) {
					if ($fullpath == $get_file['file']) {
						$aClass = ' current';
						$this->curFile = $fullpath;
						$this->curFileName = $branch['filename'];
						$this->curFileType = $branch['extension'];
					}
				}
				elseif (file_exists($untitledFile)) {
					if ($fullpath == $untitledFile) {
						$aClass = ' current';
						$this->curFile = $fullpath;
						$this->curFileName = $branch['filename'];
						$this->curFileType = $branch['extension'];
					}
				}
				$result .= '<li class="file ' . $branch['extension'] . '"><a class="file-btn'. $aClass .'" href="?file='. $fullpath .'"><em class="ext">' . $branch['extension'] . '</em><span class="filename">' . $branch['filename'] . '</span></a><a href="'. $quickView .'" target="_blank" class="quick-view"></a><a href="?file='. $fullpath .'" class="quick-edit"></a></li>';
			}
		}
		$result .= '</ul><!--/dir-tree-->';
		return $result;
	}
}