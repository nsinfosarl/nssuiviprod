<?php
/* Copyright (C) 2004-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2026      Nicolas Silobre      <contact@ns-info.fr>
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
 * \file    nssuiviprod/admin/setup.php
 * \ingroup nssuiviprod
 * \brief   Nssuiviprod setup page.
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) { $i--; $j--; }
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) $res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) $res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
// Try main.inc.php using relative path
if (!$res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (!$res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (!$res) die("Include of main fails");

global $langs, $user, $conf, $db;

// Libraries
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
dol_include_once('/nssuiviprod/lib/nssuiviprod.lib.php');
dol_include_once('/nsinfo/lib/setup.lib.php');
dol_include_once('/nssuiviprod/class/site.class.php');
dol_include_once('/nssuiviprod/lib/nssuiviprod_site.lib.php');

// Translations
$langs->loadLangs(array("admin", "nsinfo@nsinfo", "nssuiviprod@nssuiviprod"));

// Access control
if (!$user->admin && !$user->hasRight('nssuiviprod', 'setup', 'admin')) {
	accessforbidden();	// Administrator or right "Access to the setup of the module"
}

// Parameters
$action = GETPOST('action', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');
$var = GETPOST('var', 'alpha');
$vartxt = GETPOST('vartxt', 'alpha');
$value = GETPOST($var, 'alpha');
$valuetxt = GETPOST($vartxt, 'alpha');
$varcolor = GETPOST('varcolor', 'alpha');
$varround = GETPOST('varround', 'alpha');
$value = GETPOST('value', 'alpha');
$varlist = GETPOST('varlst', 'aZ09');


$arrayofparameters = array();
if ($user->admin) {
	// Dictionaries: setup page of Dolibarr for the administrators only
	$arrayofparameters['NSSUIVIPROD_TITLEDICT'] = array('type' => 'titletab', 'title' => 'NssuiviprodDictionaries', 'picto' => 'generic');
	$arrayofparameters['NssuiviprodDictSiteType'] = array('type' => 'link', 'rootfile' => nssuiviprodDictionaryUrl('c_nssuiviprod_typesite'));
	$arrayofparameters['NssuiviprodDictUrlType'] = array('type' => 'link', 'rootfile' => nssuiviprodDictionaryUrl('c_nssuiviprod_typeurl'));
} else {
	$arrayofparameters['NSSUIVIPROD_TITLEDICT'] = array('type' => 'titletab', 'title' => 'NssuiviprodReferential', 'picto' => 'generic');
}
$arrayofparameters['NssuiviprodVersionsReferential'] = array('type' => 'link', 'rootfile' => dol_buildpath('/nssuiviprod/siteversion_list.php', 1));
if (isModEnabled('categorie') && (isModEnabled('product') || isModEnabled('service'))) {
	$arrayofparameters['NSSUIVIPROD_TITLEPLUGINS'] = array('type' => 'titletab', 'title' => 'SitePlugins', 'picto' => 'fa-puzzle-piece_fas_#5A2D82');
	$arrayofparameters['TABDEB'] = array('type' => 'tabdeb');
	$arrayofparameters['NSSUIVIPROD_PLUGIN_TAG'] = array('type' => 'seltags', 'typtag' => 'product', 'Tooltip' => 1);
	$arrayofparameters['TABFIN'] = array('type' => 'tabfin');
}
// Dolistore: check of the versions launched automatically after the import of the JSON file of the external modules
$arrayofparameters['NSSUIVIPROD_TITLEDOLISTORE'] = array('type' => 'titletab', 'title' => 'NssuiviprodTitleDolistore', 'picto' => 'fa-store_fas_#22A7D8');
$arrayofparameters['TABDEB2'] = array('type' => 'tabdeb');
$arrayofparameters['NSSUIVIPROD_DOLISTORE_AUTO'] = array('type' => 'bool', 'Tooltip' => 1);
$arrayofparameters['TABFIN2'] = array('type' => 'tabfin');


$error = 0;
$setupnotempty = 0;
$defaultvalue = isset($arrayofparameters[$var]['default']) ? $arrayofparameters[$var]['default'] : '';

/*
 * Actions
 */

