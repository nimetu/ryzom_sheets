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

namespace Ryzom\Sheets;

use Ryzom\Sheets\Client\CharacterSheet;
use Ryzom\Sheets\Client\FactionSheet;
use Ryzom\Sheets\Client\ItemSheet;
use Ryzom\Sheets\Client\OutpostBuildingSheet;
use Ryzom\Sheets\Client\OutpostSheet;
use Ryzom\Sheets\Client\OutpostSquadSheet;
use Ryzom\Sheets\Client\RaceStatsSheet;
use Ryzom\Sheets\Client\SbrickSheet;
use Ryzom\Sheets\Client\SkilltreeSheet;
use Ryzom\Sheets\Client\SphraseSheet;
use Ryzom\Sheets\Client\WorldSheet;

/**
 * Entity types for packed sheets files
 * as described in Ryzom Core sources
 * code/ryzom/client/src/client_sheets/entity_sheet.h
 */
class EntityType {
	const TYPE_CHAR = 0;
	const TYPE_FAUNA = 1;
	//const TYPE_FLORA = 2;
	//const TYPE_OBJECT = 3;
	//const TYPE_FX = 4;
	//const TYPE_BUILDING = 5;
	const TYPE_ITEM = 6;
	//const TYPE_PLANT = 7;
	//const TYPE_MISSION = 8;
	const TYPE_RACE_STATS = 9;
	//const TYPE_PACT = 10;
	//const TYPE_LIGHT_CYCLE = 11;
	//const TYPE_WEATHER_SETUP = 12;
	//const TYPE_CONTINENT = 13;
	const TYPE_WORLD = 14;
	//const TYPE_WEATHER_FUNCTION_PARAMS = 15;
	//const TYPE_UNKNOWN = 16;
	//const TYPE_BOTCHAT = 17;
	//const TYPE_MISSION_ICON = 18;
	const TYPE_SBRICK = 19;
	const TYPE_SPHRASE = 20;
	const TYPE_SKILLS_TREE = 21;
	//const TYPE_UNBLOCK_TITLES = 22;
	//const TYPE_SUCCESS_TABLE = 23;
	//const TYPE_AUTOMATON_LIST = 24;
	//const TYPE_ANIMATION_SET_LIST = 25;
	//const TYPE_SPELL = 26; // obsolete
	//const TYPE_SPELL_LIST = 27; // obsolete
	//const TYPE_CAST_FX = 28; // obsolete
	//const TYPE_EMOT = 29;
	//const TYPE_ANIMATION_FX = 30;
	//const TYPE_ID_TO_STRING_ARRAY = 31;
	//const TYPE_FORAGE_SOURCE = 32;
	//const TYPE_CREATURE_ATTACK = 33;
	//const TYPE_ANIMATION_FX_SET = 34;
	//const TYPE_ATTACK_LIST = 35;
	//const TYPE_SKY = 36;
	//const TYPE_TEXT_EMOT = 37;
	const TYPE_OUTPOST = 38;
	const TYPE_OUTPOST_SQUAD = 39;
	const TYPE_OUTPOST_BUILDING = 40;
	const TYPE_FACTION = 41;

	/**
	 * Return entity type class instance
	 *
	 * @param int $entityType
	 *
	 * @return CharacterSheet|FactionSheet|ItemSheet|OutpostBuildingSheet|OutpostSheet|OutpostSquadSheet|RaceStatsSheet|SbrickSheet|SkilltreeSheet|SphraseSheet|WorldSheet
	 *
	 * @throws \RuntimeException when $entytyType is unknown
	 */
	static function factory($entityType) {
		switch ($entityType) {
			//case self::TYPE_CHAR:
			//	return new PlayerSheet();
		case self::TYPE_FAUNA:
			return new CharacterSheet();
			//case self::TYPE_FLORA:
			//	return new FloraSheet();
			//case self::TYPE_OBJECT,
			//case self::TYPE_FX,
			//case self::TYPE_BUILDING,
		case self::TYPE_ITEM:
			return new ItemSheet();
			//case self::TYPE_PLANT,
			//case self::TYPE_MISSION,
		case self::TYPE_RACE_STATS:
			return new RaceStatsSheet();
			//case self::TYPE_PACT,
			//case self::TYPE_LIGHT_CYCLE,
			//case self::TYPE_WEATHER_SETUP,
			//case self::TYPE_CONTINENT,
		case self::TYPE_WORLD:
			return new WorldSheet();
			//case self::TYPE_WEATHER_FUNCTION_PARAMS,
			//case self::TYPE_UNKNOWN,
			//case self::TYPE_BOTCHAT,
			//case self::TYPE_MISSION_ICON,
		case self::TYPE_SBRICK:
			return new SbrickSheet();
		case self::TYPE_SPHRASE:
			return new SphraseSheet();
		case self::TYPE_SKILLS_TREE:
			return new SkilltreeSheet();
			//case self::TYPE_UNBLOCK_TITLES,
			//case self::TYPE_SUCCESS_TABLE,
			//case self::TYPE_AUTOMATON_LIST,
			//case self::TYPE_ANIMATION_SET_LIST,
			//case self::TYPE_SPELL, // obsolete
			//case self::TYPE_SPELL_LIST, // obsolete
			//case self::TYPE_CAST_FX, // obsolete
			//case self::TYPE_EMOT,
			//case self::TYPE_ANIMATION_FX,
			//case self::TYPE_ID_TO_STRING_ARRAY,
			//case self::TYPE_FORAGE_SOURCE,
			//case self::TYPE_CREATURE_ATTACK,
			//case self::TYPE_ANIMATION_FX_SET,
			//case self::TYPE_ATTACK_LIST,
			//case self::TYPE_SKY,
			//case self::TYPE_TEXT_EMOT,
		case self::TYPE_OUTPOST:
			return new OutpostSheet();
		case self::TYPE_OUTPOST_SQUAD:
			return new OutpostSquadSheet();
		case self::TYPE_OUTPOST_BUILDING:
			return new OutpostBuildingSheet();
		case self::TYPE_FACTION:
			return new FactionSheet();
		default:
			throw new \RuntimeException("Unsupported entity type ($entityType)");
		}
	}
}
