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

include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

class modSentry extends DolibarrModules {

	public function __construct($db) {

		global $conf;
		global $sentryReady;
		parent::__construct($db);

		$this->numero = 105009;
		$this->rights_class = 'sentry';
		$this->family = 'technic';
		$this->name = preg_replace('/^mod/i', '', get_class($this));
		$this->editor_name = 'Editor name';
		$this->editor_url = 'https://www.example.com/';
		$this->description = 'Send errors to Sentry (status: '.(empty($sentryReady) ? '<u>disabled</u>' : '<b>enabled</b>').'). Configuration in syslog. (PHP only - Not enabled for Luracast/Api)';
		$this->version = '0.0.2';
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		$this->picto = 'sentry@sentry';
		$this->module_parts = ['syslog' => 1];
		$this->config_page_url = ['syslog.php'];
		$this->hidden = false;
		$this->depends = ['modSyslog'];
		$this->phpmin = [5, 4];
		$this->need_dolibarr_version = [5, 0];
		if (!isset($conf->sentry->enabled)) {
			$conf->sentry = new stdClass();
			$conf->sentry->enabled = 0;
		}
	}

	public function remove($options = '') {
		// disable Sentry handler in syslog configuration
		$handlers = json_decode(dolibarr_get_const($this->db, 'SYSLOG_HANDLERS', 0), true);
		$index = array_search('mod_syslog_sentry', $handlers, true);
		if ($index !== false)
			unset($handlers[$index]);
		dolibarr_set_const($this->db, 'SYSLOG_HANDLERS', json_encode($handlers), 'chaine', 0, '', 0);
		// disable module
		return $this->_remove([], $options);
	}
}
