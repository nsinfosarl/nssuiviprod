<?php
/* Copyright (C) 2026		Nicolas SILOBRE				<contact@ns-info.fr>
 * Copyright (C) 2025       Frédéric France         <frederic.france@free.fr>
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
 * \file    lib/nssuiviprod_site.lib.php
 * \ingroup nssuiviprod
 * \brief   Library files with common functions for Site
 */

/**
 * Prepare array of tabs for Site
 *
 * @param	Site	$object					Site
 * @return 	array<array{string,string,string}>	Array of tabs
 */
function sitePrepareHead($object)
{
	global $db, $langs, $conf;

	$langs->load("nssuiviprod@nssuiviprod");

	$showtabofpagecontact = getDolGlobalInt('MAIN_NSSUIVIPROD_SHOW_PAGE_OF_CONTACT');
	$showtabofpagenote = 1;
	$showtabofpagedocument = 1;
	$showtabofpageagenda = 1;

	$h = 0;
	$head = array();

	$head[$h][0] = dolBuildUrl(dol_buildpath("/nssuiviprod/core/site/site_card.php", 1), ['id' => $object->id]);
	$head[$h][1] = $langs->trans("Site");
	$head[$h][2] = 'card';
	$h++;

	// Plugins installed on the URLs of the site: summary of the versions and one tab per URL
	$nbplugins = 0;
	$sql = "SELECT COUNT(*) as nb FROM ".$db->prefix()."nssuiviprod_siteplugin WHERE fk_site = ".((int) $object->id);
	$resql = $db->query($sql);
	if ($resql) {
		$obj = $db->fetch_object($resql);
		$nbplugins = ($obj ? (int) $obj->nb : 0);
		$db->free($resql);
	}
	$head[$h][0] = dolBuildUrl(dol_buildpath("/nssuiviprod/core/site/site_card.php", 1), ['id' => $object->id, 'tab' => 'plugins']).'#siteplugins';
	$head[$h][1] = img_picto('', 'fa-puzzle-piece', 'class="pictofixedwidth" style="color: #5A2D82"').$langs->trans('SitePlugins').($nbplugins > 0 ? '<span class="badge marginleftonlyshort">'.$nbplugins.'</span>' : '');
	$head[$h][2] = 'plugins';
	$h++;

	if ($showtabofpagecontact) {
		$head[$h][0] = dolBuildUrl(dol_buildpath("/nssuiviprod/core/site/site_contact.php", 1), ['id' => $object->id]);
		$head[$h][1] = $langs->trans("Contacts");
		$head[$h][2] = 'contact';
		$h++;
	}

	if ($showtabofpagenote) {
		if (isset($object->fields['note_public']) || isset($object->fields['note_private'])) {
			$nbNote = 0;
			if (!empty($object->note_private)) {
				$nbNote++;
			}
			if (!empty($object->note_public)) {
				$nbNote++;
			}
			$head[$h][0] = dolBuildUrl(dol_buildpath('/nssuiviprod/core/site/site_note.php', 1), ['id' => $object->id]);
			$head[$h][1] = $langs->trans('Notes');
			if ($nbNote > 0) {
				$head[$h][1] .= (!getDolGlobalInt('MAIN_OPTIMIZEFORTEXTBROWSER') ? '<span class="badge marginleftonlyshort">'.$nbNote.'</span>' : '');
			}
			$head[$h][2] = 'note';
			$h++;
		}
	}

	if ($showtabofpagedocument) {
		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
		require_once DOL_DOCUMENT_ROOT.'/core/class/link.class.php';
		$upload_dir = $conf->nssuiviprod->dir_output."/site/".dol_sanitizeFileName($object->ref);
		$nbFiles = count(dol_dir_list($upload_dir, 'files', 0, '', '(\.meta|_preview.*\.png)$'));
		$nbLinks = Link::count($db, $object->element, $object->id);
		$head[$h][0] = dolBuildUrl(dol_buildpath("/nssuiviprod/core/site/site_document.php", 1), ['id' => $object->id]);
		$head[$h][1] = $langs->trans('Documents');
		if (($nbFiles + $nbLinks) > 0) {
			$head[$h][1] .= '<span class="badge marginleftonlyshort">'.($nbFiles + $nbLinks).'</span>';
		}
		$head[$h][2] = 'document';
		$h++;
	}

	if ($showtabofpageagenda) {
		$head[$h][0] = dolBuildUrl(dol_buildpath("/nssuiviprod/core/site/site_agenda.php", 1), ['id' => $object->id]);
		$head[$h][1] = $langs->trans("Events");
		$head[$h][2] = 'agenda';
		$h++;
	}

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@nssuiviprod:/nssuiviprod/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@nssuiviprod:/nssuiviprod/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'site@nssuiviprod');

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'site@nssuiviprod', 'remove');

	return $head;
}

