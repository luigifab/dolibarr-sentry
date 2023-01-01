<?php
/**
 * Copyright (C) 2004-2005 Rodolphe Quiedeville <rodolphe~quiedeville~org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy~users.sourceforge~net>
 * Copyright (C) 2015-2018 Raphaël Doursenaud   <rdoursenaud~gpcsolutions~fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

require_once DOL_DOCUMENT_ROOT.'/core/modules/syslog/logHandler.php';
require_once dirname(__FILE__).'/Raven/Autoloader.php';

if (in_array(getenv('REMOTE_ADDR'), ['::1', '127.0.0.1'])) {
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
}

if (stripos(getenv('REQUEST_URI'), '/api/') === false) {
	Raven_Autoloader::register();
}

class mod_syslog_sentry extends LogHandler implements LogHandlerInterface {

	public $code = 'sentry';

	public function __construct() {
		$this->start();
	}

	public function getName() {
		return 'Sentry';
	}

	public function getVersion() {
		return '0.0.2';
	}

	public function isActive() {
		return class_exists('Raven_Client');
	}

	public function configure() {
		return [
			[
				'constant' => 'SYSLOG_SENTRY_DSN',
				'name'     => 'DSN',
				'default'  => '',
				'attr'     => 'size="100" placeholder="https://<key>:<secret>@app.getsentry.com/<project>"',
			]
		];
	}

	public function checkConfiguration() {

		global $sentryError;
		$this->start(true);
		$errors = array_filter([$sentryError]);

		if (!empty($errors)) {
			global $db;
			// disable Sentry handler in syslog configuration
			$handlers = json_decode(dolibarr_get_const($db, 'SYSLOG_HANDLERS', 0), true);
			$index = array_search('mod_syslog_sentry', $handlers, true);
			if ($index !== false)
				unset($handlers[$index]);
			dolibarr_set_const($db, 'SYSLOG_HANDLERS', json_encode($handlers), 'chaine', 0, '', 0);
		}

		return $errors;
	}

	public function start($force = false) {

		global $conf;
		global $sentryFlag;
		global $sentryReady;
		global $sentryError;
		global $user;

		$dsn = $conf->global->SYSLOG_SENTRY_DSN;
		$cnf = (string) $conf->global->SYSLOG_HANDLERS;

		if (empty($sentryFlag) && !empty($dsn) && (stripos($cnf, 'mod_syslog_sentry') !== false) && ($force || empty($_POST['SYSLOG_SENTRY_DSN']))) {
			$sentryFlag = true;
			try {
				$this->client = new Raven_Client($dsn, [
					'curl_method' => 'sync',
					'release' => DOL_VERSION,
					'tags' => [
						'runtime'  => 'PHP '.PHP_VERSION,
						'username' => is_object($user) ? $user->login : '',
					]
				]);

				if ($force)
					$this->client->captureMessage('TEST: Sentry syslog configuration check', null, Raven_Client::DEBUG);

				$error_handler = new Raven_ErrorHandler($this->client);
				$error_handler->registerExceptionHandler(false);
				$error_handler->registerErrorHandler(false);

				$sentryReady = true;
			}
			catch (Throwable $t) {
				$sentryError = $t->getMessage();
			}
		}
	}

	public function export($content) {

		global $sentryReady;
		if ($sentryReady) {

			$map = [
				LOG_EMERG   => Raven_Client::FATAL,
				LOG_ALERT   => Raven_Client::FATAL,
				LOG_CRIT    => Raven_Client::ERROR,
				LOG_ERR     => Raven_Client::ERROR,
				LOG_WARNING => Raven_Client::WARNING,
				LOG_NOTICE  => Raven_Client::WARNING,
				LOG_INFO    => Raven_Client::INFO,
				LOG_DEBUG   => Raven_Client::DEBUG,
			];

			$level = array_key_exists($content['level'], $map) ? $map[$content['level']] : Raven_Client::ERROR;

			if (($level == Raven_Client::DEBUG) || ($level == Raven_Client::INFO) || (substr($content['message'], 0, 3) === '---')) {
				// nothing todo
			}
			else if (substr($content['message'], 0, 3) === 'sql') {
				global $db;
				$query = substr($content['message'], 4, strlen($content['message']));
				$this->client->captureMessage('SQL: '.$query, $level);
			}
			else {
				$this->client->captureMessage($content['message'], null, $level);
			}
		}
	}
}
