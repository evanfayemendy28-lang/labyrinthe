<?php
echo "<!DOCTYPE html><html lang='fr'><head><meta charset='UTF-8'><title>LabyrinthSimulator</title>";
echo "<link rel='stylesheet' href='style.css'>";
echo "</head><body>";

echo "<h1>LabyrinthSimulator.io</h1>";
echo "<a href='exemple sqlite.php?reset=1'><button>Jouer au jeu</button></a>";

echo '<div class="texte-fond">';

echo "<h1> 📜 Règles du jeu — LabyrintheSimulator.io</h1>";
echo "<h2> 🚩 Objectif : </h2> ";
echo "<h3> Trouver la sortie du labyrinthe en effectuant le moins de déplacements possible. </h3>";

echo "<h2> 🚶‍♂️ Déplacements : </h2> ";
echo "<h3> Vous pouvez vous déplacer uniquement vers les couloirs directement connectés à votre position actuelle. <br>
Les directions possibles (Nord, Sud, Est, Ouest) sont indiquées sur chaque page de couloir.<br>
Chaque déplacement compte, alors réfléchissez bien avant d’avancer ! </h3>";

echo "<h2> 🗝️ Clés et grilles : </h2> ";
echo "<h3> Une grille nécessite 1 clé pour être ouverte. <br> 
Chaque clé est consommée lors de l’ouverture d’une grille. <br>
Les clés sont ramassées automatiquement en entrant dans un couloir qui en contient.<br>
Le nombre de clés que vous possédez est toujours affiché à l’écran.</h3>";

echo "<h2> 🧭 Orientation et exploration : </h2> ";
echo "<h3> Le labyrinthe peut contenir des impasses, des boucles ou des chemins détournés.<br>
L’orientation dépend de la direction d’où vous venez : les directions restent cohérentes (Nord/Sud/Est/Ouest).<br><br>
Pensez à observer : <br><br>
l’identifiant du couloir,<br>
les directions disponibles,<br>
les clés ramassées,<br>
les grilles devant vous. </h3>";

echo "<h2> 🏁 Fin de la partie : </h2> ";
echo "<h3> Vous gagnez en trouvant la sortie. <br>
🎉 Un message de victoire<br>
🧮 Votre score total <br>
📝 Un formulaire pour enregistrer votre nom dans les meilleurs scores </h3>";
echo "</div>";

echo "</body></html>";


?>
