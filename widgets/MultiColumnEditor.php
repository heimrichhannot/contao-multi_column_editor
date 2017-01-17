<?php

namespace HeimrichHannot\MultiColumnEditor;


use HeimrichHannot\Ajax\Ajax;
use HeimrichHannot\Ajax\AjaxAction;
use HeimrichHannot\FormHybrid\FormAjax;
use HeimrichHannot\Haste\Util\Widget;

class MultiColumnEditor extends \Widget
{

    protected $blnSubmitInput    = true;
    protected $blnForAttribute   = true;
    protected $strTemplate       = 'be_multi_column_editor';
    protected $strEditorTemplate = 'multi_column_editor';
    protected $arrDca;
    protected $arrWidgetErrors   = [];

    const ACTION_ADD_ROW    = 'addRow';
    const ACTION_DELETE_ROW = 'deleteRow';

    const NAME = 'multicolumneditor';

    public function __construct($arrData)
    {
        \Controller::loadDataContainer($arrData['strTable']);
        $this->arrDca = $GLOBALS['TL_DCA'][$arrData['strTable']]['fields'][$arrData['strField']]['eval']['multiColumnEditor'];

        parent::__construct($arrData);

        if (TL_MODE == 'FE')
        {
            Ajax::runActiveAction(static::NAME, static::ACTION_ADD_ROW, new MceAjax($this->objDca));
            Ajax::runActiveAction(static::NAME, static::ACTION_DELETE_ROW, new MceAjax($this->objDca));
        }
    }


    protected function validator($varInput)
    {
        // validate every field
        $varInput     = [];
        $intRowCount  = \Input::post('rowCount');
        $blnHasErrors = false;

        for ($i = 1; $i <= $intRowCount; $i++)
        {
            foreach ($this->arrDca['fields'] as $strField => $arrData)
            {
                $strMethod = TL_MODE == 'FE' ? 'getFrontendFormField' : 'getBackendFormField';

                if (!($objWidget = Widget::$strMethod($strField . '_' . $i, $arrData, null, $strField, $this->strTable, $this->objDca)))
                {
                    continue;
                }

                $objWidget->validate();
                $varValue = $objWidget->value;

                // Convert date formats into timestamps (check the eval setting first -> #3063)
                $rgxp = $arrData['eval']['rgxp'];
                if (($rgxp == 'date' || $rgxp == 'time' || $rgxp == 'datim') && $varValue != '')
                {
                    $objDate  = new \Date($varValue, $GLOBALS['TL_CONFIG'][$rgxp . 'Format']);
                    $varValue = $objDate->tstamp;
                }

                // Save callback
                if (is_array($arrData['save_callback']))
                {
                    foreach ($arrData['save_callback'] as $callback)
                    {
                        $this->import($callback[0]);

                        try
                        {
                            $varValue = $this->{$callback[0]}->{$callback[1]}($varValue, $this->objDca);
                        } catch (\Exception $e)
                        {
                            $objWidget->class = 'error';
                            $objWidget->addError($e->getMessage());
                        }
                    }
                }

                $varInput[$i - 1][$strField] = $varValue;

                // Do not submit if there are errors
                if ($objWidget->hasErrors())
                {
                    // store the errors
                    $this->arrWidgetErrors[$strField . '_' . $i] = $objWidget->getErrors();
                    $blnHasErrors                                = true;
                }
            }
        }

        if ($blnHasErrors)
        {
            $this->addError($GLOBALS['TL_LANG']['MSC']['multiColumnEditor']['error']);
            $this->blnSubmitInput = false;
        }

        return parent::validator(serialize($varInput));
    }


