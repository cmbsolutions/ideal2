<?php declare(strict_types=1);

namespace CMB\iDEAL\Banks;

interface BankInterface
{
    public function getClient(): string;
    public function getBaseUrl(): string;
    public function getApp(): string;
}