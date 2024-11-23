<?php require dirname(__DIR__) . '/vendor/autoload.php' ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Simple PHP script to look up the entries of A, AAAA, NS, CNAME, MX, SOA and TXT records">
    <title>Simple DNS Lookup</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <div class="header clearfix">
        <h3 class="text-muted">Simple DNS Lookup</h3>
    </div>

    <?php
    ini_set('display_errors', 1); // Uncomment to display errors
    ini_set('display_startup_errors', 1); // Uncomment to display errors
    error_reporting(E_ALL); // Uncomment to display errors

    // If domain is included in URL, prefill form with domain or if form is submitted display domain in it
    if (isset($_POST['domain'])) {
        $value = $_POST['domain'];
    } elseif(isset($_GET['domain'])) {
        $value = $_GET['domain'];
    } else {
        $value = '';
    }

    // Parse url to extract host
    $posted_domain = $_POST['domain'] ?? '';
    $parsed_url = parse_url($posted_domain);

    if (array_key_exists('host', $parsed_url)) {
        $domain = $parsed_url['host'];
    } else {
        $domain = $posted_domain;
    }

    // Page URL : check if "?domain=" is in the URL to adapt http_referer content
    if (isset($_SERVER['HTTP_REFERER'])) {
        if ((strpos($_SERVER['HTTP_REFERER'], '?domain=') !== false)) {
            $page_url_domain = $_SERVER['HTTP_REFERER'];
        } else {
            $page_url_domain = $_SERVER['HTTP_REFERER'] . "?domain=" . $value;
        }
    }
    ?>

    <div class="jumbotron">
        <form action="./" method="post">
            <div class="form-group">
                <input
                        type="search"
                        class="form-control input-lg text-center"
                        name="domain"
                        id="domain"
                        placeholder="https://www.domain.com/page.html or domain.com"
                        value="<?= $value ?>"
                        required
                >
                <button type="submit" name="submit" class="btn btn-primary btn-lg">Lookup</button>
            </div>
        </form>
    </div>

    <?php if (isset($_POST['submit'])):  ?>

        <div class="row marketing">
            <table class="table table-striped table-bordered table-responsive">
                <thead class="bg-primary">
                <tr>
                    <th class="text-center">Record</th>
                    <th class="text-center">TTL</th>
                    <th>Entries for <?= $domain ?></th>
                </tr>
                </thead>
                <!-- A RECORD -->
                <?php if (empty($ipV4Records = tebe\dnsLookup\fetchIpV4Records($domain))): ?>
                    <tr>
                        <td class="align-middle text-center"><h4><span class="badge text-bg-primary">A</span></h4></td>
                        <td class="align-middle text-center">NA</td>
                        <td class="align-middle bg-warning-subtle">No record</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($ipV4Records as $ipV4Record): ?>
                        <tr>
                            <td class="align-middle text-center"><h4><span class="badge text-bg-primary">A</span></h4></td>
                            <td class="align-middle text-center"><?= $ipV4Record->ttl ?></td>
                            <td class="align-middle bg-success-subtle">
                                <?php
                                $ipapi = file_get_contents(
                                    'http://ip-api.com/json/' . $ipV4Record->ipv4 . '?fields=4195842'
                                ); // https://ip-api.com/docs/api:json#test
                                $ipapidc = json_decode($ipapi, true);
                                $country_code_flag = $ipapidc['countryCode']; // Uppercase
                                echo mb_convert_encoding(
                                    '&#' . (127397 + ord($country_code_flag[0])) . ';',
                                    'UTF-8',
                                    'HTML-ENTITIES'
                                );
                                echo mb_convert_encoding(
                                    '&#' . (127397 + ord($country_code_flag[1])) . ';',
                                    'UTF-8',
                                    'HTML-ENTITIES'
                                );
                                echo(" " . $ipapidc['countryCode'] . " · " . $ipV4Record->ipv4 . " · <small>(<b>ISP</b> " . $ipapidc['isp'] . " <b>ORG</b> " . $ipapidc['org'] . " <b>AS</b> " . $ipapidc['asname']);
                                echo ")</small>";
                                ?>
                            </td>
                        </tr>
                    <?php endforeach ?>
                <?php endif ?>

                <!-- AAAA RECORD -->
                <?php if (empty($ipV6Records = tebe\dnsLookup\fetchIpV6Records($domain))): ?>
                    <tr>
                        <td class="align-middle text-center"><h4><span class="badge text-bg-info">AAAA</span></h4></td>
                        <td class="align-middle text-center">NA</td>
                        <td class="align-middle bg-warning-subtle">No record</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($ipV6Records as $ipV6Record): ?>
                        <tr>
                            <td class="align-middle text-center"><h4><span class="badge text-bg-info">AAAA</span></h4></td>
                            <td class="align-middle text-center"><?= $ipV6Record->ttl ?></td>
                            <td class="align-middle bg-success-subtle">
                                <?php
                                $ipapi = file_get_contents(
                                    'http://ip-api.com/json/' . $ipV6Record->ipv6 . '?fields=4195842'
                                );
                                $ipapidc = json_decode($ipapi, true);
                                $country_code_flag = $ipapidc['countryCode']; // Uppercase
                                echo mb_convert_encoding(
                                    '&#' . (127397 + ord($country_code_flag[0])) . ';',
                                    'UTF-8',
                                    'HTML-ENTITIES'
                                );
                                echo mb_convert_encoding(
                                    '&#' . (127397 + ord($country_code_flag[1])) . ';',
                                    'UTF-8',
                                    'HTML-ENTITIES'
                                );
                                echo(" " . $ipapidc['countryCode'] . " · " . $ipV6Record->ipv6 . " ·<small> <b>ISP</b> " . $ipapidc['isp'] . " · <b>ORG</b> " . $ipapidc['org'] . " · <b>ASNAME</b> " . $ipapidc['asname']);
                                echo "</small>";
                                ?>
                            </td>
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
                        <tr>
                            <td class="align-middle text-center"><h4><span class="badge text-bg-success">NS</span></h4></td>
                            <td class="align-middle text-center"><?= $nsRecord->ttl ?></td>
                            <td class="align-middle bg-success-subtle">
                                <?php
                                echo($nsRecord->target);
                                echo " (";
                                $ipapi = file_get_contents(
                                    'http://ip-api.com/json/' . gethostbyname($nsRecord->target) . '?fields=2'
                                );
                                $ipapidc = json_decode($ipapi, true);
                                $country_code_flag = $ipapidc['countryCode']; // Uppercase
                                echo mb_convert_encoding(
                                    '&#' . (127397 + ord($country_code_flag[0])) . ';',
                                    'UTF-8',
                                    'HTML-ENTITIES'
                                );
                                echo mb_convert_encoding(
                                    '&#' . (127397 + ord($country_code_flag[1])) . ';',
                                    'UTF-8',
                                    'HTML-ENTITIES'
                                );
                                echo(" " . $ipapidc['countryCode'] . " · " . gethostbyname($nsRecord->target));
                                echo ")";
                                ?>
                            </td>
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
                        <tr>
                            <td class="align-middle text-center"><h4><span class="badge text-bg-success">NS</span></h4></td>
                            <td class="align-middle text-center"><?= $ptrRecord->ttl ?></td>
                            <td class="align-middle bg-success-subtle">
                                <?php
                                echo $ptrRecord->target;
                                echo " (";
                                $ipapi = file_get_contents(
                                    'http://ip-api.com/json/' . gethostbyname($ptrRecord->target) . '?fields=2'
                                );
                                $ipapidc = json_decode($ipapi, true);
                                $country_code_flag = $ipapidc['countryCode']; // Uppercase
                                echo mb_convert_encoding(
                                    '&#' . (127397 + ord($country_code_flag[0])) . ';',
                                    'UTF-8',
                                    'HTML-ENTITIES'
                                );
                                echo mb_convert_encoding(
                                    '&#' . (127397 + ord($country_code_flag[1])) . ';',
                                    'UTF-8',
                                    'HTML-ENTITIES'
                                );
                                echo(" " . $ipapidc['countryCode'] . " · " . gethostbyname($ptrRecord->target));
                                echo ")";
                                ?>
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
                        <tr>
                            <td class="align-middle text-center"><h4><span class="badge text-bg-danger">MX</span></h4></td>
                            <td class="align-middle text-center"><?= $mxRecord->ttl ?></td>
                            <td class="align-middle bg-success-subtle">
                                <?php
                                    echo("[" . $mxRecord->pri . "] " . $mxRecord->target . " (");
                                    $ipapi = file_get_contents(
                                        'http://ip-api.com/json/' . gethostbyname($mxRecord->target) . '?fields=2'
                                    );
                                    $ipapidc = json_decode($ipapi, true);
                                    $country_code_flag = $ipapidc['countryCode']; // Uppercase
                                    echo mb_convert_encoding(
                                        '&#' . (127397 + ord($country_code_flag[0])) . ';',
                                        'UTF-8',
                                        'HTML-ENTITIES'
                                    );
                                    echo mb_convert_encoding(
                                        '&#' . (127397 + ord($country_code_flag[1])) . ';',
                                        'UTF-8',
                                        'HTML-ENTITIES'
                                    );
                                    echo(" " . $ipapidc['countryCode'] . " · " . gethostbyname($mxRecord->target));
                                    echo ")";
                                ?>
                            </td>
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
                        <tr>
                            <td class="align-middle text-center"><h4><span class="badge text-bg-secondary">CNAME</span></h4></td>
                            <td class="align-middle text-center"><?= $cnameRecord->ttl ?></td>
                            <td class="align-middle bg-success-subtle">
                                <?php
                                    echo $cnameRecord->target;
                                    echo " (";
                                    $ipapi = file_get_contents(
                                        'http://ip-api.com/json/' . gethostbyname($cnameRecord->target) . '?fields=2'
                                    );
                                    $ipapidc = json_decode($ipapi, true);
                                    $country_code_flag = $ipapidc['countryCode']; // Uppercase
                                    echo mb_convert_encoding(
                                        '&#' . (127397 + ord($country_code_flag[0])) . ';',
                                        'UTF-8',
                                        'HTML-ENTITIES'
                                    );
                                    echo mb_convert_encoding(
                                        '&#' . (127397 + ord($country_code_flag[1])) . ';',
                                        'UTF-8',
                                        'HTML-ENTITIES'
                                    );
                                    echo(" " . $ipapidc['countryCode'] . " · " . gethostbyname($cnameRecord->target));
                                    echo ")";
                                ?>
                            </td>
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
                                Rname : <?= $soaRecord->rname ?><br>
                                Serial : <?= $soaRecord->serial ?><br>
                                Refresh : <?= $soaRecord->refresh ?><br>
                                Retry : <?= $soaRecord->retry ?><br>
                                Expire : <?= $soaRecord->expire ?><br>
                                Minimum TTL : <?= $soaRecord->minimumTtl ?><br>
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
                            <td class="align-middle bg-success-subtle"><?= wordwrap($txtRecord->txt, 80, "<br/>\n", true) ?></td>
                        </tr>
                    <?php endforeach ?>
                <?php endif ?>
            </table>
            <p>Direct link : <a href="<?= $page_url_domain ?>"><?= $page_url_domain ?></a></p>
        </div>
    <?php endif ?><!-- ENDIF FORM SUBMITTED -->

    <footer class="footer">
        <p class="text-center">&copy; Simple DNS Lookup -
            <a href="https://github.com/tbreuss/dns-Lookup">Sourcecode on GitHub</a></p>
    </footer>
</div>
</body>
</html>
