<?php

namespace BlawoHD;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as TF;

class Citybuild extends PluginBase implements Listener
{
    public $prefix = TF::GOLD . "Citybuild" . TF::DARK_GRAY . " | " . TF::WHITE;

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        @mkdir($this->getDataFolder());
        @mkdir($this->getDataFolder() . "players/");
    }

    public function onJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();
        $config = new Config($this->getDataFolder() . "players/" . strtolower($player->getName()) . ".yml", Config::YAML);

        $config->set("Fly", "off");
        $config->save();
    }

    public function onQuit(PlayerQuitEvent $event)
    {
        $player = $event->getPlayer();
        $config = new Config($this->getDataFolder() . "players/" . strtolower($player->getName()) . ".yml", Config::YAML);

        $config->set("Fly", "off");
        $config->save();
    }

    public function onDamage(EntityDamageEvent $event)
    {
        $player = $event->getEntity();
        $config = new Config($this->getDataFolder() . "players/" . strtolower($player->getName()) . ".yml", Config::YAML);

        if ($event->getEntity() instanceof Player) {
            if ($event instanceof EntityDamageByEntityEvent) {
                if ($config->get("Fly") === "on") {
                    $event->setCancelled(true);
                } elseif ($config->get("Fly") === "off") {
                    $event->setCancelled(false);
                }
            }
        }
    }

    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool
    {
        $config = new Config($this->getDataFolder() . "players/" . strtolower($sender->getName()) . ".yml", Config::YAML);

        if ($cmd->getName() === "fly") {
            if ($sender->hasPermission("fly.use")) {
                if ($config->get("Fly") === "off") {
                    $sender->sendMessage($this->prefix . TF::GREEN . "Dein Flugmodus wurde " . TF::GOLD . "Aktiviert");
                    $sender->setAllowFlight(true);

                    $config->set("Fly", "on");
                    $config->save();
                } elseif ($config->get("Fly") === "on") {
                    $sender->sendMessage($this->prefix . TF::RED . "Dein Flugmodus wurde " . TF::GOLD . "Deaktiviert");
                    $sender->setAllowFlight(false);

                    $config->set("Fly", "off");
                    $config->save();
                }
            }
        }

        if ($cmd->getName() === "heal") {
            if ($sender->hasPermission("heal.use")) {
                $sender->setHealth($sender->getMaxHealth());
                $sender->sendMessage($this->prefix . TF::GREEN . "Du hast nun wieder " . TF::GOLD . "volle Herzen");
            }
        }

        if ($cmd->getName() === "repair") {
            if ($sender->hasPermission("repair.use")) {
                // soon
            }
        }

        if ($cmd->getName() === "coins") {
            if ($sender->hasPermission("coins.use")) {
                // soon
            }

        }
        return false;
    }
}