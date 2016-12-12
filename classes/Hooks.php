<?php

namespace HeimrichHannot\MultiColumnEditor;


class Hooks extends \Controller
{

    public function executePostActionsHook($strAction, \DataContainer $objDc)
    {
        if ($strAction == MultiColumnEditor::ACTION_ADD_ROW || $strAction == MultiColumnEditor::ACTION_DELETE_ROW)
        {
            $objDc->field = \Input::post('field');

            if (!$objDc->field || !\Input::post('row'))
            {
                header('HTTP/1.1 400 Bad Request');
                die('Bad Request');
            }

            die(MultiColumnEditor::generateEditorForm('multi_column_editor', $objDc));
        }
    }
}