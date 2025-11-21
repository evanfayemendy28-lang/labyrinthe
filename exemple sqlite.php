<?php

	//Documentation php pour sqlite : https://www.php.net/manual/en/book.sqlite3.php
	
	/* Paramètres */
	$bdd_fichier = 'labyrinthe.db';	
	$type = 'vide';			
	

	$sqlite = new SQLite3($bdd_fichier);		
	

	$sql = 'SELECT couloir.id, couloir.type FROM couloir WHERE type=:type';

if (isset($_GET['id'])) {
    $current_id = $_GET['id'];
} else {
    
    $sql_depart = "SELECT id FROM couloir WHERE type = 'depart'"; 
	$res_depart = $sqlite->query($sql_depart);
	$row_depart = $res_depart->fetchArray(SQLITE3_ASSOC);
	$depart_id = $row_depart['id'];

}
	

	$sql_possible = 'SELECT * from passage';
	



	
	$requete = $sqlite -> prepare($sql_possible);	
	$requete -> bindValue(':type', $type, SQLITE3_TEXT);
	
	$result = $requete -> execute();	

	
	echo "<!DOCTYPE html>\n";	
	echo "<html lang=\"fr\"><head><meta charset=\"UTF-8\">\n";	
	echo "<title>Liste des couloirs</title>\n";
	echo "</head>\n";
	
	echo "<body>\n";
	echo "<h1>LabyrinthSimulator.io</h1>\n";
	echo "<ul>";
	echo "<h2> Vous êtes dans la salle $depart_id </h2>";
	while($passage = $result -> fetchArray(SQLITE3_ASSOC)) {
		if ($passage['couloir1'] == $depart_id ){
		
            $dir = $passage['position2'];
            $dest = $passage['couloir2'];
			echo "<button type='button' name='button' value='$dest'>Aller vers la salle $dest vers $dir</button>\n";
		
		}
		if ($passage['couloir2'] == $depart_id){
		    $dir = $passage['position1'];
            $dest = $passage['couloir1'];
			echo "<button type='button' name='button' value='$dest'>Aller vers la salle $dest vers $dir</button>\n";
        
		}
	}

	echo "</ul>";
	echo "</body>\n";
	echo "</html>\n";
	
	


	$sqlite -> close();	
?>


