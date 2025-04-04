<?php
/**
 * Forked from https://github.com/GPCsolutions/sentry
 * Updated D/09/03/2025
 *
 * Copyright 2004-2005 | Rodolphe Quiedeville <rodolphe~quiedeville~org>
 * Copyright 2004-2015 | Laurent Destailleur <eldy~users.sourceforge~net>
 * Copyright 2015-2018 | Raphaël Doursenaud <rdoursenaud~gpcsolutions~fr>
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

require_once(DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php');

class modSentry extends DolibarrModules {

	public function __construct($db) {

		parent::__construct($db);

		// @see https://github.com/Dolibarr/dolibarr/blob/develop/htdocs/core/modules/DolibarrModules.class.php
		$this->numero                = 105009; // 491302
		$this->editor_name           = 'luigifab';
		$this->editor_url            = 'https://github.com/luigifab/dolibarr-sentry';
		$this->family                = 'base';
		$this->name                  = 'Sentry';
		$this->module_parts          = ['syslog' => 1, 'hooks' => ['main']];
		$this->version               = '3.0.1';
		$this->description           = 'Sentry connector (errors from Luracast/Api ignored, configuration in syslog).';
		$this->const_name            = 'MAIN_MODULE_'.strtoupper($this->name); // not mb_strtoupper
		$this->picto                 = 'sentry@sentry';
		$this->config_page_url       = ['syslog.php'];
		$this->depends               = ['modSyslog'];
		$this->phpmin                = [7,2];
		$this->need_dolibarr_version = [5,0];
		$this->url_last_version      = 'https://raw.githubusercontent.com/luigifab/dolibarr-sentry/master/version.txt';
		$this->hidden                = false;

		global $conf;
		if (!isset($conf->sentry->enabled)) {
			$conf->sentry = new stdClass();
			$conf->sentry->enabled = 0;
		}
	}

	public function remove($options = '') {

		// @see https://github.com/Dolibarr/dolibarr/blob/develop/htdocs/core/modules/DolibarrModules.class.php
		// disable Sentry handler in syslog configuration
		$handlers = json_decode(dolibarr_get_const($this->db, 'SYSLOG_HANDLERS', 0), true);
		$index = array_search('mod_syslog_sentry', $handlers, true);
		if ($index !== false)
			unset($handlers[$index]);
		dolibarr_set_const($this->db, 'SYSLOG_HANDLERS', json_encode($handlers), 'chaine', 0, '', 0);

		// disable module
		return parent::remove($options);
	}
}