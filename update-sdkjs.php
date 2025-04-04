<?php
/**
 * Created J/02/11/2023
 * Updated L/10/03/2025
 *
 * Copyright 2012      | Jean Roussel <contact~jean-roussel~fr>
 * Copyright 2022-2025 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * Copyright 2022-2023 | Fabrice Creuzot <fabrice~cellublue~com>
 * https://github.com/luigifab/dolibarr-sentry
 *
 * This program is free software, you can redistribute it or modify
 * it under the terms of the GNU General Public License (GPL) as published
 * by the free software foundation, either version 3 of the license, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but without any warranty, without even the implied warranty of
 * merchantability or fitness for a particular purpose. See the
 * GNU General Public License (GPL) for more details.
 */

chdir(__DIR__);
error_reporting(E_ALL);
ini_set('display_errors', (PHP_VERSION_ID < 80100) ? '1' : 1);

if (PHP_SAPI != 'cli')
	exit(-1);

if (is_dir('./sentry/')) {
	$dest  = './sentry/js/sdk.min.js';
	$class = './sentry/class/actions_sentry.class.php';
}
else if (is_dir('./src/')) {
	$dest  = './src/js/sdk.min.js';
	$class = './src/class/actions_sentry.class.php';
}

function sendRequest(string $url) {

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
	curl_setopt($ch, CURLOPT_TIMEOUT, 20);
	curl_setopt($ch, CURLOPT_ENCODING , ''); // @see https://stackoverflow.com/q/17744112/2980105
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64; rv:136.0) Gecko/20100101 Firefox/136.0');

	$result = curl_exec($ch);
	$result = (($result === false) || (curl_errno($ch) !== 0)) ? trim('CURL_ERROR '.curl_errno($ch).' '.curl_error($ch)) : $result;
	curl_close($ch);

	return $result;
}

// @see https://github.com/getsentry/sentry-javascript
// https://browser.sentry-cdn.com/9.5.0/bundle.min.js => js/sentry/sdk.min.js
$results = sendRequest('https://api.github.com/repos/getsentry/sentry-javascript/releases');
if (mb_strpos($results, '"tag_name": "') !== false) {

	$results = @json_decode($results, true);
	foreach ($results as $result) {

		if (!empty($result['tag_name']) && !empty($result['created_at']) && empty($result['prerelease']) && empty($result['draft'])) {

			$version = $result['tag_name']; // x.x.x
			$url = 'https://browser.sentry-cdn.com/'.$version.'/bundle.min.js';

			echo 'latest version is: ',$version,"\n";
			echo 'download from: '.$url,"\n";

			$data = sendRequest($url);
			if (!empty($data) && (mb_strlen($data) > 1000) && (mb_strpos($data, '/*! @sentry/browser '.$version) === 0)) {
				$data = trim(str_replace('//# sourceMappingURL=bundle.min.js.map', '', $data));
				file_put_contents($dest, $data);
				echo ' ok: sdk updated',"\n";
				$data = file_get_contents($class);
				$data = preg_replace('#v=\d+#', 'v='.str_replace('.', '', $version), $data);
				file_put_contents($class, $data);
				echo ' ok: cache key',"\n";
				exit(0);
			}

			echo ' fatal: invalid response:',"\n",trim(mb_substr($data, 0, 100)),"\n\n";
			break;
		}
	}
}
else {
	echo ' fatal: invalid response:',"\n",trim(mb_substr($results, 0, 100)),"\n\n";
}

exit(-1);