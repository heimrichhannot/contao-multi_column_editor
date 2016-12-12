<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'HeimrichHannot',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Classes
	'HeimrichHannot\MultiColumnEditor\Hooks'             => 'system/modules/multi_column_editor/classes/Hooks.php',

	// Widgets
	'HeimrichHannot\MultiColumnEditor\MultiColumnEditor' => 'system/modules/multi_column_editor/widgets/MultiColumnEditor.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'multi_column_editor' => 'system/modules/multi_column_editor/templates',
));
