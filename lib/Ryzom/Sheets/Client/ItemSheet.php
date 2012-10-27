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

use Ryzom\Common\EItemFamily;
use Nel\Misc\MemStream;
use Nel\Misc\StreamInterface;

/**
 * @property int Id
 * @property string MaleShape
 * @property string FemaleShape
 * @property int SlotBF
 * @property int MapVariant
 * @property int Family
 * @property int ItemType
 * @property string[] Icon
 * @property int[] IconColor
 * @property string IconText
 * @property string AnimSet
 * @property int Color
 * @property bool HasFx
 * @property bool DropOrSell
 * @property bool IsItemNoRent
 * @property bool NeverHideWhenEquiped
 * @property int Stackable
 * @property bool IsConsumable
 * @property float Bulk
 * @property int EquipTime
 * @property CFX FX
 * @property CStaticFX[] StaticFXs
 * @property string[] Effect
 * @property CMpItemPart[] MpItemParts
 * @property int CraftPlan
 * @property int RequiredCharac
 * @property int RequiredCharacLevel
 * @property int RequiredSkill
 * @property int RequiredSkillLevel
 * @property int ItemOrigin
 * @property CArmor Armor
 * @property CMeleeWeapon MeleeWeapon
 * @property CRangeWeapon RangeWeapon
 * @property CAmmo Ammo
 * @property CMp Mp
 * @property CShield Shield
 * @property CTool Tool
 * @property CTeleport Teleport
 * @property CPet Pet
 * @property CGuildOption GuildOption
 * @property CCosmetic Cosmetic
 * @property CConsumable Consumable
 * @property CScroll Scroll
 */
class ItemSheet implements StreamInterface {

	/**
	 * @param MemStream $s
	 */
	public function serial(MemStream $s) {
		$s->serial_string($this->MaleShape);
		$s->serial_string($this->FemaleShape);
		$s->serial_uint64($this->SlotBF);
		$s->serial_uint32($this->MapVariant);
		$s->serial_uint32($this->Family);
		$s->serial_uint32($this->ItemType);
		$s->serial_string($this->Icon, 4);
		$s->serial_sint32($this->IconColor, 4);
		$s->serial_string($this->IconText);
		$s->serial_string($this->AnimSet);
		$s->serial_byte($this->Color);
		$s->serial_byte($this->HasFx);
		$s->serial_byte($this->DropOrSell);
		$s->serial_byte($this->IsItemNoRent);
		$s->serial_byte($this->NeverHideWhenEquiped);
		$s->serial_uint32($this->Stackable);
		$s->serial_byte($this->IsConsumable);
		$s->serial_float($this->Bulk);
		$s->serial_uint32($this->EquipTime);

		$this->FX = new CFX();
		$this->FX->serial($s);

		$s->serial_uint32($nbItems);
		$this->StaticFXs = array();
		for ($fx = 0; $fx < $nbItems; $fx++) {
			$sfx = new CStaticFX();
			$sfx->serial($s);
			$this->StaticFXs[] = $sfx;
		}

		$s->serial_string($this->Effect, 4);

		$s->serial_uint32($nbItems);
		$this->MpItemParts = array();
		for ($mp = 0; $mp < $nbItems; $mp++) {
			$row = new CMpItemPart();
			$row->serial($s);
			$this->MpItemParts[] = $row;
		}

		$s->serial_uint32($this->CraftPlan);
		$s->serial_uint32($this->RequiredCharac);
		$s->serial_short($this->RequiredCharacLevel);
		$s->serial_uint32($this->RequiredSkill);
		$s->serial_short($this->RequiredSkillLevel);
		$s->serial_uint32($this->ItemOrigin);

		switch ($this->Family) {
		case EItemFamily::ARMOR:
			$this->Armor = new CArmor();
			$this->Armor->serial($s);
			break;
		case EItemFamily::MELEE_WEAPON:
			$this->MeleeWeapon = new CMeleeWeapon();
			$this->MeleeWeapon->serial($s);
			break;
		case EItemFamily::RANGE_WEAPON:
			$this->RangeWeapon = new CRangeWeapon();
			$this->RangeWeapon->serial($s);
			break;
		case EItemFamily::AMMO:
			$this->Ammo = new CAmmo();
			$this->Ammo->serial($s);
			break;
		case EItemFamily::RAW_MATERIAL:
			$this->Mp = new CMp();
			$this->Mp->serial($s);
			break;
		case EItemFamily::SHIELD:
			$this->Shield = new CShield();
			$this->Shield->serial($s);
			break;
		case EItemFamily::CRAFTING_TOOL:
		case EItemFamily::HARVEST_TOOL:
		case EItemFamily::TAMING_TOOL:
			$this->Tool = new CTool();
			$this->Tool->serial($s);
			break;
		case EItemFamily::TELEPORT:
			$this->Teleport = new CTeleport();
			$this->Teleport->serial($s);
			break;
		case EItemFamily::PET_ANIMAL_TICKET:
			$this->Pet = new CPet();
			$this->Pet->serial($s);
			break;
		case EItemFamily::GUILD_OPTION:
			$this->GuildOption = new CGuildOption();
			$this->GuildOption->serial($s);
			break;
		case EItemFamily::COSMETIC:
			$this->Cosmetic = new CCosmetic();
			$this->Cosmetic->serial($s);
			break;
		case EItemFamily::CONSUMABLE:
			$this->Consumable = new CConsumable();
			$this->Consumable->serial($s);
			break;
		case EItemFamily::SCROLL:
			$this->Scroll = new CScroll();
			$this->Scroll->serial($s);
			break;
		}
	}

