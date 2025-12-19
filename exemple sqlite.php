<?php
// demarre la session pour garder les infos du joueur
session_start();

// chemin vers la base de donnees sqlite
$bdd_fichier = 'labyrinthe.db';
// connexion a la base
$sqlite = new SQLite3($bdd_fichier);

// si on clique sur recommencer
if (isset($_GET['reset'])) {
    $_SESSION['nombre_coup'] = 0; // remet les coups a zero
    $_SESSION['nombre_cle'] = 0;  // remet les cles a zero
    $_SESSION['cle'] = []; // vide la liste des cles
    header("Location: ?");
    exit;
}

// recuperation des 10 meilleur joueur
$listemeilleurjoueur = "SELECT Nom_joueur, score FROM JOUEUR order by score limit 10";
$res_liste = $sqlite->query($listemeilleurjoueur);

// si un score est envoye depuis le formulaire
if (isset($_POST['name'])) {
    $nom = $_POST['name'];
    $score = $_POST['score'];
    $nomintable = false;

    // verifie si le joueur existe deja
    while ($joueur = $res_liste->fetchArray(SQLITE3_ASSOC)) {
        if ($nom == $joueur['Nom_joueur']) {
            $nomintable = true;
        }
    }

    // si le joueur n existe pas on l ajoute
    if ($nomintable == false) {
        $ajoutjoueur = $sqlite->prepare(
            "INSERT INTO JOUEUR (Nom_joueur, score) VALUES (:nom, :score)"
        );
        $ajoutjoueur->bindValue(':nom', $nom, SQLITE3_TEXT);
        $ajoutjoueur->bindValue(':score', $score, SQLITE3_INTEGER);
        $ajoutjoueur->execute();
    }
    // sinon on met juste a jour son score
    else {
        $majjoueur = $sqlite->prepare(
            "UPDATE JOUEUR SET score = :score WHERE Nom_joueur = :nom"
        );
        $majjoueur->bindValue(':score', $score, SQLITE3_INTEGER);
        $majjoueur->bindValue(':nom', $nom, SQLITE3_TEXT);
        $majjoueur->execute();
    }

    // message de confirmation
    echo "<h2>score enregistre bravo $nom</h2>";
}

// recupere la salle de depart
$sql_depart = "SELECT id FROM couloir WHERE type = 'depart'";
$res_depart = $sqlite->query($sql_depart);
$row_depart = $res_depart->fetchArray(SQLITE3_ASSOC);
$debut = $row_depart['id'];

// cree les variables de session si elles n existent pas
if (!isset($_SESSION['nombre_coup'])) $_SESSION['nombre_coup'] = 0;
if (!isset($_SESSION['nombre_cle'])) $_SESSION['nombre_cle'] = 0;
if (!isset($_SESSION['cle'])) $_SESSION['cle'] = [];

// recupere les valeurs depuis la session
$nombre_coup = $_SESSION['nombre_coup'];
$nombre_cle  = $_SESSION['nombre_cle'];
$listecle    = $_SESSION['cle'];

// salle actuelle ou salle de depart
$depart_id = isset($_GET['id']) ? intval($_GET['id']) : $debut;

// si le joueur utilise une cle pour une grille
if (isset($_GET["usekey"]) && $_GET["usekey"] == 1 && $nombre_cle > 0) {
    $nombre_cle--;
}

// type de la salle actuelle
$sql_cle = "SELECT type FROM couloir WHERE id = $depart_id";
$res_cle = $sqlite->query($sql_cle);
$cle_cle = $res_cle->fetchArray(SQLITE3_ASSOC);

// debut du html
echo "<!DOCTYPE html><html lang='fr'><head>";
echo "<meta charset='UTF-8'><title>labyrinthsimulator</title>";
echo "<link rel='stylesheet' href='style.css'>";
echo "</head><body>";

echo "<h1>labyrinthsimulator.io</h1>";

// bouton vers les regles
echo "<a href='regles.php'><button>regles du jeu</button></a>";

// si le joueur arrive a la sortie
if ($cle_cle['type'] == 'sortie') {

    echo '<audio src="win.mp3" autoplay loop></audio>';
    echo "<h1>vous avez gagne</h1>";
    echo "<h1>score : $nombre_coup</h1>";

    // formulaire pour enregistrer le score
    echo "
    <form method='POST'>
        <label>votre nom :</label><br>
        <input type='text' name='name' required>
        <input type='hidden' name='score' value='$nombre_coup'>
        <button type='submit'>enregistrer</button>
    </form>
    ";

    // bouton pour rejouer
    echo "<a href='?reset=1'><button>rejouer</button></a>";

    // affiche le top 10
    while ($joueur = $res_liste->fetchArray(SQLITE3_ASSOC)) {
        echo "<h2>- $joueur[Nom_joueur] : $joueur[score]</h2>";
    }

    echo "</body></html>";
    exit;
}

