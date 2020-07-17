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

class Shop {
	public static $foods = array(
	    /** 
 		    cost,
			ItemId,
			title
		*/
		array(
			500,
            Item::APPLE_ENCHANTED,
            "§bGolden Apple",			
		),
	);
	public static $armors = array(
	    /** 
 		    cost,
			ItemId,
			title
		*/
        array(
			50,
            Item::GOLD_HELMET,
            "§eGolden Helmet",			
		),
        array(
			55,
            Item::GOLD_CHESTPLATE,
            "§eGolden Chestplate",			
		),
        array(
			45,
            Item::GOLD_LEGGINGS,
            "§eGolden Leggings",			
		),
        array(
			45,
            Item::GOLD_BOOTS,
            "§eGolden Boots",			
		),      
        array(
			60,
            Item::IRON_HELMET,
            "§fIron Helmet",			
		),
        array(
			55,
            Item::IRON_CHESTPLATE,
            "§fIron Chestplate",			
		),
        array(
			55,
            Item::IRON_LEGGINGS,
            "§fIron Leggings",			
		),
        array(
			55,
            Item::IRON_BOOTS,
            "§fIron Boots",			
		),			
	);
}
