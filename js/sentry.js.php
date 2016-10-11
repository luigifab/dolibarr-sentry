<?php
/* Send logs to a Sentry server
 * Copyright (C) 2016  Raphaël Doursenaud <rdoursenaud@gpcsolutions.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Client side logging support
 */

// Load Dolibarr environment
if (false === (@include '../../main.inc.php')) {  // From htdocs directory
	require '../../../main.inc.php'; // From "custom" directory
}

require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';

global $conf, $db, $user;

if (array_key_exists('mod_syslog_sentry', $conf->loghandlers) && !empty($conf->global->SYSLOG_SENTRY_DSN)) {
/**
 * Convert Dolibarr levels to JavaScript levels
 * @see https://developer.mozilla.org/en-US/Add-ons/SDK/Tools/console#Logging_Levels
 */
$log_level = dolibarr_get_const($db, "SYSLOG_LEVEL", 0);
switch (intval($log_level)) {
	case LOG_EMERG:
	case LOG_ALERT:
	case LOG_CRIT:
	case LOG_ERR:
		$log_level = array(
			'error'
		);
		break;
	case LOG_WARNING:
	case LOG_NOTICE:
		$log_level = array(
			'error',
			'warn'
		);
		break;
	case LOG_INFO:
	default:
		$log_level = array(
			'error',
			'warn',
			'info'
		);
		break;
	case LOG_DEBUG:
		$log_level = array(
			'error',
			'warn',
			'info',
			'debug'
		);
		break;
};

// Filter out secret key
$dsn = parse_url($conf->global->SYSLOG_SENTRY_DSN);
$public_dsn = $dsn['scheme'] . '://' . $dsn['user'] . '@' . $dsn['host'] . $dsn['path'];

header('Content-Type: application/javascript');
?>
Raven
	.config('<?php echo $public_dsn ?>')
	.addPlugin(Raven.Plugins.Console,
		null,
		{
			levels: <?php echo json_encode($log_level) ?>
		})
	.install();
Raven.setUserContext({username: '<?php echo $user->login ?>'});
Raven.setRelease('<?php echo DOL_VERSION ?>');
<?php
/**
 * Catch jQuery errors
 */
?>
$(document).ajaxError(function (event, jqXHR, ajaxSettings, thrownError) {
	Raven.captureMessage(thrownError || jqXHR.statusText, {
		extra: {
			type: ajaxSettings.type,
			url: ajaxSettings.url,
			data: ajaxSettings.data,
			status: jqXHR.status,
			error: thrownError || jqXHR.statusText,
			response: jqXHR.responseText.substring(0, 100)
		}
	});
});
<?php
}
