<?php
/* Copyright (C) 2017       Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2024-2025  Frédéric France         <frederic.france@free.fr>
 * Copyright (C) 2026		Nicolas SILOBRE				<contact@ns-info.fr>
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
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *    \file       site_card.php
 *    \ingroup    nssuiviprod
 *    \brief      Page to create/edit/view site
 */


// General defined Options
//if (! defined('CSRFCHECK_WITH_TOKEN'))     define('CSRFCHECK_WITH_TOKEN', '1');					// Force use of CSRF protection with tokens even for GET
//if (! defined('MAIN_AUTHENTICATION_MODE')) define('MAIN_AUTHENTICATION_MODE', 'aloginmodule');	// Force authentication handler
//if (! defined('MAIN_LANG_DEFAULT'))        define('MAIN_LANG_DEFAULT', 'auto');					// Force LANG (language) to a particular value
//if (! defined('MAIN_SECURITY_FORCECSP'))   define('MAIN_SECURITY_FORCECSP', 'none');				// Disable all Content Security Policies
//if (! defined('NOBROWSERNOTIF'))     		 define('NOBROWSERNOTIF', '1');					// Disable browser notification
//if (! defined('NOIPCHECK'))                define('NOIPCHECK', '1');						// Do not check IP defined into conf $dolibarr_main_restrict_ip
//if (! defined('NOLOGIN'))                  define('NOLOGIN', '1');						// Do not use login - if this page is public (can be called outside logged session). This includes the NOIPCHECK too.
//if (! defined('NOREQUIREAJAX'))            define('NOREQUIREAJAX', '1');       	  		// Do not load ajax.lib.php library
//if (! defined('NOREQUIREDB'))              define('NOREQUIREDB', '1');					// Do not create database handler $db
//if (! defined('NOREQUIREHTML'))            define('NOREQUIREHTML', '1');					// Do not load html.form.class.php
//if (! defined('NOREQUIREMENU'))            define('NOREQUIREMENU', '1');					// Do not load and show top and left menu
//if (! defined('NOREQUIRESOC'))             define('NOREQUIRESOC', '1');					// Do not load object $mysoc
//if (! defined('NOREQUIRETRAN'))            define('NOREQUIRETRAN', '1');					// Do not load object $langs
//if (! defined('NOREQUIREUSER'))            define('NOREQUIREUSER', '1');					// Do not load object $user
//if (! defined('NOSCANGETFORINJECTION'))    define('NOSCANGETFORINJECTION', '1');			// Do not check injection attack on GET parameters
//if (! defined('NOSCANPOSTFORINJECTION'))   define('NOSCANPOSTFORINJECTION', '1');			// Do not check injection attack on POST parameters
//if (! defined('NOSESSION'))                define('NOSESSION', '1');						// On CLI mode, no need to use web sessions
//if (! defined('NOSTYLECHECK'))             define('NOSTYLECHECK', '1');					// Do not check style html tag into posted data
//if (! defined('NOTOKENRENEWAL'))           define('NOTOKENRENEWAL', '1');					// Do not roll the Anti CSRF token (used if MAIN_SECURITY_CSRF_WITH_TOKEN is on)


// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include str_replace("..", "", $_SERVER["CONTEXT_DOCUMENT_ROOT"])."/main.inc.php";
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
/**
 * The main.inc.php has been included so the following variable are now defined:
 * @var Conf $conf
 * @var DoliDB $db
 * @var HookManager $hookmanager
 * @var Translate $langs
 * @var User $user
 * @var Societe $mysoc
 */
include_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
include_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
include_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
dol_include_once('/nssuiviprod/class/site.class.php');
dol_include_once('/nssuiviprod/class/siteurl.class.php');
dol_include_once('/nssuiviprod/class/siteplugin.class.php');
dol_include_once('/nssuiviprod/lib/nssuiviprod_site.lib.php');

// Load translation files required by the page
$langs->loadLangs(array("nssuiviprod@nssuiviprod", "other"));

// Get parameters
$id = GETPOSTINT('id');
$ref = GETPOST('ref', 'alpha');
$lineid   = GETPOSTINT('lineid');
//$socid = GETPOSTINT('socid');

$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : getDolDefaultContextPage(__FILE__); // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');					// if not set, a default page will be used
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');	// if not set, $backtopage will be used
$optioncss = GETPOST('optioncss', 'aZ'); // Option for the css output (always '' except when 'print')
$dol_openinpopup = GETPOST('dol_openinpopup', 'aZ09');

