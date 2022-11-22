<?php
require_once 'vendor/autoload.php';

use Ds\Set;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\ConnectionInterface;
use Ratchet\WebSocket\MessageComponentInterface;


$chatComponente = new class implements MessageComponentInterface {
    private Set $connections;

    public function __construct()
    {
        $this->connections= new Set();
    }
    public function onOpen(ConnectionInterface $conn)
    {
        echo "nova conexao" . PHP_EOL;
        $this->connections->add($conn);
    }
    
    public function onClose(ConnectionInterface $conn)
    {
        echo "encerrada uma conexao" . PHP_EOL;
        $this->connections->remove($conn);
    }
    
    public function onError(ConnectionInterface $conn , \Exception $e)
    {
        echo "ERRO".$e->getTraceAsString();
    }
    
    public function onMessage(ConnectionInterface $from , MessageInterface $msg)
    {
        foreach ($this->connections as $connection) {
            if ($connection !== $from) {
                $connection->send((string)$msg);
            }
        }
    }
};
$server =IoServer::factory(
    new HttpServer(
        new WsServer($chatComponente)
    ),
    
    8002);

$server->run();