include DOL_DOCUMENT_ROOT.'/core/actions_setmoduleoptions.inc.php';

nsinfoAction($action, $var, $value, $vartxt, $varround, $valuetxt, $defaultvalue, $varcolor, $varlist, '', '', $arrayofparameters);

// Current version of each type of site (format x.y.z, empty = latest enabled version of the referential)
if ($action == 'setcurrentversions') {	// Access to the page already checked (administrator or right on the setup)
	if (GETPOST('token', 'alpha') != currentToken()) {
		accessforbidden('Bad value for CSRF token');
	}
	$currentversions = GETPOST('currentversion', 'array:alphanohtml');
	foreach (nssuiviprodGetDictionary($db, 'c_nssuiviprod_typesite') as $typeid => $typelabel) {
		$current = trim(isset($currentversions[$typeid]) ? (string) $currentversions[$typeid] : '');
		if ($current === '') {
			dolibarr_del_const($db, 'NSSUIVIPROD_CURRENT_VERSION_'.((int) $typeid), $conf->entity);
		} elseif (preg_match('/^\d+\.\d+\.\d+$/', $current)) {
			dolibarr_set_const($db, 'NSSUIVIPROD_CURRENT_VERSION_'.((int) $typeid), $current, 'chaine', 0, '', $conf->entity);
		} else {
			$error++;
			setEventMessages($langs->trans('ErrorCurrentVersionFormat', $typelabel, $current), null, 'errors');
		}
	}
	if (!$error) {
		setEventMessages($langs->trans('SetupSaved'), null, 'mesgs');
	}
}

// Numbering models of the sites (code of the setup page generated by ModuleBuilder)
$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);
if ($action == 'updateMask') {
	$maskconst = GETPOST('maskconst', 'aZ09');
	$maskvalue = GETPOST('maskvalue', 'alpha');
	if ($maskconst && preg_match('/^NSSUIVIPROD_[A-Z]+_ADVANCED_MASK$/', $maskconst)) {
		$res = dolibarr_set_const($db, $maskconst, $maskvalue, 'chaine', 0, '', $conf->entity);
		if (!($res > 0)) {
			$error++;
		}
	}
	if (!$error) {
		setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
	} else {
		setEventMessages($langs->trans("Error"), null, 'errors');
	}
} elseif ($action == 'setmod' && preg_match('/^mod_site_[a-z0-9_]+$/', $value)) {
	dolibarr_set_const($db, 'NSSUIVIPROD_SITE_ADDON', $value, 'chaine', 0, '', $conf->entity);
}


/*
 * View
 */
$formother = new FormOther($db);
$form = new Form($db);


$page_name = "NssuiviprodSetup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="'.($backtopage ? $backtopage : DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'object_nssuiviprod@nssuiviprod');

// Configuration header
$head = nssuiviprodAdminPrepareHead();
print dol_get_fiche_head($head, 'settings', $langs->trans("Settings"), -1, "nssuiviprod@nssuiviprod");

nsinfoSetup($arrayofparameters);

// Current version of each type of site
print '<br>';
print load_fiche_titre($langs->trans('NssuiviprodCurrentVersions'), '', 'fa-code-branch_fas_#14182B');
print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="setcurrentversions">';
print '<table class="noborder centpercent">';
print '<tr class="liste_titre"><td>'.$langs->trans('SiteType').'</td><td>'.$form->textwithpicto($langs->trans('NssuiviprodCurrentVersion'), $langs->trans('NssuiviprodCurrentVersionHelp')).'</td></tr>';
foreach (nssuiviprodGetDictionary($db, 'c_nssuiviprod_typesite') as $typeid => $typelabel) {
	print '<tr class="oddeven"><td class="titlefield">'.dol_escape_htmltag($typelabel).'</td>';
	print '<td><input type="text" name="currentversion['.((int) $typeid).']" class="maxwidth100" placeholder="x.y.z" pattern="\d+\.\d+\.\d+" value="'.dol_escape_htmltag(getDolGlobalString('NSSUIVIPROD_CURRENT_VERSION_'.((int) $typeid))).'"></td></tr>';
}
print '</table>';
print '<div class="center"><input type="submit" class="button button-save" value="'.$langs->trans('Save').'"></div>';
print '</form>';