// Initialize a technical objects
$object = new Site($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->nssuiviprod->dir_output.'/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array($object->element.'card', 'globalcard')); // Note that conf->hooks_modules contains array
$soc = null;

// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);


$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

// Initialize array of search criteria
$search_all = trim(GETPOST("search_all", 'alpha'));
$search = array();
foreach ($object->fields as $key => $val) {
	if (GETPOST('search_'.$key, 'alpha')) {
		$search[$key] = GETPOST('search_'.$key, 'alpha');
	}
}

if (empty($action) && empty($id) && empty($ref)) {
	$action = 'view';
}

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be 'include', not 'include_once'.

// There is several ways to check permission.
// Set $enablepermissioncheck to 1 to enable a minimum low level of checks
$enablepermissioncheck = 1;	// Rights of the module always checked
if ($enablepermissioncheck) {
	$permissiontoread = $user->hasRight('nssuiviprod', 'site', 'read');
	$permissiontoadd = $user->hasRight('nssuiviprod', 'site', 'write'); // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = $user->hasRight('nssuiviprod', 'site', 'delete') || ($permissiontoadd && isset($object->status) && $object->status == $object::STATUS_DRAFT);
	$permissionnote = $user->hasRight('nssuiviprod', 'site', 'write'); // Used by the include of actions_setnotes.inc.php
	$permissiondellink = $user->hasRight('nssuiviprod', 'site', 'write'); // Used by the include of actions_dellink.inc.php
} else {
	$permissiontoread = 1;
	$permissiontoadd = 1; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = 1;
	$permissionnote = 1;
	$permissiondellink = 1;
}

$upload_dir = $conf->nssuiviprod->multidir_output[isset($object->entity) ? $object->entity : 1].'/site';

// Security check (enable at least one, the most restrictive one)
if ($user->socid > 0) {
	accessforbidden();	// External users only see the sites of their thirdparty (tab of the thirdparty card)
}
//if ($user->socid > 0) $socid = $user->socid;
//$isdraft = (isset($object->status) && ($object->status == $object::STATUS_DRAFT) ? 1 : 0);
//restrictedArea($user, $object->module, $object, $object->table_element, $object->element, 'fk_soc', 'rowid', $isdraft);
if (!isModEnabled($object->module)) {
	accessforbidden("Module ".$object->module." not enabled");
}
if (!$permissiontoread) {
	accessforbidden();
}

$error = 0;


/*
 * Actions
 */

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($reshook)) {
	$backurlforlist = dol_buildpath('/nssuiviprod/site_list.php', 1);

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) {
				$backtopage = $backurlforlist;
			} else {
				$backtopage = dol_buildpath('/nssuiviprod/site_card.php', 1).'?id='.((!empty($id) && $id > 0) ? $id : '__ID__');
			}
		}
	}

	$triggermodname = $object->TRIGGER_PREFIX.'_MODIFY'; // Name of trigger action code to execute when we modify record. Used in actions_addupdatedelete.inc.php

	// Actions cancel, add, update, update_extras, confirm_validate, confirm_delete, confirm_deleteline, confirm_clone, confirm_close, confirm_setdraft, confirm_reopen
	include DOL_DOCUMENT_ROOT.'/core/actions_addupdatedelete.inc.php';

	// Change of the label, the thirdparty or the type of the site from the banner
	if (in_array($action, array('setbanner_label', 'setbanner_thirdparty', 'setbanner_typesite')) && $permissiontoadd && $object->id > 0 && $object->status != $object::STATUS_CANCELED) {
		if ($action == 'setbanner_label') {
			$object->label = trim(GETPOST('label', 'alphanohtml'));
			$errorfield = ($object->label === '' ? 'SiteLabel' : '');
		} elseif ($action == 'setbanner_thirdparty') {
			$object->fk_soc = GETPOSTINT('fk_soc');
			require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
			$tmpthirdparty = new Societe($db);
			$errorfield = (($object->fk_soc <= 0 || $tmpthirdparty->fetch($object->fk_soc) <= 0 || (int) $tmpthirdparty->status != 1) ? 'ThirdParty' : '');
		} else {
			$object->fk_typesite = GETPOSTINT('fk_typesite');
			$errorfield = (!array_key_exists($object->fk_typesite, nssuiviprodGetDictionary($db, 'c_nssuiviprod_typesite')) ? 'SiteType' : '');
		}
		if ($errorfield) {
			setEventMessages($langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv($errorfield)), null, 'errors');
			$action = 'edit'.substr($action, 3);
		} elseif ($object->update($user) > 0) {
			header('Location: '.$_SERVER['PHP_SELF'].'?id='.$object->id);
			exit;
		} else {
			setEventMessages($object->error, $object->errors, 'errors');
			$action = 'edit'.substr($action, 3);
		}
	}

	// URL of the tab of the plugins (actions of the tab; set by the import of the JSON file for the actions chained after it)
	$pluginurlparam = GETPOSTINT('pluginurl');
	// Label of the URL of the tab of the plugins (detail of the events of the agenda)
	$eventurllabel = '';
	if (GETPOSTINT('pluginurl') > 0 || GETPOSTINT('plugin_fk_siteurl') > 0) {
		$tmpeventurl = new SiteUrl($db);
		if ($tmpeventurl->fetch(GETPOSTINT('pluginurl') > 0 ? GETPOSTINT('pluginurl') : GETPOSTINT('plugin_fk_siteurl')) > 0 && (int) $tmpeventurl->fk_site == (int) $object->id) {
			$eventurllabel = $tmpeventurl->getDisplayLabel();
		}
	}

	// Add an URL or a plugin to the site
	if ($action == 'addurl' && $permissiontoadd && $object->id > 0) {
		$siteurl = new SiteUrl($db);
		$siteurl->fk_site = $object->id;
		$siteurl->fk_typeurl = GETPOSTINT('url_fk_typeurl');
		$siteurl->url = trim(GETPOST('url_url', 'alphanohtml'));
		$siteurl->label = trim(GETPOST('url_label', 'alphanohtml'));
		$siteurl->environment = (in_array(GETPOSTINT('url_environment'), array(1, 2, 3)) ? GETPOSTINT('url_environment') : 1);
		$siteurl->version = dol_substr(trim(GETPOST('url_version', 'alphanohtml')), 0, 64);
		$siteurl->date_check = dol_mktime(0, 0, 0, GETPOSTINT('url_date_checkmonth'), GETPOSTINT('url_date_checkday'), GETPOSTINT('url_date_checkyear'));
		if (!array_key_exists($siteurl->fk_typeurl, nssuiviprodGetDictionary($db, 'c_nssuiviprod_typeurl'))) {
			$siteurl->fk_typeurl = 0;	// Type of URL of the dictionary only (active types)
		}
		if ($siteurl->fk_typeurl <= 0 || $siteurl->url === '') {
			setEventMessages($langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv(($siteurl->fk_typeurl <= 0) ? 'UrlType' : 'Url')), null, 'errors');
		} elseif ($siteurl->create($user) > 0) {
			header('Location: '.$_SERVER['PHP_SELF'].'?id='.$object->id.'#siteurls');
			exit;
		} else {
			setEventMessages($siteurl->error, $siteurl->errors, 'errors');
		}
		$action = '';
	}
	// Mass deletion of the selected plugins: confirmation asked first
	if ($action == 'addplugin' && GETPOST('massdeleteplugins', 'alpha') && $permissiontoadd && $object->id > 0) {
		$toselect = GETPOST('toselect', 'array:int');
		if (empty($toselect)) {
			setEventMessages($langs->trans('NoRecordSelected'), null, 'warnings');
			$action = '';
		} else {
			header('Location: '.$_SERVER['PHP_SELF'].'?id='.$object->id.'&pluginurl='.GETPOSTINT('plugin_fk_siteurl').'&action=predeleteplugins&toselect='.implode(',', array_map('intval', $toselect)).'#siteplugins');
			exit;
		}
	}
	if ($action == 'confirm_deleteplugins' && $confirm == 'yes' && $permissiontoadd && $object->id > 0) {
		$nbdeleted = 0;
		$deletedlabels = array();
		SitePlugin::$noevent = true;	// One event for the whole deletion
		foreach (explode(',', GETPOST('toselect', 'alphanohtml')) as $toselectid) {
			$child = new SitePlugin($db);
			if ((int) $toselectid > 0 && $child->fetch((int) $toselectid) > 0 && (int) $child->fk_site == (int) $object->id) {
				if ($child->delete($user) > 0) {
					$nbdeleted++;
					$deletedlabels[] = $child->ref.((string) $child->version !== '' ? ' ('.$child->version.')' : '');
				} else {
					setEventMessages($child->error, $child->errors, 'errors');
				}
			}
		}
		SitePlugin::$noevent = false;
		if ($nbdeleted) {
			nssuiviprodAddSiteEvent($db, $user, $object->id, $langs->transnoentitiesnoconv('NssuiviprodEvtPluginsDeleted', $nbdeleted, $eventurllabel), $deletedlabels);
		}
		setEventMessages($langs->trans('RecordsDeleted', $nbdeleted), null, 'mesgs');
		header('Location: '.$_SERVER['PHP_SELF'].'?id='.$object->id.'&pluginurl='.GETPOSTINT('pluginurl').'#siteplugins');
		exit;
	}
	if ($action == 'addplugin' && $permissiontoadd && $object->id > 0) {
		$siteplugin = new SitePlugin($db);
		$siteplugin->fk_site = $object->id;
		$siteplugin->fk_product = (GETPOSTINT('plugin_fk_product') > 0 ? GETPOSTINT('plugin_fk_product') : null);
		$siteplugin->label = trim(GETPOST('plugin_label', 'alphanohtml'));
		$siteplugin->version = trim(GETPOST('plugin_version', 'alphanohtml'));
		$siteplugin->rental = (GETPOSTINT('plugin_rental') ? 1 : 0);
		$siteplugin->fk_siteurl = GETPOSTINT('plugin_fk_siteurl');
		// The URL must be an URL of this site
		$tmpurl = new SiteUrl($db);
		if ($siteplugin->fk_siteurl <= 0 || $tmpurl->fetch($siteplugin->fk_siteurl) <= 0 || (int) $tmpurl->fk_site != (int) $object->id) {
			setEventMessages($langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv('SiteUrl')), null, 'errors');
		} elseif ($siteplugin->create($user) > 0) {
			header('Location: '.$_SERVER['PHP_SELF'].'?id='.$object->id.'&pluginurl='.((int) $siteplugin->fk_siteurl).'#siteplugins');
			exit;
		} else {
			setEventMessages($siteplugin->error, $siteplugin->errors, 'errors');
		}
		$action = '';
	}
	// Import of the JSON file of the external modules (module nslistmodexterne), at the level of the URLs, in one operation:
	// the URL of the instance of the file is found in the site (or created), its installed version becomes the exact
	// Dolibarr version of the file (created in the referential if needed), its date of last check is today, and its
	// plugins are added or updated. File uploaded, or file of this instance (copy written by nslistmodexterne)
	if ($action == 'importurl' && $permissiontoadd && $object->id > 0) {
		$fk_siteurl = 0;
		$content = false;
		if (GETPOST('fromlocal', 'alpha')) {
			$localjsonfile = SitePlugin::getLocalJsonFilePath();
			if (is_readable($localjsonfile)) {
				$content = file_get_contents($localjsonfile);
			}
		} elseif (!empty($_FILES['pluginjsonfile']['tmp_name']) && is_uploaded_file($_FILES['pluginjsonfile']['tmp_name'])) {
			$content = file_get_contents($_FILES['pluginjsonfile']['tmp_name']);
		}
		$data = (($content === false || $content === '') ? null : json_decode($content, true));
		$instanceurl = (is_array($data) && !empty($data['instance']['url'])) ? trim((string) $data['instance']['url']) : '';
		$dolversion = (is_array($data) && !empty($data['instance']['dolibarr_version'])) ? dol_substr(trim((string) $data['instance']['dolibarr_version']), 0, 64) : '';
		$importerror = '';
		if ($content === false || $content === '') {
			$importerror = $langs->trans('ErrorPluginJsonNoFile');
		} elseif (!is_array($data) || (isset($data['generator']) && $data['generator'] != 'nslistmodexterne') || empty($data['modules']) || !is_array($data['modules'])) {
			$importerror = $langs->trans('ErrorPluginJsonInvalid');
		} elseif (!preg_match('/^https?:\/\//i', $instanceurl)) {
			$importerror = $langs->trans('ErrorUrlJsonNoInstance');
		} else {
			$fk_siteurl = SitePlugin::findUrlOfInstance($db, $object->id, $instanceurl);
			if ($fk_siteurl == -2) {
				$importerror = $langs->trans('ErrorPluginJsonSeveralUrls', dol_escape_htmltag($instanceurl));
			}
		}

		$details = array();
		$urlcreated = 0;
		$db->begin();
		$tmpurl = new SiteUrl($db);
		// URL of the instance: updated, or created with the type and the environment chosen in the form
		if (!$importerror) {
			if ($fk_siteurl > 0 && $tmpurl->fetch($fk_siteurl) > 0) {
				$tmpurl->version = ($dolversion !== '' ? $dolversion : $tmpurl->version);	// Exact Dolibarr version of the file
				$tmpurl->date_check = dol_now();
				if ($tmpurl->update($user) > 0) {
					$details[] = $langs->transnoentitiesnoconv('UrlJsonUrlUpdated', $tmpurl->getDisplayLabel());
				} else {
					$importerror = $tmpurl->error;
				}
			} else {
				$tmpurl->fk_site = $object->id;
				$tmpurl->url = dol_substr($instanceurl, 0, 255);
				$tmpurl->label = '';
				$tmpurl->fk_typeurl = GETPOSTINT('import_fk_typeurl');
				if (!array_key_exists((int) $tmpurl->fk_typeurl, nssuiviprodGetDictionary($db, 'c_nssuiviprod_typeurl'))) {
					$tmpurl->fk_typeurl = (int) key(nssuiviprodGetDictionary($db, 'c_nssuiviprod_typeurl'));	// First active type of URL
				}
				$tmpurl->environment = (in_array(GETPOSTINT('import_environment'), array(1, 2, 3)) ? GETPOSTINT('import_environment') : 1);
				$tmpurl->version = $dolversion;	// Exact Dolibarr version of the file
				$tmpurl->date_check = dol_now();
				if ($tmpurl->create($user) > 0) {
					$urlcreated = 1;
					$details[] = $langs->transnoentitiesnoconv('UrlJsonUrlCreated', $tmpurl->getDisplayLabel());
				} else {
					$importerror = $tmpurl->error;
				}
			}
			$fk_siteurl = (int) $tmpurl->id;
		}
		// Plugins of the URL
		if (!$importerror) {
			$siteplugin = new SitePlugin($db);
			SitePlugin::$noevent = true;	// One event for the whole import
			$result = $siteplugin->importFromJson($user, $object->id, $data, $fk_siteurl);
			SitePlugin::$noevent = false;
			if (!is_array($result)) {
				$importerror = $siteplugin->error;
			}
		}
		if ($importerror) {
			$db->rollback();
			setEventMessages($importerror, null, 'errors');
			header('Location: '.$_SERVER['PHP_SELF'].'?id='.$object->id.'#siteurls');
			exit;
		}
		$db->commit();

		if ($dolversion !== '') {
			array_unshift($details, $langs->transnoentitiesnoconv('UrlJsonVersionSet', $dolversion));
		}
		array_unshift($details, $langs->transnoentitiesnoconv('NssuiviprodEvtImportInstance', $instanceurl));
		array_unshift($details, $langs->transnoentitiesnoconv('NssuiviprodEvtImportFile', GETPOST('fromlocal', 'alpha') ? SitePlugin::getLocalJsonFilePath() : dol_sanitizeFileName((string) $_FILES['pluginjsonfile']['name'])));
		$details[] = $langs->transnoentitiesnoconv('PluginJsonImported', $result['created'], $result['updated'], $result['linked']);
		if (count($result['added'])) {
			$details[] = $langs->transnoentitiesnoconv('PluginJsonAdded', count($result['added'])).' : '.implode(', ', $result['added']);
		}
		if (count($result['missing'])) {
			$details[] = $langs->transnoentitiesnoconv('PluginJsonMissing', count($result['missing'])).' : '.implode(', ', $result['missing']);
		}
		nssuiviprodAddSiteEvent($db, $user, $object->id, $langs->transnoentitiesnoconv('NssuiviprodEvtPluginsImported', $tmpurl->getDisplayLabel()), $details);
		setEventMessages($langs->trans($urlcreated ? 'UrlJsonUrlCreated' : 'UrlJsonUrlUpdated', dol_escape_htmltag($tmpurl->getDisplayLabel())).($dolversion !== '' ? ' - '.$langs->trans('UrlJsonVersionSet', dol_escape_htmltag($dolversion)) : ''), null, 'mesgs');
		setEventMessages($langs->trans('PluginJsonImported', $result['created'], $result['updated'], $result['linked']), null, 'mesgs');
		if (count($result['added'])) {
			setEventMessages($langs->trans('PluginJsonAdded', count($result['added'])).' : '.dol_escape_htmltag(implode(', ', $result['added'])), null, 'warnings');
		}
		if (count($result['missing'])) {
			setEventMessages($langs->trans('PluginJsonMissing', count($result['missing'])).' : '.dol_escape_htmltag(implode(', ', $result['missing'])), null, 'warnings');
		}
		// Then, if the option of the setup is enabled, the check of the versions on the Dolistore and the link to the
		// products (helped by the Dolistore references found): actions below, on the URL of the import
		$pluginurlparam = $fk_siteurl;
		$eventurllabel = $tmpurl->getDisplayLabel();
		if (getDolGlobalInt('NSSUIVIPROD_DOLISTORE_AUTO')) {
			$afterimport = 1;
			$action = 'checkdolistore';
		} else {
			header('Location: '.$_SERVER['PHP_SELF'].'?id='.$object->id.'&pluginurl='.$fk_siteurl.'#siteurls');
			exit;
		}
	}
	// Check the current version of the plugins of the site on the Dolistore
	if ($action == 'checkdolistore' && $permissiontoadd && $object->id > 0) {
		$tmpplugin = new SitePlugin($db);
		$plugins = $tmpplugin->fetchAll('ASC', 't.rowid', 0, 0, '(t.fk_site:=:'.((int) $object->id).') AND (t.fk_siteurl:=:'.$pluginurlparam.')');
		$nbfound = 0;
		$nbnotfound = 0;
		$nberror = 0;
		foreach ((is_array($plugins) ? $plugins : array()) as $tmpplugin) {
			$res = $tmpplugin->checkDolistoreVersion($user, 7);	// Modules not found during the last 7 days: not searched again
			if ($res == 2) {
				continue;
			} elseif ($res > 0) {
				$nbfound++;
			} elseif ($res == 0) {
				$nbnotfound++;
			} else {
				$nberror++;
				$errormsg = $tmpplugin->error;
			}
		}
		$details = array($langs->transnoentitiesnoconv('PluginDolistoreChecked', $nbfound, $nbnotfound));
		foreach ((is_array($plugins) ? $plugins : array()) as $tmpplugin) {
			if ((string) $tmpplugin->version !== '' && (string) $tmpplugin->dolistore_version !== '' && version_compare((string) $tmpplugin->dolistore_version, (string) $tmpplugin->version, '>')) {
				$details[] = $langs->transnoentitiesnoconv('NssuiviprodEvtNewerVersion', $tmpplugin->ref, $tmpplugin->version, $tmpplugin->dolistore_version);
			}
		}
		if ($nberror) {
			$details[] = $errormsg;
		}
		nssuiviprodAddSiteEvent($db, $user, $object->id, $langs->transnoentitiesnoconv('NssuiviprodEvtDolistoreChecked', $eventurllabel), $details);
		setEventMessages($langs->trans('PluginDolistoreChecked', $nbfound, $nbnotfound), null, 'mesgs');
		if ($nberror) {
			setEventMessages($errormsg, null, 'errors');
		}
		if (!empty($afterimport)) {
			$action = 'linkproducts';	// After an import: link to the products, with the Dolistore references found
		} else {
			header('Location: '.$_SERVER['PHP_SELF'].'?id='.$object->id.'&pluginurl='.$pluginurlparam.'#siteplugins');
			exit;
		}
	}
	// Link the plugins of the URL without product to the products of the plugins
	if ($action == 'linkproducts' && $permissiontoadd && $object->id > 0) {
		$tmpplugin = new SitePlugin($db);
		$plugins = $tmpplugin->fetchAll('ASC', 't.rowid', 0, 0, '(t.fk_site:=:'.((int) $object->id).') AND (t.fk_siteurl:=:'.$pluginurlparam.')');
		$nb = SitePlugin::linkProducts(is_array($plugins) ? $plugins : array());
		if ($nb < 0) {
			setEventMessages($db->lasterror(), null, 'errors');
		} else {
			if ($nb > 0) {
				// Plugins linked by this action (no product before, a product now)
				require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
				$details = array();
				foreach ($plugins as $tmpplugin) {
					if (empty($tmpplugin->fk_product)) {
						$after = new SitePlugin($db);
						$tmpproduct = new Product($db);
						if ($after->fetch($tmpplugin->id) > 0 && $after->fk_product > 0 && $tmpproduct->fetch((int) $after->fk_product) > 0) {
							$details[] = $tmpplugin->ref.' → '.$tmpproduct->ref.' - '.$tmpproduct->label;
						}
					}
				}
				nssuiviprodAddSiteEvent($db, $user, $object->id, $langs->transnoentitiesnoconv('NssuiviprodEvtProductsLinked', $nb, $eventurllabel), $details);
			}
			if ($nb > 0 || empty($afterimport)) {
				setEventMessages($langs->trans('PluginProductsLinked', $nb), null, 'mesgs');
			}
		}
		header('Location: '.$_SERVER['PHP_SELF'].'?id='.$object->id.'&pluginurl='.$pluginurlparam.'#siteplugins');
		exit;
	}
	// Set a plugin of the site rented or not rented (click on its icon)
	if ($action == 'setpluginrental' && $permissiontoadd && $object->id > 0) {
		$child = new SitePlugin($db);
		if ($child->fetch(GETPOSTINT('childid')) > 0 && (int) $child->fk_site == (int) $object->id) {
			$oldrental = (int) $child->rental;
			if ($child->setValueFrom('rental', (GETPOSTINT('value') ? 1 : 0), '', null, 'int', '', $user) <= 0) {
				setEventMessages($child->error, $child->errors, 'errors');
			} elseif ($oldrental != (GETPOSTINT('value') ? 1 : 0)) {
				nssuiviprodAddSiteEvent($db, $user, $object->id, $langs->transnoentitiesnoconv('NssuiviprodEvtPluginModified', $child->ref), array($langs->transnoentitiesnoconv('NssuiviprodEvtFieldChange', $langs->transnoentitiesnoconv('PluginRental'), $langs->transnoentitiesnoconv($oldrental ? 'Yes' : 'No'), $langs->transnoentitiesnoconv(GETPOSTINT('value') ? 'Yes' : 'No'))));
			}
		}
		header('Location: '.$_SERVER['PHP_SELF'].'?id='.$object->id.'&pluginurl='.GETPOSTINT('pluginurl').'#siteplugins');
		exit;
	}
	// Change the order of the URLs of the site
	if (in_array($action, array('urlup', 'urldown')) && $permissiontoadd && $object->id > 0) {
		$child = new SiteUrl($db);
		if ($child->fetch(GETPOSTINT('childid')) > 0 && (int) $child->fk_site == (int) $object->id && $child->move($action == 'urlup' ? 'up' : 'down') < 0) {
			setEventMessages($child->error, $child->errors, 'errors');
		}
		header('Location: '.$_SERVER['PHP_SELF'].'?id='.$object->id.'#siteurls');
		exit;
	}
	// Delete an URL or a plugin of the site (after confirmation)
	// Delete the plugins absent from the last import of the JSON file (after confirmation)
	if ($action == 'confirm_deletemissingplugins' && $confirm == 'yes' && $permissiontoadd && $object->id > 0) {
		$tmpplugin = new SitePlugin($db);
		$plugins = $tmpplugin->fetchAll('ASC', 't.rowid', 0, 0, '(t.fk_site:=:'.((int) $object->id).') AND (t.fk_siteurl:=:'.GETPOSTINT('pluginurl').')');
		$plugins = (is_array($plugins) ? $plugins : array());
		$nbdeleted = 0;
		$deletedlabels = array();
		SitePlugin::$noevent = true;	// One event for the whole deletion
		foreach (SitePlugin::getImportStates($plugins) as $pluginid => $state) {
			if ($state == 'missing' && $plugins[$pluginid]->delete($user) > 0) {
				$nbdeleted++;
				$deletedlabels[] = $plugins[$pluginid]->ref.((string) $plugins[$pluginid]->version !== '' ? ' ('.$plugins[$pluginid]->version.')' : '');
			} elseif ($state == 'missing') {
				setEventMessages($plugins[$pluginid]->error, $plugins[$pluginid]->errors, 'errors');
			}
		}
		SitePlugin::$noevent = false;
		if ($nbdeleted) {
			nssuiviprodAddSiteEvent($db, $user, $object->id, $langs->transnoentitiesnoconv('NssuiviprodEvtMissingPluginsDeleted', $nbdeleted, $eventurllabel), $deletedlabels);
		}
		setEventMessages($langs->trans('RecordsDeleted', $nbdeleted), null, 'mesgs');
		header('Location: '.$_SERVER['PHP_SELF'].'?id='.$object->id.'&pluginurl='.GETPOSTINT('pluginurl').'#siteplugins');
		exit;
	}
	if (in_array($action, array('confirm_deleteurl', 'confirm_deleteplugin')) && $confirm == 'yes' && $permissiontoadd && $object->id > 0) {
		$child = ($action == 'confirm_deleteurl' ? new SiteUrl($db) : new SitePlugin($db));
		if ($child->fetch(GETPOSTINT('childid')) > 0 && (int) $child->fk_site == (int) $object->id) {
			if ($child->delete($user) > 0) {
				setEventMessages($langs->trans('RecordDeleted'), null, 'mesgs');
			} else {
				setEventMessages($child->error, $child->errors, 'errors');
			}
		}
		header('Location: '.$_SERVER['PHP_SELF'].'?id='.$object->id.($action == 'confirm_deleteurl' ? '#siteurls' : '&pluginurl='.GETPOSTINT('pluginurl').'#siteplugins'));
		exit;
	}

	// Actions when linking object each other
	include DOL_DOCUMENT_ROOT.'/core/actions_dellink.inc.php';

	// Actions when printing a doc from card
	include DOL_DOCUMENT_ROOT.'/core/actions_printing.inc.php';

	// Action to move up and down lines of object
	//include DOL_DOCUMENT_ROOT.'/core/actions_lineupdown.inc.php';

	// Action to build doc
	include DOL_DOCUMENT_ROOT.'/core/actions_builddoc.inc.php';

	// Other special actions
	/*
	if ($action == 'set_thirdparty' && $permissiontoadd) {
		$object->setValueFrom('fk_soc', GETPOSTINT('fk_soc'), '', null, 'date', '', $user, $triggermodname);
	}
	if ($action == 'classin' && $permissiontoadd) {
		$object->setProject(GETPOSTINT('projectid'));
	}
	*/

	// Actions to send emails
	$triggersendname = 'NSSUIVIPROD_SITE_SENTBYMAIL';
	$autocopy = 'MAIN_MAIL_AUTOCOPY_SITE_TO';
	$trackid = 'site'.$object->id;
	include DOL_DOCUMENT_ROOT.'/core/actions_sendmails.inc.php';
}



