<?php
/* Copyright (C) 2026		Nicolas SILOBRE				<contact@ns-info.fr>
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
 *	\file       htdocs/custom/nssuiviprod/core/plugin/siteplugin_product.php
 *	\ingroup    nssuiviprod
 *	\brief      Tab on the product card: plugins of the sites linked to the product (shown only when there is one)
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res && file_exists("../../../../main.inc.php")) {
	$res = @include "../../../../main.inc.php";
}
if (!$res && file_exists("../../../../../main.inc.php")) {
	$res = @include "../../../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
dol_include_once('/nssuiviprod/class/site.class.php');
dol_include_once('/nssuiviprod/class/siteurl.class.php');
dol_include_once('/nssuiviprod/lib/nssuiviprod_site.lib.php');

/**
 * @var Conf $conf
 * @var DoliDB $db
 * @var HookManager $hookmanager
 * @var Translate $langs
 * @var User $user
 */

// Load translation files required by the page
$langs->loadLangs(array("products", "nssuiviprod@nssuiviprod", "other"));

require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';
dol_include_once('/nssuiviprod/class/site.class.php');
dol_include_once('/nssuiviprod/class/siteurl.class.php');
dol_include_once('/nssuiviprod/class/siteplugin.class.php');

// Get parameters
$id = GETPOSTINT('id');
$ref = GETPOST('ref', 'alpha');

// Initialize technical objects
$object = new Product($db);
$hookmanager->initHooks(array('productnssuiviprod', 'globalcard'));

if (($id > 0 || $ref) && $object->fetch($id, $ref) <= 0) {
	accessforbidden();
}

// Security check
$permissiontoread = $user->hasRight('nssuiviprod', 'site', 'read');
if (!isModEnabled('nssuiviprod') || !$permissiontoread || $user->socid > 0) {
	accessforbidden();
}
$result = restrictedArea($user, 'produit|service', $object->id, 'product&product', '', '', 'rowid');


/*
 * View
 */

$form = new Form($db);

$title = $langs->trans('ProductServiceCard').' - '.$langs->trans('NssuiviprodTabProduct');
llxHeader('', $title, '', '', 0, 0, '', '', '', 'mod-nssuiviprod page-product');

$head = product_prepare_head($object);
print dol_get_fiche_head($head, 'nssuiviprodplugins', $langs->trans($object->isService() ? 'Service' : 'Product'), -1, $object->picto);

$linkback = '<a href="'.DOL_URL_ROOT.'/product/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';
dol_banner_tab($object, 'ref', $linkback, ($user->socid ? 0 : 1), 'ref');

print dol_get_fiche_end();

// Plugins of the sites linked to the product
$tmpplugin = new SitePlugin($db);
$plugins = $tmpplugin->fetchAll('ASC', 't.fk_site,t.fk_siteurl', 0, 0, '(t.fk_product:=:'.((int) $object->id).')');
if (!is_array($plugins)) {
	setEventMessages($tmpplugin->error, $tmpplugin->errors, 'errors');
	$plugins = array();
}
// Plugins whose module is disabled on the site are not listed
$plugins = array_filter($plugins, function ($plugin) {
	return (string) $plugin->module_enabled !== '0';
});

print '<br>';
print load_fiche_titre($langs->trans('NssuiviprodTabProduct').'<span class="opacitymedium colorblack paddingleft">('.count($plugins).')</span>', '', $tmpplugin->picto);

print '<div class="div-table-responsive-no-min">';
print '<table class="noborder centpercent">';
print '<tr class="liste_titre"><td>'.$langs->trans('Site').'</td><td>'.$langs->trans('ThirdParty').'</td><td>'.$langs->trans('SiteUrl').'</td><td>'.$langs->trans('PluginLabel').'</td>';
print '<td class="center">'.img_picto($langs->trans('PluginRental'), 'fa-file-contract', 'class="opacitymedium"').'</td><td>'.$langs->trans('PluginVersion').'</td><td>'.$langs->trans('PluginDolistoreVersion').'</td></tr>';
if (empty($plugins)) {
	print '<tr class="oddeven"><td colspan="7"><span class="opacitymedium">'.$langs->trans("NoRecordFound").'</span></td></tr>';
}
$sites = array();
$urls = array();
foreach ($plugins as $plugin) {
	if (!isset($sites[(int) $plugin->fk_site])) {
		$sites[(int) $plugin->fk_site] = new Site($db);
		if ($sites[(int) $plugin->fk_site]->fetch((int) $plugin->fk_site) > 0) {
			$sites[(int) $plugin->fk_site]->fetch_thirdparty();
		}
	}
	if (!isset($urls[(int) $plugin->fk_siteurl])) {
		$urls[(int) $plugin->fk_siteurl] = new SiteUrl($db);
		$urls[(int) $plugin->fk_siteurl]->fetch((int) $plugin->fk_siteurl);
	}
	$site = $sites[(int) $plugin->fk_site];
	$url = $urls[(int) $plugin->fk_siteurl];
	print '<tr class="oddeven">';
	print '<td class="tdoverflowmax200">'.($site->id > 0 ? $site->getNomUrl(1) : '').'</td>';
	print '<td class="tdoverflowmax200">'.(is_object($site->thirdparty) && $site->thirdparty->id > 0 ? $site->thirdparty->getNomUrl(1) : '').'</td>';
	print '<td class="tdoverflowmax300">'.($url->id > 0 ? SiteUrl::getEnvironmentBadge($url->environment, 1).' '.dol_print_url($url->url, '_blank', 0, 1) : '').'</td>';
	print '<td class="tdoverflowmax200">'.$plugin->getNomUrl(1).'</td>';
	print '<td class="center">'.SitePlugin::getRentalBadge($plugin->rental, 1).'</td>';
	print '<td class="nowraponall">'.dol_escape_htmltag((string) $plugin->version).'</td>';
	print '<td class="nowraponall">'.$plugin->getDolistoreVersionBadge().'</td>';
	print '</tr>';
}
print '</table>';
print '</div>';

// End of page
llxFooter();
$db->close();
