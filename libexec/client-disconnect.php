#!/usr/bin/env php
<?php

/**
 * eduVPN - End-user friendly VPN.
 *
 * Copyright: 2016-2017, The Commons Conservancy eduVPN Programme
 * SPDX-License-Identifier: AGPL-3.0+
 */
require_once sprintf('%s/vendor/autoload.php', dirname(__DIR__));

use SURFnet\VPN\Common\Config;
use SURFnet\VPN\Common\Http\InputValidation;
use SURFnet\VPN\Common\HttpClient\CurlHttpClient;
use SURFnet\VPN\Common\HttpClient\ServerClient;
use SURFnet\VPN\Common\Logger;
use SURFnet\VPN\Node\Connection;
use SURFnet\VPN\Node\Exception\ConnectionException;

$logger = new Logger(
    basename($argv[0])
);

try {
    $envData = [];
    $envKeys = [
        'INSTANCE_ID',
        'PROFILE_ID',
        'common_name',
        'time_unix',
        'ifconfig_pool_remote_ip',
        'ifconfig_pool_remote_ip6',
        'bytes_received',
        'bytes_sent',
        'time_duration',
    ];

    // read environment variables
    foreach ($envKeys as $envKey) {
        $envData[$envKey] = getenv($envKey);
    }

    $instanceId = InputValidation::instanceId($envData['INSTANCE_ID']);
    $configDir = sprintf('%s/config/%s', dirname(__DIR__), $instanceId);
    $config = Config::fromFile(
        sprintf('%s/config.php', $configDir)
    );

    $serverClient = new ServerClient(
        new CurlHttpClient([$config->getItem('apiUser'), $config->getItem('apiPass')]),
        $config->getItem('apiUri')
    );

    $connection = new Connection($serverClient);
    $connection->disconnect($envData);
} catch (ConnectionException $e) {
    $logger->info($e->getMessage(), $e->getEnvData());
    exit(1);
} catch (Exception $e) {
    $logger->error($e->getMessage());
    exit(1);
}
