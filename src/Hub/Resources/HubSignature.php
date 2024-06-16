<?php declare(strict_types=1);

namespace CMB\iDEAL\Hub\Resources;

use DateTime;
use DateTimeZone;
use Exception;
use Firebase\JWT\JWT;
use OpenSSLAsymmetricKey;
use OpenSSLCertificate;
use CMB\iDEAL\Exceptions\IDEALException;
use CMB\iDEAL\Hub\SigningAlgorithm;

class HubSignature
{
    /**
     * @var array
     */
    private array $headers;

    /**
     * @param string $signingCertificate
     * @param OpenSSLAsymmetricKey|OpenSSLCertificate|string $signingKey
     * @param SigningAlgorithm $signingAlgorithm
     * @param string $merchantId
     * @param string $tokenRequestId
     * @param string $requestId
     * @throws IDEALException
     */
    public function __construct(
        string $signingCertificate,
        private OpenSSLAsymmetricKey|OpenSSLCertificate|string $signingKey,
        private SigningAlgorithm $signingAlgorithm,
        string $merchantId,
        string $tokenRequestId,
        string $requestId
    ) {
        try {
            $utcDate = (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d\TH:i:s.000\Z');
        } catch (Exception) {
            throw new IDEALException('Failed generating date, this shouldn\'t happen');
        }

        $this->headers = [
            'typ' => 'jose+json',
            'x5c' => [base64_encode($signingCertificate)],
            'alg' => $signingAlgorithm->value,
            'https://idealapi.nl/sub' => $merchantId,
            'https://idealapi.nl/iss' => $merchantId,
            'https://idealapi.nl/scope' => 'MERCHANT',
            'https://idealapi.nl/acq' => substr($merchantId, 0, 4),
            'https://idealapi.nl/iat' => $utcDate,
            'https://idealapi.nl/jti' => $requestId,
            'https://idealapi.nl/token-jti' => $tokenRequestId,
            'crit' => [
                "https://idealapi.nl/sub",
                "https://idealapi.nl/iss",
                "https://idealapi.nl/acq",
                "https://idealapi.nl/iat",
                "https://idealapi.nl/jti",
                "https://idealapi.nl/path",
                "https://idealapi.nl/scope",
                "https://idealapi.nl/token-jti"
            ],
        ];
    }

    /**
     * Get a detached JWT from the request
     *
     * @param string|array $payload
     * @param string $path
     * @return string
     */
    public function getSignature(string|array $payload, string $path): string
    {
        // add the path to the headers array
        $headers = $this->headers;

        $headers['https://idealapi.nl/path'] = $path;

        // create the JWT, not using the library because of non-standard working of ideal hub
        $header = ['typ' => 'JWT'];
        $header = array_merge($header, $headers);
        $header['alg'] = $this->signingAlgorithm->value;

        $segments = [];
        $segments[] = JWT::urlsafeB64Encode(JWT::jsonEncode($header));

        if (is_array($payload)) {
            $payload = JWT::jsonEncode($payload);
        }

        $segments[] = JWT::urlsafeB64Encode($payload);

        $signing_input = implode('.', $segments);

        $signature = JWT::sign($signing_input, $this->signingKey, $this->signingAlgorithm->value);
        $segments[] = JWT::urlsafeB64Encode($signature);

        $segments[1] = '';

        return implode('.', $segments);
    }
}