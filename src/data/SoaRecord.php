<?php declare(strict_types=1);

namespace tebe\dnsLookup\data;

final readonly class SoaRecord extends BasicDnsRecord
{
    public function __construct(
        public string $host,
        public string $class,
        public string $type,
        public int $ttl,
        public string $mname,
        public string $rname,
        public string $serial,
        public string $refresh,
        public string $retry,
        public string $expire,
        public string $minimumTtl,
    ) {}
}