// affiche la salle actuelle
echo "<h2>vous etes dans la salle $depart_id</h2>";

// symboles des directions
$direction = [
    "N" => "⬆",
    "S" => "⬇",
    "E" => "➡",
    "O" => "⬅",
    "C" => "✨"
];

// minimap par defaut (murs)
$map = [
    'N'  => '🧱',
    'S'  => '🧱',
    'E'  => '🧱',
    'O'  => '🧱',
    'NE' => '🧱',
    'NO' => '🧱',
    'SE' => '🧱',
    'SO' => '🧱'
];

// si il y a une cle dans la salle et pas encore prise
if ($cle_cle['type'] == 'cle' && !in_array($depart_id, $listecle)) {
    $nombre_cle++;
    $listecle[] = $depart_id;
    echo "<h2>vous trouvez une cle</h2>";
}

// infos joueur
echo "<h2>cles : $nombre_cle</h2>";
echo "<h2>coups : $nombre_coup</h2>";

// augmente le nombre de coups
$nombre_coup++;

// recupere tous les passages
$result_passage = $sqlite->query("SELECT * FROM passage");

// affiche les passages possibles
while ($passage = $result_passage->fetchArray(SQLITE3_ASSOC)) {

    // cas ou on est dans couloir1
    if ($passage['couloir1'] == $depart_id) {

        $dirCode = $passage['position2'];
        $dirIcon = $direction[$dirCode];
        $dest = $passage['couloir2'];

        // passage ferme
        if ($passage['type'] == 'grille') {
            if ($nombre_cle == 0) {
                echo "<button>cle requise vers $dest $dirIcon</button><br>";
            } else {
                echo "<a href='?id=$dest&usekey=1'><button>ouvrir grille $dirIcon</button></a><br>";
            }
        }
        // passage normal
        else {
            $map[$dirCode] = '🚪';
            echo "<a href='?id=$dest'><button>salle $dest $dirIcon</button></a><br>";
        }
    }

    // cas ou on est dans couloir2
    if ($passage['couloir2'] == $depart_id) {

        $dirCode = $passage['position1'];
        $dirIcon = $direction[$dirCode];
        $dest = $passage['couloir1'];

        if ($passage['type'] == 'grille') {
            if ($nombre_cle == 0) {
                echo "<button>cle requise vers $dest $dirIcon</button><br>";
            } else {
                echo "<a href='?id=$dest&usekey=1'><button>ouvrir grille $dirIcon</button></a><br>";
            }
        }
        else {
            $map[$dirCode] = '🚪';
            echo "<a href='?id=$dest'><button>salle $dest $dirIcon</button></a><br>";
        }
    }
}

// affichage de la minimap
echo "<div class='minimap'>";
echo "<div class='map-ligne'><div class='map-carre'>{$map['NO']}</div><div class='map-carre'>{$map['N']}</div><div class='map-carre'>{$map['NE']}</div></div>";
echo "<div class='map-ligne'><div class='map-carre'>{$map['O']}</div><div class='map-carre player'>🧍‍♂️</div><div class='map-carre'>{$map['E']}</div></div>";
echo "<div class='map-ligne'><div class='map-carre'>{$map['SO']}</div><div class='map-carre'>{$map['S']}</div><div class='map-carre'>{$map['SE']}</div></div>";
echo "</div>";

// sauvegarde des infos dans la session
$_SESSION['nombre_cle'] = $nombre_cle;
$_SESSION['nombre_coup'] = $nombre_coup;
$_SESSION['cle'] = $listecle;

// bouton recommencer
echo "<br><a href='?reset=1'><button>recommencer</button></a>";

// classement
echo "<div class='classement'><h2>top 10</h2><ul>";
$sql_top = $sqlite->query("SELECT Nom_joueur, score FROM JOUEUR ORDER BY score LIMIT 10");
while ($j = $sql_top->fetchArray(SQLITE3_ASSOC)) {
    echo "<li>{$j['Nom_joueur']} - {$j['score']}</li>";
}
echo "</ul></div>";

// fin
echo "</body></html>";
$sqlite->close();
?>
