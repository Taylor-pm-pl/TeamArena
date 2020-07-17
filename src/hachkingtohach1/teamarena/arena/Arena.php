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

namespace hachkingtohach1\teamarena\arena;

use hachkingtohach1\teamarena\Main;
use hachkingtohach1\teamarena\math\Vector3;
use hachkingtohach1\teamarena\task\ArenaScheduler;
use pocketmine\tile\Sign;
use pocketmine\Player;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\tile\Tile;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent; 
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\inventory\ChestInventory;
use pocketmine\event\Listener;

class Arena implements Listener {
	
	/** @var Main $plugin */
	public $plugin;
	
	/** @var array $data */
	public $data = array();
	
	/** @var array $borrow_1 */
	public $borrow_1 = array();
	
	/** @var array $borrow_2 */
	public $borrow_2 = array();
	
	/** @var ArenaScheduler $scheduler */
	public $scheduler;
	
    public function __construct(Main $plugin) {		
		$this->plugin = $plugin;
		$plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
		$this->plugin->getScheduler()->scheduleRepeatingTask($this->scheduler = new ArenaScheduler($this), 20);
    }

    /**
	 * 
	 * To player can join arena
	 * 
	 * @param Player $player
	 * @param string $name
	 */
    public function joinArena(Player $player, string $name) {
		$plugin = $this->plugin;
		$config = $plugin->getConfig();
		if($plugin->arenas[$name]['restarting'] !== false) {
			$player->sendMessage($config->get('arena_is_restarting'));	
			return;
		}
		$cf = $plugin->configa;
		$cfa = $cf->get($name);
		$countper = $cfa['per_team'];
		if(count($plugin->arenas[$name]) >= $countper*4) {
			$player->sendMessage($config->get('arena_is_full'));		
			return;
		}		
		$namep = $player->getName();
		$spawnrand = $cfa['spawn_random'];
        $ext = array_rand($spawnrand, 1);		
        $player->teleport(
			Position::fromObject(
				Vector3::fromString($spawnrand[$ext])->add(0.5, 0, 0.5), 
				$plugin->getServer()->getLevelByName($cfa['level'])
			)
		);	
		$plugin->arenas[$name]['players'][$namep] = $player;	
		$plugin->distagsave[$namep] = $player->getDisplayName();
		$plugin->natagsave[$namep] = $player->getNameTag();
		if(!isset($plugin->chooseteam[$name])) {
			$plugin->chooseteam[$name] = 'R';
		}
	    $chooseteam = $plugin->chooseteam[$name];
		if($chooseteam == 'R') {
			$plugin->red[$namep] = $player;
			$this->setNameTagT($player, "§c");
			$plugin->chooseteam[$name] = 'B';
		} elseif($chooseteam == 'B') {
			$plugin->blue[$namep] = $player;
			$this->setNameTagT($player, "§1");
			$plugin->chooseteam[$name] = 'G';
		} elseif($chooseteam == 'G') {
			$plugin->green[$namep] = $player;
			$this->setNameTagT($player, "§a");
			$plugin->chooseteam[$name] = 'R';
		}
		$plugin->ingame[$namep] = $player;       
        $this->getBaseKit($player);		
        $player->sendMessage($config->get('joined'));	        		
	}
	
	/**
	 * 
	 * To player can left arena
	 * 
	 * @param Player $player
	 */
	public function leftArena(Player $player) {
		$plugin = $this->plugin;
		$namep = $player->getName();
		$distag = $plugin->distagsave[$namep];
		$natag = $plugin->natagsave[$namep];		
		$player->setDisplayName($distag);
		$player->setNameTag($natag);
		unset($plugin->players[$namep]);
		if(isset($plugin->red[$namep])) {
			unset($plugin->red[$namep]);
		}
		if(isset($plugin->blue[$namep])) {
			unset($plugin->blue[$namep]);
		}
		if(isset($plugin->green[$namep])) {
			unset($plugin->green[$namep]);
		}
	}

    /**
	 * 
	 * To check point all teams in arena
	 * 
	 * @param string $name
	 * @return string
	 */
    public function checkEnd(string $name) {
		$plugin = $this->plugin;
		$i = $plugin->arenas[$name];
		$red = $i['red'];
		$blue = $i['blue'];
		$green = $i['green'];
		$f = $i['finish_points'];
		if($red >= $f and $blue < $f 
		and $green < $f) {
			return "§cRed";
		}
		if($blue >= $f and $red < $f 
		and $green < $f) {
			return "§1Blue";
		}
		if($green >= $f and $blue < $f 
		and $red < $f) {
			return "§aGreen";
		}
		return "not";
	}
	