/**
 * Return the number of events of the agenda linked to a site
 *
 * @param	Site	$object		Site
 * @return	int					Number of events (0 if the module Agenda is disabled)
 */
function nssuiviprodSiteCountEvents($object)
{
	global $db;

	if (!isModEnabled('agenda') || empty($object->id)) {
		return 0;
	}
	$sql = "SELECT COUNT(a.id) as nb FROM ".$db->prefix()."actioncomm as a";
	$sql .= " WHERE a.fk_element = ".((int) $object->id)." AND a.elementtype = '".$db->escape($object->element.'@'.$object->module)."'";
	$sql .= " AND a.entity IN (".getEntity('agenda').")";
	$resql = $db->query($sql);
	if ($resql && ($obj = $db->fetch_object($resql))) {
		return (int) $obj->nb;
	}

	return 0;
}

/**
 * Return the active entries of a dictionary of the module
 *
 * @param	DoliDB	$db			Database handler
 * @param	string	$table		Dictionary table without prefix ('c_nssuiviprod_typesite' or 'c_nssuiviprod_typeurl')
 * @return	array<int,string>	Id => label
 */
function nssuiviprodGetDictionary($db, $table)
{
	$list = array();
	if (!in_array($table, array('c_nssuiviprod_typesite', 'c_nssuiviprod_typeurl'))) {
		return $list;
	}
	$sql = "SELECT t.rowid, t.label FROM ".$db->prefix().$table." as t";
	$sql .= " WHERE t.active = 1";
	$sql .= " AND t.entity IN (".getEntity($table).")";
	$sql .= " ORDER BY t.position ASC, t.label ASC";
	$resql = $db->query($sql);
	if ($resql) {
		while ($obj = $db->fetch_object($resql)) {
			$list[(int) $obj->rowid] = $obj->label;
		}
		$db->free($resql);
	}

	return $list;
}

/**
 * Return the script that fills the name of the site with the label of the selected type of site: when the name is empty or
 * still holds the label of the previously selected type (a name typed by the user is never replaced)
 *
 * @return	string		HTML code
 */
function nssuiviprodSiteLabelScript()
{
	return '<script type="text/javascript">
jQuery(document).ready(function() {
	var typefield = jQuery("select[name=\'fk_typesite\']");
	var labelfield = jQuery("input[name=\'label\']");
	if (!typefield.length || !labelfield.length) {
		return;
	}
	function typelabel() {
		return (typefield.val() > 0 ? jQuery.trim(typefield.find("option:selected").text()) : "");
	}
	var previous = typelabel();
	if (labelfield.val() === "") {
		labelfield.val(previous);
	}
	typefield.on("change", function() {
		var current = typelabel();
		if (labelfield.val() === "" || labelfield.val() === previous) {
			labelfield.val(current);
		}
		previous = current;
	});
});
</script>';
}

/**
 * Return the information shown under the reference in the banner of all the tabs of a site: label, thirdparty and type,
 * each with a pencil to change it (edited in the banner of the card of the site: actions editbanner_label, editbanner_thirdparty,
 * editbanner_typesite, saved by setbanner_label, setbanner_thirdparty, setbanner_typesite of site_card.php)
 *
 * @param	Site	$object		Site
 * @param	string	$action		Current action of the card of the site ('' on the other tabs)
 * @return	string				HTML code (to use as $morehtmlref of dol_banner_tab())
 */
