<?php

namespace BlawoHD;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\item\Armor;
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as TF;

class Citybuild extends PluginBase implements Listener
{
    public $prefix = TF::AQUA . "RushyMC" . TF::DARK_GRAY . " | " . TF::WHITE;
    public $pvp = array();

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        @mkdir("/home/Datenbank/");
        @mkdir("/home/Datenbank/Citybuild/" . "players/");
        $config = new Config("/home/Datenbank/Citybuild/" . "config.yml", Config::YAML);
        if ($config->get("PVP") == null) {
            $config->set("PVP", array("Citybuild-1"));
            $config->save();
        }
        $this->pvp = $config->get("PVP");
    }

    public function isRepairable(Item $item): bool
    {
        return $item instanceof Tool || $item instanceof Armor;
    }

    public function onDeath(PlayerDeathEvent $event)
    {
        $event->setKeepInventory(true);
    }

    public function onJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();

        $event->setJoinMessage(TF::DARK_GRAY . "[" . TF::GREEN . "+" . TF::DARK_GRAY . "] " . TF::GOLD . $player->getName());

        if ($player->getFirstPlayed()) {
            $player->sendMessage(TF::BOLD . TF::DARK_GRAY . "➤ " . TF::RESET . TF::GREEN . "Herzlich Willkommen auf " . TF::YELLOW . "RushyMC");
            $player->sendMessage(TF::BOLD . TF::DARK_GRAY . "➤ " . TF::RESET . TF::GREEN . "Erfahre mehr auf unserem Discord unter " . TF::YELLOW . "discord.rushymc.de");

            //$player->sendMessage(TF::BOLD . TF::DARK_GRAY . "» " . TF::RESET . TF::GREEN . "Alle Informationen findest du in den " . TF::YELLOW . "Settings" . TF::GRAY . "(" . TF::YELLOW . "Einstellungen" . TF::GRAY . ")");
        } else {

        }
        $config = new Config("/home/Datenbank/Citybuild/" . "players/" . strtolower($player->getName()) . ".yml", Config::YAML);

        $config->set("Name", $player->getName());
        $config->set("Fly", "off");
        $config->save();
    }

    public function onQuit(PlayerQuitEvent $event)
    {
        $player = $event->getPlayer();

        $event->setQuitMessage(TF::DARK_GRAY . "[" . TF::DARK_RED . "-" . TF::DARK_GRAY . "] " . TF::GOLD . $player->getName());

        $config = new Config("/home/Datenbank/Citybuild/" . "players/" . strtolower($player->getName()) . ".yml", Config::YAML);

        $config->set("Fly", "off");
        $config->save();
    }

    public function onDamage(EntityDamageEvent $event)
    {
        $player = $event->getEntity();
        $config = new Config("/home/Datenbank/Citybuild/" . "players/" . strtolower($player->getName()) . ".yml", Config::YAML);
        if ($event->getEntity() instanceof Player) {
            $entity = $event->getEntity();

            if (in_array($entity->getLevel()->getFolderName(), $this->pvp)) {
                $event->setCancelled();
            }

            if ($event instanceof EntityDamageByEntityEvent) {

                if ($event->getEntity() instanceof Player && $event->getDamager() instanceof Player) {

                    if (in_array($event->getEntity()->getLevel()->getFolderName(), $this->pvp)) {
                        $event->setCancelled();
                    }
                }

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
        $config = new Config("/home/Datenbank/Citybuild/" . "players/" . strtolower($sender->getName()) . ".yml", Config::YAML);

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

        if ($cmd->getName() === "feed") {
            if ($sender->hasPermission("feed.use")) {
                $sender->setFood(20);
                $sender->sendMessage($this->prefix . TF::GREEN . "Du wurdest gefüttert");
            }
        }

        if ($cmd->getName() === "size") {
            if ($sender->hasPermission("size.use")) {
                if (isset($args[0])) {
                    if (is_numeric($args[0])) {
                        if ($args[0] >= 0 && $args[0] <= 20) {
                            $sender->sendMessage($this->prefix . TF::GREEN . "Deine Grösse beträgt derzeit " . TF::GOLD . $args[0] . TF::GREEN . ".");
                        } else {
                            $sender->sendMessage($this->prefix . TF::RED . "Wähle eine Grösse zwischen " . TF::GOLD . "0 - 20" . TF::GREEN . ".");
                        }
                    }
                } else {
                    $sender->sendMessage(TF::RED . "/size <0-20>");
                }
            }
        }

        $index = $sender->getInventory()->getHeldItemIndex();
        $item = $sender->getInventory()->getItem($index);
        if ($cmd->getName() === "repair") {
            if ($sender->hasPermission("repair.use")) {
                if ($sender instanceof Player) {
                    if ($item->getDamage() > 0) {
                        $sender->getInventory()->setItem($index, $item->setDamage(0));
                    }
                    $sender->sendMessage($this->prefix . TF::GREEN . "Das Item wurde erfolgreich repariert");
                }
            }
        }

        if ($cmd->getName() === "repairall") {
            if ($sender->hasPermission("repairall.use")) {
                if ($sender instanceof Player) {
                    foreach ($sender->getInventory()->getContents() as $index => $item) {
                        if ($this->isRepairable($item)) {
                            if ($item->setDamage() > 0) {
                                $sender->getInventory()->setItem($index, $item->setDamage(0));
                            }
                        }
                    }
                    foreach ($sender->getArmorInventory()->getContents() as $index => $item) {
                        if ($this->isRepairable($item)) {
                            if ($item->setDamage() > 0) {
                                $sender->getArmorInventory()->setItem($index, $item->setDamage(0));
                            }
                        }
                    }
                    $sender->sendMessage($this->prefix . TF::GREEN . "Alle Items wurden erfolgreich repariert");
                }
            }
        }

        if ($cmd->getName() === "clear") {
            if($sender->hasPermission("clear.use")) {
                $sender->getInventory()->clearAll();
                $sender->getArmorInventory()->clearAll();
                $sender->sendMessage($this->prefix . TF::GREEN . "Du hast dein Inventar gecleart!");
            }
        }
        return false;
    }
}