<?php
$bdd_fichier = 'labyrinthe.db';
$sqlite = new SQLite3($bdd_fichier);

// ---------------------
// Déterminer la salle actuelle
// ---------------------
$current_id = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$current_id) {
    $res_depart = $sqlite->query("SELECT id FROM couloir WHERE type='depart'");
    $row_depart = $res_depart->fetchArray(SQLITE3_ASSOC);
    if ($row_depart) {
        $current_id = $row_depart['id'];
    } else {
        die("Erreur : aucune salle de départ trouvée dans la base.");
    }
}

// ---------------------
// Récupérer les passages de la salle actuelle
// ---------------------
$sql_possible = "SELECT * FROM passage WHERE couloir1=$current_id OR couloir2=$current_id";
$result = $sqlite->query($sql_possible);

// ---------------------
// Affichage HTML
// ---------------------
echo "<!DOCTYPE html>\n<html lang='fr'><head><meta charset='UTF-8'><title>Labyrinth Simulator.IO</title></head><body>";
echo "<h1>Labyrinth Simulator.IO</h1>";
echo "<h2>Vous êtes dans la salle $current_id</h2>";

// ---------------------
// Affichage des passages possibles
// ---------------------
$has_passages = false;
echo "<ul>";

while ($passage = $result->fetchArray(SQLITE3_ASSOC)) {
    // Déterminer la salle de destination et la direction
    if ($passage['couloir1'] == $current_id) {
        $dest = $passage['couloir2'];
        $dir  = $passage['position1'];
    } elseif ($passage['couloir2'] == $current_id) {
        $dest = $passage['couloir1'];
        $dir  = $passage['position2'];
    } else {
        continue;
    }

    // Afficher le lien vers la salle
    echo '<li><a href="labyrinthe.php?id='.$dest.'">Aller vers la salle '.$dest.' ('.$dir.')</a></li>';
    $has_passages = true;
}

if (!$has_passages) {
    echo "<li>Pas de sorties depuis cette salle. Vous êtes coincé !</li>";
}

echo "</ul>";
echo "</body></html>";

$sqlite->close();
?>
