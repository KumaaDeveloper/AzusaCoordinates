<?php
declare(strict_types=1);

namespace KumaDev\Coordinates;

use pocketmine\plugin\PluginBase;
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\network\mcpe\protocol\types\BoolGameRule;
use pocketmine\player\Player;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\utils\Config;
use pocketmine\math\Vector3;

class Main extends PluginBase implements Listener {

    private Config $config;
    private array $lastDeathCoordinates = [];
    private array $lastDeathWorldNames = [];

    public function onEnable(): void {
        @mkdir($this->getDataFolder());
        $this->saveResource('config.yml');
        $this->config = new Config($this->getDataFolder() . 'config.yml', Config::YAML);

        if ($this->config->get("show_coordinates", true)) {
            foreach ($this->getServer()->getOnlinePlayers() as $player) {
                $this->enableCoordinates($player);
            }
        }

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    /**
     * @param PlayerJoinEvent $event
     */
    public function onPlayerJoin(PlayerJoinEvent $event): void {
        if ($this->config->get("show_coordinates", true)) {
            $this->enableCoordinates($event->getPlayer());
        }
    }

    /**
     * @param PlayerDeathEvent $event
     */
    public function onPlayerDeath(PlayerDeathEvent $event): void {
        $player = $event->getPlayer();
        $lastCoordinates = $player->getPosition();
        $this->lastDeathCoordinates[$player->getName()] = $lastCoordinates;
        $this->lastDeathWorldNames[$player->getName()] = $player->getWorld()->getFolderName();
    }

    /**
     * @param PlayerRespawnEvent $event
     */
    public function onPlayerRespawn(PlayerRespawnEvent $event): void {
        $player = $event->getPlayer();
        if ($this->config->get("show_coordinates", true)) {
            $this->enableCoordinates($player);
        }
        if ($this->config->get("show_death_coordinates", true) && isset($this->lastDeathCoordinates[$player->getName()])) {
            $lastCoordinates = $this->lastDeathCoordinates[$player->getName()];
            $lastDeathWorldName = $this->lastDeathWorldNames[$player->getName()];
            $message = $this->config->get("last_death_message", "Last Coordinates When You Died: [%s] [%d, %d, %d]");
            $player->sendMessage(sprintf($message, $lastDeathWorldName, $lastCoordinates->getX(), $lastCoordinates->getY(), $lastCoordinates->getZ()));
        }
    }

    private function enableCoordinates(Player $player): void {
        $pk = new GameRulesChangedPacket();
        $pk->gameRules = ["showcoordinates" => new BoolGameRule(true, false)];
        $player->getNetworkSession()->sendDataPacket($pk);
    }
}
