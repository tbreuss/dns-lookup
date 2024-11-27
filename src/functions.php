<?php

namespace tebe\dnsLookup;

use tebe\dnsLookup\data\AaaaRecord;
use tebe\dnsLookup\data\ARecord;
use tebe\dnsLookup\data\CnameRecord;
use tebe\dnsLookup\data\MxRecord;
use tebe\dnsLookup\data\NsRecord;
use tebe\dnsLookup\data\PtrRecord;
use tebe\dnsLookup\data\SoaRecord;
use tebe\dnsLookup\data\TxtRecord;

function createCnameRecord(array $record): CnameRecord
{
    return new CnameRecord(
        $record['host'],
        $record['class'],
        $record['type'],
        $record['ttl'],
        $record['target'],
    );
}

function createARecord(array $record): ARecord
{
    return new ARecord(
        $record['host'],
        $record['class'],
        $record['type'],
        $record['ttl'],
        $record['ip'],
    );
}

function createAaaaRecord(array $record): AaaaRecord
{
    return new AaaaRecord(
        $record['host'],
        $record['class'],
        $record['type'],
        $record['ttl'],
        $record['ipv6'],
    );
}

function createMxRecord(array $record): MxRecord
{
    return new MxRecord(
        $record['host'],
        $record['class'],
        $record['type'],
        $record['ttl'],
        $record['pri'],
        $record['target'],
    );
}

function createNsRecord(array $record): NsRecord
{
    return new NsRecord(
        $record['host'],
        $record['class'],
        $record['type'],
        $record['ttl'],
        $record['target'],
    );
}

function createPtrRecord(array $record): PtrRecord
{
    return new PtrRecord(
        $record['host'],
        $record['class'],
        $record['type'],
        $record['ttl'],
        $record['target'],
    );
}

function createSoaRecord(array $record): SoaRecord
{
    return new SoaRecord(
        $record['host'],
        $record['class'],
        $record['type'],
        $record['ttl'],
        $record['mname'],
        $record['rname'],
        $record['serial'],
        $record['refresh'],
        $record['retry'],
        $record['expire'],
        $record['minimum-ttl'],
    );
}

function createTxtRecord(array $record): TxtRecord
{
    return new TxtRecord(
        $record['host'],
        $record['class'],
        $record['type'],
        $record['ttl'],
        $record['txt'],
        $record['entries'],
    );
}

/**
 * @return CnameRecord[]
 */
function fetchCnameRecords(string $domain): array
{
    $records = [];

    foreach (fetchDnsRecord($domain, DNS_CNAME) as $record) {
        $records[] = createCnameRecord($record);
    }

    return $records;
}

/**
 * @return ARecord[]
 */
function fetchARecords(string $domain): array
{
    $records = [];

    foreach (fetchDnsRecord($domain, DNS_A) as $record) {
        $records[] = createARecord($record);
    }

    return $records;
}

/**
 * @return AaaaRecord[]
 */
function fetchAaaaRecords(string $domain): array
{
    $records = [];

    foreach (fetchDnsRecord($domain, DNS_AAAA) as $record) {
        $records[] = createAaaaRecord($record);
    }

    return $records;
}

/**
 * @return MxRecord[]
 */
function fetchMxRecords(string $domain): array
{
    $records = [];

    foreach (fetchDnsRecord($domain, DNS_MX) as $record) {
        $records[] = createMxRecord($record);
    }

    return $records;
}

/**
 * @return NsRecord[]
 */
function fetchNsRecords(string $domain): array
{
    $records = [];

    foreach (fetchDnsRecord($domain, DNS_NS) as $record) {
        $records[] = createNsRecord($record);
    }

    return $records;
}

/**
 * @return PtrRecord[]
 */
function fetchPtrRecords(string $domain): array
{
    $records = [];

    foreach (fetchDnsRecord($domain, DNS_PTR) as $record) {
        $records[] = createPtrRecord($record);
    }

    return $records;
}

/**
 * @return SoaRecord[]
 */
function fetchSoaRecords(string $domain): array
{
    $records = [];

    foreach (fetchDnsRecord($domain, DNS_SOA) as $record) {
        $records[] = createSoaRecord($record);
    }

    return $records;
}

/**
 * @return TxtRecord[]
 */
function fetchTxtRecords(string $domain): array
{
    $records = [];

    foreach (fetchDnsRecord($domain, DNS_TXT) as $record) {
        $records[] = createTxtRecord($record);
    }

    return $records;
}

function fetchDnsRecord(string $domain, int $type): array
{
    $records = dns_get_record($domain, $type);

    if ($records === false) {
        return [];
    }

    return $records;
}

// doesn't work properly
function fetchDnsRecords(string $domain): array
{
    $records = [
        'A' => [],
        'AAAA' => [],
        'CNAME' => [],
        'MX' => [],
        'NS' => [],
        'PTR' => [],
        'SOA' => [],
        'TXT' => [],
    ];

    foreach (dns_get_record($domain, DNS_A + DNS_AAAA + DNS_CNAME + DNS_MX + DNS_NS + DNS_SOA + DNS_TXT) as $record) {
        $records[$record['type']][] = match ($record['type']) {
            'A' => createARecord($record),
            'AAAA' => createAaaaRecord($record),
            'CNAME' => createCnameRecord($record),
            'MX' => createMxRecord($record),
            'NS' => createNsRecord($record),
            'PTR' => createPtrRecord($record),
            'SOA' => createSoaRecord($record),
            'TXT' => createTxtRecord($record),
            default => throw new \InvalidArgumentException('Invalid type: ' . $record['type']),
        };
    }

    return $records;
}

function locateIp(string $ip, int $fields): array
{
    $response = file_get_contents(
        'http://ip-api.com/json/' . $ip . '?fields=' . $fields
    );

    return json_decode($response, true);
}

function validateHostname(string $domain): bool
{
    $status = filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);

    return !($status === false);
}

function countryFlag(string $countryFlag): string
{
    return 
        mb_convert_encoding(
            '&#' . (127397 + ord($countryFlag[0])) . ';',
            'UTF-8',
            'HTML-ENTITIES'
        ) .
        mb_convert_encoding(
            '&#' . (127397 + ord($countryFlag[1])) . ';',
            'UTF-8',
            'HTML-ENTITIES'
        );
}
