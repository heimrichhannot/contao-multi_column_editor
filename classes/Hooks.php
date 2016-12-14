<?php

namespace HeimrichHannot\MultiColumnEditor;


use HeimrichHannot\Haste\Dca\General;

class Hooks extends \Controller
{

    public function executePostActionsHook($strAction, \DataContainer $objDc)
    {
        if ($strAction == MultiColumnEditor::ACTION_ADD_ROW || $strAction == MultiColumnEditor::ACTION_DELETE_ROW)
        {
            $objDc->field = \Input::post('field');

            if (!$objDc->field)
            {
                header('HTTP/1.1 400 Bad Request');
                die('Bad Request');
            }

//            if($objDc->activeRecord === null)
//            {
//                $objDc->activeRecord = General::getModelInstance($objDc->table, $objDc->id);
//            }

            die(MultiColumnEditor::generateEditorForm('multi_column_editor', $objDc));
        }
    }
}