	/**
	 * 
	 * To get kill for player when joined arena
	 * 
	 * @param Player $player
	 */
	public function getBaseKit(Player $player) {
		$player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->setFood(20);
        $player->setHealth(20);
        $player->setGamemode(2);
		// ARMOR
		$armor = $player->getArmorInventory();
		$armor->setLeggings(Item::get(300, 0, 1));
		$armor->setBoots(Item::get(301, 0, 1));
		$armor->setHelmet(Item::get(298, 0, 1));				
		$armor->setChestplate(Item::get(299, 0, 1));		
		// Items
		$items = [
		    [275, 0, 1],
			[364, 0 ,1]
		];		
		foreach ($items as $item) {
			$player->getInventory()->addItem(
			    Item::get((int)$item[0],
				(int)$item[1],
				(int)$item[2])
			);								
		}
	}
	
	/**
	 * 
	 * Set name tag for player
	 * 
	 * @param Player $player
	 * @param string $color
	 */
	public function setNameTagT(Player $player, string $color) {
		$namep = $player->getName();
		$player->setDisplayName($color.$namep);
		$player->setNameTag($color.$namep);
	}
	
	/**
	 * 
	 * To restart data for arena
	 * 
	 * @param string $name
	 */
	public function restartArena(string $name) {
		$plugin = $this->plugin;
		$cf = $plugin->configa;
		$plugin->timecount[$name] = 10;
		$plugin->arenas[$name]['restarting'] = true;		
	}
	
	/**
	 * 
	 * To restart data player when leaved arena
	 * 
	 * @param Player $player
	 */
	public function restartPlayer(Player $player) {
		$plugin = $this->plugin;
		$sv = $plugin->getServer();
        $namep = $player->getName();		
		$player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->setFood(20);
        $player->setHealth(20);
        $player->setGamemode($sv->getDefaultGamemode());						
	}
	
	/**
	 * 
	 * To restart data player when player changed level
	 * 
	 * @param Player $player
	 */
	public function playerChangeLevel(Player $player) {
		$plugin = $this->plugin;
		$namep = $player->getName();
		foreach($plugin->arenas as $i) {
			$players = $i['players'];
			foreach($players as $p) {
			    if($p->getName() == $namep) {
				    unset($i['players'][$namep]);
		        }
			}
		}
	}
	
	/**
	 * 
	 * To perform a complete restart for the arena
	 * 
	 * @param string $name
	 */
	public function doneRestart(string $name) {
		$plugin = $this->plugin;
		$plugin->arenas[$name] = $plugin->dataBasicForArena($name);
	}
	
	public function reloadSign() {
		$plugin = $this->plugin;
		$config = $plugin->getConfig();
		$cf = $plugin->configa;
		foreach($cf->getAll() as $i) {
			$signpos = Position::fromObject(
			    Vector3::fromString($i["joinsign"][0]),
				$plugin->getServer()->getLevelByName($i["joinsign"][1])
			);
			$level = $signpos->getLevel();
            if(!$level instanceof Level) return;
            if($level->getTile($signpos) === null) return;			
			if($i['status'] === "disable") {
				$sgnt = [
                "???name???",
                "--------",
                "--------",
                "--------"				
            ];
				/** @var Sign $sign */
                $sign = $signpos->getLevel()->getTile($signpos);
                $sign->setText($sgnt[0], $sgnt[1], $sgnt[2], $sgnt[3]);
                return;
            }
			$arena = $plugin->arenas[$i['name_data']];
			foreach($arena['players'] as $p) {
				if(!isset($plugin->ingame[$p->getName()])) {
					unset($arena['players'][$p->getName()]);
				}
			}
			$online = count($arena['players']);
			$maxslot = ($i['per_team'])*4;
			$n = $i['name_data'];
            $namemap = $i['name'];			
			$this->borrow_1 = ["%map", "%slots", "%maxslots"];
			$this->borrow_2 = ["$namemap", "$online", "$maxslot"];
			$change = function(string $text): string {      
                $text = str_replace($this->borrow_1, $this->borrow_2, $text);
                return $text;
            };			
			if($plugin->arenas[$n]['restarting'] !== true) {
			    $sgnt = [
				    $change($config->get('line_1')),
					$change($config->get('line_2')),
					$change($config->get('line_3')),
					$change($config->get('line_4'))
				];
			} else {
				$sgnt[0] = $i['name'];
			    $sgnt[1] = "--------";	
			    $sgnt[2] = "Restarting...";
			    $sgnt[3] = "--------";
			}
			/** @var Sign $sign */
			$sign = $signpos->getLevel()->getTile($signpos);
            $sign->setText($sgnt[0], $sgnt[1], $sgnt[2], $sgnt[3]);
		}
	}
	
    public function getTeamP(Player $player) {
		$namep = $player->getName();
		$plugin = $this->plugin;
		if(isset($plugin->red[$namep])) {
			return "§cRED";
		}
		if(isset($plugin->blue[$namep])) {
			return "§1BLUE";
		}
		if(isset($plugin->green[$namep])) {
			return "§aGREEN";
		}
	}

