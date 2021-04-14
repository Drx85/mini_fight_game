<?php

class Wizard extends Character
{
	public function doASpell(Character $character)
	{
		if ($character->id() == $this->id) {
			return self::YOURSELF;
		}
		if ($this->asset() < 10) {
			return self::SPELL_LIMIT_REACHED;
		}

		$this->asset -= 10;
		$character->freezeTimeStamp = time() + 6 * 3600;

		return self::SUCCESS_SPELL_TARGET;
	}
}
