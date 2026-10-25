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
 * Fields of a plugin (SitePlugin) shown in tabs (see SitePlugin::getFieldGroups()), with the templates of Dolibarr.
 * Included in siteplugin_card.php (scope of the page).
 *
 * @var SitePlugin	$object
 * @var string		$fieldgroupsmode	'add', 'edit' or 'view'
 */

if (empty($object) || !is_object($object)) {
	print "Error, template page can't be called as URL";
	exit(1);
}

$fieldgroups = SitePlugin::getFieldGroups();
$allfields = $object->fields;
$groupedkeys = array();
foreach ($fieldgroups as $groupkeys) {
	$groupedkeys = array_merge($groupedkeys, $groupkeys);
}

// Tabs (switched in the page, all the fields stay in the form)
print '<div class="tabs pluginfieldtabs" data-role="controlgroup" data-type="horizontal">';
$first = true;
foreach ($fieldgroups as $groupname => $groupkeys) {
	print '<div class="inline-block tabsElem'.($first ? ' tabsElemActive' : '').'" data-group="'.$groupname.'">';
	print '<div class="tab tab'.($first ? 'active' : 'unactive').'" style="margin: 0 !important">';
	print '<a class="tab inline-block valignmiddle pluginfieldtab" href="#" data-group="'.$groupname.'">'.$langs->trans('PluginTab'.ucfirst($groupname)).'</a>';
	print '</div></div>';
	$first = false;
}
print '</div>';

$first = true;
foreach ($fieldgroups as $groupname => $groupkeys) {
	// Fields of the group (the fields of no group are in the first one)
	$object->fields = array();
	foreach ($allfields as $key => $val) {
		if (in_array($key, $groupkeys) || ($first && !in_array($key, $groupedkeys))) {
			$object->fields[$key] = $val;
		}
	}
	print '<div class="tabBar tabBarWithBottom pluginfieldgroup" data-group="'.$groupname.'"'.($first ? '' : ' style="display: none"').'>';
	print '<table class="border centpercent '.($fieldgroupsmode == 'view' ? 'tableforfield' : 'tableforfield'.($fieldgroupsmode == 'add' ? 'create' : 'edit')).'">'."\n";
	if ($fieldgroupsmode == 'add') {
		include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_add.tpl.php';
		if ($first) {
			include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_add.tpl.php';
		}
	} elseif ($fieldgroupsmode == 'edit') {
		include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_edit.tpl.php';
		if ($first) {
			include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_edit.tpl.php';
		}
	} else {
		include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_view.tpl.php';
		if ($first) {
			include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_view.tpl.php';
		}
	}
	// Fields of the module of the plugin (identity, Dolistore): read only, modified on the card of the module
	$groupmodule = ($fieldgroupsmode == 'view' ? $object->getModule() : null);
	if ($groupmodule) {
		foreach ($groupkeys as $key) {
			if (!isset(SitePlugin::$modulefields[$key]) || !isset($groupmodule->fields[SitePlugin::$modulefields[$key]])) {
				continue;
			}
			$modulefield = SitePlugin::$modulefields[$key];
			$moduleval = $groupmodule->fields[$modulefield];
			print '<tr class="field_'.$key.'"><td class="titlefieldmiddle fieldname_'.$key.'">';
			print (!empty($moduleval['help']) ? $form->textwithpicto($langs->trans($moduleval['label']), $langs->trans($moduleval['help'])) : $langs->trans($moduleval['label']));
			print '</td><td class="valuefield fieldname_'.$key.((string) $moduleval['type'] == 'text' ? ' wordbreak' : '').'">';
			print ($modulefield == 'ref' ? $groupmodule->getNomUrl(1) : $groupmodule->showOutputField($moduleval, $modulefield, (string) $groupmodule->$modulefield, ''));
			print '</td></tr>';
		}
	}
	print '</table>';
	print '</div>';
	$first = false;
}
$object->fields = $allfields;

print '<script nonce="'.getNonce().'">'."\n";
print 'jQuery(document).ready(function() {'."\n";
print '	jQuery(".pluginfieldtab").on("click", function(e) {'."\n";
print '		e.preventDefault();'."\n";
print '		var group = jQuery(this).data("group");'."\n";
print '		jQuery(".pluginfieldtabs .tabsElem").removeClass("tabsElemActive").children(".tab").removeClass("tabactive").addClass("tabunactive");'."\n";
print '		jQuery(".pluginfieldtabs .tabsElem[data-group=\'" + group + "\']").addClass("tabsElemActive").children(".tab").removeClass("tabunactive").addClass("tabactive");'."\n";
print '		jQuery(".pluginfieldgroup").hide();'."\n";
print '		jQuery(".pluginfieldgroup[data-group=\'" + group + "\']").show();'."\n";
print '	});'."\n";
print '});'."\n";
print '</script>'."\n";
