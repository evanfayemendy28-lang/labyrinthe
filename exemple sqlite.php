<?php

//Documentation php pour sqlite : https://www.php.net/manual/en/book.sqlite3.php

$bdd_fichier = 'labyrinthe.db';
$sqlite = new SQLite3($bdd_fichier);

$sql_depart = "SELECT id FROM couloir WHERE type = 'depart'";
$res_depart = $sqlite->query($sql_depart);
$row_depart = $res_depart->fetchArray(SQLITE3_ASSOC);
$debut = $row_depart['id'];

if (isset($_GET['id'])) {
    $depart_id = $_GET['id'];
} else {
    $depart_id = $debut;
}



$cle = false;


$sql_possible = 'SELECT * FROM passage';
$result = $sqlite->query($sql_possible);


echo "<!DOCTYPE html>\n";
echo "<html lang=\"fr\"><head><meta charset=\"UTF-8\">\n";
echo "<title>Liste des couloirs</title>\n";
echo "</head>\n";

echo "<body>\n";
echo "<h1>LabyrinthSimulator.io</h1>\n";
echo "<h2>Vous êtes dans la salle $depart_id</h2>\n";

while ($passage = $result->fetchArray(SQLITE3_ASSOC)) {

    if ($passage['couloir1'] == $depart_id) {
        $dir = $passage['position2'];
        $dest = $passage['couloir2'];
        $type_sql = $passage['type'];

    
        if ($type_sql == 'grille') {
            if ($cle == false) {
                echo "<button type='button'>Il faut une clé pour aller vers la salle ⬆️ $dest ($dir)</button><br>\n";
            } else {
                echo "<a href='?id=$dest'><button>Vous ouvrez la grille et allez vers la salle $dest ($dir)</button></a><br>\n";
            }
        } 
		else {
            echo "<a href='?id=$dest'><button>Aller vers la salle ⬆️ $dest ($dir)</button></a><br>\n";
        }
    }

    if ($passage['couloir2'] == $depart_id) {
        $dir = $passage['position1'];
        $dest = $passage['couloir1'];
        $type_sql = $passage['type'];

        if ($type_sql == 'grille') {
            if ($cle == false) {
                echo "<button type='button'>Il faut une clé pour aller vers la salle ⬆️ $dest ($dir)</button><br>\n";
            } else {
                echo "<a href='?id=$dest'><button>Vous ouvrez la grille et allez vers la salle $dest ($dir)</button></a><br>\n";
            }
        } else {
            echo "<a href='?id=$dest'><button>Aller vers la salle ⬆️ $dest ($dir)</button></a><br>\n";
        }
    }
}
echo "<a href='?id=$debut'><button>RECOMMENCER LE JEU</button></a><br>\n";
echo "</body>\n";
echo "</html>\n";

$sqlite->close();
?>
