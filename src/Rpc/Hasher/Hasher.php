<?php

namespace Rpc\Hasher;

use Crypto\sr25519;
use Rpc\Util;
use SodiumException;

class Hasher
{
    /**
     * @var sr25519 FFI
     * sr25519 hasher
     * https://github.com/gmajor-encrypt/sr25519-bindings
     */
    public sr25519 $sr;

    public function __construct ()
    {
        $this->sr = new sr25519();
    }

    /**
     * hash a hex string by hash name
     *
     * XXhash https://docs.substrate.io/v3/advanced/cryptography/#xxhash
     * blake2 https://docs.substrate.io/v3/advanced/cryptography/#blake2
     * sr25519 https://docs.substrate.io/v3/advanced/cryptography/#sr25519
     * ed25519 https://docs.substrate.io/v3/advanced/cryptography/#ed25519
     *
     *
     * @param string $hasher
     * @param string $hex
     * @return string
     * @throws SodiumException
     */
    public function ByHasherName (string $hasher, string $hex): string
    {
        switch ($hasher) {
            case "Blake2_128":
                return sprintf("%s", sodium_bin2hex(sodium_crypto_generichash(hex2bin(Util::trimHex($hex)), '', 16)));
            case "Blake2_256":
                return sprintf("%s", sodium_bin2hex(sodium_crypto_generichash(hex2bin(Util::trimHex($hex)))));
            case "Twox128":
                // https://php.watch/versions/8.1/xxHash
                return sprintf("%s", $this->TwoxHash($hex, 128));
            case "Twox256":
                return sprintf("%s", $this->TwoxHash($hex, 256));
            case "Twox64Concat":
                return sprintf("%s%s", $this->XXHash64(0, $hex), Util::trimHex($hex));
            case "Identity":
                return $hex;
            case "Blake2_128Concat":
                return sprintf("%s%s", sodium_bin2hex(sodium_crypto_generichash(hex2bin( Util::trimHex($hex)), '', 16)), Util::trimHex($hex));
            default:
                throw new \InvalidArgumentException(sprintf("invalid hasher %s", $hasher));
        }
    }

    /**
     * @param int $seed
     * @param string $data
     * @return string
     */
    public function XXHash64 (int $seed, string $data): string
    {
        return $this->sr->XXHash64CheckSum($seed, $data);
    }


    /**
     * Twox hasher
     *
     * @param string $data
     * @param int $bitLength
     * @return string
     *
     */
    public function TwoxHash (string $data, int $bitLength): string
    {
        $iterations = ceil($bitLength / 64);
        $hash = "";
        for ($seed = 0; $seed < $iterations; $seed++) {
            $hash .= $this->sr->XXHash64CheckSum($seed, $data);
        }
        return $hash;
    }
}