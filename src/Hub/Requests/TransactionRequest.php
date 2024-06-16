<?php declare(strict_types=1);

namespace CMB\iDEAL\Hub\Requests;

use CMB\iDEAL\Exceptions\IDEALException;
use CMB\iDEAL\Hub\Resources\Transaction;

final class TransactionRequest extends Request
{
    protected string $endpoint = '/v2/merchant-cpsp/transactions';
    protected string $requestMethod = 'POST';

    /**
     * Creates a new transaction on the Currence iDEAL hub
     *
     * @param int $amount Amount to be paid in cents
     * @param string $description Description of the transaction, payer will see this on the transaction page
     * @param string $reference Payment reference of the transaction, this will show up on bank statements
     * @param string $returnUrl The URL the payer whould return to after completing or cancelling the payment
     * @param string|null $callbackUrl Optional URL Currecnce will send the webhook notification to
     * @return Transaction
     * @throws IDEALException
     */
    public function execute(int $amount, string $description, string $reference, string $returnUrl, string $callbackUrl = null): Transaction
    {
        $this->body = [
            'amount' => [
                'amount' => $amount,
                'type'  => 'FIXED',
                'currency' => 'EUR'
            ],
            'creditor' => [
                "countryCode" => "NL",
            ],
            'returnUrl' => $returnUrl,
            'description' => $description,
            'reference' => $reference,
        ];

        if (!is_null($callbackUrl)) {
            $this->body['transactionCallbackUrl'] = $callbackUrl;
        }
        
        $transactionData = $this->send();

        return Transaction::fromArray($transactionData);
    }
}