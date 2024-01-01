<?php
/**
 * Created J/09/11/2023
 * Updated D/24/12/2023
 *
 * Copyright 2004-2005 | Rodolphe Quiedeville <rodolphe~quiedeville~org>
 * Copyright 2004-2015 | Laurent Destailleur <eldy~users.sourceforge~net>
 * Copyright 2015-2018 | Raphaël Doursenaud <rdoursenaud~gpcsolutions~fr>
 * Copyright 2022-2024 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
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

class ActionsSentry {

	public function addHtmlHeader($parameters, &$object, &$action, $hookmanager) {

		global $conf, $user;
		if (!empty($conf->sentry->enabled) && !empty($_SERVER['SENTRY_ENABLED'])) {

			try {
				ob_start();
				require_once(__DIR__.'/head.phtml');
				$html = ob_get_clean();
			}
			catch (Throwable $t) {
				$html = '<script nonce="'.getNonce().'">alert("Sentry: '.addslashes($t->getMessage()).'");</script>'."\n";
			}

			if (empty($hookmanager->resPrint))
				$hookmanager->resPrint = $html;
			else
				$hookmanager->resPrint .= $html;
		}

		return 0;
	}

	protected function getUrl($conf, $file) {
		return dol_buildpath('custom/sentry/'.$file, 1).'?v=7910';
	}
}