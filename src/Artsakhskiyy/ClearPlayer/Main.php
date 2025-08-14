<?php

namespace Artsakhskiyy\ClearPlayer;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class Main extends PluginBase {

    public function onEnable(): void {
        $this->saveDefaultConfig();
    }

    private function msg(string $key): string {
        $v = $this->getConfig()->getNested("messages.$key");
        if (!is_string($v) || $v === '') {
            return match($key) {
                'no-permission' => "§l§2ClearPlayer » §cYou do not have permissions to use this command...",
                'only-ingame' => "§l§2ClearPlayer » §cThis command is only available in the game...",
                'usage-clearinv' => "§l§2ClearPlayer » Usage: /clearinv",
                'usage-clearplayer' => "§l§2ClearPlayer » Usage: /clearplayer <nickname>",
                'cleared-own' => "§l§2ClearPlayer » Your inventory has been successfully cleared...",
                'cleared-by-other' => "§l§2ClearPlayer » Your inventory has been cleared by §e{sender}§2...",
                'cleared-other' => "§l§2ClearPlayer » You have cleared §e{player}§2's inventory...",
                'player-not-found' => "§l§2ClearPlayer » §cPlayer not found...",
                default => "",
            };
        }
        return $v;
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        switch (strtolower($command->getName())) {
            case "clearinv":
                if (!$sender instanceof Player) {
                    $sender->sendMessage($this->msg('only-ingame'));
                    return true;
                }
                if (!$sender->hasPermission("clearinv.use")) {
                    $sender->sendMessage($this->msg('no-permission'));
                    return true;
                }
                if (count($args) > 0) {
                    $sender->sendMessage($this->msg('usage-clearinv'));
                    return true;
                }

                $sender->getInventory()->clearAll();
                $sender->getArmorInventory()->clearAll();
                $sender->getOffHandInventory()->clearAll();

                $sender->sendMessage($this->msg('cleared-own'));
                return true;

            case "clearplayer":
                if (!$sender->hasPermission("clearinv.other")) {
                    $sender->sendMessage($this->msg('no-permission'));
                    return true;
                }
                if (count($args) !== 1) {
                    $sender->sendMessage($this->msg('usage-clearplayer'));
                    return true;
                }

                $target = $this->getServer()->getPlayerExact($args[0]);
                if ($target === null) {
                    $sender->sendMessage($this->msg('player-not-found'));
                    return true;
                }

                $target->getInventory()->clearAll();
                $target->getArmorInventory()->clearAll();
                $target->getOffHandInventory()->clearAll();

                $target->sendMessage(str_replace("{sender}", $sender->getName(), $this->msg('cleared-by-other')));
                $sender->sendMessage(str_replace("{player}", $target->getName(), $this->msg('cleared-other')));
                return true;
        }
        return false;
    }
}
