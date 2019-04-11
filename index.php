<?php

	error_reporting(E_ALL);

	session_start();

	include("class.BookLibrary.php");

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
			
			$thelibrary  = new BookLibrary();
			$books = $thelibrary->decode();
			$item = $books[$id];
			$title  = $item['title'];
			$isbn = $item['isbn'];
			$weight = $item['weight'];
			$description = $item['description'];
		
		?>
		<h1>Edit book.</h1>
			<form action="" method="post">
				<fieldset>
				<label>Book title:</label><br />
				<input type = "text" name="title" size="80" value="<?= $thelibrary->cleanInput($title); ?>" />
				<label>ISBN nummer:</label><br />
				<input type = "text" name="isbn" size="80" value="<?= $thelibrary->cleanInput($isbn); ?>"/>
				<label>Weight (gram):</label><br />
				<input type = "text" name="weight" size="80" value="<?= $thelibrary->cleanInput($weight); ?>" />
				<label>Description:</label><br />
				<textarea rows="10" cols="40" name="description"><?= $thelibrary->cleanInput($description); ?></textarea><br />
				<input type= "hidden" value="<?=$thelibrary->cleanInput($isbn);?>" name="editbook" /><br />
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
				<textarea rows="10" cols="40" name="description"><?= ($formfill) ? $thelibrary->cleanInput($_POST['description']) : ''; ?></textarea><br />
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
