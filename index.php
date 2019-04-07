<?php

error_reporting(E_ALL);

session_start();

class BookLibrary {

	CONST BOOKCASE = "./Library.json";
	CONST DEPTH	= 1024;
	CONST MAXWEIGHT = 10000;
	
	public function __construct() {
		$incomplete = false;
	}
	/**
	* Sanitizes user-input
	* @param string
	* @return string
	*/
	public function cleanInput($string) 
	{
		return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
	}
	/**
	* Encodes JSON object
	* @param book
	* @return void
	*/
	public function encode($book) 
	{
		return json_encode($book, JSON_PRETTY_PRINT);
	}
	/**
	* Loads and decodes JSON object
	* @return mixed object/array
	*/
	public function decode() 
	{
		return json_decode(file_get_contents(self::BOOKCASE), true, self::DEPTH, JSON_BIGINT_AS_STRING);
	}

	public function addBook() 
	{
		$newbook = 
				array(
				"title" => "{$this->titlebook}", 
				"isbn" => "{$this->isbnbook}",
				"weight" => "{$this->weightbook}",
				"description" => "{$this->introbook}"
		);
		$lijst = $this->decode();
		$i = count($lijst);
		if($i >=1) {
			usort($lijst, $this->sortISBN('isbn'));
			array_push($lijst,$newbook);
			} else {
			$lijst = array($newbook);
		}
		$this->storeBook($lijst);
	}

	public function editBook($id) 
	{
		// todo! 
	}

	public function checkForm() 
	{
		isset($_POST['title']) ? 		$this->titlebook = $this->cleanInput($_POST['title']) : $titlebook = false;
		isset($_POST['isbn']) ? 		$this->isbnbook = $this->cleanInput($_POST['isbn']) : $isbnbook = false;
		isset($_POST['weight']) ? 		$this->weightbook = $this->cleanInput($_POST['weight']) : $weightbook = false;
		isset($_POST['description']) ?  $this->introbook = $this->cleanInput($_POST['description']) : $introbook = false;

		$_SESSION['messages'] = array();

		if($this->titlebook != false) {
			if(strlen($this->titlebook) > 60 ) {
				$this->message('Title may not be longer than 60 characters.');
				return false;
			}
		}  else {
				$this->message('Title may not be empty.');
				return false;
		}

		if($this->isbnbook != false) {
			if(!preg_match("/[a-zA-Z]/i",$this->isbnbook)) {  
				if(strlen($this->isbnbook) > 13 || strlen($this->isbnbook) < 13) { 
					$this->message('ISBN number is wrong. (13 digits.)');
					return false;
				}
			} else {
					$this->message('ISBN number may only contain numbers!');
					return false;
			}
		} 

		if($this->weightbook != false) {
			if(!is_int((int)$this->weightbook) || preg_match("/[a-zA-Z]/i",$this->weightbook)) { 
				$this->message('Weight may not contain characters.');
				return false;
			}
		}  else {
				$this->message('Weight must not be empty.');
				return false;
		}

		if($this->introbook != false) {
			if(strlen($this->introbook) > 60 ) {
				$this->message('Description may not be longer than 60 characters.');
				return false;
			}
		}  else {
				$this->message('Description magy not be empty.');
				return false;
		}

	}

	public function tooHeavy($weight) 
	{
		return ($weight > self::MAXWEIGHT) ? true:false;
	}

	public function message($value) 
	{
		if(isset($_SESSION['messages'])) { 
			array_push($_SESSION['messages'],$value);  
			} else { 
			$_SESSION['messages'] = array(); 
		} 	
	}

	public function showmessage() 
	{ 
		if(isset($_SESSION['messages'])) { 
			echo "<pre>"; 
			echo "<strong>Message:</strong>\r\n"; 
			foreach($_SESSION['messages'] as $message) { 
				echo $message . "\r\n" ; 
			} echo "</pre>"; 
		} 
		$_SESSION['messages'] = array();
	} 

	/**
	* Store book into BookLibrary
	* @param array $book
	* @return boolean, true for success, false for failure.
	*/
	public function storeBook($book) 
	{
		file_put_contents(self::BOOKCASE, $this->encode($book));
	}

	public function deleteBook($book) 
	{
		$lijst = $this->decode();
		if($lijst !== null) {
			$libraylist = usort($lijst, $this->sortISBN('isbn'));
			$books = array();
			foreach($lijst as $c) {	
				echo $book."<br>";
				if($c['isbn'] != $book) {
					array_push($books,$c);
				}
			}
		}
		$this->storeBook($books);
	}

