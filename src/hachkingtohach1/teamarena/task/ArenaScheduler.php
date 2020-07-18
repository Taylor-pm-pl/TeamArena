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

namespace hachkingtohach1\teamarena\task;

use hachkingtohach1\teamarena\arena\Arena;
use pocketmine\scheduler\Task;

class ArenaScheduler extends Task {

    /** @var Arena $plugin */
    public $plugin;
 
    public function __construct(Arena $plugin) {
        $this->plugin = $plugin;       
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick) {
        $arena = $this->plugin;
		$arena->reloadSign();		
		foreach($arena->plugin->arenas as $i) {			
			foreach($i['players'] as $p) {
				$n = $p->getName();
		        $t = $arena->getTeamP($p);
				$api = $arena->plugin->api;
				$data = $api->getProvider()->getPlayerStats($p);
				$kills = $data["PlayerKills"];
				$deaths = $data["Deaths"];
				$coins = $arena->plugin->getCoins($p);
				$r = $i['red'];
				$b = $i['blue'];
				$g = $i['green'];
			    if($i['restarting'] != true) {
                    if(isset($arena->plugin->ingame[$p->getName()])) {				
			            $p->sendTip("§aK: ".$kills." D: ".$deaths." G: ".$coins." T: ".$t);
					    $p->sendPopup("§ePoints |"." §l§cR: ".$r." §l§1B: ".$b." §l§aG: ".$g);
				    }
					$level = $arena->plugin->getServer()->getLevelByName($i['level']);
				    $end = $arena->checkEnd($i['name_data']);
				    if($end != "not") {
					    
					    foreach($level->getPlayers() as $player) {
						    $player->sendMessage('Team '.$end.' won the game!');
						    $arena->restartPlayer($player);
							$player->teleport($arena->plugin->getServer()->getDefaultLevel()->getSpawnLocation());
					    }	
			            $arena->restartArena($i['name_data']);
				    }
					if($arena->plugin->time_refill == 0) {
						foreach($level->getPlayers() as $player) {
						    $player->sendMessage('All chests were refilled!');
							$arena->plugin->filledchests = [];
					    }
						$arena->plugin->time_refill = 180;
					}
					$arena->plugin->time_refill--;					
			    } else {
					if(!empty($arena->plugin->timecount[$i['name_data']])) {
						if($arena->plugin->timecount[$i['name_data']] == 0) {
							$arena->doneRestart($i['name_data']);
							return;
						} 
						$arena->plugin->timecount[$i['name_data']]--;
					}
			    }	
			}				
		}		
	}
}
