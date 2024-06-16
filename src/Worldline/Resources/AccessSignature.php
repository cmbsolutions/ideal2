<?php declare(strict_types=1);

namespace CMB\iDEAL\Worldline\Resources;

use DateTime;
use CMB\iDEAL\Exceptions\IDEALException;
use CMB\iDEAL\Worldline\iDEAL;

class AccessSignature
{
    private array $headers;
    public function __construct(
        private readonly iDEAL $iDEAL,
        private readonly DateTime $dateTime,
    ) {
        $this->headers = [
            'app' => $this->iDEAL->getConfig()->getBank()->getApp(),
            'client' => $this->iDEAL->getConfig()->getBank()->getClient(),
            'id' => $this->iDEAL->getConfig()->getMerchantId(),
            'date' => $this->dateTime->format(DATE_ATOM),
        ];
    }

    /**
     * Get a signature based on the headers
     *
     * @return string
     * @throws IDEALException
     */
    public function getSignature(): string
    {
        $privateKey = openssl_pkey_get_private($this->iDEAL->getConfig()->getMerchantKey(), $this->iDEAL->getConfig()->getMerchantPassphrase());

        if ($privateKey === false) {
            throw new IDEALException('Could not get private key: ' . openssl_error_string());
        }

        $headerPieces = [];

        foreach ($this->headers as $name => $value) {
            $headerPieces[] = $name . ': ' . $value;
        }

        $headerPieces = implode("\n", $headerPieces);

        $stringToSign = $headerPieces;

        $result = openssl_sign($stringToSign, $signature, $privateKey, 'sha256WithRSAEncryption');

        if ($result === false) {
            throw new IDEALException('Could not sign: ' .  openssl_error_string());
        }

        return sprintf(
            'Signature keyId="%s", algorithm="SHA256withRSA", headers="%s", signature="%s"',
            openssl_x509_fingerprint($this->iDEAL->getConfig()->getMerchantCertificate()),
            implode(' ', array_keys($this->headers)),
            base64_encode($signature)
        );
    }
}