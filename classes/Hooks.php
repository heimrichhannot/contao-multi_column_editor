<?php

namespace HeimrichHannot\MultiColumnEditor;

use HeimrichHannot\Haste\Dca\General;
use HeimrichHannot\Request\Request;

class Hooks extends \Controller
{
    public function executePostActionsHook($strAction, \DataContainer $objDc)
    {
        if ($strAction == MultiColumnEditor::ACTION_ADD_ROW || $strAction == MultiColumnEditor::ACTION_DELETE_ROW ||
            $strAction == MultiColumnEditor::ACTION_SORT_ROWS
        ) {

            $objDc->field = Request::getPost('field');
            $objDc->table = Request::getPost('table');

            if (!$objDc->field || !$objDc->table) {
                header('HTTP/1.1 400 Bad Request');
                die('Bad Request');
            }

            $objDc->activeRecord = General::getModelInstance($objDc->table, $objDc->id);

            die(MultiColumnEditor::generateEditorForm('multi_column_editor', $objDc->table, $objDc->field, $objDc->value, $objDc));
        }
    }

    public function loadDataContainerHook($strTable)
    {
        if (!isset($GLOBALS['TL_DCA'][$strTable]))
        {
            return;
        }

        $dca = &$GLOBALS['TL_DCA'][$strTable];

        if (!($name = Request::getPost('name'))) {
            return;
        }

        if (isset($dca['fields'][$name])) {
            return;
        }

        // support for jumpTo fields -> bypass check in \Contao\Ajax -> comment "The field does not exist" line 282
        if ((Request::getPost('action') != 'reloadPagetree' && Request::getPost('action') != 'reloadFiletree') || $strTable === 'fieldpalette') {
            return;
        }

        if ($this->isMceField($name, $dca))
        {
            $dca['fields'][$name] = [];
        }
    }

    protected function isMceField($name, $dca)
    {
        $isMce = false;
        $cleanedName = preg_replace('/_\d+$/i', '', $name);
        $mceFieldArrays = [];

        foreach ($dca['fields'] as $field => $data)
        {
            if ($data['inputType'] !== 'multiColumnEditor' || !isset($data['eval']['multiColumnEditor']['fields']))
            {
                continue;
            }

            $mceFieldArrays[$field] = $data;
        }

        if (empty($mceFieldArrays))
        {
            return false;
        }

        foreach ($mceFieldArrays as $field => $mceData)
        {
            if (in_array(preg_replace('/^' . $field . '_/', '', $cleanedName), array_keys($mceData['eval']['multiColumnEditor']['fields'])))
            {
                $isMce = true;
            }
        }

        return $isMce;
    }
}
