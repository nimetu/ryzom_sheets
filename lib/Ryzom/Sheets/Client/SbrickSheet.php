<?php
//
// RyzomSheets - https://github.com/nimetu/ryzom_sheets
// Copyright (c) 2012 Meelis Mägi <nimetu@gmail.com>
//
// This file is part of RyzomSheets.
//
// RyzomSheets is free software; you can redistribute it and/or modify
// it under the terms of the GNU Lesser General Public License as published by
// the Free Software Foundation; either version 3 of the License, or
// (at your option) any later version.
//
// RyzomSheets is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with this program; if not, write to the Free Software Foundation,
// Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301  USA
//

namespace Ryzom\Sheets\Client;

use Nel\Misc\MemStream;
use Nel\Misc\StreamInterface;

class SbrickSheet implements StreamInterface {
	/** @var array */
	public $UsedSkills = array();

	/** @var int */
	public $BrickFamily = 0;

	/** @var int */
	public $IndexInFamily = 0;

	/** @var int */
	public $Level = 0;

	/** @var string */
	public $sTmp = '';

	/** @var array */
	public $Icon = array();

	/** @var int */
	public $IconColor = 0;

	/** @var int */
	public $SabrinaCost = 0;

	/** @var float */
	public $SabrinaRelativeCost = 0;

	/** @var array */
	public $MandatoryFamilies = array();

	/** @var array */
	public $OptionalFamilies = array();

	/** @var array */
	public $ParameterFamilies = array();

	/** @var array */
	public $CreditFamilies = array();

	/** @var string */
	public $ForbiddenDef = '';

	/** @var string */
	public $ForbiddenExclude = '';

	/** @var CFaberPlan */
	public $FaberPlan;

	/** @var \stdClass[] stdClass::Text */
	public $Properties = array();

	/** @var int */
	public $MinCastTime = 0;

	/** @var int */
	public $MaxCastTime = 0;

	/** @var int */
	public $MinRange = 0;

	/** @var int */
	public $MaxRange = 0;

	/** @var int */
	public $BrickRequiredFlags = 0;

	/** @var int */
	public $SPCost = 0;

	/** @var int */
	public $ActionNature = 0;

	/** @var array SheetId */
	public $RequiredSkills = array();

	/** @var array SheetId */
	public $RequireAllSkills = array();

	/** @var array SheetId */
	public $RequiredBricks = array();

	/** @var int */
	public $AvoidCyclic = 0;

	/** @var int */
	public $UsableWithEmptyHands = 0;

	/** @var int */
	public $CivRestriction = 0;

	/** @var int */
	public $FactionIndex = 0;

	/** @var int */
	public $MinFameValue = 0;

	/** @var int */
	public $MagicResistType = 0;

	public function __construct() {
		$this->FaberPlan = new CFaberPlan;
	}

	public function serial(MemStream $s) {
		$s->serial_uint32($nbItems);
		$s->serial_uint32($this->UsedSkills, $nbItems);

		$s->serial_uint32($this->BrickFamily);
		$s->serial_byte($this->IndexInFamily);
		$s->serial_byte($this->Level);
		$s->serial_string($this->sTmp);
		$s->serial_string($this->Icon, 4);
		$s->serial_sint32($this->IconColor, 4);
		$s->serial_sint32($this->SabrinaCost);
		$s->serial_float($this->SabrinaRelativeCost);

		$s->serial_uint32($nbItems);
		$s->serial_short($this->MandatoryFamilies, $nbItems);
		$s->serial_uint32($nbItems);
		$s->serial_short($this->OptionalFamilies, $nbItems);
		$s->serial_uint32($nbItems);
		$s->serial_short($this->ParameterFamilies, $nbItems);
		$s->serial_uint32($nbItems);
		$s->serial_short($this->CreditFamilies, $nbItems);
		$s->serial_string($this->ForbiddenDef);
		$s->serial_string($this->ForbiddenExclude);

		$this->FaberPlan->serial($s);

		$this->Properties = array();
		$s->serial_uint32($nbItems);
		for ($nb = 0; $nb < $nbItems; $nb++) {
			$row = new \stdClass();
			$s->serial_string($row->Text);
			$this->Properties[] = $row;
		}
		$s->serial_byte($this->MinCastTime);
		$s->serial_byte($this->MaxCastTime);
		$s->serial_byte($this->MinRange);
		$s->serial_byte($this->MaxRange);
		$s->serial_uint64($this->BrickRequiredFlags);
		$s->serial_uint32($this->SPCost);
		$s->serial_uint32($this->ActionNature);

		$this->RequiredSkills = array();
		$s->serial_uint32($nbItems);
		for ($nb = 0; $nb < $nbItems; $nb++) {
			$row = new CRequiredSkill();
			$row->serial($s);
			$this->RequiredSkills[] = $row;
		}
		$this->RequireAllSkills = array();
		$s->serial_uint32($nbItems);
		for ($nb = 0; $nb < $nbItems; $nb++) {
			$row = new CRequiredSkill();
			$row->serial($s);
			$this->RequireAllSkills[] = $row;
		}
		$s->serial_uint32($nbItems);
		$s->serial_uint32($this->RequiredBricks, $nbItems);
		$s->serial_byte($this->AvoidCyclic);
		$s->serial_byte($this->UsableWithEmptyHands);
		$s->serial_uint32($this->CivRestriction);
		$s->serial_sint32($this->FactionIndex);
		$s->serial_sint32($this->MinFameValue);
		$s->serial_uint32($this->MagicResistType);
	}
}
