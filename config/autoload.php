<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(
    [
	'HeimrichHannot',]
);


/**
 * Register the classes
 */
ClassLoader::addClasses(
    [
	// Classes
	'HeimrichHannot\MultiColumnEditor\MceAjax'               => 'system/modules/multi_column_editor/classes/MceAjax.php',
	'HeimrichHannot\MultiColumnEditor\Hooks'                 => 'system/modules/multi_column_editor/classes/Hooks.php',

	// Widgets
	'HeimrichHannot\MultiColumnEditor\MultiColumnEditor'     => 'system/modules/multi_column_editor/widgets/MultiColumnEditor.php',
	'HeimrichHannot\MultiColumnEditor\FormMultiColumnEditor' => 'system/modules/multi_column_editor/widgets/FormMultiColumnEditor.php',]
);


/**
 * Register the templates
 */
TemplateLoader::addFiles(
    [
	'be_multi_column_editor' => 'system/modules/multi_column_editor/templates',
	'multi_column_editor'    => 'system/modules/multi_column_editor/templates',]
);