    /**
     * Generate the widget and return it as string
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE')
        {
            $strTable     = $this->objDca->table;
            $strFieldName = $this->objDca->field;
            $varValue     = $this->objDca->value;
        }
        else
        {
            $strTable     = $this->strTable;
            $strFieldName = $this->strName;
            $varValue     = $this->varValue;
        }

        return '<div class="multi-column-editor-wrapper">' . static::generateEditorForm(
            $this->strEditorTemplate,
            $strTable,
            $strFieldName,
            $varValue,
            $this->objDca,
            $this->arrDca,
            $this->arrWidgetErrors
        ) . '</div>';
    }

    public static function generateEditorForm($strEditorTemplate, $strTable, $strFieldName, $varValue, $objDc, $arrDca = null, $arrErrors = [], $strAction = null)
    {
        if ($arrDca === null)
        {
            $arrDca = $GLOBALS['TL_DCA'][$strTable]['fields'][$strFieldName]['eval']['multiColumnEditor'];
        }

        $objTemplate              = new \BackendTemplate($strEditorTemplate);
        $objTemplate->fieldName   = $strFieldName;
        $objTemplate->table       = $strTable;
        $objTemplate->class       = $arrDca['class'];
        $intMinRowCount           = isset($arrDca['minRowCount']) ? $arrDca['minRowCount'] : 1;
        $intMaxRowCount           = isset($arrDca['maxRowCount']) ? $arrDca['maxRowCount'] : 0;
        $objTemplate->minRowCount = $intMinRowCount;
        $objTemplate->maxRowCount = $intMaxRowCount;

        // actions
        $objTemplate->ajaxAddUrl    = TL_MODE == 'BE' ? \Environment::get('request') : AjaxAction::generateUrl(static::NAME, static::ACTION_ADD_ROW);
        $objTemplate->ajaxDeleteUrl =
            TL_MODE == 'BE' ? \Environment::get('request') : AjaxAction::generateUrl(static::NAME, static::ACTION_DELETE_ROW);

        $intRowCount = \Input::post('rowCount') ?: $intMinRowCount;
        $strAction   = $strAction ?: \Input::post('action');

        // restore from entity
        if ($varValue)
        {
            $arrValues = deserialize($varValue, true);
        }
        else
        {
            $arrValues = [];
        }

        // handle ajax requests
        if (TL_MODE == 'BE' && \Environment::get('isAjaxRequest'))
        {
            switch ($strAction)
            {
                case static::ACTION_ADD_ROW:
                    $arrValues = static::addRow($arrValues, $arrDca, $intRowCount, $intMaxRowCount);
                    break;

                case static::ACTION_DELETE_ROW:
                    $arrValues = static::deleteRow($arrValues, $arrDca, $intRowCount, $intMinRowCount);
                    break;
            }
        }
        elseif (Ajax::isRelated(static::NAME))
        {
            switch ($strAction)
            {
                case static::ACTION_ADD_ROW:
                    $arrValues = static::addRow($arrValues, $arrDca, $intRowCount, $intMaxRowCount);
                    break;

                case static::ACTION_DELETE_ROW:
                    $arrValues = static::deleteRow($arrValues, $arrDca, $intRowCount, $intMinRowCount);
                    break;
            }
        }

        // add row count field
        $objWidget = Widget::getFrontendFormField(
            'rowCount',
            [
                'inputType' => 'hidden',
            ],
            count($arrValues) ?: $intMinRowCount
        );

        $objTemplate->rowCount = $objWidget;

        // add rows
        $objTemplate->editorFormAction = \Environment::get('request');
        $objTemplate->rows             = static::generateRows($intRowCount, $arrDca, $strTable, $objDc, $arrValues, $arrErrors);

        return $objTemplate->parse();
    }

    public static function addRow($arrValues, $arrDca, $intRowCount, $intMaxRowCount)
    {
        if (!($intIndex = \Input::post('row')))
        {
            $arrRow = [];

            foreach (array_keys($arrDca['fields']) as $strField)
            {
                $arrRow[$strField] = null;
            }

            $arrValues[] = $arrRow;

            return $arrValues;
        }

        $arrValues = [];

        for ($i = 1; $i <= $intRowCount; $i++)
        {
            $arrRow = [];

            foreach (array_keys($arrDca['fields']) as $strField)
            {
                $arrRow[$strField] = \Input::post($strField . '_' . $i);
            }

            $arrValues[] = $arrRow;

            if ($i == $intIndex && ($intMaxRowCount == 0 || ($intRowCount + 1 <= $intMaxRowCount)))
            {
                $arrValues[] = $arrRow;
            }
        }

        return $arrValues;
    }

    public static function deleteRow($arrValues, $arrDca, $intRowCount, $intMinRowCount)
    {
        if (!($intIndex = \Input::post('row')))
        {
            return $arrValues;
        }

        $arrValues = [];

        for ($i = 1; $i <= $intRowCount; $i++)
        {
            if ($i == $intIndex && $intRowCount - 1 >= $intMinRowCount)
            {
                continue;
            }

            $arrRow = [];

            foreach (array_keys($arrDca['fields']) as $strField)
            {
                $arrRow[$strField] = \Input::post($strField . '_' . $i);
            }

            $arrValues[] = $arrRow;
        }

        return $arrValues;
    }

    public static function generateRows($intRowCount, $arrDca, $strTable, $objDc, array $arrValues = [], $arrErrors = [])
    {
        $arrRows = [];

        for ($i = 1; $i <= (empty($arrValues) ? $intRowCount : count($arrValues)); $i++)
        {
            $arrFields = [];

            foreach ($arrDca['fields'] as $strField => $arrData)
            {
                $strMethod = TL_MODE == 'FE' ? 'getFrontendFormField' : 'getBackendFormField';

                if (!($objWidget = Widget::$strMethod($strField . '_' . $i, $arrData, null, $strField, $strTable, $objDc)))
                {
                    continue;
                }

                $objWidget->noIndex = $strField;

                if (!empty($arrValues))
                {
                    $objWidget->value = $arrValues[$i - 1][$strField];
                }

                if (isset($arrErrors[$strField . '_' . $i]))
                {
                    $objWidget->addError(implode('', $arrErrors[$strField . '_' . $i]));
                }

                $arrFields[$strField . '_' . $i] = $objWidget;
            }

            $arrRows[] = $arrFields;
        }

        return $arrRows;
    }
}
