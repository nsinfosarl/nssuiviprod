<?php
/* Copyright (C) 2004-2018  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2019  Nicolas ZABOURI         <info@inovea-conseil.com>
 * Copyright (C) 2019-2024  Frédéric France         <frederic.france@free.fr>
 * Copyright (C) 2026       Nicolas Silobre         <contact@ns-info.fr>
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
 *  \defgroup   nssuiviprod     Module Nssuiviprod
 *  \brief      Nssuiviprod module descriptor.
 *
 *  \file       htdocs/nssuiviprod/core/modules/modNssuiviprod.class.php
 *  \ingroup    nssuiviprod
 *  \brief      Description and activation file for module Nssuiviprod
 */
include_once DOL_DOCUMENT_ROOT . '/core/modules/DolibarrModules.class.php';

/**
 *  Description and activation class for module Nssuiviprod
 */
class modNssuiviprod extends DolibarrModules
{
	/**
	 * Constructor. Define names, constants, directories, boxes, permissions
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		global $langs, $conf;
		$this->db = $db;
		$this->numero = 172791;
		$this->rights_class = 'nssuiviprod';
		$this->family = "NS INFO";
		$this->module_position = '90';
		$this->name = preg_replace('/^mod/i', '', get_class($this));
		$this->description = "NssuiviprodDescription";
		$this->descriptionlong = "NssuiviprodDescription";

		// Author
		$this->editor_name = 'NS INFO';
		$this->editor_url = 'https://www.ns-info.fr';

		// Possible values for version are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated', 'experimental_deprecated' or a version string like 'x.y.z'
		$this->version = trim(file_get_contents(__DIR__.'/../../VERSION'));
		$this->const_name = 'MAIN_MODULE_' . strtoupper($this->name);
		$this->picto = 'nssuiviprod@nssuiviprod';

		// Define some features supported by module (triggers, login, substitutions, menus, css, etc...)
		$this->module_parts = array(
			'triggers' => 0,
			'models' => 1,	// Numbering models of the sites (core/modules/nssuiviprod)
			'css' => array('/nssuiviprod/css/nssuiviprod.css.php'),
			/* BEGIN MODULEBUILDER HOOKSCONTEXTS */
			'hooks' => array(
				'data' => array('rowinterface'),	// Drag and drop of the URLs of a site (permission in core/ajax/row.php)
				'entity' => '0',
			),
			/* END MODULEBUILDER HOOKSCONTEXTS */
		);
		$this->dirs = array("/nssuiviprod/temp");
		$this->config_page_url = array("setup.php@nssuiviprod");
		$this->hidden = false;
		$this->depends = array('modNsinfo');	// NSINFO framework (checked in init() : activation refused if not enabled)
		$this->requiredby = array();
		$this->conflictwith = array();
		$this->langfiles = array("nssuiviprod@nssuiviprod");

		// Prerequisites
		$this->phpmin = array(7, 4); // Minimum version of PHP required by module
		$this->need_dolibarr_version = array(20, 0); // Minimum version of Dolibarr required by module (functions of Dolibarr 23: backport of the NSINFO framework)
		$this->need_javascript_ajax = 0;

		// Messages at activation
		$this->warnings_activation = array(); // Warning to show when we activate module. array('always'='text') or array('FR'='textfr','MX'='textmx'...)
		$this->warnings_activation_ext = array(); // Warning to show when we activate an external module. array('always'='text') or array('FR'='textfr','MX'='textmx'...)

		// Constants created at the first activation only (an existing value is kept): name, type, value, note, visible, entity, delete on deactivation
		$this->const = array(
			0 => array('NSSUIVIPROD_SITE_ADDON', 'chaine', 'mod_site_standard', 'Numbering model of the sites', 0, 'current', 0),
			1 => array('NSSUIVIPROD_DOLISTORE_AUTO', 'chaine', '1', 'Check of the Dolistore versions after the import of the JSON file of the external modules', 0, 'current', 0),
		);

		if (!isModEnabled("nssuiviprod")) {
			$conf->nssuiviprod = new stdClass();
			$conf->nssuiviprod->enabled = 0;
		}

		// Keep the MODULEBUILDER tags : ModuleBuilder uses them to add objects, permissions, menus...
		/* BEGIN MODULEBUILDER TABS */
		$this->tabs = array();
		// Tab on the thirdparty card: sites of the thirdparty (with the number of sites)
		$this->tabs[] = array('data' => "thirdparty:+nssuiviprod:NssuiviprodTabThirdparty,Site,/nssuiviprod/class/site.class.php,countForThirdparty:nssuiviprod@nssuiviprod:isModEnabled('nssuiviprod') && \$user->hasRight('nssuiviprod', 'site', 'read'):/nssuiviprod/site_thirdparty.php?socid=__ID__");
		/* END MODULEBUILDER TABS */

		/* BEGIN MODULEBUILDER DICTIONARIES */
		$this->dictionaries = array(
			'langs' => 'nssuiviprod@nssuiviprod',
			'tabname' => array('c_nssuiviprod_typesite','c_nssuiviprod_typeurl'),
			'tablib' => array('DictionarySiteType','DictionaryUrlType'),
			'tabsql' => array('SELECT t.rowid as rowid, t.code, t.label, t.position, t.active FROM '.$this->db->prefix().'c_nssuiviprod_typesite as t WHERE t.entity IN ('.getEntity('c_nssuiviprod_typesite').')','SELECT t.rowid as rowid, t.code, t.label, t.position, t.active FROM '.$this->db->prefix().'c_nssuiviprod_typeurl as t WHERE t.entity IN ('.getEntity('c_nssuiviprod_typeurl').')'),
			'tabsqlsort' => array('position ASC, label ASC','position ASC, label ASC'),
			'tabfield' => array('code,label,position','code,label,position'),
			'tabfieldvalue' => array('code,label,position','code,label,position'),
			'tabfieldinsert' => array('code,label,position,entity','code,label,position,entity'),
			'tabrowid' => array('rowid','rowid'),
			'tabcond' => array(isModEnabled('nssuiviprod'), isModEnabled('nssuiviprod')),
			'tabhelp' => array(array('code' => $langs->trans('CodeTooltipHelp'), 'field2' => 'field2tooltip'),array('code' => $langs->trans('CodeTooltipHelp'), 'field2' => 'field2tooltip')),
		);
		/* END MODULEBUILDER DICTIONARIES */

		/* BEGIN MODULEBUILDER WIDGETS */
		$this->boxes = array();
		/* END MODULEBUILDER WIDGETS */

		/* BEGIN MODULEBUILDER CRON */
		$this->cronjobs = array();
		/* END MODULEBUILDER CRON */

		// Permissions provided by this module
		$this->rights = array();
		$r = 0;
		/* BEGIN MODULEBUILDER PERMISSIONS */
		$this->rights[$r][0] = $this->numero . sprintf('%02d', (0 * 10) + 0 + 1);
		$this->rights[$r][1] = 'Read sites';
		$this->rights[$r][4] = 'site';
		$this->rights[$r][5] = 'read';
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf('%02d', (0 * 10) + 1 + 1);
		$this->rights[$r][1] = 'Create/Update sites, their URLs and plugins';
		$this->rights[$r][4] = 'site';
		$this->rights[$r][5] = 'write';
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf('%02d', (0 * 10) + 2 + 1);
		$this->rights[$r][1] = 'Delete sites';
		$this->rights[$r][4] = 'site';
		$this->rights[$r][5] = 'delete';
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf('%02d', (2 * 10) + 0 + 1);
		$this->rights[$r][1] = 'Access to the setup of the module';
		$this->rights[$r][4] = 'setup';
		$this->rights[$r][5] = 'admin';
		$r++;

		/* END MODULEBUILDER PERMISSIONS */

		// Menus provided by this module
		$this->menu = array();
		$r = 0;
		/* BEGIN MODULEBUILDER TOPMENU */
		$this->menu[$r++] = array(
			'fk_menu' => '',
			'type' => 'top',
			'titre' => 'ModuleNssuiviprodName',
			'prefix' => img_picto('', 'object_nssuiviprod@nssuiviprod', 'class="pictofixedwidth valignmiddle marginrightonlyshort"'),
			'mainmenu' => 'nssuiviprod',
			'leftmenu' => '',
			'url' => '/nssuiviprod/nssuiviprodindex.php',
			'langs' => 'nssuiviprod@nssuiviprod',
			'position' => 1000 + $r,
			'enabled' => 'isModEnabled("nssuiviprod")',
			'perms' => '$user->hasRight("nssuiviprod", "site", "read")',
			'target' => '',
			'user' => 0,	// Internal users only
		);
		/* END MODULEBUILDER TOPMENU */

		/* BEGIN MODULEBUILDER LEFTMENU SITE */
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=nssuiviprod',
			'type' => 'left',
			'titre' => 'Sites',
			'prefix' => img_picto('', 'fa-globe', 'class="pictofixedwidth valignmiddle marginrightonlyshort" style="color: #3879B2"'),
			'mainmenu' => 'nssuiviprod',
			'leftmenu' => 'site',
			'url' => '/nssuiviprod/site_list.php',
			'langs' => 'nssuiviprod@nssuiviprod',
			'position' => 1000 + $r,
			'enabled' => 'isModEnabled("nssuiviprod")',
			'perms' => '$user->hasRight("nssuiviprod", "site", "read")',
			'target' => '',
			'user' => 0,	// Internal users only
			'object' => 'Site'
		);
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=nssuiviprod,fk_leftmenu=site',
			'type' => 'left',
			'titre' => 'NewSite',
			'prefix' => img_picto('', 'fa-plus-circle', 'class="pictofixedwidth valignmiddle marginrightonlyshort" style="color: #3879B2"'),
			'mainmenu' => 'nssuiviprod',
			'leftmenu' => 'nssuiviprod_site_new',
			'url' => '/nssuiviprod/site_card.php?action=create',
			'langs' => 'nssuiviprod@nssuiviprod',
			'position' => 1000 + $r,
			'enabled' => 'isModEnabled("nssuiviprod")',
			'perms' => '$user->hasRight("nssuiviprod", "site", "write")',
			'target' => '',
			'user' => 0,	// Internal users only
			'object' => 'Site'
		);
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=nssuiviprod,fk_leftmenu=site',
			'type' => 'left',
			'titre' => 'ListSite',
			'prefix' => img_picto('', 'fa-list', 'class="pictofixedwidth valignmiddle marginrightonlyshort" style="color: #3879B2"'),
			'mainmenu' => 'nssuiviprod',
			'leftmenu' => 'nssuiviprod_site_list',
			'url' => '/nssuiviprod/site_list.php',
			'langs' => 'nssuiviprod@nssuiviprod',
			'position' => 1000 + $r,
			'enabled' => 'isModEnabled("nssuiviprod")',
			'perms' => '$user->hasRight("nssuiviprod", "site", "read")',
			'target' => '',
			'user' => 0,	// Internal users only
			'object' => 'Site'
		);
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=nssuiviprod,fk_leftmenu=site',
			'type' => 'left',
			'titre' => 'SitesToUpdate',
			'prefix' => img_picto('', 'fa-exclamation-triangle', 'class="pictofixedwidth valignmiddle marginrightonlyshort" style="color: #3879B2"'),
			'mainmenu' => 'nssuiviprod',
			'leftmenu' => 'nssuiviprod_site_toupdate',
			'url' => '/nssuiviprod/siteurl_list.php?search_situation=toupdate',
			'langs' => 'nssuiviprod@nssuiviprod',
			'position' => 1000 + $r,
			'enabled' => 'isModEnabled("nssuiviprod")',
			'perms' => '$user->hasRight("nssuiviprod", "site", "read")',
			'target' => '',
			'user' => 0,	// Internal users only
			'object' => 'Site'
		);
		/* END MODULEBUILDER LEFTMENU SITE */
		/* BEGIN MODULEBUILDER LEFTMENU SITEURL */
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=nssuiviprod',
			'type' => 'left',
			'titre' => 'SiteUrls',
			'prefix' => img_picto('', 'fa-link', 'class="pictofixedwidth valignmiddle marginrightonlyshort" style="color: #22A7D8"'),
			'mainmenu' => 'nssuiviprod',
			'leftmenu' => 'siteurl',
			'url' => '/nssuiviprod/siteurl_list.php',
			'langs' => 'nssuiviprod@nssuiviprod',
			'position' => 1000 + $r,
			'enabled' => 'isModEnabled("nssuiviprod")',
			'perms' => '$user->hasRight("nssuiviprod", "site", "read")',
			'target' => '',
			'user' => 0,	// Internal users only
			'object' => 'SiteUrl'
		);
		/* END MODULEBUILDER LEFTMENU SITEURL */
		/* BEGIN MODULEBUILDER LEFTMENU SITEPLUGIN */
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=nssuiviprod',
			'type' => 'left',
			'titre' => 'SitePlugins',
			'prefix' => img_picto('', 'fa-puzzle-piece', 'class="pictofixedwidth valignmiddle marginrightonlyshort" style="color: #5A2D82"'),
			'mainmenu' => 'nssuiviprod',
			'leftmenu' => 'siteplugin',
			'url' => '/nssuiviprod/siteplugin_list.php',
			'langs' => 'nssuiviprod@nssuiviprod',
			'position' => 1000 + $r,
			'enabled' => 'isModEnabled("nssuiviprod")',
			'perms' => '$user->hasRight("nssuiviprod", "site", "read")',
			'target' => '',
			'user' => 0,	// Internal users only
			'object' => 'SitePlugin'
		);
		/* END MODULEBUILDER LEFTMENU SITEPLUGIN */
		/* BEGIN MODULEBUILDER LEFTMENU SETUP */
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=nssuiviprod',
			'type' => 'left',
			'titre' => 'NssuiviprodSetupMenu',
			'prefix' => img_picto('', 'fa-cogs', 'class="pictofixedwidth valignmiddle marginrightonlyshort" style="color: #5A2D82"'),
			'mainmenu' => 'nssuiviprod',
			'leftmenu' => 'nssuiviprod_setup',
			'url' => '/nssuiviprod/admin/setup.php',
			'langs' => 'nssuiviprod@nssuiviprod',
			'position' => 1000 + $r,
			'enabled' => 'isModEnabled("nssuiviprod")',
			'perms' => '$user->hasRight("nssuiviprod", "setup", "admin")',	// Menu hidden without the right
			'target' => '',
			'user' => 0,	// Internal users only
		);
		/* END MODULEBUILDER LEFTMENU SETUP */
		/* BEGIN MODULEBUILDER LEFTMENU MYOBJECT */
		/* END MODULEBUILDER LEFTMENU MYOBJECT */

		// Last entry: "Minimize the menu" (left menu reduced to its icons, choice kept in the browser), NSINFO framework:
		// position 0 = computed by nsinfo (after the other entries, no collision with the entry of another module)
		dol_include_once('/nsinfo/lib/nsinfo.lib.php');
		if (function_exists('nsinfoMenuMinimize')) {
			$this->menu[$r++] = nsinfoMenuMinimize('nssuiviprod', 0, 'isModEnabled("nssuiviprod")');
		}

		// Exports profiles provided by this module
		$r = 0;
		/* BEGIN MODULEBUILDER EXPORT MYOBJECT */
		/* END MODULEBUILDER EXPORT MYOBJECT */

		// Imports profiles provided by this module
		$r = 0;
		/* BEGIN MODULEBUILDER IMPORT MYOBJECT */
		/* END MODULEBUILDER IMPORT MYOBJECT */
	}

	/**
	 *  Function called when module is enabled.
	 *  The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *  It also creates data directories
	 *
	 * @param string $options Options when enabling module ('', 'noboxes')
	 * @return     int                1 if OK, 0 if KO
	 */
	public function init($options = '')
	{
		global $langs;

		// NSINFO framework (module nsinfo) must be installed and enabled, in version 23.8.0 at least (backport of the
		// functions of Dolibarr 23 used by the pages generated by ModuleBuilder)
		$nsinfoversion = (string) @file_get_contents(dol_buildpath('/nsinfo/VERSION'));
		if (!isModEnabled('nsinfo') || version_compare(trim($nsinfoversion), '23.8.0', '<')) {
			$langs->load('nssuiviprod@nssuiviprod');
			$this->error = $langs->trans('NssuiviprodErrorNsinfoRequired');
			dol_syslog(get_class($this).'::init '.$this->error, LOG_ERR);
			return 0;
		}

		$result = $this->_load_tables('/nssuiviprod/sql/');
		if ($result < 0) {
			return -1; // Do not activate module if error 'not allowed' returned when loading module SQL queries (the _load_table run sql with run_sql with the error allowed parameter set to 'default')
		}

		// Permissions
		$this->remove($options);

		$sql = array();

		return $this->_init($sql, $options);
	}

	/**
	 *  Function called when module is disabled.
	 *  Remove from database constants, boxes and permissions from Dolibarr database.
	 *  Data directories are not deleted
	 *
	 * @param string $options Options when enabling module ('', 'noboxes')
	 * @return     int                 1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		$sql = array();
		return $this->_remove($sql, $options);
	}
}
