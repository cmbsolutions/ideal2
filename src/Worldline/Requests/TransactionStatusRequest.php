<?php declare(strict_types=1);

namespace CMB\iDEAL\Worldline\Requests;

use CMB\iDEAL\Exceptions\IDEALException;
use CMB\iDEAL\Worldline\Resources\TransactionStatus;

final class TransactionStatusRequest extends Request
{
    protected string $requestMethod = 'GET';

    /**
     * @param string $transactionId
     * @return TransactionStatus
     * @throws IDEALException
     */
    public function execute(string $transactionId): TransactionStatus
    {
        $this->endpoint = '/xs2a/routingservice/services/ob/pis/v3/payments/'. urlencode($transactionId) .'/status';

        $data = parent::send();

        return TransactionStatus::fromArray($data);
    }
}