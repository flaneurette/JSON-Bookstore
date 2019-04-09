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

?>
