<?php

namespace HeimrichHannot\MultiColumnEditor;


use HeimrichHannot\Haste\Util\Widget;

class MultiColumnEditor extends \Widget
{

    protected $blnSubmitInput    = true;
    protected $blnForAttribute   = true;
    protected $strTemplate       = 'be_widget';
    protected $strEditorTemplate = 'multicolumneditor_editor';
    protected $arrDca;
    protected $arrWidgetErrors   = array();

    const ACTION_ADD_ROW    = 'addRow';
    const ACTION_DELETE_ROW = 'deleteRow';

    public function __construct($arrData)
    {
        \Controller::loadDataContainer($arrData['strTable']);
        $this->arrDca = $GLOBALS['TL_DCA'][$arrData['strTable']]['fields'][$arrData['strField']]['eval']['multiColumnEditor'];

        parent::__construct($arrData);
    }


    protected function validator($varInput)
    {
        // validate every field
        $varInput     = array();
        $intRowCount  = \Input::post('rowCount') ?: 1;
        $blnHasErrors = false;

        for ($i = 1; $i <= $intRowCount; $i++)
        {
            foreach ($this->arrDca['fields'] as $strField => $arrData)
            {
                if (!($objWidget = Widget::getBackendFormField($strField . '_' . $i, $arrData, null, $strField, $this->strTable, $this->objDca)))
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
                            $varValue = $this->{$callback[0]}->{$callback[1]}($varValue, $this);
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
        return static::generateEditorForm($this->strEditorTemplate, $this->objDca, $this->arrDca, $this->arrWidgetErrors);
    }

    public static function generateEditorForm($strEditorTemplate, $objDc, $arrDca = null, $arrErrors = array())
    {
        $objTemplate            = new \BackendTemplate($strEditorTemplate);
        $objTemplate->fieldName = $objDc->field;
        $objTemplate->class     = $arrDca['class'];

        $intRowCount = \Input::post('rowCount') ?: 1;
        $strAction   = \Input::post('action');

        if ($arrDca === null)
        {
            $arrDca = $GLOBALS['TL_DCA'][$objDc->table]['fields'][$objDc->field]['eval']['multiColumnEditor'];
        }

        // restore from entity
        if ($objDc->activeRecord->{$objDc->field})
        {
            $arrValues = deserialize($objDc->activeRecord->{$objDc->field}, true);
        }
        else
        {
            $arrValues = array();
        }

        // handle ajax requests
        if (\Environment::get('isAjaxRequest') && ($intIndex = \Input::post('row')))
        {
            switch ($strAction)
            {
                case MultiColumnEditor::ACTION_ADD_ROW:
                    $arrValues = array();

                    for ($i = 1; $i <= $intRowCount; $i++)
                    {
                        $arrRow = array();

                        foreach (array_keys($arrDca['fields']) as $strField)
                        {
                            $arrRow[$strField] = \Input::post($strField . '_' . $i);
                        }

                        $arrValues[] = $arrRow;

                        if ($i == $intIndex)
                        {
                            $arrValues[] = $arrRow;
                        }
                    }

                    break;

                case MultiColumnEditor::ACTION_DELETE_ROW:
                    $arrValues = array();

                    for ($i = 1; $i <= $intRowCount; $i++)
                    {
                        if ($i == $intIndex)
                        {
                            continue;
                        }

                        $arrRow = array();

                        foreach (array_keys($arrDca['fields']) as $strField)
                        {
                            $arrRow[$strField] = \Input::post($strField . '_' . $i);
                        }

                        $arrValues[] = $arrRow;
                    }

                    break;
            }
        }

        // add row count field
        $objWidget = Widget::getFrontendFormField(
            'rowCount',
            array(
                'inputType' => 'hidden',
            ),
            count($arrValues)
        );

        $objTemplate->rowCount = $objWidget;

        // add rows
        $objTemplate->editorFormAction = \Environment::get('request');
        $objTemplate->rows             = static::generateRows($intRowCount, $arrDca, $objDc, $arrValues, $arrErrors);

        return $objTemplate->parse();
    }

    public static function generateRows($intRowCount, $arrDca, $objDc, array $arrValues = array(), $arrErrors = array())
    {
        $arrRows = array();

        for ($i = 1; $i <= (empty($arrValues) ? $intRowCount : count($arrValues)); $i++)
        {
            $arrFields = array();

            foreach ($arrDca['fields'] as $strField => $arrData)
            {
                if (!($objWidget = Widget::getBackendFormField($strField . '_' . $i, $arrData, null, $strField, $objDc->table, $objDc)))
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