	/**
	 * Use only when Family is ItemType::RAW_MATERIAL
	 *
	 * Return
	 * 0 - foraged
	 * 1 - looted
	 * -1 - unknown
	 * -2 - unknown
	 *
	 * @return int
	 */
	public function isLooted() {
		if ($this->Family !== EItemFamily::RAW_MATERIAL) {
			return -1;
		}

		$namesArray = array(
			'foraged' => array(
				'beng', 'hash', 'pha', 'sha', 'soo', 'zun',
				'adriel', 'becker', 'mitexi', 'oath', 'perfli',
				'anete', 'buo', 'dzao', 'shu',
				'gulatc', 'irin', 'koorin', 'pilan',
				'dung', 'fung', 'glue', 'moon',
				'dante', 'enola', 'redhot', 'silver', 'visc',
				'capric', 'sarina', 'sauron', 'silvio',
				'big', 'cuty', 'horny', 'smart', 'splint',
				'abhaya', 'eyota', 'kachin', 'motega', 'tama',
				'nita', 'patee', 'scrath', 'tansy', 'yana',
				'kitin', // kitin larva
			),
			'looted' => array(
				// avian
				'igara', 'izam', 'yber',
				// carnivore
				'cloppr', 'cuttlr', 'gingo', 'goari', 'hornch',
				'jugula', 'najab', 'ocyx', 'ragus', 'torbak',
				'tyranc', 'varinx', 'vorax', 'yetin', 'zerx',
				// flora
				'cratch', 'jubla', 'psykop', 'shooki', 'slaven', 'stinga',
				// herbivore
				'arana', 'arma', 'bawaab', 'bodoc', 'bolobi', 'capryn',
				'cray', 'frippo', 'gnoof', 'gubani', 'lumper', 'madaka',
				'messab', 'ploder', 'raspal', 'rendor', 'shalah', 'timari',
				'wombai', 'yelk', 'yubo',
				// javan
				'javing',
				// kitin
				'kiban', 'kidina', 'kinchr', 'kinrey', 'kipee', 'kipest',
				'kipuck', 'kirost', 'kizara', 'kizoar',
			),
			'unknown' => array(
				// corrup - corrupt moon ??
				// grand
				// mp - generic mats / faction mats ??
				'corrup', 'grand', 'mp',
			),
		);

		$txt = strtolower($this->IconText);
		if (in_array($txt, $namesArray['foraged'])) {
			if ($this->Mp->Family == 774) {
				// Supreme Kitin Sting, probably new type of mat that can be looted like kitin larva
				return -1;
			} else {
				return 0;
			}
		} else if (in_array($txt, $namesArray['looted'])) {
			return 1;
		} else if ($txt == '' || in_array($txt, $namesArray['unknown'])) {
			return -2;
		} else {
			return -1;
		}
	}
}
