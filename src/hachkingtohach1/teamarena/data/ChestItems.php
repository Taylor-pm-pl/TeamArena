<?php

/**
 * Copyright 2020 DragoVN
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
 
declare(strict_types=1);

namespace hachkingtohach1\teamarena\data;

use pocketmine\item\Item;

class ChestItems {
	public static $items = array(
	    // Foods 
		array(Item::STEAK, 100, true),
		// Items
		array(Item::BOW, 200, false),		
		array(Item::WOODEN_SWORD, 120, false),
		array(Item::STONE_SWORD, 345, false),
		array(Item::GOLD_SWORD, 456, false),
		array(Item::ARROW, 235, true),
		array(Item::DIAMOND, 600, false),
		//Armors
		array(Item::LEATHER_CAP, 543, false),
		array(Item::LEATHER_TUNIC, 654, false),
		array(Item::LEATHER_PANTS, 544, false),
		array(Item::LEATHER_BOOTS, 545, false),
		array(Item::GOLD_HELMET, 674, false),
		array(Item::GOLD_CHESTPLATE,686, false),
		array(Item::GOLD_LEGGINGS, 654, false),
		array(Item::GOLD_BOOTS, 879, false),
		array(Item::CHAIN_HELMET, 657, false),
		array(Item::CHAIN_CHESTPLATE, 965, false),
		array(Item::CHAIN_LEGGINGS, 964, false),			
		array(Item::CHAIN_BOOTS, 767, false),
		array(Item::IRON_HELMET, 654, false),
		array(Item::IRON_CHESTPLATE, 976, false),
		array(Item::IRON_LEGGINGS, 999, false),
		array(Item::IRON_BOOTS, 1000, false)
	);
}
