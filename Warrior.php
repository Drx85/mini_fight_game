<?php


class Warrior extends Character
{
	public function receiveDamages($strength_char)
	{
		if ($this->asset < 5) {
			$this->damages += (5 + $strength_char);
		} else {
			$this->damages += (1 + $strength_char);
			$this->asset   -= 5;
			if ($this->asset < 0) {
				$this->asset = 0;
			}
		}
		
		if ($this->damages >= 100) {
			return self::KILLED_CHARACTER;
		}
		
		return self::HIT_CHARACTER;
	}
}