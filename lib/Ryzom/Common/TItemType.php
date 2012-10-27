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

namespace Ryzom\Common;

/**
 * <code/ryzom/common/src/game_share/item_type.h>
 */
class TItemType {
	const DAGGER = 0;
	const SWORD = 1;
	const MACE = 2;
	const AXE = 3;
	const SPEAR = 4;
	const STAFF = 5;

	const TWO_HAND_SWORD = 6;
	const TWO_HAND_AXE = 7;
	const PIKE = 8;
	const TWO_HAND_MACE = 9;

	const AUTOLAUCH = 10;
	const BOWRIFLE = 11;
	const LAUNCHER = 12;
	const PISTOL = 13;
	const BOWPISTOL = 14;
	const RIFLE = 15;

	const AUTOLAUNCH_AMMO = 16;
	const BOWRIFLE_AMMO = 17;
	const LAUNCHER_AMMO = 18;
	const PISTOL_AMMO = 19;
	const BOWPISTOL_AMMO = 20;
	const RIFLE_AMMO = 21;

	const SHIELD = 22;
	const BUCKLER = 23;

	const LIGHT_BOOTS = 24;
	const LIGHT_GLOVES = 25;
	const LIGHT_PANTS = 26;
	const LIGHT_SLEEVES = 27;
	const LIGHT_VEST = 28;
	const MEDIUM_BOOTS = 29;
	const MEDIUM_GLOVES = 30;
	const MEDIUM_PANTS = 31;
	const MEDIUM_SLEEVES = 32;
	const MEDIUM_VEST = 33;
	const HEAVY_BOOTS = 34;
	const HEAVY_GLOVES = 35;
	const HEAVY_PANTS = 36;
	const HEAVY_SLEEVES = 37;
	const HEAVY_VEST = 38;
	const HEAVY_HELMET = 39;

	const ANKLET = 40;
	const BRACELET = 41;
	const DIADEM = 42;
	const EARING = 43;
	const PENDANT = 44;
	const RING = 45;

	const SHEARS = 46;

	const ARMOR_TOOL = 47;
	const AMMO_TOOL = 48;
	const MELEE_WEAPON_TOOL = 49;
	const RANGE_WEAPON_TOOL = 50;
	const JEWELRY_TOOL = 51;
	const TOOLMAKER = 52;

	const CAMPSFIRE = 53;
	const MEKTOUB_PACKER_TICKET = 54;
	const MEKTOUB_MOUNT_TICKET = 55;
	const FOOD = 56;

	const MAGICIAN_STAFF = 57;

	const HAIR_MALE = 58;
	const HAIR_COLOR_MALE = 59;
	const TATOO_MALE = 60;

	const HAIR_FEMALE = 61;
	const HAIR_COLOR_FEMALE = 62;
	const TATOO_FEMALE = 63;

	const SERVICE_STABLE = 64;
	const GENERIC = 65;

	const UNDEFINED = 66;

	/**
	 * Return true if type is 1h melee weapon
	 *
	 * @param int $type
	 *
	 * @return bool
	 */
	public static function is1hMelee($type) {
		return in_array($type, array(
			self::DAGGER, self::SWORD, self::MACE,
			self::AXE, self::SPEAR, self::STAFF
		));
	}

}
