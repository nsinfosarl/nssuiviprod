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
 *	\file       nssuiviprod/site_thirdparty.php
 *	\ingroup    nskeepass
 *	\brief      Tab on the thirdparty card: list of the sites of the thirdparty
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
$langs->loadLangs(array("companies", "nssuiviprod@nssuiviprod", "other"));

// Get parameters
$socid = GETPOSTINT('socid') ? GETPOSTINT('socid') : GETPOSTINT('id');
if ($user->socid > 0) {
	$socid = $user->socid;
}

// Initialize technical objects
$object = new Societe($db);
$site = new Site($db);
$hookmanager->initHooks(array('thirdpartynssuiviprod', 'globalcard'));

if ($socid > 0 && $object->fetch($socid) <= 0) {
	accessforbidden();
}

// Security check
$permissiontoread = $user->hasRight('nssuiviprod', 'site', 'read');
$permissiontoadd = $user->hasRight('nssuiviprod', 'site', 'write');
if (!isModEnabled('nssuiviprod') || !$permissiontoread) {
	accessforbidden();
}
$result = restrictedArea($user, 'societe', $socid, '&societe');


/*
 * View
 */

$form = new Form($db);

$title = $langs->trans("ThirdParty").' - '.$langs->trans("Sites");
if (getDolGlobalString('MAIN_HTML_TITLE') && preg_match('/thirdpartynameonly/', getDolGlobalString('MAIN_HTML_TITLE')) && $object->name) {
	$title = $object->name.' - '.$langs->trans("Sites");
}
llxHeader('', $title, '', '', 0, 0, '', '', '', 'mod-nssuiviprod page-thirdparty');

$head = societe_prepare_head($object);
print dol_get_fiche_head($head, 'nssuiviprod', $langs->trans("ThirdParty"), -1, 'company');

$linkback = '<a href="'.DOL_URL_ROOT.'/societe/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';
dol_banner_tab($object, 'socid', $linkback, ($user->socid ? 0 : 1), 'rowid', 'nom');

print dol_get_fiche_end();

// Sites of the thirdparty
$records = $site->fetchAll('ASC', 't.label', 0, 0, '(t.fk_soc:=:'.((int) $object->id).')');
if (!is_array($records)) {
	setEventMessages($site->error, $site->errors, 'errors');
	$records = array();
}

// URLs of these sites
$urlsbysite = array();
if (count($records)) {
	$siteurl = new SiteUrl($db);
	$urls = $siteurl->fetchAll('ASC', 't.rang,t.rowid', 0, 0, '(t.fk_site:IN:'.implode(',', array_map('intval', array_keys($records))).')');
	if (is_array($urls)) {
		foreach ($urls as $tmpurl) {
			$urlsbysite[(int) $tmpurl->fk_site][] = $tmpurl;
		}
	}
}
$typeurls = nssuiviprodGetDictionary($db, 'c_nssuiviprod_typeurl');

$backtopage = $_SERVER['PHP_SELF'].'?socid='.$object->id;
$newcardbutton = dolGetButtonTitle($langs->trans('NewSite'), '', 'fa fa-plus-circle', dol_buildpath('/nssuiviprod/site_card.php', 1).'?action=create&fk_soc='.$object->id.'&backtopage='.urlencode($backtopage), '', $permissiontoadd);

print '<br>';
print load_fiche_titre($langs->trans("Sites"), $newcardbutton, $site->picto);

// Fields shown in the list (only the ones enabled and visible in list)
$arrayoffields = array();
foreach (array('ref', 'label', 'fk_typesite') as $key) {
	if (!empty($site->fields[$key]) && (int) dol_eval((string) $site->fields[$key]['enabled'], 1, 1, '1') && abs((int) $site->fields[$key]['visible']) != 0) {
		$arrayoffields[$key] = $site->fields[$key];
	}
}

print '<div class="div-table-responsive-no-min">';
print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
foreach ($arrayoffields as $key => $val) {
	print '<td>'.$langs->trans($val['label']).'</td>';
}
print '<td>'.$langs->trans("SiteUrls").'</td>';
print '<td class="right">'.$langs->trans("Status").'</td>';
print '</tr>';

if (empty($records)) {
	print '<tr class="oddeven"><td colspan="'.(count($arrayoffields) + 2).'"><span class="opacitymedium">'.$langs->trans("NoRecordFound").'</span></td></tr>';
}
foreach ($records as $record) {
	print '<tr class="oddeven">';
	foreach ($arrayoffields as $key => $val) {
		if ($key == 'ref') {
			print '<td class="nowraponall">'.$record->getNomUrl(1).'</td>';
		} else {
			print '<td class="'.(empty($val['csslist']) ? 'tdoverflowmax200' : $val['csslist']).'">'.$record->showOutputField($val, $key, (string) $record->$key, '').'</td>';
		}
	}
	print '<td class="tdoverflowmax400">';
	if (!empty($urlsbysite[(int) $record->id])) {
		$links = array();
		foreach ($urlsbysite[(int) $record->id] as $tmpurl) {
			$typelabel = isset($typeurls[(int) $tmpurl->fk_typeurl]) ? $typeurls[(int) $tmpurl->fk_typeurl] : '';
			// URL (instance) with its installed version and its situation
			$links[] = SiteUrl::getEnvironmentBadge($tmpurl->environment, 1).' <span class="opacitymedium">'.dol_escape_htmltag($typelabel).' :</span> '.dol_print_url($tmpurl->url, '_blank', 0, 1)
				.((string) $tmpurl->version !== '' ? ' <span class="nowraponall">'.dol_escape_htmltag($tmpurl->version).'</span>' : '')
				.' '.SiteUrl::getSituationBadge(SiteUrl::computeSituation((int) $record->fk_typesite, (string) $tmpurl->version))
				.(!empty($tmpurl->date_check) ? ' <span class="opacitymedium small">'.dol_print_date($tmpurl->date_check, 'day').'</span>' : '');
		}
		print implode('<br>', $links);
	}
	print '</td>';
	print '<td class="right nowraponall">'.$record->getLibStatut(5).'</td>';
	print '</tr>';
}
print '</table>';
print '</div>';

// End of page
llxFooter();
$db->close();
