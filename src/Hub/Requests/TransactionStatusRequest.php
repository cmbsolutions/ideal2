<?php declare(strict_types=1);

namespace CMB\iDEAL\Hub\Requests;

use CMB\iDEAL\Exceptions\IDEALException;
use CMB\iDEAL\Hub\Resources\Transaction;

final class TransactionStatusRequest extends Request
{
    protected string $requestMethod = 'GET';

    /**
     * @param string $transactionId
     * @return Transaction
     * @throws IDEALException
     */
    public function execute(string $transactionId): Transaction
    {
        $this->endpoint = '/v2/merchant-cpsp/transactions/'.urlencode($transactionId);

        $data = parent::send();

        return Transaction::fromArray($data);
    }
}