// Numbering models of the sites
print '<br>';
print load_fiche_titre($langs->trans("NumberingModules").' : '.$langs->trans("Sites"), '', 'fa-globe_fas_#3879B2');

print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Name").'</td>';
print '<td>'.$langs->trans("Description").'</td>';
print '<td class="nowrap">'.$langs->trans("Example").'</td>';
print '<td class="center" width="60">'.$langs->trans("Status").'</td>';
print '<td class="center" width="16">'.$langs->trans("ShortInfo").'</td>';
print '</tr>'."\n";

clearstatcache();
foreach ($dirmodels as $reldir) {
	$dir = dol_buildpath($reldir."core/modules/nssuiviprod");
	if (!is_dir($dir)) {
		continue;
	}
	$handle = opendir($dir);
	if (!is_resource($handle)) {
		continue;
	}
	while (($file = readdir($handle)) !== false) {
		if (strpos($file, 'mod_site_') !== 0 || substr($file, dol_strlen($file) - 3, 3) != 'php') {
			continue;
		}
		$file = substr($file, 0, dol_strlen($file) - 4);
		require_once $dir.'/'.$file.'.php';
		$module = new $file($db);

		// Show modules according to features level
		if ($module->version == 'development' && getDolGlobalInt('MAIN_FEATURES_LEVEL') < 2) {
			continue;
		}
		if ($module->version == 'experimental' && getDolGlobalInt('MAIN_FEATURES_LEVEL') < 1) {
			continue;
		}
		if (!$module->isEnabled()) {
			continue;
		}

		print '<tr class="oddeven"><td>'.$module->getName($langs)."</td><td>\n";
		print $module->info($langs);
		print '</td>';

		// Example of numbering
		print '<td class="nowrap">';
		$tmp = $module->getExample();
		if (preg_match('/^Error/', $tmp)) {
			$langs->load("errors");
			print '<div class="error">'.$langs->trans($tmp).'</div>';
		} elseif ($tmp == 'NotConfigured') {
			print $langs->trans($tmp);
		} else {
			print $tmp;
		}
		print '</td>'."\n";

		// Active model (mod_site_standard by default, see Site::getNextNumRef())
		print '<td class="center">';
		if (getDolGlobalString('NSSUIVIPROD_SITE_ADDON', 'mod_site_standard') == $file) {
			print img_picto($langs->trans("Activated"), 'switch_on');
		} else {
			print '<a href="'.$_SERVER["PHP_SELF"].'?action=setmod&token='.newToken().'&value='.urlencode($file).'">';
			print img_picto($langs->trans("Disabled"), 'switch_off');
			print '</a>';
		}
		print '</td>';

		// Info: version and next value
		$specimen = new Site($db);
		$specimen->initAsSpecimen();
		$htmltooltip = $langs->trans("Version").': <b>'.$module->getVersion().'</b><br>';
		$nextval = $module->getNextValue($specimen);
		if ("$nextval" != $langs->trans("NotAvailable")) {
			$htmltooltip .= $langs->trans("NextValue").': ';
			if ($nextval) {
				if (preg_match('/^Error/', $nextval) || $nextval == 'NotConfigured') {
					$nextval = $langs->trans($nextval);
				}
				$htmltooltip .= $nextval.'<br>';
			} else {
				$htmltooltip .= $langs->trans($module->error).'<br>';
			}
		}
		print '<td class="center">'.$form->textwithpicto('', $htmltooltip, 1, 'info').'</td>';

		print "</tr>\n";
	}
	closedir($handle);
}
print "</table>\n";

// Page end
print dol_get_fiche_end();

llxFooter();
$db->close();
