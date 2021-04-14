<?php
ob_start();

function loadClass($class)
{
	require $class . '.php';
}

spl_autoload_register('loadClass');

session_start();

if (isset($_GET['deconnect'])) {
	session_destroy();
	header('Location: index.php');
	exit();
}

if (isset($_SESSION['character'])) {
	$character = $_SESSION['character'];
}


$db = new PDO('mysql:host=localhost;dbname=test;charset=utf8', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$manager = new Manager($db);


if (isset($_POST['name']) && isset($_POST['create_character'])) {
	$character = $manager->returnNewCharacterDependsSpecialty($_POST['specialty'], ['name' => $_POST['name']]);
	
	if (!$character->validName()) {
		echo 'Veuillez indiquer un nom.';
		unset($character);
	} elseif ($manager->exists($character->name())) {
		echo 'Ce nom est déjà pris.';
		unset($character);
	} else {
		$manager->add($character);
	}
} elseif (isset($_POST['name']) && isset($_POST['useCharacter'])) {
	if ($manager->exists($_POST['name'])) {
		$character = $manager->get($_POST['name']);
		
		if ($character->lastConnectDate() != date("Y-m-d")) {
			$character->removeDamages();
			$character->addAssetPoints();
			$manager->updateLoginData($character);
			$connectionMessage = 'Pour votre nouvelle connexion aujourd\'hui, votre personnage perd 10 points de dégats et gagne 5 points d\'atout.';
		}
	} else {
		echo 'Ce personnage n\'existe pas !';
	}
} elseif (isset($_GET['hit'])) {
	if (!isset($character)) {
		echo 'Merci de créer un personnage ou de vous connecter';
	} else {
		if (!$manager->exists((int)$_GET['hit'])) {
			echo 'Le personnage à frapper n\'existe pas.';
		} else {
			$toHitCharacter    = $manager->get((int)$_GET['hit']);
			$hitReturning      = $character->hit($toHitCharacter);
			$hitAndXpReturning = $manager->hitSwitchReturning($hitReturning, $character, $toHitCharacter);
		}
	}
} elseif (isset($_GET['spell'])) {
	if (!isset($character)) {
		echo 'Merci de créer un personnage ou de vous connecter';
	} else {
		if (!$manager->exists((int)$_GET['spell'])) {
			echo 'Le personnage ciblé n\'existe pas.';
		} elseif ($character->specialty() != 'wizard') {
			echo 'Vous ne pouvez pas lancer de sort, vous n\'êtes pas magicien.';
		} else {
			$toSpellCharacter = $manager->get((int)$_GET['spell']);
			$spellReturning   = $character->doASpell($toSpellCharacter);
			$spellMessage     = $manager->spellSwitchReturning($spellReturning, $character, $toSpellCharacter);
		}
	}
}
$headContent = ob_get_clean();

ob_start(); ?>

<p>Nombre de personnages créés : <?= $manager->countCharacters() ?></p>

<?php
if (isset($connectionMessage)) {
	echo '<p>', $connectionMessage, '</p>';
}

if (isset($spellMessage)) {
	echo '<p>', $spellMessage, '</p>';
}

if (isset($hitAndXpReturning['hitMessage'])) {
	echo '<p>', $hitAndXpReturning['hitMessage'], '</p>';
}

if (isset($hitAndXpReturning['xpMessage'])) {
	echo '<p>', $hitAndXpReturning['xpMessage'], '</p>';
}

if (isset($character)) {
	?>
	<p><a href="index.php?deconnect=1">Déconnexion</a></p>
	
	<fieldset>
		<legend>Mes informations</legend>
		<p>
			Nom : <?= htmlspecialchars($character->name()) ?><br/>
			Spécialité : <?= $character->stringConvertSpecialtyToFr($character->specialty()) ?><br/>
			Dégâts reçus : <?= $character->damages() ?><br/>
			Force : <?= $character->strength() ?><br/>
			Expérience : <?= $character->experience() ?><br/>
			Niveau : <?= $character->level() ?><br/>
			Points d'atout : <?= $character->asset() ?>
		</p>
	</fieldset>
	
	<fieldset>
	<legend>Qui frapper ?</legend>
	<p>
	<?php
	$characters = $manager->getList($character->name());
	
	if (empty($characters)) {
		echo 'Personne à frapper !';
	} else {
		foreach ($characters as $listCharacter) {
			echo '<a href="?hit=', $listCharacter->id(), '">', htmlspecialchars($listCharacter->name()), '</a>
					(', $character->stringConvertSpecialtyToFr($listCharacter->specialty()), ')
					(dégâts reçus : ', $listCharacter->damages(), ')';
			
			if ($character->specialty() === 'wizard') {
				echo ' <a href="?spell=', $listCharacter->id(), '">Lancer un sort</a>';
			}
			echo '<br/>';
		}
		?>
		</p>
		</fieldset>
		<?php
	}
} else {
	?>
	<form action="" method="post">
		<p>
			Nom : <input type="text" name="name" maxlength="50"/>
			<select name="specialty">
				<option value="wizard">Magicien</option>
				<option value="warrior">Guerrier</option>
			</select>
			<input type="submit" value="Créer ce personnage" name="create_character"/>
			<input type="submit" value="Utiliser ce personnage" name="useCharacter"/>
		</p>
	</form>
	<?php
}
$bodyContent = ob_get_clean();

require('template.php');

if (isset($character)) {
	$_SESSION['character'] = $character;
}
?>