function nssuiviprodSiteBannerRef($object, $action = '')
{
	global $db, $langs, $user, $form;

	$langs->load("nssuiviprod@nssuiviprod");
	if (!is_object($form)) {
		require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
		$form = new Form($db);
	}
	$canedit = ($user->hasRight('nssuiviprod', 'site', 'write') && $object->status != Site::STATUS_CANCELED);
	$cardurl = dol_buildpath('/nssuiviprod/core/site/site_card.php', 1).'?id='.((int) $object->id);
	$pencil = function ($editaction, $label) use ($canedit, $cardurl, $langs) {
		return $canedit ? ' <a class="editfielda reposition" href="'.$cardurl.'&action='.$editaction.'&token='.newToken().'">'.img_edit($langs->trans('Modify').' : '.$langs->trans($label), 0, 'class="paddingleft"').'</a>' : '';
	};
	$editform = function ($setaction, $input) use ($cardurl, $langs) {
		$html = '<form method="POST" action="'.$cardurl.'" class="inline-block">';
		$html .= '<input type="hidden" name="token" value="'.newToken().'">';
		$html .= '<input type="hidden" name="action" value="'.$setaction.'">';
		$html .= $input;
		$html .= ' <input type="submit" class="button smallpaddingimp" name="save" value="'.dol_escape_htmltag($langs->trans('Modify')).'">';
		$html .= ' <a class="button button-cancel smallpaddingimp" href="'.$cardurl.'">'.$langs->trans('Cancel').'</a>';
		$html .= '</form>';
		return $html;
	};

	$out = '<div class="refidno">';

	// Label of the site
	if ($action == 'editbanner_label' && $canedit) {
		$out .= $editform('setbanner_label', '<input type="text" name="label" class="minwidth300" maxlength="255" value="'.dol_escape_htmltag((string) $object->label).'" autofocus>');
	} else {
		$out .= '<strong>'.dol_escape_htmltag((string) $object->label).'</strong>'.$pencil('editbanner_label', 'SiteLabel');
	}

	// Thirdparty
	$out .= '<br>';
	if ($action == 'editbanner_thirdparty' && $canedit) {
		$out .= $editform('setbanner_thirdparty', img_picto('', 'company', 'class="pictofixedwidth"').$form->select_company((int) $object->fk_soc, 'fk_soc', '(s.status:=:1)', '', 0, 0, array(), 0, 'minwidth300'));
	} else {
		if (!empty($object->fk_soc)) {
			require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
			$thirdparty = new Societe($db);
			if ($thirdparty->fetch((int) $object->fk_soc) > 0) {
				$out .= $thirdparty->getNomUrl(1);
			}
		}
		$out .= $pencil('editbanner_thirdparty', 'ThirdParty');
	}

	// Type of site
	$types = nssuiviprodGetDictionary($db, 'c_nssuiviprod_typesite');
	$out .= '<br>';
	if ($action == 'editbanner_typesite' && $canedit) {
		$out .= $editform('setbanner_typesite', img_picto('', 'fa-tag', 'class="pictofixedwidth"').$form->selectarray('fk_typesite', $types, (int) $object->fk_typesite, 0, 0, 0, '', 0, 0, 0, '', 'minwidth200'));
	} else {
		$out .= img_picto($langs->trans('SiteType'), 'fa-tag', 'class="pictofixedwidth"');
		$out .= (!empty($object->fk_typesite) && isset($types[(int) $object->fk_typesite]) ? dol_escape_htmltag($types[(int) $object->fk_typesite]) : '');
		$out .= $pencil('editbanner_typesite', 'SiteType');
	}
	$out .= '</div>';

	return $out;
}


/**
 * Summary of the plugins of a site : one row per module, one column per URL (instance) with the installed version, the
 * absence of a module on an URL flagged, the different versions and the version of the Dolistore
 *
 * @param	array<int,SiteUrl>		$urls			URLs of the site (id => SiteUrl), in the order of the tabs
 * @param	array<int,SitePlugin>	$allplugins		Plugins of the site
 * @return	string							HTML
 */
