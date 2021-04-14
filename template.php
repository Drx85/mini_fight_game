<?= $headContent ?>

<!DOCTYPE html>
<html lang="fr">
<head>
	<title>Mini jeu de combat</title>
	
	<meta charset="utf-8"/>
</head>
<body>
<h1>Mini-jeu de combat</h1>

<?= $bodyContent ?>

<h2>Règles du jeu générales</h2>

<ul>
	<li>Un personnage gagne 5 points d'expérience dès qu'il attaque.</li>
	<li>Un personnage gagne 5 points d'expérience supplémentaires s'il tue sa cible.</li>
	<li>Un niveau est acquis tous les 100 points d'expérience.</li>
	<li>Un demi-point de force est acquis à chaque montée en niveau.</li>
	<li>A chaque attaque sont infligés 5 points de dégâts, auxquels viennent s'ajouter la valeur des points de force du personnage qui attaque.</li>
	<li>A 100 points de dégâts reçus, tout personnage meurt.</li>
	<li>Le niveau maximum de tout personnage est 100.</li>
	<li>Le nombre de coups maximum par personnage par jour est de 3.</li>
	<li>Tout personnage perd 10 points de dégâts et gagne 10 points d'atout quand il se connecte (1 fois par jour maximum).</li>
	<li>Lors de la création d'un personnage, il faut lui choisir une spécialité.</li>
</ul>

<h2>Les différentes spécialités</h2>

<ul>
	<li>Le Magicien : Il peut endormir un personnage pendant 6h en échange de 10 points d'atout. Un personnage endormi ne peut pas attaquer, mais peut utiliser sa spécialité.</li>
	<li>Le Guerrier : Lorsqu'il est attaqué, s'il a assez de points d'atout, il parera automatiquement 4 points de dégâts. Cela lui coûte 5 points d'atout.</li>
</ul>
</body>
</html>
