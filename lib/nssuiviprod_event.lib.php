<?php
/* Copyright (C) 2026		Nicolas Silobre					<contact@ns-info.fr>
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
 * \file    nssuiviprod/lib/nssuiviprod_event.lib.php
 * \ingroup nssuiviprod
 * \brief   Events of the agenda recorded for the actions on a site (site, URL, plugins), with the detail of the changes
 */

/**
 * Record an event of the agenda (done, automatic) linked to a site and to its thirdparty
 *
 * @param	DoliDB		$db			Database handler
 * @param	User		$user		User who did the action
 * @param	int			$fk_site	Id of the site (0 = event linked to the thirdparty only, example: site deleted)
 * @param	string		$label		Label of the event
 * @param	string[]	$details	Lines of the detail of the action (text, not HTML)
 * @param	int			$fk_soc		Thirdparty (0 = thirdparty of the site)
 * @return	int						Id of the event, 0 if not recorded (agenda disabled), <0 if KO
 */
function nssuiviprodAddSiteEvent($db, $user, $fk_site, $label, $details = array(), $fk_soc = 0)
{
	global $conf;

	if (!isModEnabled('agenda') || !is_object($user) || empty($user->id)) {
		return 0;
	}
	if (empty($fk_soc) && $fk_site > 0) {
		$resql = $db->query("SELECT fk_soc FROM ".$db->prefix()."nssuiviprod_site WHERE rowid = ".((int) $fk_site));
		if ($resql && ($obj = $db->fetch_object($resql))) {
			$fk_soc = (int) $obj->fk_soc;
		}
	}

	require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';
	$event = new ActionComm($db);
	$event->type_code = 'AC_OTH_AUTO';
	$event->code = 'AC_NSSUIVIPROD';
	$event->label = dol_trunc($label, 250, 'right', 'UTF-8', 1);
	$event->note_private = '';
	if (count($details)) {
		$event->note_private = '<ul><li>'.implode('</li><li>', array_map('dol_escape_htmltag', $details)).'</li></ul>';
	}
	$event->datep = dol_now();
	$event->datef = $event->datep;
	$event->percentage = -1;	// Not applicable (automatic event)
	$event->userownerid = $user->id;
	$event->authorid = $user->id;
	$event->socid = (int) $fk_soc;
	if ($fk_site > 0) {
		$event->fk_element = (int) $fk_site;
		$event->elementid = (int) $fk_site;
		$event->elementtype = 'site@nssuiviprod';
	}

	$result = $event->create($user, 1);
	if ($result < 0) {
		dol_syslog(__FUNCTION__.' '.$event->error, LOG_ERR);
	}

	return $result;
}

/**
 * Return the changes of the fields of an object (one line per changed field: "Label: old value → new value"), or its
 * values when it is created (one line per filled field: "Label: value")
 *
 * @param	CommonObject		$object		Object after the change
 * @param	CommonObject|null	$old		Object before the change, null for a new object
 * @return	string[]
 */
function nssuiviprodObjectChanges($object, $old = null)
{
	global $langs;

	$skip = array('rowid', 'ref', 'entity', 'tms', 'date_creation', 'fk_user_creat', 'fk_user_modif', 'import_key', 'rang', 'last_main_doc', 'model_pdf', 'json_date', 'fk_site');
	$created = ($old === null);
	if ($created) {
		$classname = get_class($object);
		$old = new $classname($object->db);
	}
	$changes = array();
	foreach ($object->fields as $key => $val) {
		if (in_array($key, $skip) || empty($val['visible']) || !property_exists($object, $key)) {
			continue;
		}
		$new = $object->$key;
		$before = $old->$key;
		if (in_array($val['type'], array('date', 'datetime'))) {
			$new = (empty($new) ? '' : (string) (is_numeric($new) ? $new : $object->db->jdate($new)));
			$before = (empty($before) ? '' : (string) (is_numeric($before) ? $before : $object->db->jdate($before)));
			if ($val['type'] == 'date') {	// Same day: no change
				$new = ($new === '' ? '' : dol_print_date((int) $new, 'day', 'gmt'));
				$before = ($before === '' ? '' : dol_print_date((int) $before, 'day', 'gmt'));
			}
		} elseif (preg_match('/^(integer|int|sellist|double|boolean)/', $val['type'])) {
			$new = (($new === null || $new === '' || (preg_match('/^(integer:|sellist)/', $val['type']) && (int) $new <= 0)) ? '' : (string) ($new + 0));
			$before = (($before === null || $before === '' || (preg_match('/^(integer:|sellist)/', $val['type']) && (int) $before <= 0)) ? '' : (string) ($before + 0));
		} else {
			$new = trim((string) $new);
			$before = trim((string) $before);
		}
		if ($new === $before) {
			continue;
		}
		$label = $langs->transnoentitiesnoconv($val['label']);
		if (preg_match('/^(text|html)/', $val['type'])) {
			$changes[] = $langs->transnoentitiesnoconv('NssuiviprodEvtFieldChanged', $label);	// Long text: no detail
			continue;
		}
		if ($created) {
			$changes[] = $label.' : '.nssuiviprodEventValue($object, $key, $object->$key);
		} else {
			$changes[] = $langs->transnoentitiesnoconv('NssuiviprodEvtFieldChange', $label, nssuiviprodEventValue($old, $key, $old->$key), nssuiviprodEventValue($object, $key, $object->$key));
		}
	}

	return $changes;
}

/**
 * Return the value of a field of an object as a text for the detail of an event
 *
 * @param	CommonObject	$object		Object
 * @param	string			$key		Field
 * @param	mixed			$value		Value
 * @return	string
 */
function nssuiviprodEventValue($object, $key, $value)
{
	global $langs;

	if ($value === null || $value === '' || (preg_match('/^(integer:|sellist)/', $object->fields[$key]['type']) && (int) $value <= 0)) {
		return $langs->transnoentitiesnoconv('NssuiviprodEvtEmpty');
	}
	// Linked object: its reference and its label
	$reg = array();
	if (preg_match('/^integer:([^:]+):([^:]+)/', $object->fields[$key]['type'], $reg)) {
		dol_include_once('/'.$reg[2]);
		if (class_exists($reg[1])) {
			$linked = new $reg[1]($object->db);
			if ($linked->fetch((int) $value) > 0) {
				$ref = (property_exists($linked, 'url') && !empty($linked->url) ? $linked->url : $linked->ref);	// URL: the address, not its reference
				$parts = array_unique(array_filter(array(trim((string) $ref), trim((string) (property_exists($linked, 'label') ? $linked->label : '')))));
				if (count($parts)) {
					return dol_trunc(implode(' - ', $parts), 100);
				}
			}
		}
	}
	if (in_array($object->fields[$key]['type'], array('date', 'datetime')) && !is_numeric($value)) {
		$value = $object->db->jdate($value);
	}
	$text = dol_string_nohtmltag((string) $object->showOutputField($object->fields[$key], $key, $value, '', '', '', ''), 1);
	$text = trim(preg_replace('/\s+/u', ' ', html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8')));

	return ($text === '' ? (string) $value : dol_trunc($text, 100));
}
