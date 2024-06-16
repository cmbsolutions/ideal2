<?php declare(strict_types=1);

namespace POM\iDEAL\Worldline\Resources;

use DateInterval;
class AccessToken
{
    /**
     * @param string $token
     * @param DateInterval|null $expire
     */
    public function __construct(
        private string $token,
        private ?DateInterval $expire = null,
    ) {
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return DateInterval
     */
    public function getExpire(): DateInterval
    {
        return $this->expire;
    }

}