<?php

abstract class Character
{
	protected $id,
		$name,
		$damages,
		$experience,
		$level,
		$strength,
		$todayShotsNb,
		$lastShotDate,
		$lastConnectDate,
		$specialty,
		$asset,
		$freezeTimeStamp;
	
	const YOURSELF             = 1;
	const KILLED_CHARACTER     = 2;
	const HIT_CHARACTER        = 3;
	const EXPERIENCE_ACQUIRED  = 4;
	const LEVEL_ACQUIRED       = 5;
	const LEVEL_MAX            = 6;
	const HIT_LIMIT_REACHED    = 7;
	const SPELL_LIMIT_REACHED  = 8;
	const CHARACTER_FREEZED    = 9;
	const SUCCESS_SPELL_TARGET = 10;
	
	public function __construct(array $data)
	{
		$this->hydrate($data);
		$this->specialty = strtolower(static::class);
	}
	
	public function hydrate(array $data)
	{
		foreach ($data as $key => $value) {
			$method = 'set' . ucfirst($key);
			
			if (method_exists($this, $method)) {
				$this->$method($value);
			}
		}
	}
	
	public function hit(Character $character)
	{
		if ($character->id() == $this->id) {
			return self::YOURSELF;
		}
		if ($this->freezeTimeStamp() > time()) {
			return self::CHARACTER_FREEZED;
		}
		if ($this->lastShotDate() == date("Y-m-d")) {
			$this->todayShotsNb++;
		} else {
			$this->todayShotsNb = 1;
		}
		
		if ($this->todayShotsNb() > 3) {
			return self::HIT_LIMIT_REACHED;
		}
		
		$this->lastShotDate = date("Y-m-d");
		return $character->receiveDamages($this->strength());
	}
	
	public function receiveDamages($strength_char)
	{
		$this->damages += (5 + $strength_char);
		
		if ($this->damages >= 100) {
			return self::KILLED_CHARACTER;
		}
		return self::HIT_CHARACTER;
	}
	
	public function removeDamages()
	{
		$this->damages -= 10;
		if ($this->damages < 0) {
			$this->damages = 0;
		}
	}
	
	public function addAssetPoints()
	{
		$this->asset += 5;
		if ($this->asset >= 100) {
			$this->asset = 100;
		}
	}
	
	public function earnExperience($infoKill)
	{
		if ($this->level >= 100) {
			return self::LEVEL_MAX;
		}
		
		$this->experience += 5;
		
		if ($infoKill === 'killed') {
			$this->experience += 5;
		}
		
		$result = self::EXPERIENCE_ACQUIRED;
		
		if ($this->experience >= 100) {
			$result = $this->levelUp();
		}
		
		return $result;
	}
	
	public function levelUp()
	{
		
		$this->experience -= 100;
		$this->level      += 1;
		$this->strength   += 0.5;
		return self::LEVEL_ACQUIRED;
	}
	
	public function id()
	{
		return $this->id;
	}
	
	public function name()
	{
		return $this->name;
	}
	
	public function damages()
	{
		return $this->damages;
	}
	
	public function experience()
	{
		return $this->experience;
	}
	
	public function level()
	{
		return $this->level;
	}
	
	public function strength()
	{
		return $this->strength;
	}
	
	public function todayShotsNb()
	{
		return $this->todayShotsNb;
	}
	
	public function lastShotDate()
	{
		return $this->lastShotDate;
	}
	
	public function lastConnectDate()
	{
		return $this->lastConnectDate;
	}
	
	public function specialty()
	{
		return $this->specialty;
	}
	
	public function asset()
	{
		return $this->asset;
	}
	
	public function freezeTimeStamp()
	{
		return $this->freezeTimeStamp;
	}
	
	public function setId($id)
	{
		$id = (int)$id;
		
		if ($id > 0) {
			$this->id = $id;
		}
	}
	
	public function setName($name)
	{
		if (is_string($name)) {
			$this->name = $name;
		}
	}
	
	public function setDamages($damages)
	{
		$damages = (int)$damages;
		
		if ($damages >= 0 and $damages <= 100) {
			$this->damages = $damages;
		}
	}
	
	public function setExperience($experience)
	{
		$experience = (int)$experience;
		
		if ($experience >= 0 and $experience <= 100) {
			$this->experience = $experience;
		}
	}
	
	public function setLevel($level)
	{
		$level = (int)$level;
		
		if ($level >= 0 and $level <= 100) {
			$this->level = $level;
		}
	}
	
	public function setStrength($strength)
	{
		$strength = (int)$strength;
		
		if ($strength >= 0 and $strength <= 100) {
			$this->strength = $strength;
		}
	}
	
	public function setTodayShotsNb($todayShotsNb)
	{
		$todayShotsNb       = (int)$todayShotsNb;
		$this->todayShotsNb = $todayShotsNb;
	}
	
	public function setLastShotDate($lastShotDate)
	{
		$this->lastShotDate = $lastShotDate;
	}
	
	public function setLastConnectDate($lastConnectDate)
	{
		$this->lastConnectDate = $lastConnectDate;
	}
	
	public function setAsset($asset)
	{
		$asset = (int)$asset;
		
		if ($asset >= 0 and $asset <= 100) {
			$this->asset = $asset;
		}
	}
	
	public function setFreezeTimeStamp($freezeTimeStamp)
	{
		$freezeTimeStamp       = (int)$freezeTimeStamp;
		$this->freezeTimeStamp = $freezeTimeStamp;
	}
	
	public function validName()
	{
		return !empty($this->name);
	}
	
	public function stringConvertSpecialtyToFr($specialty)
	{
		if ($specialty === 'wizard') {
			$specialty = 'Magicien';
		}
		if ($specialty === 'warrior') {
			$specialty = 'Guerrier';
		}
		return $specialty;
	}
}
