<?php

namespace Rpc;

use Rpc\Hasher\Hasher;
use Rpc\KeyPair\KeyPair;
use WebSocket\ConnectionException;

class SubstrateRpc
{

    /**
     * @var Rpc instance
     */
    public Rpc $rpc;

    /**
     * @var Tx instance
     */
    public Tx $tx;

    /**
     * hasher instance
     *
     * @var Hasher
     */
    public Hasher $hasher;

    /**
     * Rpc construct
     *
     * @param string $endpoint
     * @param array $header
     * @throws ConnectionException
     */
    public function __construct (string $endpoint, array $header = [])
    {
        $this->rpc = new Rpc($endpoint, $header);
        $this->tx = new Tx($this->rpc);
        $this->hasher = new Hasher();
    }

    /**
     *  client close connection
     *
     * @return void
     */
    public function close ()
    {
        $this->rpc->client->close();
    }

    /**
     * setClient
     *
     * @param string $endpoint
     * @param array $header
     * @return IClient
     */
    public static function setClient (string $endpoint, array $header = []): IClient
    {
        $parse = parse_url($endpoint);
        // check url protocol is websocket or http
        if ($parse["scheme"] == "ws" || $parse["scheme"] == "wss") {
            return new WSClient($endpoint, $header);
        } elseif ($parse["scheme"] == "http" || $parse["scheme"] == "https") {
            return new HttpClient($endpoint, $header);
        }
        throw new \InvalidArgumentException("please provider http/ws endpoint");
    }

    /**
     *
     * @param keyPair $keyPair
     * @return void
     */
    public function setSigner (keyPair $keyPair)
    {
        $this->tx->setkeyPair($keyPair);
    }

}
