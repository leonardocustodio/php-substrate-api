<?php

namespace Rpc;

use Codec\Utils;
use Rpc\Contract\Abi\ContractMetadataV4;
use Rpc\Contract\Call;
use Rpc\Contract\State;

class Contract
{


    /**
     * Tx send transaction instance
     *
     * @param Rpc $rpc
     */

    protected Tx $tx;


    /**
     * state instance
     *
     * @var State
     */
    public State $state;

    /**
     * call instance
     *
     * @var Call $call
     */
    public Call $call;

    /**
     * Contract construct
     *
     *
     * @param Tx $tx
     * @param string $address
     * @param ContractMetadataV4|null $ABI
     */
    public function __construct (Tx $tx, string $address = "", ContractMetadataV4 $ABI = null)
    {
        $this->tx = $tx;
        $this->state = new State($tx, $address, $ABI);
        $this->call = new Call($tx, $address, $ABI);
    }


    /**
     * deploy new contract
     *
     * @param string $code
     * @param string $data
     * @param array $option set gasLimit storageDepositLimit
     * @return string
     */

    // https://github.com/paritytech/substrate/blob/0ce39208841e519920b57d3ba5a3962188c4c66c/frame/contracts/src/lib.rs#L187
    public function new (string $code, string $data, array $option = []): string
    {
        $code = Utils::trimHex($code);
        $data = Utils::trimHex($data);
        $gasLimit = array_key_exists("gasLimit", $option) ? $option["gasLimit"] : "50000000000";
        $storageDepositLimit = array_key_exists("storageDepositLimit", $option) ? $option["storageDepositLimit"] : 0;
        $salt = Utils::hexToBytes(dechex(time()));
        // Contracts.Instantiate_with_code(value,gas_limit,storage_deposit_limit,code,data,salt)
        return $this->tx->Contracts->instantiate_with_code($storageDepositLimit, ["proof_size" => 0, "ref_time" => $gasLimit], null, Utils::hexToBytes($code), Utils::hexToBytes($data), $salt);
    }

}