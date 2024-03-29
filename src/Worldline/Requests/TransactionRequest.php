<?php declare(strict_types=1);

namespace POM\iDEAL\Worldline\Requests;

readonly class TransactionRequest
{
    /**
     * @param iDEAL $iDEAL
     */
    public function __construct(private iDEAL $iDEAL)
    {

    }
}