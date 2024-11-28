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
        public int $serial,
        public int $refresh,
        public int $retry,
        public int $expire,
        public int $minimumTtl,
    ) {}
}
