<?php
session_start();  

$bdd_fichier = 'labyrinthe.db';
$sqlite = new SQLite3($bdd_fichier);

if (isset($_GET['reset'])) {
    $_SESSION['nombre_coup'] = 0;
    $_SESSION['nombre_cle'] = 0;
    $_SESSION['cle'] = [];
    header("Location: ?");
    exit;
}
$listemeilleurjoueur = "SELECT Nom_joueur, score FROM JOUEUR order by score limit 10 "; 
$res_liste = $sqlite->query($listemeilleurjoueur);

if (isset($_POST['name'])) {
    $nom = $_POST['name'];
    $score = $_POST['score'];
    $nomintable = false;

    while ($joueur = $res_liste->fetchArray(SQLITE3_ASSOC)) {
        if ($nom == $joueur['Nom_joueur']){
            $nomintable = true;
        }
    }
    if ($nomintable == false) {
        $ajoutjoueur = $sqlite->prepare("INSERT INTO JOUEUR (Nom_joueur, score) VALUES (:nom, :score)");
        $ajoutjoueur->bindValue(':nom', $nom, SQLITE3_TEXT);
        $ajoutjoueur->bindValue(':score', $score, SQLITE3_INTEGER);
        $ajoutjoueur->execute();
    }
    else {
        $majjoueur = $sqlite->prepare("UPDATE JOUEUR SET score = :score WHERE Nom_joueur = :nom");
        $majjoueur->bindValue(':score', $score, SQLITE3_INTEGER);
        $majjoueur->bindValue(':nom', $nom, SQLITE3_TEXT);
        $majjoueur->execute();

    }
    echo "<h2>Score enregistré ! Bravo $nom 🎉</h2>";
}
$sql_depart = "SELECT id FROM couloir WHERE type = 'depart'";
$res_depart = $sqlite->query($sql_depart);
$row_depart = $res_depart->fetchArray(SQLITE3_ASSOC);
$debut = $row_depart['id'];

if (!isset($_SESSION['nombre_coup'])) $_SESSION['nombre_coup'] = 0;
if (!isset($_SESSION['nombre_cle'])) $_SESSION['nombre_cle'] = 0;
if (!isset($_SESSION['cle'])) $_SESSION['cle'] = [];

$nombre_coup = $_SESSION['nombre_coup'];
$nombre_cle  = $_SESSION['nombre_cle'];
$listecle    = $_SESSION['cle'];

$depart_id = isset($_GET['id']) ? $_GET['id'] : $debut;

if (isset($_GET["usekey"]) && $_GET["usekey"] == 1 && $nombre_cle > 0) {
    $nombre_cle -= 1;
}
$sql_cle = "SELECT type FROM couloir WHERE id = $depart_id";
$res_cle = $sqlite->query($sql_cle);
$cle_cle = $res_cle->fetchArray(SQLITE3_ASSOC);

echo "<!DOCTYPE html><html lang='fr'><head><meta charset='UTF-8'><title>LabyrinthSimulator</title>";
echo "<link rel='stylesheet' href='style.css'>";
echo "</head><body>";
echo "<h1>LabyrinthSimulator.io</h1>";


if ($cle_cle['type'] == 'sortie') {

    echo "<h1>VOUS AVEZ GAGNÉ LE JEU !! 🎉</h1>";
    echo "<h1>VOTRE SCORE : $nombre_coup</h1>";

    echo "
    <form method='POST'>
        <label>Entrez votre nom :</label><br>
        <input type='text' name='name' required>
        <input type='hidden' name='score' value='$nombre_coup'>
        <button type='submit'>Enregistrer le score</button>
    </form>
    ";

    echo "<a href='?reset=1'><button>REJOUER</button></a>";
    
    while ($joueur = $res_liste->fetchArray(SQLITE3_ASSOC)) {
        
       
        echo" <h2> - $joueur[Nom_joueur] : $joueur[score] </h2>";

    }
    echo "</body></html>";
    exit;
}


echo "<h2>Vous êtes dans la salle $depart_id</h2>";

$direction = [
    "N" => "⬆",
    "S" => "⬇",
    "E" => "➡",
    "O" => "⬅"
];


if ($cle_cle['type'] == 'cle' && !in_array($depart_id, $listecle)) {
    $nombre_cle += 1;
    $listecle[] = $depart_id;
    echo "<p><strong>Vous trouvez une clé ! 🗝️</strong></p>";
}


echo "<h2>Clés : $nombre_cle 🗝️</h2>";
echo "<h2>Nombre de coups : $nombre_coup</h2>";

$nombre_coup++;


$sql_possible = 'SELECT * FROM passage';
$result_passage = $sqlite->query($sql_possible);

while ($passage = $result_passage->fetchArray(SQLITE3_ASSOC)) {

    if ($passage['couloir1'] == $depart_id) {

        $dir  = $direction[$passage['position2']];
        $dest = $passage['couloir2'];

        if ($passage['type'] == 'grille') {
            if ($nombre_cle == 0) {
                echo "<button>Il faut une clé → salle $dest $dir</button><br>";
            } else {
                echo "<a href='?id=$dest&usekey=1'><button>Ouvrir grille → $dest $dir</button></a><br>";
            }
        } else {
            echo "<a href='?id=$dest'><button>Salle $dest $dir</button></a><br>";
        }
    }

    if ($passage['couloir2'] == $depart_id) {

        $dir  = $direction[$passage['position1']];
        $dest = $passage['couloir1'];

        if ($passage['type'] == 'grille') {
            if ($nombre_cle == 0) {
                echo "<button>Il faut une clé → salle $dest $dir</button><br>";
            } else {
                echo "<a href='?id=$dest&usekey=1'><button>Ouvrir grille → $dest $dir</button></a><br>";
            }
        } else {
            echo "<a href='?id=$dest'><button>Salle $dest $dir</button></a><br>";
        }
    }
}


$_SESSION['nombre_cle'] = $nombre_cle;
$_SESSION['nombre_coup'] = $nombre_coup;
$_SESSION['cle'] = $listecle;

echo "<a href='?rese t=1'><button>RECOMMENCER</button></a>";
echo "</body></html>";

$sqlite->close();
?>
