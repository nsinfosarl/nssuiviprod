<?php
/* Copyright (C) 2026 Nicolas Silobre <contact@ns-info.fr>
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    nssuiviprod/class/actions_nssuiviprod.class.php
 * \ingroup nssuiviprod
 * \brief   Hooks of the module nssuiviprod
 */

/**
 * Class of the hooks of the module nssuiviprod
 */
class ActionsNssuiviprod
{
	/**
	 * @var DoliDB	Database handler
	 */
	public $db;

	/**
	 * @var string	Error message
	 */
	public $error = '';

	/**
	 * @var string[]	Error messages
	 */
	public $errors = array();

	/**
	 * @var array<string,mixed>	Results of the hook (read by the caller in $hookmanager->resArray)
	 */
	public $results = array();

	/**
	 * @var string	HTML code returned by the hook
	 */
	public $resprints = '';

	/**
	 * Constructor
	 *
	 * @param	DoliDB	$db		Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * Permission to change the order of the lines by drag and drop (core/ajax/row.php): URLs of a site
	 *
	 * @param	array<string,mixed>	$parameters		Hook parameters (table_element_line, fk_element, element_id, perm)
	 * @param	CommonObject		$object			Object (GenericObject)
	 * @param	string				$action			Action
	 * @param	HookManager			$hookmanager	Hook manager
	 * @return	int									1 to use the permission of the module, 0 otherwise
	 */
	public function checkRowPerms($parameters, &$object, &$action, $hookmanager)
	{
		global $user;

		if ($parameters['table_element_line'] == 'nssuiviprod_siteurl' && $parameters['fk_element'] == 'fk_site' && isModEnabled('nssuiviprod')) {
			$this->results = array('perm' => ($user->hasRight('nssuiviprod', 'site', 'write') ? 1 : 0));
			if ($this->results['perm'] && (int) $parameters['element_id'] > 0) {
				// Event of the agenda of the site: new order of its URLs. Never blocks the change of the order: row.php loads
				// no translation ($langs empty), and any error of the event is only logged
				global $langs, $conf;
				try {
					if (!is_object($langs)) {
						require_once DOL_DOCUMENT_ROOT.'/core/class/translate.class.php';
						$langs = new Translate('', $conf);
						$langs->setDefaultLang(!empty($user->conf->MAIN_LANG_DEFAULT) ? $user->conf->MAIN_LANG_DEFAULT : getDolGlobalString('MAIN_LANG_DEFAULT', 'auto'));
					}
					dol_include_once('/nssuiviprod/lib/nssuiviprod_event.lib.php');
					$langs->loadLangs(array("main", "nssuiviprod@nssuiviprod"));
					$labels = array();
					$resql = $this->db->query("SELECT rowid, label, url FROM ".$this->db->prefix()."nssuiviprod_siteurl WHERE fk_site = ".((int) $parameters['element_id']));
					while ($resql && ($obj = $this->db->fetch_object($resql))) {
						$labels[(int) $obj->rowid] = (trim((string) $obj->label) !== '' ? $obj->label : $obj->url);	// Label, or address without label
					}
					$order = array();
					foreach (explode(',', (string) $parameters['roworder']) as $rowid) {
						if (isset($labels[(int) $rowid])) {
							$order[] = (count($order) + 1).'. '.$labels[(int) $rowid];
						}
					}
					nssuiviprodAddSiteEvent($this->db, $user, (int) $parameters['element_id'], $langs->transnoentitiesnoconv('NssuiviprodEvtUrlOrder'), $order);
				} catch (Throwable $e) {
					dol_syslog(__METHOD__.' event of the order of the URLs not recorded: '.$e->getMessage(), LOG_WARNING);
				}
			}
			return 1;
		}

		return 0;
	}
}