	public function sortISBN($key) {
		return function ($a, $b) use ($key) {
			return strnatcmp($a[$key], $b[$key]);
		};
	}
}

	$thelibrary  = new BookLibrary();
	$libraylist = $thelibrary->decode();

	if(isset($_POST['addbook']) == '1') {
		$check = $thelibrary->checkForm();
		if($check !== false) {
			$thelibrary->addBook(); 
			} else {
			$thelibrary->showmessage();
		}
	} 

	if(isset($_POST['editbook'])) {
		$check = $thelibrary->checkForm();
		if($check !== false) {
			$thelibrary->editBook($_POST['editbook']); 
			} else {
			$thelibrary->showmessage();
		}
	} 

	if(isset($_GET['remove'])) {
		$thelibrary->deleteBook($_GET['remove']); 
	} 

?>

<html>

	<head>
	<link rel="stylesheet" type="text/css" href="style.css">
	</head>
	<body>
		<h1>The BookLibrary.</h1>

		<div id="BookLibrary">
			<?php 

				$thelibrary  = new BookLibrary();
				$lijst = $thelibrary->decode();

				if($lijst !== null) {

					$libraylist = usort($lijst, $thelibrary->sortISBN('isbn'));
					$books = array();
					$heavybooks = array();
					$weightplank = 0;
					
					$i = 0;
						foreach($lijst as $c) {	
							if($thelibrary->tooHeavy($c['weight']) == true) {
								array_push($heavybooks,$c);
								} else {
								array_push($books,$c);
							}
							$thelibrary->cleanInput($c['title']);
							$i++;
						}
					
					echo '<table border="1" class="BookLibrary" cellpadding="0" cellspacing="5" width="300" height="700">';
					echo '<tr><td height=\"150\">';
					$i = count($books)-1;
					if($i >= 0) { 
						while($i >= 0) {
							if(($weightplank + $books[$i]['weight']) > $thelibrary::MAXWEIGHT) {
								echo "</td></tr><tr><td height=\"150\">";
								$weightplank	= 0;
								} else {
								echo "<div class=\"rood\"><a href=\"?view=".$i."\" alt=\"view book\">".$thelibrary->cleanInput($books[$i]['title']).' - '.$books[$i]['weight']."</a></div>";
								$weightplank = ($weightplank + $books[$i]['weight']);
							}
						$i--;
						}
					}
					echo '</table>';

				} else {
					echo "<p class='book'><em>BookLibrary is empty...</em></p>";
				}
			?>
		

		<?php

		if(isset($_REQUEST['view'])) {
				$id = (int) $_REQUEST['view'];	
				echo "<div class=\"cover\">"; 
				echo "<a href=\"?edit=".$id."&view=".$id."\" alt=\"edit book\">edit</a> | <a href=\"?remove=".$thelibrary->cleanInput(ucfirst($books[$id]['isbn']))."\" alt=\"remove book\">remove</a>";
				echo "<p>".$thelibrary->cleanInput(ucfirst($books[$id]['title']))."</p>";
				echo "</div>";
			}

		if(isset($_REQUEST['edit'])) {
		$id = (int)$_REQUEST['edit'];			
		?>
		<h1>Edit book.</h1>
			<form action="" method="post">
				<fieldset>
				<label>Book title:</label><br />
				<input type = "text" name="title" size="80" value="<?= $thelibrary->cleanInput($books[$id]['title']); ?>" />
				<label>ISBN nummer:</label><br />
				<input type = "text" name="isbn" size="80" value="<?= $thelibrary->cleanInput($books[$id]['isbn']); ?>"/>
				<label>Weight (gram):</label><br />
				<input type = "text" name="weight" size="80" value="<?= $thelibrary->cleanInput($books[$id]['weight']); ?>" />
				<label>Description:</label><br />
				<textarea rows="10" cols="40" name="description">
				<?= $thelibrary->cleanInput($books[$id]['description']); ?>
				</textarea><br />
				<input type= "hidden" value="<?=$thelibrary->cleanInput($books[$id]['isbn']);?>" name="editbook" /><br />
				<input type= "submit" value="Edit." />
				</fieldset>
			</form>
		<?
			} else {
			$formfill = isset($_POST['addbook']) ? true : false;
			
		?>

		<h1>Add book.</h1>
			<form action="" method="post">
				<fieldset>
				<label>Book title:</label><br />
				<input type = "text" name="title" size="80" value="<?= ($formfill) ? $thelibrary->cleanInput($_POST['title']) : '';  ?>" />
				<label>ISBN nummer:</label><br />
				<input type = "text" name="isbn" size="80" value="<?= ($formfill) ? $thelibrary->cleanInput($_POST['isbn']) : '';  ?>"/>
				<label>Weight (gram):</label><br />
				<input type = "text" name="weight" size="80" value="<?= ($formfill) ? $thelibrary->cleanInput($_POST['weight']) : '';  ?>" />
				<label>Description:</label><br />
				<textarea rows="10" cols="40" name="description">
				<?= ($formfill) ? $thelibrary->cleanInput($_POST['description']) : ''; ?>
				</textarea><br />
				<input type= "hidden" value="1" name="addbook" /><br />
				<input type= "submit" value="Add." />
				</fieldset>
			</form>
		<?
			}
		?>
		<div id="output">
		</div>
	</body>

</html>