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
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class Main extends PluginBase implements Listener {

    private Config $config;
    private array $lastDeathCoordinates = [];
    private array $lastDeathWorldNames = [];
    private array $playersWithCoordinates = [];

    public function onEnable(): void {
        @mkdir($this->getDataFolder());
        $this->saveResource('config.yml');
        $this->config = new Config($this->getDataFolder() . 'config.yml', Config::YAML);

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if ($command->getName() === 'coordinates') {
            if (count($args) === 0) {
                $sender->sendMessage("§eUse /coordinates [on/off]");
                return true;
            }

            if ($args[0] === 'on') {
                $this->enableCoordinates($sender);
                $sender->sendMessage($this->config->get("coordinates_enabled_message", "§aCoordinates Successfully Activated for All Worlds"));
            } elseif ($args[0] === 'off') {
                $this->disableCoordinates($sender);
                $sender->sendMessage($this->config->get("coordinates_disabled_message", "§cCoordinates Successfully Disabled for All Worlds"));
            } else {
                $sender->sendMessage("§eUse /coordinates [on/off]");
            }
            return true;
        }
        return false;
    }

    /**
     * @param PlayerJoinEvent $event
     */
    public function onPlayerJoin(PlayerJoinEvent $event): void {
        if (in_array($event->getPlayer()->getName(), $this->playersWithCoordinates, true)) {
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
        if (in_array($player->getName(), $this->playersWithCoordinates, true)) {
            $this->enableCoordinates($player);
            if ($this->config->get("show_death_coordinates", true) && isset($this->lastDeathCoordinates[$player->getName()])) {
                $lastCoordinates = $this->lastDeathCoordinates[$player->getName()];
                $lastDeathWorldName = $this->lastDeathWorldNames[$player->getName()];
                $message = $this->config->get("last_death_message", "Last Coordinates When You Died: [%s] [%d, %d, %d]");
                $player->sendMessage(sprintf($message, $lastDeathWorldName, $lastCoordinates->getX(), $lastCoordinates->getY(), $lastCoordinates->getZ()));
            }
        }
    }

    private function enableCoordinates(Player $player): void {
        $pk = new GameRulesChangedPacket();
        $pk->gameRules = ["showcoordinates" => new BoolGameRule(true, false)];
        $player->getNetworkSession()->sendDataPacket($pk);
        $this->playersWithCoordinates[] = $player->getName();
    }

    private function disableCoordinates(CommandSender $sender): void {
        if ($sender instanceof Player) {
            $player = $sender;
            $pk = new GameRulesChangedPacket();
            $pk->gameRules = ["showcoordinates" => new BoolGameRule(false, false)];
            $player->getNetworkSession()->sendDataPacket($pk);
            $key = array_search($player->getName(), $this->playersWithCoordinates, true);
            if ($key !== false) {
                unset($this->playersWithCoordinates[$key]);
            }
        }
    }
}