/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);

$title = $langs->trans("Site")." - ".$langs->trans('Card');
//$title = $object->ref." - ".$langs->trans('Card');
if ($action == 'create') {
	$title = $langs->trans("NewObject", $langs->transnoentitiesnoconv("Site"));
}
$help_url = '';

llxHeader('', $title, $help_url, '', 0, 0, '', '', '', 'mod-nssuiviprod page-card');

// Example : Adding jquery code
// print '<script type="text/javascript">
// jQuery(document).ready(function() {
// 	function init_myfunc()
// 	{
// 		jQuery("#myid").removeAttr(\'disabled\');
// 		jQuery("#myid").attr(\'disabled\',\'disabled\');
// 	}
// 	init_myfunc();
// 	jQuery("#mybutton").click(function() {
// 		init_myfunc();
// 	});
// });
// </script>';


// Part to create
if ($action == 'create') {
	if (empty($permissiontoadd)) {
		accessforbidden('NotEnoughPermissions', 0, 1);
	}

	print load_fiche_titre($title, '', $object->picto);

	print '<form method="POST" action="'.dolBuildUrl($_SERVER["PHP_SELF"]).'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="add">';
	if ($backtopage) {
		print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	}
	if ($backtopageforcancel) {
		print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';
	}
	if ($dol_openinpopup) {
		print '<input type="hidden" name="dol_openinpopup" value="'.$dol_openinpopup.'">';
	}

	print dol_get_fiche_head(array(), '');


	print '<table class="border centpercent tableforfieldcreate">'."\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_add.tpl.php';

	// Other attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_add.tpl.php';

	print '</table>'."\n";

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel("Create");

	print '</form>';

	//dol_set_focus('input[name="ref"]');
}

