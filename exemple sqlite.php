<?php
// Démarre la session PHP pour stocker les informations du joueur (score, clés, etc.)
session_start();  

// Définition du fichier de base de données SQLite et ouverture de la connexion
$bdd_fichier = 'labyrinthe.db';
$sqlite = new SQLite3($bdd_fichier);

// --- Réinitialisation du jeu si le paramètre "reset" est présent ---
if (isset($_GET['reset'] )) {
    $_SESSION['nombre_coup'] = 0; // Réinitialise le nombre de déplacements
    $_SESSION['nombre_cle'] = 0;  // Réinitialise le nombre de clés
    $_SESSION['cle'] = [];         // Réinitialise la liste des clés collectées
    header("Location: ?");         // Redirige vers la page principale
    exit;
}

// --- Récupération des 10 meilleurs joueurs ---
$listemeilleurjoueur = "SELECT Nom_joueur, score FROM JOUEUR order by score limit 10 "; 
$res_liste = $sqlite->query($listemeilleurjoueur);

// --- Enregistrement du score si le joueur soumet son nom ---
if (isset($_POST['name'])) {
    $nom = $_POST['name'];
    $score = $_POST['score'];
    $nomintable = false;

    // Vérifie si le joueur existe déjà dans la base
    while ($joueur = $res_liste->fetchArray(SQLITE3_ASSOC)) {
        if ($nom == $joueur['Nom_joueur']){
            $nomintable = true;
        }
    }

    if ($nomintable == false) {
        // Ajoute un nouveau joueur si le nom n'existe pas
        $ajoutjoueur = $sqlite->prepare("INSERT INTO JOUEUR (Nom_joueur, score) VALUES (:nom, :score)");
        $ajoutjoueur->bindValue(':nom', $nom, SQLITE3_TEXT);
        $ajoutjoueur->bindValue(':score', $score, SQLITE3_INTEGER);
        $ajoutjoueur->execute();
    }
    else {
        // Met à jour le score du joueur existant
        $majjoueur = $sqlite->prepare("UPDATE JOUEUR SET score = :score WHERE Nom_joueur = :nom");
        $majjoueur->bindValue(':score', $score, SQLITE3_INTEGER);
        $majjoueur->bindValue(':nom', $nom, SQLITE3_TEXT);
        $majjoueur->execute();
    }

    // Affiche un message de confirmation
    echo "<h2>Score enregistré ! Bravo $nom 🎉</h2>";
}

// --- Récupère l'ID du couloir de départ depuis la base ---
$sql_depart = "SELECT id FROM couloir WHERE type = 'depart'";
$res_depart = $sqlite->query($sql_depart);
$row_depart = $res_depart->fetchArray(SQLITE3_ASSOC);
$debut = $row_depart['id'];

// --- Initialisation des variables de session si elles n'existent pas ---
if (!isset($_SESSION['nombre_coup'])) $_SESSION['nombre_coup'] = 0;
if (!isset($_SESSION['nombre_cle'])) $_SESSION['nombre_cle'] = 0;
if (!isset($_SESSION['cle'])) $_SESSION['cle'] = [];

// --- Récupération des valeurs depuis la session ---
$nombre_coup = $_SESSION['nombre_coup'];
$nombre_cle  = $_SESSION['nombre_cle'];
$listecle    = $_SESSION['cle'];

// --- Détermination de la salle actuelle (GET 'id') ou départ ---
$depart_id = isset($_GET['id']) ? $_GET['id'] : $debut;

// --- Utilisation d'une clé si le joueur passe une grille ---
if (isset($_GET["usekey"]) && $_GET["usekey"] == 1 && $nombre_cle > 0) {
    $nombre_cle -= 1;
}

// --- Récupère le type de la salle actuelle (normale, clé, sortie) ---
$sql_cle = "SELECT type FROM couloir WHERE id = $depart_id";
$res_cle = $sqlite->query($sql_cle);
$cle_cle = $res_cle->fetchArray(SQLITE3_ASSOC);