    /**
	 * 
	 * To checking for player about team
	 *
	 * @param EntityDamageEvent $event
	 */
    public function onEntityDamageEvent(EntityDamageEvent $event) {
		$plugin = $this->plugin;
		$config = $plugin->getConfig();
		$entity = $event->getEntity(); 
	    if($event instanceof EntityDamageByEntityEvent) {
	        $damager = $event->getDamager();
            if($entity instanceof Player and $damager instanceof Player) {
				$named = $damager->getName();
				$namee = $entity->getName();
				if(!empty($plugin->ingame[$named]) and !empty($plugin->ingame[$named])) {
				    if($this->getTeamP($entity) == $this->getTeamP($damager)) {
					    $event->setCancelled();
                        return;					
				    }				
                    $damage = $event->getFinalDamage() >= $entity->getHealth();	
                    if($damage) {
					    $level = $damager->getLevel()->getName();
					    $players = $plugin->getServer()->getLevelByName($level)->getPlayers();										    
						
						foreach($plugin->arenas as $i) {
			                $players = $i['players'];
				            if(!empty($players[$named])) {
                                if(isset($plugin->red[$named])) {
			                        $plugin->arenas[$i['name_data']]['red'] += 1;
		                        }
		                        if(isset($plugin->blue[$named])) {
                                    $plugin->arenas[$i['name_data']]['blue'] += 1;
		                        }
		                        if(isset($plugin->green[$named])) {
								    $plugin->arenas[$i['name_data']]['green'] += 1;
		                        }
				            }
		                }
						$damager->sendMessage('+5 coins');
						$plugin->addCoins($damager, 5);
						$array_1 = ["%player", "%killer"];
						$array_2 = ["$namee", "$named"];
						foreach($players as $p) {							
                            $p->sendMessage(str_replace($array_1, $array_2,$config->get('kill_by')));												    
					    }
			        }
				}				
			}           			
		}
	}		
	
	/**
	 * 
	 * @param EntityLevelChangeEvent $event
	 */
	public function onEntityLevelChange(EntityLevelChangeEvent $event) {
		$plugin = $this->plugin;
        $player = $event->getEntity();
        if($player instanceof Player) {
			$namep = $player->getName();
            if(isset($plugin->ingame[$namep])) {
				$this->playerChangeLevel($player);
				$this->leftArena($player);
				$this->restartPlayer($player);	
                unset($plugin->ingame[$namep]);				
			}			
		}
	}
	
	/**
	 * 
	 * @param PlayerDeathEvent $event
	 */
	public function onDeath(PlayerDeathEvent $event) {
        $player = $event->getPlayer();
		$namep = $player->getName();
		$plugin = $this->plugin;
		if(isset($plugin->ingame[$namep])) {
			$event->setDrops([]);
			$this->leftArena($player);
		}		
	}
	
	/**
	 * 
	 * @param PlayerQuitEvent $event
	 */
	public function onQuit(PlayerQuitEvent $event) {
		$player = $event->getPlayer();
		$namep = $player->getName();
		$plugin = $this->plugin;
        if(isset($plugin->ingame[$namep])) {
			$this->leftArena($player);
		}		
    }
	
	/**
	 * It will calls when player open chest
	 * 
	 * @param InventoryOpenEvent $event
	 */
	public function onInventoryOpen(InventoryOpenEvent $event) {
		$plugin = $this->plugin;
		$inv = $event->getInventory();
		$player = $event->getPlayer();		
		if (!($inv instanceof ChestInventory)) {
			return;
		}
		if(isset($plugin->ingame[$player->getName()])) {	
		    $plugin->fillChest($event, $player);
		}
	}
	
	/**
	 * 
	 * To action when player touch sign
	 * 
	 * @param PlayerInteractEvent $event
	 */
	public function onPlayerInteractEvent(PlayerInteractEvent $event) {	
        $player = $event->getPlayer();
        $block = $event->getBlock();  
        $lvb = $block->getLevel(); 		
		if(!$block->getLevel()->getTile($block) instanceof Tile) {
            return;
        }
		$plugin = $this->plugin;
		$cf = $plugin->configa;
		foreach($cf->getAll() as $i) {
            $signPos = Position::fromObject(
			    Vector3::fromString($i["joinsign"][0]),
			    $plugin->getServer()->getLevelByName($i["joinsign"][1])
		    );
			$lvs = $signPos->getLevel();
            if((!$signPos->equals($block)) or $lvs->getId() != $lvb->getId()) {
                return;
            }
			$this->joinArena($player, $i['name_data']);
		}
	}
	
	/**
	 * 
	 * This is running task
	 */
	public function __destruct() {
        unset($this->scheduler);
    }
}	
