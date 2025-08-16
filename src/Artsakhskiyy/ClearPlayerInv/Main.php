<?php

namespace Artsakhskiyy\ClearPlayerInv;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class Main extends PluginBase {

    public function onEnable(): void {
        $this->saveDefaultConfig();
    }

    private function msg(string $key, array $replacements = []): string {
        $message = $this->getConfig()->getNested("messages.$key", "");
        foreach ($replacements as $placeholder => $value) {
            $message = str_replace("{" . $placeholder . "}", $value, $message);
        }
        return $message;
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        switch (strtolower($command->getName())) {
            case "clearinv":
                if (!$sender instanceof Player) {
                    $sender->sendMessage($this->msg('only-ingame'));
                    return true;
                }
                if (!$sender->hasPermission("clearplayerinv.command.clearinv")) {
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
                if (!$sender->hasPermission("clearplayerinv.command.clearplayer")) {
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

                $target->sendMessage($this->msg('cleared-by-other', ['sender' => $sender->getName()]));
                $sender->sendMessage($this->msg('cleared-other', ['player' => $target->getName()]));
                return true;
        }
        return false;
    }
}
