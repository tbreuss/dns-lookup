<?php
ini_set('display_errors', false);
ini_set('display_startup_errors', false);
ini_set('log_errors', true);
ini_set('error_log', dirname(__DIR__) . '/logs/errors.log');
error_reporting(E_ALL);
require dirname(__DIR__) . '/vendor/autoload.php';
?>
<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Simple PHP script to look up the entries of A, AAAA, NS, CNAME, MX, SOA and TXT records">
    <title>Simple DNS Lookup</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="d-flex flex-column h-100">
<main class="flex-shrink-0">
    <div class="container">
    <div class="header clearfix">
        <h3 class="text-muted"><a href="./" class="text-dark text-decoration-none">Simple DNS Lookup</a></h3>
    </div>
    <?php
    // If domain is included in URL, prefill form with domain or if form is submitted display domain in it
    if (isset($_POST['domain'])) {
        $value = $_POST['domain'];
    } elseif(isset($_GET['domain'])) {
        $value = $_GET['domain'];
    } else {
        $value = '';
    }

    // Parse url to extract host
    $postedDomain = $_POST['domain'] ?? '';
    $parsedUrl = parse_url($postedDomain);

    if (array_key_exists('host', $parsedUrl)) {
        $domain = $parsedUrl['host'];
    } else {
        $domain = $postedDomain;
    }

    // Page URL: check if "?domain=" is in the URL to adapt http_referer content
    if (isset($_SERVER['HTTP_REFERER'])) {
        if (str_contains($_SERVER['HTTP_REFERER'], '?domain=')) {
            $pageUrlDomain = $_SERVER['HTTP_REFERER'];
        } else {
            $pageUrlDomain = $_SERVER['HTTP_REFERER'] . "?domain=" . $value;
        }
    }
    ?>
    <div class="jumbotron">
        <form action="./" method="post">
            <div class="input-group">
                <input
                    type="search"
                    class="form-control input-lg text-center"
                    name="domain"
                    id="domain"
                    placeholder="https://www.domain.com/page.html or domain.com"
                    value="<?= $value ?>"
                    required
                    autofocus
                >
                <button type="submit" name="submit" class="btn btn-primary btn-lg">Lookup</button>
            </div>
        </form>
    </div>
    <?php if (isset($_POST['submit'])):  ?>
        <?php if (tebe\dnsLookup\validateHostname($domain)): ?>
            <div class="marketing">
            <table class="table table-striped table-bordered">
                <thead class="bg-primary">
                <tr>
                    <th class="text-center">Record</th>
                    <th class="text-center">TTL</th>
                    <th>Entries for <?= $domain ?></th>
                </tr>
                </thead>
                <!-- A RECORD -->
                <?php if (empty($ipV4Records = tebe\dnsLookup\fetchARecords($domain))): ?>
                    <tr>
                        <td class="align-middle text-center"><h4><span class="badge text-bg-primary">A</span></h4></td>
                        <td class="align-middle text-center">NA</td>
                        <td class="align-middle bg-warning-subtle">No record</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($ipV4Records as $ipV4Record): ?>
                        <?php $locatedIp = tebe\dnsLookup\locateIp($ipV4Record->ipv4, 4195842) ?>
                        <tr>
                            <td class="align-middle text-center"><h4><span class="badge text-bg-primary">A</span></h4></td>
                            <td class="align-middle text-center"><?= $ipV4Record->ttl ?></td>
                            <?php if ($locatedIp): ?>
                                <td class="align-middle bg-success-subtle">
                                    <?= $ipV4Record->ipv4 ?> 
                                    (<?= tebe\dnsLookup\countryFlag($locatedIp['countryCode']) ?> 
                                    <?= $locatedIp['countryCode'] ?> ·
                                    <b>ISP</b> <?= $locatedIp['isp'] ?> · 
                                    <b>ORG</b> <?= $locatedIp['org'] ?> · 
                                    <b>AS</b> <?= $locatedIp['asname'] ?>)
                                </td>
                            <?php else: ?>
                                <td class="align-middle bg-success-subtle">
                                    <?= $ipV4Record->ipv4 ?> 
                                </td>                                
                            <?php endif ?>
                        </tr>
                    <?php endforeach ?>
                <?php endif ?>
                <!-- AAAA RECORD -->
                <?php if (empty($ipV6Records = tebe\dnsLookup\fetchAaaaRecords($domain))): ?>
                    <tr>
                        <td class="align-middle text-center"><h4><span class="badge text-bg-info">AAAA</span></h4></td>
                        <td class="align-middle text-center">NA</td>
                        <td class="align-middle bg-warning-subtle">No record</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($ipV6Records as $ipV6Record): ?>
                        <?php $locatedIp = tebe\dnsLookup\locateIp($ipV6Record->ipv6, 4195842) ?>
                        <tr>
                            <td class="align-middle text-center"><h4><span class="badge text-bg-info">AAAA</span></h4></td>
                            <td class="align-middle text-center"><?= $ipV6Record->ttl ?></td>
                            <?php if ($locatedIp): ?>
                                <td class="align-middle bg-success-subtle">
                                    <?= $ipV6Record->ipv6 ?>
                                    (<?= tebe\dnsLookup\countryFlag($locatedIp['countryCode']) ?>
                                    <?= $locatedIp['countryCode'] ?> ·
                                    <b>ISP</b> <?= $locatedIp['isp'] ?> ·
                                    <b>ORG</b> <?= $locatedIp['org'] ?> ·
                                    <b>ASNAME</b> <?= $locatedIp['asname'] ?>)
                                </td>
                            <?php else: ?>
                                <td class="align-middle bg-success-subtle">
                                    <?= $ipV6Record->ipv6 ?>
                                </td>
                            <?php endif ?>
                        </tr>
                    <?php endforeach ?>
                <?php endif ?>
                <!-- NS RECORD -->
                <?php if (empty($nsRecords = tebe\dnsLookup\fetchNsRecords($domain))): ?>
                    <tr>
                        <td class="align-middle text-center"><h4><span class="badge text-bg-success">NS</span></h4></td>
                        <td class="align-middle text-center">NA</td>
                        <td class="align-middle bg-warning-subtle">No record</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($nsRecords as $nsRecord): ?>
                        <?php $locatedIp = tebe\dnsLookup\locateIp(gethostbyname($nsRecord->target), 2) ?>
                        <tr>
                            <td class="align-middle text-center"><h4><span class="badge text-bg-success">NS</span></h4></td>
                            <td class="align-middle text-center"><?= $nsRecord->ttl ?></td>
                            <?php if ($locatedIp): ?>
                                <td class="align-middle bg-success-subtle">
                                    <?= $nsRecord->target ?>
                                    (<?= tebe\dnsLookup\countryFlag($locatedIp['countryCode']) ?>
                                    <?= $locatedIp['countryCode'] ?> · 
                                    <?= gethostbyname($nsRecord->target) ?>)
                                </td>
                            <?php else: ?>
                                <td class="align-middle bg-success-subtle">
                                    <?= $nsRecord->target ?>
                                    (<?= gethostbyname($nsRecord->target) ?>)
                                </td>                                
                            <?php endif ?>
                        </tr>
                    <?php endforeach ?>
                <?php endif ?>
                <!-- PTR RECORD -->
                <?php /*if (empty($ptrRecords = tebe\dnsLookup\fetchPtrRecords($domain))): ?>
                    <tr>
                        <td class="align-middle text-center"><h4><span class="badge text-bg-success">PTR</span></h4></td>
                        <td class="align-middle text-center">NA</td>
                        <td class="align-middle bg-warning-subtle">No record</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($ptrRecords as $ptrRecord): ?>
                        <?php $ipapidc = tebe\dnsLookup\locateIp($ipV6Record->ipv6, 2) ?>
                        <tr>
                            <td class="align-middle text-center"><h4><span class="badge text-bg-success">NS</span></h4></td>
                            <td class="align-middle text-center"><?= $ptrRecord->ttl ?></td>
                            <td class="align-middle bg-success-subtle">
                                <?= $ptrRecord->target ?>
                                (<?= tebe\dnsLookup\countryFlag($locatedIp['countryCode']) ?>
                                <?= $ipapidc['countryCode'] ?> · 
                                <?= gethostbyname($ptrRecord->target) ?>)
                            </td>
                        </tr>
                    <?php endforeach ?>
                <?php endif */ ?>
                <!-- MX RECORD -->
                <?php if (empty($mxRecords = tebe\dnsLookup\fetchMxRecords($domain))): ?>
                    <tr>
                        <td class="align-middle text-center"><h4><span class="badge text-bg-danger">MX</span></h4></td>
                        <td class="align-middle text-center">NA</td>
                        <td class="align-middle bg-warning-subtle">No record</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($mxRecords as $mxRecord): ?>
                        <?php $locatedIp = tebe\dnsLookup\locateIp(gethostbyname($mxRecord->target), 2) ?>
                        <tr>
                            <td class="align-middle text-center"><h4><span class="badge text-bg-danger">MX</span></h4></td>
                            <td class="align-middle text-center"><?= $mxRecord->ttl ?></td>
                            <?php if ($locatedIp): ?>
                                <td class="align-middle bg-success-subtle">
                                    [<?= $mxRecord->pri ?>] <?= $mxRecord->target ?>
                                    (<?= tebe\dnsLookup\countryFlag($locatedIp['countryCode']) ?>
                                    <?= $locatedIp['countryCode'] ?> ·
                                    <?= gethostbyname($mxRecord->target) ?>)
                                </td>
                            <?php else: ?>
                                <td class="align-middle bg-success-subtle">
                                    [<?= $mxRecord->pri ?>] <?= $mxRecord->target ?>
                                    (<?= gethostbyname($mxRecord->target) ?>)
                                </td>
                            <?php endif ?>    
                        </tr>
                    <?php endforeach ?>
                <?php endif ?>
                <!-- CNAME RECORD -->
                <?php if (empty($cnameRecords = tebe\dnsLookup\fetchCnameRecords($domain))): ?>
                    <tr>
                        <td class="align-middle text-center"><h4><span class="badge text-bg-secondary">CNAME</span></h4></td>
                        <td class="align-middle text-center">NA</td>
                        <td class="align-middle bg-warning-subtle">No record</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($cnameRecords as $cnameRecord): ?>
                        <?php $locatedIp = tebe\dnsLookup\locateIp(gethostbyname($cnameRecord->target), 2) ?>
                        <tr>
                            <td class="align-middle text-center"><h4><span class="badge text-bg-secondary">CNAME</span></h4></td>
                            <td class="align-middle text-center"><?= $cnameRecord->ttl ?></td>
                            <?php if ($locatedIp): ?>
                                <td class="align-middle bg-success-subtle">
                                    <?= $cnameRecord->target ?>
                                    (<?= tebe\dnsLookup\countryFlag($locatedIp['countryCode']) ?>
                                    <?= $locatedIp['countryCode'] ?> ·
                                    <?= gethostbyname($cnameRecord->target) ?>)
                                </td>
                            <?php else: ?>
                                <td class="align-middle bg-success-subtle">
                                    <?= $cnameRecord->target ?>
                                    (<?= gethostbyname($cnameRecord->target) ?>)
                                </td>
                            <?php endif ?>
                        </tr>
                    <?php endforeach ?>
                <?php endif ?>
                <!-- SOA RECORD -->
                <?php if (empty($soaRecords = tebe\dnsLookup\fetchSoaRecords($domain))): ?>
                    <tr>
                        <td class="align-middle text-center"><h4><span class="badge text-bg-warning">SOA</span></h4></td>
                        <td class="align-middle text-center">NA</td>
                        <td class="align-middle bg-warning-subtle">No record</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($soaRecords as $soaRecord): ?>
                        <tr>
                            <td class="align-middle text-center"><h4><span class="badge text-bg-warning">SOA</span></h4></td>
                            <td class="align-middle text-center"><?= $soaRecord->ttl ?></td>
                            <td class="align-middle bg-success-subtle">
                                Mname: <?= $soaRecord->mname ?><br>
                                Rname: <?= $soaRecord->rname ?><br>
                                Serial: <?= $soaRecord->serial ?><br>
                                Refresh: <?= $soaRecord->refresh ?><br>
                                Retry: <?= $soaRecord->retry ?><br>
                                Expire: <?= $soaRecord->expire ?><br>
                                Minimum TTL: <?= $soaRecord->minimumTtl ?><br>
                            </td>
                        </tr>
                    <?php endforeach ?>
                <?php endif ?>
                <!-- TXT RECORD -->
                <?php if (empty($txtRecords = tebe\dnsLookup\fetchTxtRecords($domain))): ?>
                    <tr>
                        <td class="align-middle text-center"><h4><span class="badge text-bg-secondary">TXT</span></h4></td>
                        <td class="align-middle text-center">NA</td>
                        <td class="align-middle bg-warning-subtle">No record</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($txtRecords as $txtRecord): ?>
                        <tr>
                            <td class="align-middle text-center"><h4><span class="badge text-bg-secondary">TXT</span></h4></td>
                            <td class="align-middle text-center"><?= $txtRecord->ttl ?></td>
                            <td class="align-middle bg-success-subtle text-break"><?= $txtRecord->txt ?></td>
                        </tr>
                    <?php endforeach ?>
                <?php endif ?>
            </table>
            <p>Direct link: <a href="<?= $pageUrlDomain ?>"><?= $pageUrlDomain ?></a></p>
        </div>
        <?php else: ?>
            <div class="marketing alert alert-danger">
                The given domain "<?= $domain ?>" is invalid.
            </div>
        <?php endif ?>
    <?php endif ?>
</div>
</main>
<footer class="footer mt-auto">
    <div class="container d-flex justify-content-between">
        <div class="text-start">A tiny <a href="https://tebe.ch/" target="_blank">tebe.ch</a> project</div>
        <div class="text-end">&copy; Simple DNS Lookup - <a href="https://github.com/tbreuss/simple-dns-Lookup" target="_blank">Sourcecode on GitHub</a></div>
    </div>
</footer>
</body>
</html>
