<?php declare(strict_types=1);

namespace CMB\iDEAL\Hub;

enum SigningAlgorithm: string
{
    case ES256 = 'ES256';
    case ES384 = 'ES384';
}
