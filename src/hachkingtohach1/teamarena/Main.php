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

namespace hachkingtohach1\teamarena;

use hachkingtohach1\teamarena\arena\Arena;
use hachkingtohach1\teamarena\data\ChestItems;
use hachkingtohach1\teamarena\data\Shop;
use pocketmine\utils\Config;
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\command\{Command,CommandSender};

class Main extends PluginBase implements Listener {
	
	/** @var array $arenas */
	public $arenas = array();
	
	/** @var array $players */
    public $players = array();
	
	/** @var array $red */
	public $red = array();
	
	/** @var array $blue */
	public $blue = array();
	
	/** @var array $green */
	public $green = array();
    
	/** @var array $chooseteam */
    public $chooseteam = array();

    /** @var array $distagsave */
    public $distagsave = array();

    /** @var array $natagsave */
    public $natagsave = array();	
	
	/** @var array $ingame */
	public $ingame = array();
	
	/** @var array $timecount */
	public $timecount = array();
	
	/** @var array $setup */
	public $setup = array();
	
	/** @var array $filledchests */
	public $filledchests = array();
	
	/** @var array $sentforms */
    public $sentforms = [];
	
	/** @var int $lastid */
    public $lastid = 0;
	
	/** @var int $time_refill */
	public $time_refill = 180;
	
    public function onEnable() : void {		
	    $this->checkApi();
		$this->saveDefaultConfig();		
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->events = new Arena($this);
		$this->saveResource("arenas.yml");		
		$datafolder = $this->getDataFolder();
		$this->configa = new Config($datafolder.'arenas.yml', Config::YAML);
		$cf = $this->getConfig();
		switch($cf->get('economy')) {
			case "EconomyAPI":
			    $this->economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
			break;
		}
		$this->checkDataArenas();
	}
	
	public function onDisable() : void {}
	
	public function checkDataArenas() {
		$cf = $this->configa;
		foreach($cf->getAll() as $i) {			
			$this->arenas[$i['name_data']] = $this->dataBasicForArena($i['name_data']);
			if(!$this->getServer()->isLevelLoaded($i['level'])) {
                $this->getServer()->loadLevel($i['level']);
			}
			$this->getLogger()->info($i['name_data'].' data was loaded!');
		}
	}
	
	/**
	 * 
	 * @param string $name
	 * @return array $data
	 */
	public function dataBasicForArena(string $name) {
		$cf = $this->configa;
		$cfa = $cf->get($name);
		$data = [
			'players' => [],
		    'name' => $cfa['name'],
			'name_data' => $cfa['name_data'],
			'level' => $cfa['level'],
			'finish_points' => $cfa['finish_points'],
			'restarting' => false,
            'red' => 0,
            'blue' => 0,
            'green' => 0				
		];
		return $data;
	}
	
	/**
	 * 
	 * @return bool true:false
	 */
	public function checkApi() : bool {
		$this->api = $this->getServer()->getPluginManager()->getPlugin("KillCounter");
		if($this->api == null) {
			$this->getLogger()->warning('Install plugin: https://poggit.pmmp.io/ci/hachkingtohach1/KillCounter/');
			return false;
		}
		return true;
	}
	
	/**
	 * 
	 * @param CommandSender $s
 	 * @param Command $cmd
	 * @param String $label
	 * @param array $args
	 * @return bool false
	 */
	public function onCommand(CommandSender $s, Command $cmd, String $label, array $args) : bool {
		switch($cmd->getName()) {
            case "mypos":			
		        if(!$s instanceof Player) return true;
				$s->sendMessage(
				    '[TeamArena] Your pos: x= '.(int)$s->getX().
					' y= '.(int)$s->getY().
					' z= '.(int)$s->getZ()
				);
			break;
			case "blockpos":			
		        if(!$s instanceof Player) return true;
				$s->sendMessage('[TeamArena] Break one block!');
				$this->setup[$s->getName()] = 0;
			break;
		}
		return false;
	}
	
	/**
	 *
	 * @param Player $player
	 * @return int $coins
	 */
	public function getCoins(Player $player) {
		$cf = $this->getConfig();
		$namep = $player->getName();
		switch($cf->get('economy')) {
			case "EconomyAPI":
				$coins = $this->economy->myMoney($player);
			break;
		}
		return $coins;
	}
	
	/**
	 *
	 * @param Player $player
	 * @param $amount
	 */
	public function addCoins(Player $player, $amount) {
		$cf = $this->getConfig();
		$namep = $player->getName();
		switch($cf->get('economy')) {
			case "EconomyAPI":
				$this->economy->addMoney($player, $amount);
			break;
		}		
	}
	
	/**
	 *
	 * @param Player $player
	 * @param $amount
	 */
	public function takeCoins(Player $player, $amount) {
		$cf = $this->getConfig();
		$namep = $player->getName();
		switch($cf->get('economy')) {
			case "EconomyAPI":
				$this->economy->reduceMoney($player, $amount);
			break;
		}		
	}
	
	/**
	 *
	 * To fill for chest when player touch the chest
	 *
	 * @param $chest
	 * @param Player $player
	 */
	public function fillChest($chest, Player $player) {
		$items = ChestItems::$items;
		$block = $chest->getInventory()->getHolder();
		$data = $block->getX().",".$block->getY().",".$block->getZ();
		if (isset($this->filledchests[$data])) { 
		    return; 
		}
		$this->filledchests[$data] = $data;
		$chest->getInventory()->clearAll();
		$inv = $chest->getInventory();
		for($i = 0; $i < 2; $i++) {
			foreach($items as $key => $item) {
				if(rand(1, 1000) > $item[1]) {
					$count = $item[2] ? rand(1, 2) : 1;	
					if(rand(1,5) == 2) {
                        $inv->addItem(Item::get(0, 0, 0));
					} else {
                        $inv->addItem(Item::get($item[0], 0, $count));					
					}
                    if($item[0] === Item::BOW) {
						$inv->addItem(Item::get(Item::ARROW, 0, rand(3, 6)));	
					}
					unset($items[$key]);				
				}
			}			
		}
	}
	
	/**
	 * 
	 * @param BlockBreakEvent $event
	 */
	public function onBreak(BlockBreakEvent $event) {
        $player = $event->getPlayer();
        $block = $event->getBlock();
		$namep = $player->getName();
        if(isset($this->setup[$namep])) {
            switch($this->setup[$namep]) {
                case 0:                   
                    $player->sendMessage(
					    '[TeamArena] Block pos: x= '.(int)$block->getX().
						'y= '.(int)$block->getY().
						'z= '.(int)$block->getZ()
					);
                    unset($this->setup[$namep]);
                    $event->setCancelled(true);
                break;
			}
		}
	}
}
