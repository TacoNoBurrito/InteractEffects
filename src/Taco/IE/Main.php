<?php namespace Taco\IE;

use pocketmine\{entity\Effect,
	entity\EffectInstance,
	item\Item,
	plugin\PluginBase,
	event\Listener,
	event\player\PlayerInteractEvent};

class Main extends PluginBase implements Listener {

	/**
	 * @var array
	 */
	private $config = [];

	/**
	 * @var array
	 */
	private $cooldown = [];

	/**
	 * @var string
	 */
	private $cdMessage;

	public function onEnable() : void {
		$this->saveResource("config.yml");
		$this->config = $this->getConfig()->getAll();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->cdMessage = $this->config["cooldown-message"];
	}

	/**
	 * @param PlayerInteractEvent $event
	 */
	public function onInteract(PlayerInteractEvent $event) : void {
		$player = $event->getPlayer();
		$item = $event->getItem();
		$id = $item->getId();
		$meta = $item->getDamage();
		if(!empty($this->config[$id.":".$meta])) {
			$name = $player->getName();
			if ((!empty($this->cooldown[$name])) and (isset($this->cooldown[$name])) and (time() - $this->cooldown[$name][$id.":".$meta] < $this->config[$id.":".$meta]["cooldown"])) {
				$event->setCancelled(true);
				$message = $this->cdMessage;
				$message = str_replace("{cooldown}", $this->config[$id.":".$meta]["cooldown"] - time(), $message);
				$player->sendMessage($message);
			} else {
				$this->cooldown[$name][$id.":".$meta] = time();
				$player->getInventory()->removeItem(Item::get($item->getId()));
				$effect = new EffectInstance(Effect::getEffect($this->config[$id.":".$meta]["effectid"]), $this->config[$id.":".$meta]["length"] * 20,   $this->config[$id.":".$meta]["amplifier"]-1);
				$player->addEffect($effect);
			}
		}
	}

}
