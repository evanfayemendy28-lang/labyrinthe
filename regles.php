<?php
// debut du document html
echo "<!DOCTYPE html><html lang='fr'><head>";
echo "<meta charset='UTF-8'><title>labyrinthsimulator</title>";

// lien vers le fichier css
echo "<link rel='stylesheet' href='style.css'>";
echo "</head><body>";

// titre principale du site
echo "<h1>labyrinthsimulator.io</h1>";

// bouton pour lancer le jeu
echo "<a href='exemple sqlite.php?reset=1'><button>jouer au jeu</button></a>";

// zone avec fond pour le texte des regle
echo '<div class="texte-fond">';

// titre des regles
echo "<h1>regles du jeu labyrinthsimulator.io</h1>";

// objectif du jeu
echo "<h2>objectif</h2>";
echo "<h3>trouver la sortie du labyrinthe avec le moins de deplacements possible</h3>";

// explication des deplacements
echo "<h2>deplacements</h2>";
echo "<h3>
vous pouvez vous deplacer seulement vers les couloirs relies a votre position<br>
les directions possibles sont affichees a l ecran<br>
chaque deplacement augmente le score
</h3>";

// explication des cles et grilles
echo "<h2>cles et grilles</h2>";
echo "<h3>
une grille demande une cle pour etre ouverte<br>
la cle est consommee apres ouverture<br>
les cles sont ramassees automatiquement<br>
le nombre de cles est toujours visible
</h3>";

// orientation dans le labyrinthe
echo "<h2>orientation et exploration</h2>";
echo "<h3>
le labyrinthe peut contenir des impasses ou des boucles<br>
les directions restent coherentes nord sud est ouest<br><br>
pensez a observer<br>
l id de la salle<br>
les directions possibles<br>
les cles<br>
les grilles
</h3>";

// fin de partie
echo "<h2>fin de la partie</h2>";
echo "<h3>
vous gagnez en trouvant la sortie<br>
un message de victoire apparait<br>
votre score est affiche<br>
vous pouvez enregistrer votre nom
</h3>";

// fin de la zone de texte
echo "</div>";

// fin du document html
echo "</body></html>";
?>
