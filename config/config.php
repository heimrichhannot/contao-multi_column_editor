<?php

/**
 * Backend form fields
 */
$GLOBALS['BE_FFL']['multiColumnEditor'] = 'HeimrichHannot\MultiColumnEditor\MultiColumnEditor';

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['executePostActions']['multiColumnEditor'] = array('HeimrichHannot\MultiColumnEditor\Hooks', 'executePostActionsHook');

/**
 * CSS
 */
if (TL_MODE == 'BE')
{
    $GLOBALS['TL_CSS']['multi_column_editor'] = 'system/modules/multi_column_editor/assets/css/multi_column_editor.css';
}

/**
 * JS
 */
if (TL_MODE == 'BE')
{
    $GLOBALS['TL_JAVASCRIPT']['multi_column_editor'] = 'system/modules/multi_column_editor/assets/js/jquery.multi_column_editor.js';
}