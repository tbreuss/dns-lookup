<?php declare(strict_types=1);

namespace tebe\dnsLookup\data;

abstract readonly class BasicDnsRecord
{
    public function __construct(
        public string $host,
        public string $class,
        public string $type,
        public int $ttl,
    ) {}
}
