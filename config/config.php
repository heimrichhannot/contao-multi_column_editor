<?php

/**
 * Backend form fields
 */
$GLOBALS['BE_FFL']['multiColumnEditor'] = 'HeimrichHannot\MultiColumnEditor\MultiColumnEditor';

/**
 * Frontend form fields
 */
$GLOBALS['TL_FFL']['multiColumnEditor'] = 'HeimrichHannot\MultiColumnEditor\FormMultiColumnEditor';

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['executePostActions']['multiColumnEditor'] = ['HeimrichHannot\MultiColumnEditor\Hooks', 'executePostActionsHook'];
$GLOBALS['TL_HOOKS']['loadDataContainer']['multiColumnEditor']  = ['HeimrichHannot\MultiColumnEditor\Hooks', 'loadDataContainerHook'];

/**
 * CSS
 */
if (\HeimrichHannot\Haste\Util\Container::isBackend()) {
    $GLOBALS['TL_CSS']['multi_column_editor'] = 'system/modules/multi_column_editor/assets/css/multi_column_editor.css';
}

/**
 * JS
 */
if (\HeimrichHannot\Haste\Util\Container::isBackend()) {
    $GLOBALS['TL_JAVASCRIPT']['multi_column_editor'] = 'system/modules/multi_column_editor/assets/js/jquery.multi_column_editor.be.min.js|static';
}
else
{
    $GLOBALS['TL_JAVASCRIPT']['multi_column_editor'] = 'system/modules/multi_column_editor/assets/js/jquery.multi_column_editor.fe.min.js|static';
}

/**
 * Ajax
 */
$GLOBALS['AJAX'][\HeimrichHannot\MultiColumnEditor\MultiColumnEditor::NAME] = [
    'actions' => [
        \HeimrichHannot\MultiColumnEditor\MultiColumnEditor::ACTION_ADD_ROW    => [
            'arguments' => ['.*_rowCount', '.*_row', 'field', 'table'],
            'optional'  => [],
        ],
        \HeimrichHannot\MultiColumnEditor\MultiColumnEditor::ACTION_DELETE_ROW => [
            'arguments' => ['.*_rowCount', '.*_row', 'field', 'table'],
            'optional'  => [],
        ],
    ],
];