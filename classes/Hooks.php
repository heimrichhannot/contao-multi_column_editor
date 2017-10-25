<?php

namespace HeimrichHannot\MultiColumnEditor;

class Hooks extends \Controller
{
    public function executePostActionsHook($strAction, \DataContainer $objDc)
    {
        if ($strAction == MultiColumnEditor::ACTION_ADD_ROW || $strAction == MultiColumnEditor::ACTION_DELETE_ROW ||
            $strAction == MultiColumnEditor::ACTION_SORT_ROWS
        ) {

            $objDc->field = \Input::post('field');
            $objDc->table = \Input::post('table');

            if (!$objDc->field || !$objDc->table) {
                header('HTTP/1.1 400 Bad Request');
                die('Bad Request');
            }

            die(MultiColumnEditor::generateEditorForm('multi_column_editor', $objDc->table, $objDc->field, $objDc->value, $objDc));
        }
    }

    public function loadDataContainerHook($strTable)
    {
        // support for jumpTo fields -> bypass check in \Contao\Ajax -> comment "The field does not exist" line 282
        if ((\Input::post('action') != 'reloadPagetree' && \Input::post('action') != 'reloadFiletree') || $strTable === 'fieldpalette') {
            return;
        }

        $GLOBALS['TL_DCA'][$strTable]['fields'][\Input::post('name')] = true;
    }
}