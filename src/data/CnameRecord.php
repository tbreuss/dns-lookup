<?php declare(strict_types=1);

namespace tebe\dnsLookup\data;

final readonly class CnameRecord extends BasicDnsRecord
{
    public function __construct(
        public string $host,
        public string $class,
        public string $type,
        public int $ttl,
        public string $target,
    ) {}
}
