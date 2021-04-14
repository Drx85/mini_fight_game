<?php

class Manager extends Character
{
	private $db;
	
	public function __construct($db)
	{
		$this->setDb($db);
	}
	
	public function add(Character $character)
	{
		$q = $this->db->prepare('INSERT INTO characters(name, specialty) VALUES(:name, :specialty)');
		$q->bindValue(':name', $character->name());
		$q->bindValue(':specialty', $character->specialty());
		$q->execute();
		
		$character->hydrate([
			'id'              => $this->db->lastInsertId(),
			'damages'         => 0,
			'experience'      => 0,
			'level'           => 0,
			'strength'        => 0,
			'todayShotsNb'    => 0,
			'lastShotDate'    => date("Y-m-d"),
			'lastConnectDate' => date("Y-m-d"),
			'asset'           => 0,
			'freezeTimeStamp' => 0,
		]);
	}
	
	public function delete(Character $character)
	{
		$this->db->exec('DELETE FROM characters WHERE id = ' . $character->id());
	}
	
	public function get($info)
	{
		if (is_int($info)) {
			$q    = $this->db->query('SELECT * FROM characters WHERE id = ' . $info);
			$data = $q->fetch(PDO::FETCH_ASSOC);
		} else {
			$q = $this->db->prepare('SELECT * FROM characters WHERE name = :name');
			$q->execute([':name' => $info]);
			$data = $q->fetch(PDO::FETCH_ASSOC);
		}
		
		return $this->returnNewCharacterDependsSpecialty($data['specialty'], $data);
	}
	
	public function getList($name)
	{
		$characters = [];
		
		$q = $this->db->prepare('SELECT * FROM characters WHERE name <> :name ORDER BY name');
		$q->execute([':name' => $name]);
		
		while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
			$characters[] = $this->returnNewCharacterDependsSpecialty($data['specialty'], $data);
		}
		return $characters;
	}
	
	public function update(Character $character)
	{
		$q = $this->db->prepare('UPDATE characters SET 
		damages = :damages, 
		experience = :experience, 
		level = :level, 
		strength = :strength, 
		todayShotsNb = :todayShotsNb, 
		lastShotDate = :lastShotDate,
		asset = :asset,
		freezeTimeStamp = :freezeTimeStamp
		WHERE id = :id');
		
		$q->bindValue(':damages', $character->damages(), PDO::PARAM_INT);
		$q->bindValue(':experience', $character->experience(), PDO::PARAM_INT);
		$q->bindValue(':level', $character->level(), PDO::PARAM_INT);
		$q->bindValue(':strength', $character->strength(), PDO::PARAM_INT);
		$q->bindValue(':todayShotsNb', $character->todayShotsNb(), PDO::PARAM_INT);
		$q->bindValue(':lastShotDate', $character->lastShotDate());
		$q->bindValue(':asset', $character->asset(), PDO::PARAM_INT);
		$q->bindValue(':freezeTimeStamp', $character->freezeTimeStamp(), PDO::PARAM_INT);
		$q->bindValue(':id', $character->id(), PDO::PARAM_INT);
		$q->execute();
	}
	
	public function updateLoginData(Character $character)
	{
		$q = $this->db->prepare('UPDATE characters SET lastConnectDate = :lastConnectDate, damages = :damages, asset = :asset WHERE id = :id');
		$q->bindValue(':damages', $character->damages(), PDO::PARAM_INT);
		$q->bindValue(':asset', $character->asset(), PDO::PARAM_INT);
		$q->bindValue(':lastConnectDate', date("Y-m-d"));
		$q->bindValue(':id', $character->id(), PDO::PARAM_INT);
		$q->execute();
	}
	
	public function countCharacters()
	{
		return $this->db->query('SELECT COUNT(id) FROM characters')->fetchColumn();
	}
	
	public function exists($info)
	{
		if (is_int($info)) {
			return (bool)$this->db->query('SELECT COUNT(id) FROM characters WHERE id = ' . $info)->fetchColumn();
		}
		
		$q = $this->db->prepare('SELECT COUNT(id) FROM characters WHERE name = :name');
		$q->execute([':name' => $info]);
		
		return (bool)$q->fetchColumn();
	}
	
	public function returnNewCharacterDependsSpecialty($specialty, $characterData)
	{
		switch ($specialty) {
			case 'wizard':
				return $character = new Wizard($characterData);
				break;
			
			case 'warrior':
				return $character = new Warrior($characterData);
				break;
			
			default :
				return null;
				break;
		}
	}
	
	public function hitSwitchReturning($hitReturning, $character, $toHitCharacter)
	{
		switch ($hitReturning) {
			case Character::YOURSELF :
				$hitMessage = 'Vous ne pouvez pas vous cibler vous même !';
				$xpMessage  = null;
				break;
			
			case Character::CHARACTER_FREEZED :
				$hitMessage = 'Vous ne pouvez pas frapper : Vous avez été endormi jusqu\'au ' .
					date('d/m/Y', $character->freezeTimeStamp()) . ' à ' . date('H:i:s', $character->freezeTimeStamp());
				$xpMessage  = null;
				break;
			
			case Character::HIT_LIMIT_REACHED :
				$hitMessage = 'Limite de coups atteinte : vous ne pouvez frapper que 3 fois par jour.';
				$xpMessage  = null;
				break;
			
			case Character::HIT_CHARACTER :
				$hitMessage = 'Le personnage a bien été frappé.';
				
				$xpReturning = $character->earnExperience('not killed');
				$xpMessage   = $this->xpSwitchReturning($xpReturning);
				
				$this->update($character);
				$this->update($toHitCharacter);
				break;
			
			case Character::KILLED_CHARACTER :
				$hitMessage = 'Le personnage ciblé a été tué.';
				
				$xpReturning = $character->earnExperience('killed');
				$xpMessage   = $this->xpSwitchReturning($xpReturning);
				
				$this->update($character);
				$this->delete($toHitCharacter);
				break;
		}
		return array(
			'hitMessage' => $hitMessage,
			'xpMessage'  => $xpMessage
		);
	}
	
	public function xpSwitchReturning($xpReturning)
	{
		switch ($xpReturning) {
			case Character::LEVEL_MAX :
				$xpMessage = 'Vous avez atteint le niveau maximum.';
				break;
			
			case Character::EXPERIENCE_ACQUIRED :
				$xpMessage = 'Vous gagnez 5 points de niveau.';
				break;
			
			case Character::LEVEL_ACQUIRED :
				$xpMessage = 'Vous gagnez un nouveau niveau !';
				break;
		}
		return $xpMessage;
	}
	
	public function spellSwitchReturning($spellReturning, $character, $toSpellCharacter)
	{
		switch ($spellReturning) {
			case Character::YOURSELF :
				$spellMessage = 'Vous ne pouvez pas vous cibler vous même !';
				break;
			
			case Character::SPELL_LIMIT_REACHED :
				$spellMessage = 'Vous n\'avez pas assez de points d\'atout.';
				break;
			
			case Character::SUCCESS_SPELL_TARGET :
				$spellMessage = 'Le personnage a bien été endormi pendant 6h.';
				$this->update($character);
				$this->update($toSpellCharacter);
				break;
		}
		return $spellMessage;
	}
	
	public function setDb(PDO $db)
	{
		$this->db = $db;
	}
	
}
