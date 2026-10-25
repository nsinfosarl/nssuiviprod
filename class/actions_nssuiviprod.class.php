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

	/**
	 * Tabs of the cards: the tab of the plugins of the product card is shown only when the product is linked to a plugin
	 *
	 * @param	array<string,mixed>	$parameters		Hook parameters (object, mode, head by reference)
	 * @param	CommonObject		$object			Object of the card
	 * @param	string				$action			Action
	 * @param	HookManager			$hookmanager	Hook manager
	 * @return	int									0 (tabs completed in $parameters['head'])
	 */
	public function completeTabsHead(&$parameters, &$object, &$action, $hookmanager)
	{
		$this->resprints = '';	// Not reset by the hook manager between two calls
		if (empty($parameters['mode']) || $parameters['mode'] != 'add' || !is_object($object) || $object->element != 'product' || !isModEnabled('nssuiviprod') || empty($parameters['head']) || !is_array($parameters['head'])) {
			return 0;
		}
		foreach ($parameters['head'] as $key => $tab) {
			if (isset($tab[2]) && $tab[2] == 'nssuiviprodplugins') {
				dol_include_once('/nssuiviprod/class/siteplugin.class.php');
				$tmpplugin = new SitePlugin($this->db);
				// Services (products if the setup says so) linked to at least one plugin only
				if (!SitePlugin::isShownOnProductType((int) $object->type) || $tmpplugin->countForProduct((int) $object->id) <= 0) {
					unset($parameters['head'][$key]);
					$parameters['head'] = array_values($parameters['head']);
				}
				break;
			}
		}

		return 0;
	}

	/**
	 * List of the products and services: column of the plugins of the sites (picto of the module with the number of
	 * installations), hidden by the selector of the columns of the list (Dolibarr 21+)
	 *
	 * @param	array<string,mixed>	$parameters		Hook parameters
	 * @return	bool							true if the column is shown on this list
	 */
	private function showProductPluginColumn($parameters)
	{
		global $user;

		if (empty($parameters['currentcontext']) || $parameters['currentcontext'] != 'productservicelist' || !isModEnabled('nssuiviprod') || !$user->hasRight('nssuiviprod', 'site', 'read')) {
			return false;
		}
		// List of the products only or of the services only (type of the list): column shown if this type is shown
		dol_include_once('/nssuiviprod/class/siteplugin.class.php');
		$listtype = GETPOST('type', 'alpha');
		if ($listtype !== '' && in_array($listtype, array('0', '1')) && !SitePlugin::isShownOnProductType($listtype)) {
			return false;
		}
		if (!SitePlugin::isShownOnProductType(0) && !SitePlugin::isShownOnProductType(1)) {
			return false;
		}
		// Column selector of the list (completeArrayFields does not exist with Dolibarr 20: column always shown)
		return (!isset($parameters['arrayfields']['nssuiviprod_nbplugins']) || !empty($parameters['arrayfields']['nssuiviprod_nbplugins']['checked']));
	}

	/**
	 * List of the products and services: column of the plugins in the selector of the columns
	 *
	 * @param	array<string,mixed>	$parameters		Hook parameters (arrayfields by reference)
	 * @param	CommonObject		$object			Object
	 * @param	string				$action			Action
	 * @param	HookManager			$hookmanager	Hook manager
	 * @return	int									0
	 */
	public function completeArrayFields($parameters, &$object, &$action, $hookmanager)
	{
		$this->resprints = '';	// Not reset by the hook manager between two calls
		global $user, $langs;

		if (!empty($parameters['currentcontext']) && $parameters['currentcontext'] == 'productservicelist' && isModEnabled('nssuiviprod') && $user->hasRight('nssuiviprod', 'site', 'read') && isset($parameters['arrayfields']) && is_array($parameters['arrayfields'])) {
			$langs->load("nssuiviprod@nssuiviprod");
			$parameters['arrayfields']['nssuiviprod_nbplugins'] = array('label' => $langs->trans('NssuiviprodTabProduct'), 'checked' => '1', 'enabled' => '1', 'position' => 1000);
		}

		return 0;
	}

	/**
	 * List of the products and services: number of plugins of the sites of each product
	 *
	 * @param	array<string,mixed>	$parameters		Hook parameters
	 * @param	CommonObject		$object			Object
	 * @param	string				$action			Action
	 * @param	HookManager			$hookmanager	Hook manager
	 * @return	int									0
	 */
	public function printFieldListSelect($parameters, &$object, &$action, $hookmanager)
	{
		$this->resprints = '';	// Not reset by the hook manager between two calls
		global $user;

		if (!empty($parameters['currentcontext']) && $parameters['currentcontext'] == 'productservicelist' && isModEnabled('nssuiviprod') && $user->hasRight('nssuiviprod', 'site', 'read')) {
			// Number of plugins, of sites, and the first plugin (site and URL of the link when there is one site only)
			$from = " FROM ".$this->db->prefix()."nssuiviprod_siteplugin as nssp WHERE nssp.fk_product = p.rowid AND nssp.entity IN (".getEntity('siteplugin').")";
			$this->resprints = ", (SELECT COUNT(nssp.rowid)".$from.") as nssuiviprod_nbplugins";
			$this->resprints .= ", (SELECT COUNT(DISTINCT nssp.fk_site)".$from.") as nssuiviprod_nbsites";
			$this->resprints .= ", (SELECT MIN(nssp.fk_site)".$from.") as nssuiviprod_fk_site";
			$this->resprints .= ", (SELECT MIN(nssp.fk_siteurl)".$from.") as nssuiviprod_fk_siteurl";
		}

		return 0;
	}

	/**
	 * List of the products and services: empty search field of the column of the plugins
	 *
	 * @param	array<string,mixed>	$parameters		Hook parameters
	 * @param	CommonObject		$object			Object
	 * @param	string				$action			Action
	 * @param	HookManager			$hookmanager	Hook manager
	 * @return	int									0
	 */
	public function printFieldListOption($parameters, &$object, &$action, $hookmanager)
	{
		$this->resprints = '';	// Not reset by the hook manager between two calls
		if ($this->showProductPluginColumn($parameters)) {
			$this->resprints = '<td class="liste_titre"></td>';
		}

		return 0;
	}

	/**
	 * List of the products and services: title of the column of the plugins (picto of the module)
	 *
	 * @param	array<string,mixed>	$parameters		Hook parameters
	 * @param	CommonObject		$object			Object
	 * @param	string				$action			Action
	 * @param	HookManager			$hookmanager	Hook manager
	 * @return	int									0
	 */
	public function printFieldListTitle($parameters, &$object, &$action, $hookmanager)
	{
		$this->resprints = '';	// Not reset by the hook manager between two calls
		global $langs;

		if ($this->showProductPluginColumn($parameters)) {
			$langs->load("nssuiviprod@nssuiviprod");
			$this->resprints = '<th class="liste_titre center" title="'.dol_escape_htmltag($langs->trans('NssuiviprodTabProduct')).'">'.img_picto('', 'nssuiviprod@nssuiviprod', 'class="valignmiddle" style="height: 16px"').'</th>';
			if (isset($parameters['totalarray']['nbfield'])) {
				$parameters['totalarray']['nbfield']++;
			}
		}

		return 0;
	}

	/**
	 * List of the products and services: picto of the module with the number of plugins, linked to the tab of the product
	 *
	 * @param	array<string,mixed>	$parameters		Hook parameters (obj: line of the list)
	 * @param	CommonObject		$object			Object
	 * @param	string				$action			Action
	 * @param	HookManager			$hookmanager	Hook manager
	 * @return	int									0
	 */
	public function printFieldListValue($parameters, &$object, &$action, $hookmanager)
	{
		$this->resprints = '';	// Not reset by the hook manager between two calls
		global $langs;

		if ($this->showProductPluginColumn($parameters)) {
			$obj = (isset($parameters['obj']) ? $parameters['obj'] : null);
			$nb = (is_object($obj) && isset($obj->nssuiviprod_nbplugins) ? (int) $obj->nssuiviprod_nbplugins : 0);
			if (is_object($obj) && isset($obj->fk_product_type) && !SitePlugin::isShownOnProductType((int) $obj->fk_product_type)) {
				$nb = 0;	// Type of product not shown (setup): empty cell
			}
			$out = '<td class="center nowraponall">';
			if ($nb > 0 && !empty($obj->rowid)) {
				$langs->load("nssuiviprod@nssuiviprod");
				// One site: its card, on the tab of the plugins of the URL; several sites: tab of the product listing them
				if ((int) $obj->nssuiviprod_nbsites == 1) {
					$link = dol_buildpath('/nssuiviprod/core/site/site_card.php', 1).'?id='.((int) $obj->nssuiviprod_fk_site).((int) $obj->nssuiviprod_nbplugins == 1 ? '&pluginurl='.((int) $obj->nssuiviprod_fk_siteurl) : '').'#siteplugins';
				} else {
					$link = dol_buildpath('/nssuiviprod/core/plugin/siteplugin_product.php', 1).'?id='.((int) $obj->rowid);
				}
				$out .= '<a href="'.$link.'" title="'.dol_escape_htmltag($langs->trans('NssuiviprodTabProduct').' : '.$nb).'">';
				$out .= img_picto('', 'nssuiviprod@nssuiviprod', 'class="valignmiddle" style="height: 16px"').'<span class="badge marginleftonlyshort">'.$nb.'</span></a>';
			}
			$out .= '</td>';
			$this->resprints = $out;
			if (empty($parameters['i']) && isset($parameters['totalarray']['nbfield'])) {
				$parameters['totalarray']['nbfield']++;
			}
		}

		return 0;
	}
}