// --- Début du HTML ---
echo "<!DOCTYPE html><html lang='fr'><head><meta charset='UTF-8'><title>LabyrinthSimulator</title>";
echo "<link rel='stylesheet' href='style.css'>";
echo "</head><body>";
echo "<h1>LabyrinthSimulator.io</h1>";

// Bouton pour accéder aux règles du jeu
echo "<a href='regles.php'><button>Règles du jeu</button></a>";

// --- Si la salle est la sortie, le joueur a gagné ---
if ($cle_cle['type'] == 'sortie') {

    echo "<h1>VOUS AVEZ GAGNÉ LE JEU !! 🎉</h1>";
    echo "<h1>VOTRE SCORE : $nombre_coup</h1>";

    // Formulaire pour enregistrer le nom du joueur et son score
    echo "
    <form method='POST'>
        <label>Entrez votre nom :</label><br>
        <input type='text' name='name' required>
        <input type='hidden' name='score' value='$nombre_coup'>
        <button type='submit'>Enregistrer le score</button>
    </form>
    ";

    // Bouton pour recommencer le jeu
    echo "<a href='?reset=1'><button>REJOUER</button></a>";
    
    // Affichage du top 10 des joueurs
    while ($joueur = $res_liste->fetchArray(SQLITE3_ASSOC)) {
        echo" <h2> - $joueur[Nom_joueur] : $joueur[score] </h2>";
    }

    echo "</body></html>";
    exit; // Fin de script après victoire
}

// --- Affiche la salle actuelle ---
echo "<h2>Vous êtes dans la salle $depart_id</h2>";

// Tableau pour afficher les directions
$direction = [
    "N" => "⬆",
    "S" => "⬇",
    "E" => "➡",
    "O" => "⬅"
];

// --- Ramassage d'une clé si présente et non déjà collectée ---
if ($cle_cle['type'] == 'cle' && !in_array($depart_id, $listecle)) {
    $nombre_cle += 1;           // Augmente le nombre de clés
    $listecle[] = $depart_id;   // Marque la clé comme ramassée
    echo "<h2><strong>Vous trouvez une clé ! 🗝️</strong></h2>";
}

// --- Affichage des infos clés et déplacements ---
echo "<h2>Clés : $nombre_cle 🗝️</h2>";
echo "<h2>Nombre de coups : $nombre_coup</h2>";

// Incrémente le nombre de coups (déplacements)
$nombre_coup++;

// --- Récupération des passages possibles depuis la base ---
$sql_possible = 'SELECT * FROM passage';
$result_passage = $sqlite->query($sql_possible);

// --- Boucle sur tous les passages pour afficher les boutons ---
while ($passage = $result_passage->fetchArray(SQLITE3_ASSOC)) {

    // Cas où le joueur est dans couloir1
    if ($passage['couloir1'] == $depart_id) {
        $dir  = $direction[$passage['position2']];
        $dest = $passage['couloir2'];

        if ($passage['type'] == 'grille') {
            if ($nombre_cle == 0) {
                // Bouton bloqué si pas de clé
                echo "<button>Il faut une clé → salle $dest $dir</button><br>";
            } else {
                // Lien pour utiliser une clé et passer la grille
                echo "<a href='?id=$dest&usekey=1'><button>Ouvrir grille → $dest $dir</button></a><br>";
            }
        } else {
            // Bouton normal pour déplacement
            echo "<a href='?id=$dest'><button>Salle $dest $dir</button></a><br>";
        }
    }

    // Cas où le joueur est dans couloir2
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

// --- Sauvegarde des données dans la session ---
$_SESSION['nombre_cle'] = $nombre_cle;
$_SESSION['nombre_coup'] = $nombre_coup;
$_SESSION['cle'] = $listecle;

// Bouton pour recommencer la partie
echo "<br>";
echo "<a href='?reset=1'><button>RECOMMENCER</button></a>";

// Fin du HTML et fermeture de la base
echo "</body></html>";
$sqlite->close();
?>
