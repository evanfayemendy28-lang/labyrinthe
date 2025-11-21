<?php
session_start();  

//Documentation php pour sqlite : https://www.php.net/manual/en/book.sqlite3.php

$bdd_fichier = 'labyrinthe.db';
$sqlite = new SQLite3($bdd_fichier);

$sql_depart = "SELECT id FROM couloir WHERE type = 'depart'";
$res_depart = $sqlite->query($sql_depart);
$row_depart = $res_depart->fetchArray(SQLITE3_ASSOC);
$debut = $row_depart['id'];

if (isset($_GET['reset'])) {
    $_SESSION['nombre_coup'] = 0;
    header("Location: ?id=$debut");
    exit;
}

if (!isset($_SESSION['nombre_coup'])) {
    $_SESSION['nombre_coup'] = 0;
}
$nombre_coup = $_SESSION['nombre_coup'];

if (isset($_GET['id'])) {
    $depart_id = $_GET['id'];
} else {
    $depart_id = $debut;
}

if ($nombre_coup == 0) {
    $cle = false;
}

$sql_possible = 'SELECT * FROM passage';
$result_passage = $sqlite->query($sql_possible);

$sql_couloir = 'SELECT * FROM couloir';
$result_couloir = $sqlite->query($sql_couloir);

echo "<!DOCTYPE html>\n";
echo "<html lang=\"fr\"><head><meta charset=\"UTF-8\">\n";
echo "<title>Liste des couloirs</title>\n";
echo "</head>\n";

echo "<body>\n";
echo "<h1>LabyrinthSimulator.io</h1>\n";
echo "<h2>Vous êtes dans la salle $depart_id</h2>\n";

$direction = [
    "N" => "⬆",
    "S" => "⬇",
    "E"=> "➡",
    "O"=> "⬅"
];

$sql_cle = "SELECT type FROM couloir WHERE id = $depart_id";
$res_cle = $sqlite->query($sql_cle);
$cle_cle = $res_cle->fetchArray(SQLITE3_ASSOC);


if ($cle_cle && $cle_cle['type'] == 'cle') {
    $cle = true;
}

if ($cle == true ){
    echo "<h2>Inventaire : 🗝️ </h2>\n";
}
else {
    echo "<h2>Inventaire : ❌ </h2>\n";
}

echo "<h2>$nombre_coup</h2>\n";

$nombre_coup += 1;

while ($passage = $result_passage->fetchArray(SQLITE3_ASSOC)) {

    if ($passage['couloir1'] == $depart_id) {
        $dir = $direction[$passage['position2']];
        $dest = $passage['couloir2'];
        $type_sql = $passage['type'];

        if ($type_sql == 'grille') {
            if ($cle == false) {
                echo "<button type='button'>Il faut une clé pour aller vers la salle $dest $dir</button><br>\n";
            } else {
                echo "<a href='?id=$dest'><button>Vous ouvrez la grille et allez vers la salle $dest $dir</button></a><br>\n";
                $cle = false;
            }
        }
        else {
            echo "<a href='?id=$dest'><button>Aller vers la salle $dest $dir</button></a><br>\n";
        }
    }

    if ($passage['couloir2'] == $depart_id) {
        $dir = $direction[$passage['position1']];
        $dest = $passage['couloir1'];
        $type_sql = $passage['type'];

        if ($type_sql == 'grille') {
            if ($cle == false) {
                echo "<button type='button'>Il faut une clé pour aller vers la salle $dest $dir</button><br>\n";
            } else {
                echo "<a href='?id=$dest'><button>Vous ouvrez la grille et allez vers la salle $dest $dir</button></a><br>\n";
                $cle = false;
            }
        } else {
            echo "<a href='?id=$dest'><button>Aller vers la salle $dest $dir</button></a><br>\n";
        }
    }
}


$_SESSION['nombre_coup'] = $nombre_coup;

echo "<a href='?reset=1'><button>RECOMMENCER LE JEU</button></a><br>\n";
echo "</body>\n";
echo "</html>\n";

$sqlite->close();
?>