// Part to edit record
if (($id || $ref) && $action == 'edit') {
	print load_fiche_titre($langs->trans("Site"), '', $object->picto);

	print '<form method="POST" action="'.dolBuildUrl($_SERVER["PHP_SELF"]).'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="id" value="'.$object->id.'">';
	if ($backtopage) {
		print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	}
	if ($backtopageforcancel) {
		print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';
	}

	print dol_get_fiche_head();

	print '<table class="border centpercent tableforfieldedit">'."\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_edit.tpl.php';

	// Other attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_edit.tpl.php';

	print '</table>';

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel();

	print '</form>';
}

// Part to show record
if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create'))) {
	$head = sitePrepareHead($object);

	print dol_get_fiche_head($head, 'card', $langs->trans("Site"), -1, $object->picto, 0, '', '', 0, '', 1);

	$formconfirm = '';

	// Confirmation to delete (using preloaded confirm popup)
	if ($action == 'delete' || ($conf->use_javascript_ajax && empty($conf->dol_use_jmobile))) {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('DeleteSite'), $langs->trans('ConfirmDeleteObject'), 'confirm_delete', '', 0, 'action-delete');
	}
	// Confirmation to delete line
	if ($action == 'deleteline') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&lineid='.$lineid, $langs->trans('DeleteLine'), $langs->trans('ConfirmDeleteLine'), 'confirm_deleteline', '', 0, 1);
	}

	// Confirmation to delete an URL or a plugin of the site
	if ($action == 'deleteurl' || $action == 'deleteplugin') {
		// Deletion of an URL: its plugins are deleted with it
		$confirmtext = $langs->trans('ConfirmDeleteObject');
		if ($action == 'deleteurl') {
			$tmpdelurl = new SiteUrl($db);
			if ($tmpdelurl->fetch(GETPOSTINT('childid')) > 0 && $tmpdelurl->countPlugins() > 0) {
				$confirmtext = $langs->trans('ConfirmDeleteUrlWithPlugins', $tmpdelurl->countPlugins());
			}
		}
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&childid='.GETPOSTINT('childid').'&pluginurl='.GETPOSTINT('pluginurl'), $langs->trans($action == 'deleteurl' ? 'DeleteSiteUrl' : 'DeleteSitePlugin'), $confirmtext, 'confirm_'.$action, '', 0, 1);
	}
	// Confirmation to delete the selected plugins
	if ($action == 'predeleteplugins') {
		$toselectids = implode(',', array_filter(array_map('intval', explode(',', GETPOST('toselect', 'alphanohtml')))));
		$formquestion = array(array('type' => 'hidden', 'name' => 'toselect', 'value' => $toselectids));
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&pluginurl='.GETPOSTINT('pluginurl'), $langs->trans('DeleteSitePlugin'), $langs->trans('PluginDeleteSelectedConfirm', count(explode(',', $toselectids))), 'confirm_deleteplugins', $formquestion, 0, 1);
	}
	// Confirmation to delete the plugins absent from the last import
	if ($action == 'deletemissingplugins') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&pluginurl='.GETPOSTINT('pluginurl'), $langs->trans('PluginDeleteMissing'), $langs->trans('PluginDeleteMissingConfirm'), 'confirm_deletemissingplugins', '', 0, 1);
	}

	// Clone confirmation
	if ($action == 'clone') {
		// Create an array for form
		$formquestion = array();
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('ToClone'), $langs->trans('ConfirmCloneAsk', $object->ref), 'confirm_clone', $formquestion, 'yes', 1);
	}

	// Confirmation of action xxxx (You can use it for xxx = 'close', xxx = 'reopen', ...)
	// if ($action == 'xxx') {
	// 	$text = $langs->trans('ConfirmActionXxx', $object->ref);
	// 	if (isModEnabled('notification')) {
	// 		require_once DOL_DOCUMENT_ROOT . '/core/class/notify.class.php';
	// 		$notify = new Notify($db);
	// 		$text .= '<br>';
	// 		$text .= $notify->confirmMessage('MYOBJECT_CLOSE', $object->socid, $object);
	// 	}

	// 	$formquestion = array();

	// 	$forcecombo=0;
	// 	if ($conf->browser->name == 'ie') $forcecombo = 1;	// There is a bug in IE10 that make combo inside popup crazy
	// 	$formquestion = array(
	// 		// 'text' => $langs->trans("ConfirmClone"),
	// 		// array('type' => 'checkbox', 'name' => 'clone_content', 'label' => $langs->trans("CloneMainAttributes"), 'value' => 1),
	// 		// array('type' => 'checkbox', 'name' => 'update_prices', 'label' => $langs->trans("PuttingPricesUpToDate"), 'value' => 1),
	// 		// array('type' => 'other',    'name' => 'idwarehouse',   'label' => $langs->trans("SelectWarehouseForStockDecrease"), 'value' => $formproduct->selectWarehouses(GETPOST('idwarehouse')?GETPOST('idwarehouse'):'ifone', 'idwarehouse', '', 1, 0, 0, '', 0, $forcecombo))
	// 	);
	// 	$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('XXX'), $text, 'confirm_xxx', $formquestion, 0, 1, 220);
	// }

	// Call Hook formConfirm
	$parameters = array('formConfirm' => $formconfirm, 'lineid' => $lineid);
	$reshook = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
	if (empty($reshook)) {
		$formconfirm .= $hookmanager->resPrint;
	} elseif ($reshook > 0) {
		$formconfirm = $hookmanager->resPrint;
	}

	// Print form confirm
	print $formconfirm;


	// Object card
	// ------------------------------------------------------------
	$linkback = '<a href="'.dol_buildpath('/nssuiviprod/site_list.php', 1).'?restore_lastsearch_values=1'.(!empty($socid) ? '&socid='.$socid : '').'">'.$langs->trans("BackToList").'</a>';

	$morehtmlref = '<div class="refidno">';
	/*
		// Ref customer
		$morehtmlref .= $form->editfieldkey("RefCustomer", 'ref_client', $object->ref_client, $object, $usercancreate, 'string', '', 0, 1);
		$morehtmlref .= $form->editfieldval("RefCustomer", 'ref_client', $object->ref_client, $object, $usercancreate, 'string'.(getDolGlobalInt('THIRDPARTY_REF_INPUT_SIZE') ? ':'.getDolGlobalInt('THIRDPARTY_REF_INPUT_SIZE') : ''), '', null, null, '', 1);
		// Thirdparty
		$morehtmlref .= '<br>'.$object->thirdparty->getNomUrl(1, 'customer');
		if (!getDolGlobalInt('MAIN_DISABLE_OTHER_LINK') && $object->thirdparty->id > 0) {
			$morehtmlref .= ' (<a href="'.DOL_URL_ROOT.'/commande/list.php?socid='.$object->thirdparty->id.'&search_societe='.urlencode($object->thirdparty->name).'">'.$langs->trans("OtherOrders").'</a>)';
		}
		// Project
		if (isModEnabled('project')) {
			$langs->load("projects");
			$morehtmlref .= '<br>';
			if ($permissiontoadd) {
				$morehtmlref .= img_picto($langs->trans("Project"), 'project', 'class="pictofixedwidth"');
				if ($action != 'classify') {
					$morehtmlref .= '<a class="editfielda" href="'.dolBuildUrl($_SERVER['PHP_SELF'], ['action' => 'classify', 'id' => $object->id], true).'">'.img_edit($langs->transnoentitiesnoconv('SetProject')).'</a> ';
				}
				$morehtmlref .= $form->form_project($_SERVER['PHP_SELF'].'?id='.$object->id, $object->socid, $object->fk_project, ($action == 'classify' ? 'projectid' : 'none'), 0, 0, 0, 1, '', 'maxwidth300');
			} else {
				if (!empty($object->fk_project)) {
					$proj = new Project($db);
					$proj->fetch($object->fk_project);
					$morehtmlref .= $proj->getNomUrl(1);
					if ($proj->title) {
						$morehtmlref .= '<span class="opacitymedium"> - '.dol_escape_htmltag($proj->title).'</span>';
					}
				}
			}
		}
	*/
	$morehtmlref .= '</div>';


	$morehtmlref = nssuiviprodSiteBannerRef($object, $action);	// Label, thirdparty and type of the site
	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);


	print '<div class="fichecenter">';
	print '<div class="fichehalfleft">';
	print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent tableforfield">'."\n";

	// Common attributes
	//$keyforbreak='fieldkeytoswitchonsecondcolumn';	// We change column just before this field
	//unset($object->fields['fk_project']);				// Hide field already shown in banner
	//unset($object->fields['fk_soc']);					// Hide field already shown in banner
	// Only the description under the banner (label, thirdparty and type of site are shown and edited in the banner)
	$savedfields = $object->fields;
	$object->fields = array_intersect_key($object->fields, (trim((string) $object->description) !== '' ? array('description' => 1) : array()));	// Description hidden when empty
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_view.tpl.php';
	$object->fields = $savedfields;

	// Other attributes. Fields from hook formObjectOptions and Extrafields.
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_view.tpl.php';

	print '</table>';
	print '</div>';
	print '</div>';

	print '<div class="clearboth"></div>';

	print dol_get_fiche_end();


	/*
	 * Lines
	 */

	if (!empty($object->table_element_line)) {
		// Show object lines
		$result = $object->getLinesArray();

		print '	<form name="addproduct" id="addproduct" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.(($action != 'editline') ? '' : '#line_'.GETPOSTINT('lineid')).'" method="POST">
		<input type="hidden" name="token" value="' . newToken().'">
		<input type="hidden" name="action" value="' . (($action != 'editline') ? 'addline' : 'updateline').'">
		<input type="hidden" name="mode" value="">
		<input type="hidden" name="page_y" value="">
		<input type="hidden" name="id" value="' . $object->id.'">
		';

		if (!empty($conf->use_javascript_ajax) && $object->status == 0) {
			include DOL_DOCUMENT_ROOT.'/core/tpl/ajaxrow.tpl.php';
		}

		print '<div class="div-table-responsive-no-min">';
		if (!empty($object->lines) || ($object->status == $object::STATUS_DRAFT && $permissiontoadd && $action != 'selectlines' && $action != 'editline')) {
			print '<table id="tablelines" class="noborder noshadow" width="100%">';
		}

		if (!empty($object->lines)) {
			$object->printObjectLines($action, $mysoc, null, GETPOSTINT('lineid'), 1);
		}

		// Form to add new line
		if ($object->status == 0 && $permissiontoadd && $action != 'selectlines') {
			if ($action != 'editline') {
				// Add products/services form

				$parameters = array();
				$reshook = $hookmanager->executeHooks('formAddObjectLine', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
				if ($reshook < 0) {
					setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
				}
				if (empty($reshook)) {
					$object->formAddObjectLine(1, $mysoc, $soc);
				}
			}
		}

		if (!empty($object->lines) || ($object->status == $object::STATUS_DRAFT && $permissiontoadd && $action != 'selectlines' && $action != 'editline')) {
			print '</table>';
		}
		print '</div>';

		print "</form>\n";
	}


	// URLs of the site
	$siteurl = new SiteUrl($db);
	$urls = $siteurl->fetchAll('ASC', 't.rang,t.rowid', 0, 0, '(t.fk_site:=:'.((int) $object->id).')');
	if (!is_array($urls)) {
		setEventMessages($siteurl->error, $siteurl->errors, 'errors');
		$urls = array();
	}
	$typeurls = nssuiviprodGetDictionary($db, 'c_nssuiviprod_typeurl');
	$backtocard = urlencode($_SERVER['PHP_SELF'].'?id='.$object->id);
	// Current version of the type of the site (setup of the module): reference of the situation of the URLs
	$currentversion = SiteUrl::getCurrentVersion((int) $object->fk_typesite);

	print '<br><a name="siteurls"></a>';
	print load_fiche_titre($langs->trans('SiteUrls').'<span class="badge marginleftonlyshort">'.count($urls).'</span>', '', $siteurl->picto);
	// Import of the JSON file of the external modules (module nslistmodexterne): URL of the instance of the file found or
	// created, exact Dolibarr version, date of check, and plugins of the URL, in one operation
	if ($permissiontoadd) {
		$localjsonfile = SitePlugin::getLocalJsonFilePath();
		$envs = array(1 => $langs->trans('UrlEnvProd'), 2 => $langs->trans('UrlEnvDev'), 3 => $langs->trans('UrlEnvSandbox'));
		print '<form method="POST" enctype="multipart/form-data" action="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'">';
		print '<input type="hidden" name="token" value="'.newToken().'">';
		print '<input type="hidden" name="action" value="importurl">';
		print '<div class="div-table-responsive-no-min marginbottomonly">';
		print '<table class="noborder centpercent">';
		print '<tr class="oddeven"><td class="titlefield">'.img_picto('', 'fa-file-import', 'class="pictofixedwidth"').$langs->trans('PluginJsonImport');
		print $form->textwithpicto('', $langs->trans('UrlJsonImportHelp'));
		print '</td><td>';
		// Type of URL and environment of the URL if it is created, then the file
		print '<span class="opacitymedium small">'.$langs->trans('UrlJsonNewUrl').'</span> ';
		print $form->selectarray('import_fk_typeurl', $typeurls, (int) key($typeurls), 0, 0, 0, '', 0, 0, 0, '', 'maxwidth150 small');
		print ' '.$form->selectarray('import_environment', $envs, 1, 0, 0, 0, '', 0, 0, 0, '', 'maxwidth150 small');
		print '<br><input type="file" name="pluginjsonfile" accept=".json,application/json"> ';
		print '<input type="submit" class="button small" value="'.$langs->trans('PluginJsonImportFile').'">';
		if (is_readable($localjsonfile)) {
			print ' <input type="submit" class="button small" name="fromlocal" value="'.$langs->trans('PluginJsonImportLocal').'">';
			print ' <span class="opacitymedium small">'.$langs->trans('PluginJsonFileDate').' : '.dol_print_date(dol_filemtime($localjsonfile), 'dayhour', 'tzuser').'</span>';
		}
		print '</td></tr>';
		print '</table>';
		print '</div>';
		print '</form>';
	}
	print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="addurl">';
	print '<div class="div-table-responsive-no-min">';
	print '<table class="noborder centpercent" id="tablelinesurls">';
	print '<tr class="liste_titre nodrag nodrop"><td class="center">'.img_picto($langs->trans('UrlEnvironment'), 'fa-layer-group', 'class="opacitymedium"').'</td><td>'.$langs->trans('UrlType').'</td><td>'.$langs->trans('Url').'</td><td>'.$langs->trans('Label').'</td><td>'.$langs->trans('InstalledVersion').'</td><td class="center">'.$langs->trans('Situation').'</td><td class="center">'.$langs->trans('DateLastCheck').'</td><td>'.$langs->trans('UrlComplement').'</td><td></td><td class="linecolmove"></td></tr>';
	if (empty($urls)) {
		print '<tr class="oddeven nodrag nodrop"><td colspan="10"><span class="opacitymedium">'.$langs->trans('NoRecordFound').'</span></td></tr>';
	}
	foreach ($urls as $tmpurl) {
		print '<tr id="row-'.((int) $tmpurl->id).'" class="drag drop oddeven">';
		print '<td class="center width25">'.SiteUrl::getEnvironmentBadge($tmpurl->environment, 1).'</td>';	// Colored icon, label in the tooltip
		print '<td class="tdoverflowmax150">'.dol_escape_htmltag(isset($typeurls[(int) $tmpurl->fk_typeurl]) ? $typeurls[(int) $tmpurl->fk_typeurl] : '').'</td>';
		print '<td class="tdoverflowmax400">'.dol_print_url($tmpurl->url, '_blank', 0, 1).'</td>';
		print '<td class="tdoverflowmax200">'.dol_escape_htmltag($tmpurl->label).'</td>';
		// Installed version, situation (current version of the type of the site when it is not the installed one)
		print '<td class="nowraponall">'.dol_escape_htmltag((string) $tmpurl->version).'</td>';
		print '<td class="center nowraponall">'.SiteUrl::getSituationBadge(SiteUrl::computeSituation((int) $object->fk_typesite, (string) $tmpurl->version));
		if ($currentversion !== '' && trim((string) $tmpurl->version) !== '' && SiteUrl::compareVersions($tmpurl->version, $currentversion) != 0) {
			print '<br><span class="opacitymedium small">'.$langs->trans('NssuiviprodCurrentVersion').' : '.dol_escape_htmltag($currentversion).'</span>';
		}
		print '</td>';
		print '<td class="center nowraponall">'.dol_print_date($tmpurl->date_check, 'day').'</td>';
		print '<td class="wordbreak">'.dolPrintHTML((string) $tmpurl->complement).'</td>';	// Advanced text: entered on the card of the URL
		print '<td class="right nowraponall">';
		if ($permissiontoadd) {
			print '<a class="editfielda reposition marginrightonly" href="'.dol_buildpath('/nssuiviprod/siteurl_card.php', 1).'?id='.$tmpurl->id.'&action=edit&token='.newToken().'&backtopage='.$backtocard.'">'.img_edit().'</a>';
			print '<a class="reposition" href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&action=deleteurl&childid='.$tmpurl->id.'&token='.newToken().'">'.img_delete().'</a>';
		}
		print '</td>';
		// Order of the URLs (also order of the tabs of the plugins): drag and drop as the lines of the proposals, arrows otherwise
		print '<td class="linecolmove tdlineupdown center">';
		if ($permissiontoadd && count($urls) > 1) {
			print '<a class="lineupdown reposition" href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&action=urlup&childid='.$tmpurl->id.'&token='.newToken().'">'.img_up('default', 0, 'imgupforline').'</a>';
			print '<a class="lineupdown reposition" href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&action=urldown&childid='.$tmpurl->id.'&token='.newToken().'">'.img_down('default', 0, 'imgdownforline').'</a>';
		}
		print '</td>';
		print '</tr>';
	}
	if ($permissiontoadd) {
		print '<tr class="oddeven nodrag nodrop">';
		$envs = array(1 => $langs->trans('UrlEnvProd'), 2 => $langs->trans('UrlEnvDev'), 3 => $langs->trans('UrlEnvSandbox'));
		print '<td>'.$form->selectarray('url_environment', $envs, (GETPOSTINT('url_environment') ? GETPOSTINT('url_environment') : 1), 0, 0, 0, '', 0, 0, 0, '', 'maxwidth150').'</td>';
		print '<td>'.$form->selectarray('url_fk_typeurl', $typeurls, GETPOSTINT('url_fk_typeurl'), 1, 0, 0, '', 0, 0, 0, '', 'maxwidth200').'</td>';
		print '<td><input type="text" name="url_url" class="minwidth300 widthcentpercentminusx" placeholder="https://" value="'.dol_escape_htmltag(GETPOST('url_url', 'alphanohtml')).'"></td>';
		print '<td><input type="text" name="url_label" class="minwidth150" value="'.dol_escape_htmltag(GETPOST('url_label', 'alphanohtml')).'"></td>';
		print '<td><input type="text" name="url_version" class="maxwidth100" placeholder="x.y.z" value="'.dol_escape_htmltag(GETPOST('url_version', 'alphanohtml')).'"></td>';
		print '<td></td>';
		print '<td class="center nowraponall">'.$form->selectDate((GETPOSTISSET('url_date_checkday') ? dol_mktime(12, 0, 0, GETPOSTINT('url_date_checkmonth'), GETPOSTINT('url_date_checkday'), GETPOSTINT('url_date_checkyear')) : dol_now()), 'url_date_check', 0, 0, 1, '', 1, 0).'</td>';
		print '<td><span class="opacitymedium small">'.$langs->trans('UrlComplementOnCard').'</span></td>';
		print '<td class="right"><input type="submit" class="button small" value="'.$langs->trans('Add').'"></td>';
		print '<td></td>';
		print '</tr>';
	}
	print '</table>';
	print '</div>';
	print '</form>';
	if ($permissiontoadd) {
		$table_element_line = 'nssuiviprod_siteurl';
		$fk_element = 'fk_site';
		$nboflines = count($urls);
		$tagidfortablednd = 'tablelinesurls';
		include DOL_DOCUMENT_ROOT.'/core/tpl/ajaxrow.tpl.php';
		// New order saved: page reloaded to show the tabs of the plugins in the same order as the URLs
		print '<script nonce="'.getNonce().'">'."\n";
		print 'jQuery(document).ajaxComplete(function(event, xhr, settings) {'."\n";
		print '	if (settings.url && settings.url.indexOf("/core/ajax/row.php") >= 0 && String(settings.data).indexOf("table_element_line=nssuiviprod_siteurl") >= 0) {'."\n";
		print '		window.location.replace("'.dol_escape_js($_SERVER['PHP_SELF'].'?id='.$object->id.'&pluginurl='.GETPOSTINT('pluginurl')).'&reload=" + Date.now() + "#siteurls");'."\n";
		print '	}'."\n";
		print '});'."\n";
		print '</script>'."\n";
	}

	// Plugins installed on the site: one tab per URL of the site (instance), plugins without URL in a last tab
	$siteplugin = new SitePlugin($db);
	$allplugins = $siteplugin->fetchAll('ASC', 't.ref', 0, 0, '(t.fk_site:=:'.((int) $object->id).')');
	if (!is_array($allplugins)) {
		setEventMessages($siteplugin->error, $siteplugin->errors, 'errors');
		$allplugins = array();
	}
	$pluginsbyurl = array();
	foreach ($urls as $tmpurl) {
		$pluginsbyurl[(int) $tmpurl->id] = array();
	}
	foreach ($allplugins as $tmpplugin) {
		$pluginsbyurl[isset($pluginsbyurl[(int) $tmpplugin->fk_siteurl]) ? (int) $tmpplugin->fk_siteurl : 0][] = $tmpplugin;
	}
	$pluginurl = GETPOSTINT('pluginurl');
	if (!array_key_exists($pluginurl, $pluginsbyurl)) {
		$pluginurl = (count($pluginsbyurl) ? (int) key($pluginsbyurl) : 0);
	}
	$plugins = (isset($pluginsbyurl[$pluginurl]) ? $pluginsbyurl[$pluginurl] : array());
	$withproduct = (isModEnabled('product') || isModEnabled('service'));
	$backtocard = urlencode($_SERVER['PHP_SELF'].'?id='.$object->id.'&pluginurl='.$pluginurl.'#siteplugins');
	$urlparam = '?id='.$object->id.'&pluginurl='.$pluginurl;

	print '<br><a name="siteplugins"></a>';
	print load_fiche_titre($langs->trans('SitePlugins'), '', $siteplugin->picto);
	if (!count($pluginsbyurl)) {
		print '<div class="opacitymedium">'.$langs->trans('PluginNoUrlYet').'</div>';
	} else {
		$head = array();
		foreach ($pluginsbyurl as $urlid => $urlplugins) {
			if ($urlid && isset($urls[$urlid])) {
				// Icon of the environment, then the label of the URL if it has one (address in the tooltip)
				$tablabel = '<span title="'.dol_escape_htmltag($urls[$urlid]->url).'">'.SiteUrl::getEnvironmentBadge($urls[$urlid]->environment, 1);
				$tablabel .= (trim((string) $urls[$urlid]->label) !== '' ? ' '.dol_escape_htmltag($urls[$urlid]->label) : '').'</span>';
			} else {
				$tablabel = $langs->trans('PluginNoUrl');
			}
			$head[] = array($_SERVER['PHP_SELF'].'?id='.$object->id.'&pluginurl='.$urlid.'#siteplugins', $tablabel.'<span class="badge marginleftonlyshort">'.count($urlplugins).'</span>', 'pluginurl'.$urlid);
		}
		print dol_get_fiche_head($head, 'pluginurl'.$pluginurl, '', -1, '', 0, '', '', 0, 'pluginurltabs');

		$importstates = SitePlugin::getImportStates($plugins);	// Plugins added by the last import of the JSON file, or absent from it
		$nbmissing = count(array_keys($importstates, 'missing'));
		// Buttons of the tab
		$morehtmlright = '';
		if ($permissiontoadd && $nbmissing) {
			$morehtmlright .= dolGetButtonTitle($langs->trans('PluginDeleteMissing').' ('.$nbmissing.')', '', 'fa fa-trash', $_SERVER['PHP_SELF'].$urlparam.'&action=deletemissingplugins&token='.newToken().'#siteplugins');
		}
		if ($permissiontoadd && $withproduct && !empty($plugins)) {
			$morehtmlright .= dolGetButtonTitle($langs->trans('PluginLinkProducts'), '', 'fa fa-link', $_SERVER['PHP_SELF'].$urlparam.'&action=linkproducts&token='.newToken());
		}
		if ($permissiontoadd && !empty($plugins)) {
			// Icon of the Dolistore with an update badge
			$morehtmlright .= '<a class="btnTitle reposition classfortooltip" href="'.$_SERVER['PHP_SELF'].$urlparam.'&action=checkdolistore&token='.newToken().'" title="'.dol_escape_htmltag($langs->trans('PluginDolistoreCheck')).'"><img src="'.dol_buildpath('/nssuiviprod/img/dolistore_update.svg', 1).'" class="valignmiddle" style="height: 24px" alt="'.dol_escape_htmltag($langs->trans('PluginDolistoreCheck')).'"></a>';
		}
		// Where to get the JSON file of the external modules of the URL of the tab (setup of the module nslistmodexterne of its
		// Dolibarr; the file is imported in the section of the URLs), and buttons of the tab
		// Only for an URL already imported once (at least one of its plugins comes from an import)
		$urlimported = false;
		foreach ($plugins as $tmpplugin) {
			if (!empty($tmpplugin->import_key)) {
				$urlimported = true;
				break;
			}
		}
		$tmpurladdress = (($permissiontoadd && $pluginurl > 0 && $urlimported && isset($urls[$pluginurl])) ? trim((string) $urls[$pluginurl]->url) : '');
		if (preg_match('/^https?:\/\//i', $tmpurladdress)) {
			print '<div class="div-table-responsive-no-min marginbottomonly">';
			print '<table class="noborder centpercent">';
			$nslistmodexterneurl = rtrim($tmpurladdress, '/').'/custom/nslistmodexterne/admin/setup.php';
			print '<tr class="oddeven"><td><span class="opacitymedium">'.img_picto('', 'fa-info-circle', 'class="pictofixedwidth"').$langs->trans('PluginJsonWhere').'</span> ';
			print '<a href="'.dol_escape_htmltag($nslistmodexterneurl).'" target="_blank" rel="noopener noreferrer">'.img_picto('', 'fa-external-link-alt', 'class="pictofixedwidth"').dol_escape_htmltag($nslistmodexterneurl).'</a>';
			// Quick copy button just after the URL (same copy as the module nsclipboardcpbutton)
			print '<span class="nssuiviprodcp">'.showValueWithClipboardCPButton($nslistmodexterneurl, 0, 'none').'</span></td>';
			print '<td class="right nowraponall">'.$morehtmlright.'</td></tr>';
			print '<script>
			// Copy with the API navigator.clipboard (the standard copy with execCommand fails on some pages), set on "load" to replace the handler of lib_foot.js.php
			jQuery(window).on("load", function() {
				jQuery(".nssuiviprodcp .clipboardCPButton").off("click").on("click", function() {
					var cp = jQuery(this).closest(".clipboardCP");
					var value = cp.children(".clipboardCPValue").text();
					var showTick = function() {
						cp.children(".clipboardCPButton").hide();
						cp.children(".clipboardCPTick").css("display", "inline-block");
						setTimeout(function() { cp.children(".clipboardCPTick").hide(); cp.children(".clipboardCPButton").css("display", "inline-block"); }, 2000);
					};
					if (navigator.clipboard && window.isSecureContext) {
						navigator.clipboard.writeText(value).then(showTick, function(e) { console.log("nssuiviprod: copy failed", e); });
					} else {
						var tmp = jQuery("<textarea>").val(value).css({position: "fixed", top: 0, left: 0, opacity: 0}).appendTo("body");
						tmp[0].select();
						try { if (document.execCommand("copy")) { showTick(); } } catch (e) { console.log("nssuiviprod: copy failed", e); }
						tmp.remove();
					}
				});
			});
			</script>';
			print '</table>';
			print '</div>';
		} elseif ($morehtmlright) {
			print '<div class="right marginbottomonly">'.$morehtmlright.'</div>';
		}

		print '<form method="POST" action="'.$_SERVER['PHP_SELF'].$urlparam.'">';
		print '<input type="hidden" name="token" value="'.newToken().'">';
		print '<input type="hidden" name="action" value="addplugin">';
		print '<input type="hidden" name="plugin_fk_siteurl" value="'.$pluginurl.'">';
		print '<div class="div-table-responsive-no-min">';
		print '<table class="noborder centpercent" id="tablesiteplugins">';
		print '<tr class="liste_titre">';
		print '<td class="center width25">'.($permissiontoadd && !empty($plugins) ? '<input type="checkbox" id="checkallplugins" title="'.dol_escape_htmltag($langs->trans('All')).'">' : '').'</td>';
		// Titles: a click sorts the rows (client side), a second click reverses the order
		print '<td class="center pluginsort cursorpointer" data-sort="rental" title="'.dol_escape_htmltag($langs->trans('PluginSortHelp')).'">'.img_picto($langs->trans('PluginRental'), 'fa-file-contract', 'class="opacitymedium"').'</td>';
		if ($withproduct) {
			print '<td class="pluginsort cursorpointer" data-sort="product" title="'.dol_escape_htmltag($langs->trans('PluginSortHelp')).'">'.$langs->trans('Product').'</td>';
		}
		print '<td class="pluginsort cursorpointer" data-sort="name" title="'.dol_escape_htmltag($langs->trans('PluginSortHelp')).'">'.$langs->trans('PluginLabel').'</td>';
		print '<td class="pluginsort cursorpointer" data-sort="publisher" title="'.dol_escape_htmltag($langs->trans('PluginSortHelp')).'">'.$langs->trans('PluginPublisher').'</td>';
		print '<td class="center">'.img_picto('Dolistore', 'fa-store', 'class="opacitymedium"').'</td>';
		print '<td class="center">'.img_picto($langs->trans('PluginPublisherUrl'), 'fa-globe', 'class="opacitymedium"').'</td>';
		print '<td class="pluginsort cursorpointer" data-sort="version" title="'.dol_escape_htmltag($langs->trans('PluginSortHelp')).'">'.$langs->trans('PluginVersion').'</td><td class="pluginsort cursorpointer" data-sort="dolistore" title="'.dol_escape_htmltag($langs->trans('PluginSortHelp')).'">'.$langs->trans('PluginDolistoreVersion').'</td><td></td></tr>';
		$nbcols = ($withproduct ? 10 : 9);
		// Search fields (filter of the rows in the page)
		if (!empty($plugins)) {
			print '<tr class="liste_titre_filter">';
			// Mass action: delete the selected plugins
			print '<td class="liste_titre center">'.($permissiontoadd ? '<button type="submit" class="button smallpaddingimp small" name="massdeleteplugins" value="1" title="'.dol_escape_htmltag($langs->trans('PluginDeleteSelected')).'">'.img_picto('', 'delete').'</button>' : '').'</td>';
			print '<td class="liste_titre center">'.$form->selectarray('search_plugin_rental', array('1' => $langs->trans('PluginRentalYes'), '0' => $langs->trans('PluginRentalNo')), '', 1, 0, 0, 'data-filter="rental"', 0, 0, 0, '', 'maxwidth75 pluginfilter').'</td>';
			if ($withproduct) {
				print '<td class="liste_titre"><input type="text" class="flat maxwidth100 pluginfilter" data-filter="product"></td>';
			}
			print '<td class="liste_titre"><input type="text" class="flat maxwidth150 pluginfilter" data-filter="label"></td>';
			print '<td class="liste_titre"><input type="text" class="flat maxwidth100 pluginfilter" data-filter="publisher"></td>';
			print '<td class="liste_titre"></td><td class="liste_titre"></td>';
			print '<td class="liste_titre"><input type="text" class="flat maxwidth75 pluginfilter" data-filter="version"></td>';
			print '<td class="liste_titre center">'.$form->selectarray('search_plugin_newer', array('1' => $langs->trans('PluginDolistoreNewer')), '', 1, 0, 0, 'data-filter="newer"', 0, 0, 0, '', 'maxwidth100 pluginfilter').'</td>';
			print '<td class="liste_titre"></td>';
			print '</tr>';
		}
		if (empty($plugins)) {
			print '<tr class="oddeven"><td colspan="'.$nbcols.'"><span class="opacitymedium">'.$langs->trans('NoRecordFound').'</span></td></tr>';
		}
		$tmpproduct = null;
		if ($withproduct) {
			require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
			$tmpproduct = new Product($db);
		}
		foreach ($plugins as $tmpplugin) {
			$productref = '';
			if ($withproduct && $tmpplugin->fk_product > 0 && $tmpproduct->fetch((int) $tmpplugin->fk_product) > 0) {
				$productref = $tmpproduct->ref.' '.$tmpproduct->label;
			}
			$newer = ((string) $tmpplugin->dolistore_version !== '' && (string) $tmpplugin->version !== '' && version_compare((string) $tmpplugin->dolistore_version, (string) $tmpplugin->version, '>'));
			print '<tr class="oddeven pluginrow" data-rental="'.((int) $tmpplugin->rental).'" data-product="'.dol_escape_htmltag(dol_strtolower($productref)).'" data-label="'.dol_escape_htmltag(dol_strtolower($tmpplugin->label.' '.$tmpplugin->module_name.' '.$tmpplugin->module_description)).'" data-publisher="'.dol_escape_htmltag(dol_strtolower((string) $tmpplugin->publisher)).'" data-name="'.dol_escape_htmltag(dol_strtolower((string) $tmpplugin->label)).'" data-dolistore="'.dol_escape_htmltag((string) $tmpplugin->dolistore_version).'" data-version="'.dol_escape_htmltag(dol_strtolower((string) $tmpplugin->version)).'" data-newer="'.($newer ? 1 : 0).'">';
			print '<td class="center">'.($permissiontoadd ? '<input type="checkbox" class="checkforselectplugin" name="toselect[]" value="'.((int) $tmpplugin->id).'">' : '').'</td>';
			// Rental state: colored icon, a click switches it
			print '<td class="center width25">';
			if ($permissiontoadd) {
				print '<a class="reposition" href="'.$_SERVER['PHP_SELF'].$urlparam.'&action=setpluginrental&childid='.$tmpplugin->id.'&value='.($tmpplugin->rental ? 0 : 1).'&token='.newToken().'">'.SitePlugin::getRentalBadge($tmpplugin->rental, 1).'</a>';
			} else {
				print SitePlugin::getRentalBadge($tmpplugin->rental, 1);
			}
			print '</td>';
			if ($withproduct) {
				print '<td class="tdoverflowmax150">'.($productref !== '' ? $tmpproduct->getNomUrl(1) : '').'</td>';
			}
			// Name and description: the column adapts its width (400 px at most), the description stays on one line with
			// "..." when it is too long (full text in the tooltip)
			print '<td class="minwidth200 wordbreak" style="max-width: 400px">'.dol_escape_htmltag($tmpplugin->label).(isset($importstates[(int) $tmpplugin->id]) ? SitePlugin::getImportStateBadge($importstates[(int) $tmpplugin->id]) : '');
			// Description of the module (JSON file of the external modules), full text in the tooltip
			$tmpdesc = trim(dol_string_nohtmltag((string) $tmpplugin->module_description, 1));
			if ($tmpdesc !== '') {
				print '<span class="opacitymedium small" style="display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="'.dol_escape_htmltag($tmpdesc).'">'.dol_escape_htmltag(dol_trunc($tmpdesc, 300)).'</span>';
			}
			print '</td>';
			print '<td class="tdoverflowmax100" title="'.dol_escape_htmltag((string) $tmpplugin->publisher).'">'.dol_escape_htmltag((string) $tmpplugin->publisher).'</td>';
			print '<td class="center width25">'.$tmpplugin->getLinks('dolistore').'</td>';
			print '<td class="center width25">'.$tmpplugin->getLinks('publisher').'</td>';
			print '<td class="nowraponall">'.dol_escape_htmltag($tmpplugin->version).'</td>';
			print '<td class="nowraponall">'.$tmpplugin->getDolistoreVersionBadge().'</td>';
			print '<td class="right nowraponall">';
			if ($permissiontoadd) {
				print '<a class="editfielda reposition marginrightonly" href="'.dol_buildpath('/nssuiviprod/siteplugin_card.php', 1).'?id='.$tmpplugin->id.'&action=edit&token='.newToken().'&backtopage='.$backtocard.'">'.img_edit().'</a>';
				print '<a class="reposition" href="'.$_SERVER['PHP_SELF'].$urlparam.'&action=deleteplugin&childid='.$tmpplugin->id.'&token='.newToken().'">'.img_delete().'</a>';
			}
			print '</td>';
			print '</tr>';
		}
		if ($permissiontoadd && $pluginurl > 0) {
			print '<tr class="oddeven">';
			print '<td></td>';
			print '<td class="center"><input type="checkbox" name="plugin_rental" value="1" title="'.dol_escape_htmltag($langs->trans('PluginRentalYes')).'"'.(GETPOSTINT('plugin_rental') ? ' checked' : '').'></td>';
			if ($withproduct) {
				print '<td>';
				$pluginproducts = SitePlugin::getPluginProductIds($db);
				if (is_array($pluginproducts)) {
					// Category of the plugins set in the setup: only its products
					$listproducts = array();
					if (count($pluginproducts)) {
						$sql = "SELECT p.rowid, p.ref, p.label FROM ".$db->prefix()."product as p";
						$sql .= " WHERE p.rowid IN (".$db->sanitize(implode(',', $pluginproducts)).")";
						$sql .= " AND p.entity IN (".getEntity('product').")";
						$sql .= " ORDER BY p.ref ASC";
						$resql = $db->query($sql);
						if ($resql) {
							while ($obj = $db->fetch_object($resql)) {
								$listproducts[(int) $obj->rowid] = $obj->ref.' - '.$obj->label;
							}
							$db->free($resql);
						}
					}
					print $form->selectarray('plugin_fk_product', $listproducts, GETPOSTINT('plugin_fk_product'), 1, 0, 0, '', 0, 0, 0, '', 'minwidth100 maxwidth200');
				} else {
					$form->select_produits(GETPOSTINT('plugin_fk_product'), 'plugin_fk_product', '', 0, 0, -1, 2, '', 0, array(), 0, '1', 0, 'minwidth100 maxwidth200');
				}
				print '</td>';
			}
			print '<td><input type="text" name="plugin_label" class="minwidth150 widthcentpercentminusx" value="'.dol_escape_htmltag(GETPOST('plugin_label', 'alphanohtml')).'"></td>';
			print '<td></td>';
			print '<td></td><td></td>';
			print '<td><input type="text" name="plugin_version" class="maxwidth100" value="'.dol_escape_htmltag(GETPOST('plugin_version', 'alphanohtml')).'"></td>';
			print '<td></td>';
			print '<td class="right"><input type="submit" class="button small" value="'.$langs->trans('Add').'"></td>';
			print '</tr>';
		}
		print '</table>';
		print '</div>';
		print '</form>';
		print dol_get_fiche_end();

		// Filter of the rows of the plugins with the search fields
		print '<script nonce="'.getNonce().'">'."\n";
		print 'jQuery(document).ready(function() {'."\n";
		print '	function filterPlugins() {'."\n";
		print '		var filters = {};'."\n";
		print '		jQuery("#tablesiteplugins .pluginfilter").each(function() { var v = String(jQuery(this).val() || "").toLowerCase().trim(); if (v !== "" && v !== "-1") { filters[jQuery(this).data("filter")] = v; } });'."\n";
		print '		jQuery("#tablesiteplugins tr.pluginrow").each(function() {'."\n";
		print '			var row = jQuery(this), show = true;'."\n";
		print '			jQuery.each(filters, function(key, value) {'."\n";
		print '				var data = String(row.attr("data-" + key) || "");'."\n";
		print '				if ((key === "rental" || key === "newer") ? data !== value : data.indexOf(value) < 0) { show = false; }'."\n";
		print '			});'."\n";
		print '			row.toggle(show);'."\n";
		print '		});'."\n";
		print '	}'."\n";
		print '	jQuery("#tablesiteplugins .pluginfilter").on("keyup change", filterPlugins);'."\n";
		// Sort of the rows of the plugins: text, or version numbers compared number by number
		print '	function comparePluginValues(a, b, key) {'."\n";
		print '		if (key === "version" || key === "dolistore") {'."\n";
		print '			var pa = String(a).split(/[^0-9]+/), pb = String(b).split(/[^0-9]+/);'."\n";
		print '			if (a === "") { return (b === "" ? 0 : 1); } if (b === "") { return -1; }'."\n";
		print '			for (var i = 0; i < Math.max(pa.length, pb.length); i++) { var na = parseInt(pa[i] || 0, 10), nb = parseInt(pb[i] || 0, 10); if (na !== nb) { return na - nb; } }'."\n";
		print '			return 0;'."\n";
		print '		}'."\n";
		print '		return String(a).localeCompare(String(b), undefined, {numeric: true, sensitivity: "base"});'."\n";
		print '	}'."\n";
		print '	jQuery("#tablesiteplugins .pluginsort").on("click", function() {'."\n";
		print '		var key = jQuery(this).data("sort"), asc = !(jQuery(this).data("asc") === 1);'."\n";
		print '		jQuery("#tablesiteplugins .pluginsort").removeData("asc").find(".pluginsortarrow").remove();'."\n";
		print '		jQuery(this).data("asc", asc ? 1 : 0).append(\'<span class="pluginsortarrow paddingleft fas fa-caret-\' + (asc ? "up" : "down") + \'"></span>\');'."\n";
		print '		var rows = jQuery("#tablesiteplugins tr.pluginrow").get();'."\n";
		print '		if (!rows.length) { return; }'."\n";
		print '		var anchor = jQuery(rows[0]).prev();'."\n";
		print '		rows.sort(function(ra, rb) { var r = comparePluginValues(String(jQuery(ra).attr("data-" + key) || ""), String(jQuery(rb).attr("data-" + key) || ""), key); return asc ? r : -r; });'."\n";
		print '		jQuery.each(rows, function(i, row) { anchor.after(row); anchor = jQuery(row); });'."\n";
		print '	});'."\n";
		print '	jQuery("#checkallplugins").on("change", function() { jQuery("#tablesiteplugins tr.pluginrow:visible .checkforselectplugin").prop("checked", jQuery(this).is(":checked")); });'."\n";
		print '});'."\n";
		print '</script>'."\n";
	}
	print '<br>';

	// Buttons for actions

	if ($action != 'presend' && $action != 'editline') {
		print '<div class="tabsAction">'."\n";
		$parameters = array();
		$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
		if ($reshook < 0) {
			setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
		}

		if (empty($reshook)) {
			// Send
			if (empty($user->socid)) {
				print dolGetButtonAction('', $langs->trans('SendMail'), 'email', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=presend&token='.newToken().'&mode=init#formmailbeforetitle');
			}

			// Modify
			print dolGetButtonAction('', $langs->trans('Modify'), 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=edit&token='.newToken(), '', $permissiontoadd);

			// Stop / put back in service
			if ($object->status == $object::STATUS_VALIDATED) {
				print dolGetButtonAction('', $langs->trans('SiteStop'), 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=confirm_close&confirm=yes&token='.newToken(), '', $permissiontoadd);
			} elseif ($object->status == $object::STATUS_CANCELED) {
				print dolGetButtonAction('', $langs->trans('SiteReopen'), 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=confirm_reopen&confirm=yes&token='.newToken(), '', $permissiontoadd);
			}

			// Clone
			if ($permissiontoadd) {
				print dolGetButtonAction('', $langs->trans('ToClone'), 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.(!empty($object->socid) ? '&socid='.$object->socid : '').'&action=clone&token='.newToken(), '', $permissiontoadd);
			}

			/*
			// Disable / Enable
			if ($permissiontoadd) {
				if ($object->status == $object::STATUS_ENABLED) {
					print dolGetButtonAction('', $langs->trans('Disable'), 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=disable&token='.newToken(), '', $permissiontoadd);
				} else {
					print dolGetButtonAction('', $langs->trans('Enable'), 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=enable&token='.newToken(), '', $permissiontoadd);
				}
			}
			if ($permissiontoadd) {
				if ($object->status == $object::STATUS_VALIDATED) {
					print dolGetButtonAction('', $langs->trans('Cancel'), 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=close&token='.newToken(), '', $permissiontoadd);
				} else {
					print dolGetButtonAction('', $langs->trans('Re-Open'), 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=reopen&token='.newToken(), '', $permissiontoadd);
				}
			}
			*/

			// Delete (with preloaded confirm popup)
			$deleteUrl = $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=delete&token='.newToken();
			$buttonId = 'action-delete-no-ajax';
			if ($conf->use_javascript_ajax && empty($conf->dol_use_jmobile)) {	// We can use preloaded confirm if not jmobile
				$deleteUrl = '';
				$buttonId = 'action-delete';
			}
			$params = array();
			print dolGetButtonAction('', $langs->trans("Delete"), 'delete', $deleteUrl, $buttonId, $permissiontodelete, $params);
		}
		print '</div>'."\n";
	}


	// Select mail models is same action as presend
	if (GETPOST('modelselected')) {
		$action = 'presend';
	}

	if ($action != 'presend') {
		print '<div class="fichecenter"><div class="fichehalfleft">';
		print '<a name="builddoc"></a>'; // ancre

		$includedocgeneration = 0;

		// Documents
		if ($includedocgeneration) {
			$objref = dol_sanitizeFileName($object->ref);
			$relativepath = $objref.'/'.$objref.'.pdf';
			$filedir = $conf->nssuiviprod->dir_output.'/'.$object->element.'/'.$objref;
			$urlsource = $_SERVER["PHP_SELF"]."?id=".$object->id;
			$genallowed = $permissiontoread; // If you can read, you can build the PDF to read content
			$delallowed = $permissiontoadd; // If you can create/edit, you can remove a file on card
			print $formfile->showdocuments('nssuiviprod:Site', $object->element.'/'.$objref, $filedir, $urlsource, $genallowed, $delallowed, $object->model_pdf, 1, 0, 0, 28, 0, '', '', '', $langs->defaultlang);
		}

		// Show links to link elements
		$tmparray = $form->showLinkToObjectBlock($object, array(), array('site'), 1);
		if (is_array($tmparray)) {
			$linktoelem = $tmparray['linktoelem'];
			$htmltoenteralink = $tmparray['htmltoenteralink'];
			print $htmltoenteralink;
			$somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);
		} else {
			// backward compatibility
			$somethingshown = $form->showLinkedObjectBlock($object, $tmparray);
		}

		print '</div><div class="fichehalfright">';

		$MAXEVENT = 10;

		$morehtmlcenter = dolGetButtonTitle($langs->trans('SeeAll'), '', 'fa fa-bars imgforviewmode', dol_buildpath('/nssuiviprod/site_agenda.php', 1).'?id='.$object->id);

		// Latest events of the site (button to add an event linked to the site and its thirdparty provided by showactions())
		$includeeventlist = (isModEnabled('agenda') && ($user->hasRight('agenda', 'myactions', 'read') || $user->hasRight('agenda', 'allactions', 'read'))) ? 1 : 0;

		// List of actions on element
		if ($includeeventlist) {
			include_once DOL_DOCUMENT_ROOT.'/core/class/html.formactions.class.php';
			$formactions = new FormActions($db);
			$somethingshown = $formactions->showactions($object, $object->element.'@'.$object->module, (is_object($object->thirdparty) ? $object->thirdparty->id : 0), 1, '', $MAXEVENT, '', $morehtmlcenter);
		}

		print '</div></div>';
	}

	//Select mail models is same action as presend
	if (GETPOST('modelselected')) {
		$action = 'presend';
	}

	// Presend form
	$modelmail = 'site';
	$defaulttopic = 'InformationMessage';
	$diroutput = $conf->nssuiviprod->dir_output;
	$trackid = 'site'.$object->id;

	include DOL_DOCUMENT_ROOT.'/core/tpl/card_presend.tpl.php';
}

// End of page
llxFooter();
$db->close();
