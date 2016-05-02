<?php
require __DIR__.'/../vendor/autoload.php';

use Lvht\MsgpackRpc\Handler;
use Lvht\MsgpackRpc\Server;
use Lvht\MsgpackRpc\ForkServer;
use Lvht\MsgpackRpc\DefaultMsgpacker;
use Lvht\MsgpackRpc\StdIo;
use Lvht\Phpfold\Folder;

$server = new ForkServer(new DefaultMsgpacker, new StdIo, new Folder);
$server->loop(true);