function nssuiviprodPluginSummary($urls, $allplugins)
{
	global $langs;

	// Modules not listed (setup, sub-tab "Plugins summary"): hidden, shown in grey with the box "Show the ignored modules"
	$ignored = array_filter(array_map('intval', explode(',', getDolGlobalString('NSSUIVIPROD_SUMMARY_IGNORED'))));

	$langs->load('nssuiviprod@nssuiviprod');
	$rows = array();
	foreach ($allplugins as $plugin) {
		$urlid = (int) $plugin->fk_siteurl;
		if ((string) $plugin->module_enabled === '0' || !isset($urls[$urlid])) {
			continue;	// Module disabled on the site, or plugin without URL: not compared
		}
		$key = ((int) $plugin->fk_module > 0 ? 'm'.(int) $plugin->fk_module : 'l'.dol_strtolower(trim((string) $plugin->label)));
		if (!isset($rows[$key])) {
			$rows[$key] = array('label' => (string) $plugin->label, 'publisher' => (string) $plugin->publisher, 'plugin' => $plugin, 'versions' => array());
		}
		if (!isset($rows[$key]['versions'][$urlid])) {
			$rows[$key]['versions'][$urlid] = (string) $plugin->version;
		}
	}
	if (!count($rows)) {
		return '<div class="opacitymedium">'.$langs->trans('PluginSummaryEmpty').'</div>';
	}
	uasort($rows, function ($a, $b) {
		return strcasecmp($a['label'], $b['label']);
	});

	$nburls = count($urls);
	$nbissues = 0;
	$nbabsences = 0;
	$nbignored = 0;
	$body = '';
	foreach ($rows as $key => $row) {
		$isignored = (substr($key, 0, 1) == 'm' && in_array((int) substr($key, 1), $ignored));
		$present = $row['versions'];
		$max = '';
		foreach ($present as $version) {
			if ($version !== '' && ($max === '' || version_compare($version, $max, '>'))) {
				$max = $version;
			}
		}
		$nbabsent = $nburls - count($present);
		$different = (count(array_unique(array_map('strval', $present))) > 1);
		$issue = ($nbabsent > 0 || $different);
		if ($isignored) {
			$nbignored++;
		} else {
			$nbabsences += $nbabsent;
			$nbissues += ($issue ? 1 : 0);
		}

		$module = $row['plugin']->getModule();
		$name = dol_escape_htmltag($row['label']);
		if ($module) {
			$name = '<a href="'.dol_buildpath('/nssuiviprod/core/module/pluginmodule_card.php', 1).'?id='.((int) $module->id).'" title="'.dol_escape_htmltag($langs->trans('ShowPluginModule')).'">'.$name.'</a>';
		}
		$body .= '<tr class="oddeven nssuiviprod-sumrow" data-issue="'.($issue ? 1 : 0).'" data-ignored="'.($isignored ? 1 : 0).'"'.($isignored ? ' style="display: none; opacity: 0.5"' : '').'>';
		$body .= '<td class="tdoverflowmax200" title="'.dol_escape_htmltag($row['publisher']).'">'.$name.($row['publisher'] !== '' ? '<span class="opacitymedium small" style="display: block">'.dol_escape_htmltag($row['publisher']).'</span>' : '').'</td>';
		// Dolistore: links and last version (in orange when newer than the oldest version installed)
		$oldest = '';
		foreach ($present as $version) {
			if ($version !== '' && ($oldest === '' || version_compare($version, $oldest, '<'))) {
				$oldest = $version;
			}
		}
		$body .= '<td class="nowraponall">'.$row['plugin']->getLinks('dolistore').($module ? ' '.$module->getDolistoreVersionBadge($oldest) : '').'</td>';
		foreach ($urls as $urlid => $url) {
			$body .= '<td class="center nowraponall">';
			if (!array_key_exists($urlid, $present)) {
				$body .= '<span class="badge badge-status8 classfortooltip" title="'.dol_escape_htmltag($langs->trans('PluginSummaryAbsentHelp')).'">'.img_picto('', 'fa-times-circle', 'class="pictofixedwidth"').$langs->trans('PluginSummaryAbsent').'</span>';
			} elseif ($present[$urlid] !== '' && $max !== '' && version_compare($present[$urlid], $max, '<')) {
				$body .= '<strong style="color: #E67E22" class="classfortooltip" title="'.dol_escape_htmltag($langs->trans('PluginSummaryOlder', $max)).'">'.dol_escape_htmltag($present[$urlid]).'</strong>';
			} else {
				$body .= dol_escape_htmltag($present[$urlid]);
			}
			$body .= '</td>';
		}
		$body .= '</tr>';
	}

	$out = '<div class="marginbottomonly">';
	$out .= '<span class="opacitymedium">'.$langs->trans('PluginSummaryCount', count($rows) - $nbignored, $nbissues, $nbabsences).'</span>';
	$out .= ' &nbsp; <label class="nowraponall cursorpointer"><input type="checkbox" id="nssuiviprodsumonly"> '.$langs->trans('PluginSummaryOnlyIssues').'</label>';
	if ($nbignored > 0) {
		$out .= ' &nbsp; <label class="nowraponall cursorpointer"><input type="checkbox" id="nssuiviprodsumignored"> '.$langs->trans('PluginSummaryShowIgnored', $nbignored).'</label>';
	}
	$out .= '</div>';
	$out .= '<div class="div-table-responsive-no-min"><table class="noborder centpercent" id="nssuiviprodsummary">';
	$out .= '<tr class="liste_titre"><td>'.$langs->trans('PluginLabel').'</td><td>'.img_picto('Dolistore', 'fa-store', 'class="pictofixedwidth opacitymedium"').'Dolistore</td>';
	foreach ($urls as $url) {
		$label = SiteUrl::getEnvironmentBadge($url->environment, 1).(trim((string) $url->label) !== '' ? ' '.dol_escape_htmltag($url->label) : '');
		// The address of the URL is the tooltip of the whole header (the tooltip of the icon of the environment is not shown)
		$out .= '<td class="center nowraponall" title="'.dol_escape_htmltag($url->url).'"><span style="pointer-events: none">'.$label.'</span></td>';
	}
	$out .= '</tr>';
	$out .= $body.'</table></div>';
	$out .= '<script nonce="'.getNonce().'">jQuery(function() { function filterSummary() { var only = jQuery("#nssuiviprodsumonly").is(":checked"), ign = jQuery("#nssuiviprodsumignored").is(":checked"); jQuery("#nssuiviprodsummary tr.nssuiviprod-sumrow").each(function() { var r = jQuery(this); r.toggle((ign || r.attr("data-ignored") !== "1") && (!only || r.attr("data-issue") === "1")); }); } jQuery("#nssuiviprodsumonly, #nssuiviprodsumignored").on("change", filterSummary); });</script>';

	return $out;
}
