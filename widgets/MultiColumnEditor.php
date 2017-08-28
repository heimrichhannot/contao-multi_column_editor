<?php

namespace HeimrichHannot\MultiColumnEditor;


use HeimrichHannot\Ajax\Ajax;
use HeimrichHannot\Ajax\AjaxAction;
use HeimrichHannot\FormHybrid\FormAjax;
use HeimrichHannot\Haste\Util\Widget;

class MultiColumnEditor extends \Widget
{

    protected $blnSubmitInput = true;
    protected $blnForAttribute = true;
    protected $strTemplate = 'be_multi_column_editor';
    protected $strEditorTemplate = 'multi_column_editor';
    protected $arrDca;
    protected $arrWidgetErrors = [];

    const ACTION_ADD_ROW    = 'addRow';
    const ACTION_DELETE_ROW = 'deleteRow';
    const ACTION_SORT_ROWS  = 'sortRows';

    const NAME = 'multicolumneditor';

    public function __construct($arrData)
    {
        \Controller::loadDataContainer($arrData['strTable']);
        $this->arrDca = $GLOBALS['TL_DCA'][$arrData['strTable']]['fields'][$arrData['strField']]['eval']['multiColumnEditor'];

        parent::__construct($arrData);

        if (TL_MODE == 'FE') {
            Ajax::runActiveAction(static::NAME, static::ACTION_ADD_ROW, new MceAjax($this->objDca));
            Ajax::runActiveAction(static::NAME, static::ACTION_DELETE_ROW, new MceAjax($this->objDca));
        }
    }


    protected function validator($varInput)
    {
        // validate every field
        $varInput     = [];
        $intRowCount  = \Input::post($this->strName . '_' . 'rowCount');
        $blnHasErrors = false;

        for ($i = 1; $i <= $intRowCount; $i++) {
            foreach ($this->arrDca['fields'] as $strField => $arrData) {
                $strMethod = TL_MODE == 'FE' ? 'getFrontendFormField' : 'getBackendFormField';

                if (!($objWidget =
                    Widget::$strMethod($this->strName . '_' . $strField . '_' . $i, $arrData, null, $strField, $this->strTable, $this->objDca))
                ) {
                    continue;
                }

                $objWidget->validate();
                $varValue = $objWidget->value;

                // Convert date formats into timestamps (check the eval setting first -> #3063)
                $rgxp = $arrData['eval']['rgxp'];
                if (($rgxp == 'date' || $rgxp == 'time' || $rgxp == 'datim') && $varValue != '') {
                    $objDate  = new \Date($varValue, $GLOBALS['TL_CONFIG'][$rgxp . 'Format']);
                    $varValue = $objDate->tstamp;
                }

                // Save callback
                if (is_array($arrData['save_callback'])) {
                    foreach ($arrData['save_callback'] as $callback) {
                        $this->import($callback[0]);

                        try {
                            $varValue = $this->{$callback[0]}->{$callback[1]}($varValue, $this->objDca);
                        } catch (\Exception $e) {
                            $objWidget->class = 'error';
                            $objWidget->addError($e->getMessage());
                        }
                    }
                }

                $varInput[$i - 1][$strField] = $varValue;

                // Do not submit if there are errors
                if ($objWidget->hasErrors()) {
                    // store the errors
                    $this->arrWidgetErrors[$this->strName . '_' . $strField . '_' . $i] = $objWidget->getErrors();
                    $blnHasErrors                                                       = true;
                }
            }
        }

        if ($blnHasErrors) {
            $this->addError($GLOBALS['TL_LANG']['MSC']['multiColumnEditor']['error']);
            $this->blnSubmitInput = false;
        }

        $varInput = static::unprefixValuesWithFieldName($varInput, $this->strName);

        return parent::validator(serialize($varInput));
    }


    /**
     * Generate the widget and return it as string
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            $strTable     = $this->objDca->table;
            $strFieldName = $this->objDca->field;
            $varValue     = $this->objDca->value;
        } else {
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

    public static function generateEditorForm(
        $strEditorTemplate,
        $strTable,
        $strFieldName,
        $varValue,
        $objDc,
        $arrDca = null,
        $arrErrors = [],
        $strAction = null
    ) {
        if ($arrDca === null) {
            $arrDca = $GLOBALS['TL_DCA'][$strTable]['fields'][$strFieldName]['eval']['multiColumnEditor'];
        }

        $objTemplate              = new \BackendTemplate($strEditorTemplate);
        $objTemplate->fieldName   = $strFieldName;
        $objTemplate->table       = $strTable;
        $objTemplate->class       = $arrDca['class'];
        $objTemplate->sortable    = $arrDca['sortable'];
        $intMinRowCount           = isset($arrDca['minRowCount']) ? $arrDca['minRowCount'] : 1;
        $intMaxRowCount           = isset($arrDca['maxRowCount']) ? $arrDca['maxRowCount'] : 0;
        $blnSkipCopyValuesOnAdd   = isset($arrDca['skipCopyValuesOnAdd']) ? $arrDca['skipCopyValuesOnAdd'] : false;
        $objTemplate->minRowCount = $intMinRowCount;
        $objTemplate->maxRowCount = $intMaxRowCount;

        // actions
        $objTemplate->ajaxAddUrl    = TL_MODE == 'BE' ? \Environment::get('request') : AjaxAction::generateUrl(static::NAME, static::ACTION_ADD_ROW);
        $objTemplate->ajaxDeleteUrl =
            TL_MODE == 'BE' ? \Environment::get('request') : AjaxAction::generateUrl(static::NAME, static::ACTION_DELETE_ROW);
        $objTemplate->ajaxSortUrl   =
            TL_MODE == 'BE' ? \Environment::get('request') : AjaxAction::generateUrl(static::NAME, static::ACTION_SORT_ROWS);

        $intRowCount = \Input::post($strFieldName . '_rowCount') ?: $intMinRowCount;
        $strAction   = $strAction ?: \Input::post('action');

        // restore from entity
        if ($varValue) {
            $arrValues = static::prefixValuesWithFieldName(deserialize($varValue, true), $strFieldName);
        } else {
            $arrValues = [];
        }

        // handle ajax requests
        if (TL_MODE == 'BE' && \Environment::get('isAjaxRequest')) {
            switch ($strAction) {
                case static::ACTION_ADD_ROW:
                    $arrValues = static::addRow($arrValues, $arrDca, $intRowCount, $intMaxRowCount, $strFieldName, $blnSkipCopyValuesOnAdd);
                    break;

                case static::ACTION_DELETE_ROW:
                    $arrValues = static::deleteRow($arrValues, $arrDca, $intRowCount, $intMinRowCount, $strFieldName);
                    break;

                case static::ACTION_SORT_ROWS:
                    $arrValues = static::sortRows($arrValues, $arrDca, $intRowCount, $intMinRowCount, $strFieldName);
                    break;
            }
        } elseif (Ajax::isRelated(static::NAME)) {
            switch ($strAction) {
                case static::ACTION_ADD_ROW:
                    $arrValues = static::addRow($arrValues, $arrDca, $intRowCount, $intMaxRowCount, $strFieldName, $blnSkipCopyValuesOnAdd);
                    break;

                case static::ACTION_DELETE_ROW:
                    $arrValues = static::deleteRow($arrValues, $arrDca, $intRowCount, $intMinRowCount, $strFieldName);
                    break;
            }
        }

        // add row count field
        $objWidget = Widget::getFrontendFormField(
            $strFieldName . '_rowCount',
            [
                'inputType' => 'hidden',
            ],
            count($arrValues) ?: ($intRowCount ?: $intMinRowCount)
        );

        $objTemplate->rowCount = $objWidget;

        // add rows
        $objTemplate->editorFormAction = \Environment::get('request');
        $objTemplate->rows             = static::generateRows($intRowCount, $arrDca, $strTable, $objDc, $arrValues, $arrErrors, $strFieldName);

        return $objTemplate->parse();
    }

    private static function prefixValuesWithFieldName(array $arrValues, $strFieldName)
    {
        $arrResult = [];

        foreach ($arrValues as $arrValue) {
            $arrRow = [];

            foreach ($arrValue as $strKey => $varValue) {
                $arrRow[$strFieldName . '_' . $strKey] = $varValue;
            }

            $arrResult[] = $arrRow;
        }

        return $arrResult;
    }

    private static function unprefixValuesWithFieldName(array $arrValues, $strFieldName)
    {
        $arrResult = [];

        foreach ($arrValues as $arrValue) {
            $arrRow = [];

            foreach ($arrValue as $strKey => $varValue) {
                $arrRow[str_replace($strFieldName, '', $strKey)] = $varValue;
            }

            $arrResult[] = $arrRow;
        }

        return $arrResult;
    }

    public static function addRow($arrValues, $arrDca, $intRowCount, $intMaxRowCount, $strFieldName, $blnSkipCopyValuesOnAdd = false)
    {
        if (!($intIndex = \Input::post('row'))) {
            $arrRow = [];

            foreach (array_keys($arrDca['fields']) as $strField) {
                $arrRow[$strFieldName . '_' . $strField] = null;
            }

            $arrValues[] = $arrRow;

            return $arrValues;
        }

        $arrValues = [];

        for ($i = 1; $i <= $intRowCount; $i++) {
            $arrRow = [];

            foreach (array_keys($arrDca['fields']) as $strField) {
                $arrRow[$strFieldName . '_' . $strField] = \Input::post($strFieldName . '_' . $strField . '_' . $i);
            }

            $arrValues[] = $arrRow;

            if ($i == $intIndex && ($intMaxRowCount == 0 || ($intRowCount + 1 <= $intMaxRowCount))) {
                if ($blnSkipCopyValuesOnAdd) {
                    foreach ($arrRow as $strField => &$varValue) {
                        $varValue = '';
                    }
                }

                $arrValues[] = $arrRow;
            }
        }

        return $arrValues;
    }

    public static function deleteRow($arrValues, $arrDca, $intRowCount, $intMinRowCount, $strFieldName)
    {
        if (!($intIndex = \Input::post('row'))) {
            return $arrValues;
        }

        $arrValues = [];

        for ($i = 1; $i <= $intRowCount; $i++) {
            if ($i == $intIndex && $intRowCount - 1 >= $intMinRowCount) {
                continue;
            }

            $arrRow = [];

            foreach (array_keys($arrDca['fields']) as $strField) {
                $arrRow[$strFieldName . '_' . $strField] = \Input::post($strFieldName . '_' . $strField . '_' . $i);
            }

            $arrValues[] = $arrRow;
        }

        return $arrValues;
    }

    public static function sortRows($arrValues, $arrDca, $intRowCount, $intMinRowCount, $strFieldName)
    {
        if (\Input::post('action') != static::ACTION_SORT_ROWS || !($varNewIndices = \Input::post('newIndices'))) {
            return $arrValues;
        }

        $arrNewIndices = explode(',', $varNewIndices);

        if (empty($arrNewIndices))
        {
            return $arrValues;
        }

        $arrValues = [];

        foreach ($arrNewIndices as $intIndex) {
            $arrRow = [];

            foreach (array_keys($arrDca['fields']) as $strField) {
                $arrRow[$strFieldName . '_' . $strField] = \Input::post($strFieldName . '_' . $strField . '_' . $intIndex);
            }

            $arrValues[] = $arrRow;
        }

        return $arrValues;
    }

    public static function generateRows($intRowCount, $arrDca, $strTable, $objDc, array $arrValues = [], $arrErrors = [], $strFieldName)
    {
        $arrRows = [];

        for ($i = 1; $i <= (empty($arrValues) ? $intRowCount : count($arrValues)); $i++) {
            $arrFields = [];

            foreach ($arrDca['fields'] as $strField => $arrData) {
                $strMethod = TL_MODE == 'FE' ? 'getFrontendFormField' : 'getBackendFormField';

                if (!($objWidget = Widget::$strMethod($strFieldName . '_' . $strField . '_' . $i, $arrData, null, $strField, $strTable, $objDc))) {
                    continue;
                }

                // add correct dca for bootstrapper since by normal behavior retrieval of the dca is impossible
                $objWidget->arrDca = $arrData;

                $objWidget->noIndex = $strField;

                if (!empty($arrValues)) {
                    $objWidget->value = $arrValues[$i - 1][$strFieldName . '_' . $strField];

                    if (is_numeric($objWidget->value))
                    {
                        // date/time fields
                        if ($arrData['eval']['rgxp'] == 'date') {
                            $objWidget->value = \Date::parse(\Config::get('dateFormat'), $objWidget->value);
                        } elseif ($arrData['eval']['rgxp'] == 'time') {
                            $objWidget->value = \Date::parse(\Config::get('timeFormat'), $objWidget->value);
                        } elseif ($arrData['eval']['rgxp'] == 'datim') {
                            $objWidget->value = \Date::parse(\Config::get('datimFormat'), $objWidget->value);
                        }
                    }
                }

                if (isset($arrErrors[$strFieldName . '_' . $strField . '_' . $i])) {
                    $objWidget->addError(implode('', $arrErrors[$strFieldName . '_' . $strField . '_' . $i]));
                }

                static::handleSpecialFields($objWidget, $arrData, $strFieldName, $strTable);

                $arrFields[$strFieldName . '_' . $strField . '_' . $i] = $objWidget;
            }

            $arrRows[] = $arrFields;
        }

        return $arrRows;
    }

    public static function handleSpecialFields($objWidget, $arrData, $strField, $strTable)
    {
        $wizard = '';

        if ($arrData['eval']['datepicker'])
        {
            $rgxp = $arrData['eval']['rgxp'];
            $format = \Date::formatToJs(\Config::get($rgxp.'Format'));

            switch ($rgxp)
            {
                case 'datim':
                    $time = ",\n        timePicker: true";
                    break;

                case 'time':
                    $time = ",\n        pickOnly: \"time\"";
                    break;

                default:
                    $time = '';
                    break;
            }

            $strOnSelect = '';

            // Trigger the auto-submit function (see #8603)
            if ($arrData['eval']['submitOnChange'])
            {
                $strOnSelect = ",\n        onSelect: function() { Backend.autoSubmit(\"" . $strTable . "\"); }";
            }

            $wizard .= ' ' . \Image::getHtml('assets/datepicker/images/icon.svg', '', 'title="'.\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['datepicker']).'" id="toggle_' . $objWidget->id . '" style="cursor:pointer"') . '
  <script>
    window.addEvent("domready", function() {
      new Picker.Date($("ctrl_' . $objWidget->id . '"), {
        draggable: false,
        toggle: $("toggle_' . $objWidget->id . '"),
        format: "' . $format . '",
        positionOffset: {x:-211,y:-209}' . $time . ',
        pickerClass: "datepicker_bootstrap",
        useFadeInOut: !Browser.ie' . $strOnSelect . ',
        startDay: ' . $GLOBALS['TL_LANG']['MSC']['weekOffset'] . ',
        titleFormat: "' . $GLOBALS['TL_LANG']['MSC']['titleFormat'] . '"
      });
    });
  </script>';
        }

        // Color picker
        if ($arrData['eval']['colorpicker'])
        {
            // Support single fields as well (see #5240)
            $strKey = $arrData['eval']['multiple'] ? $strField . '_0' : $strField;

            $wizard .= ' ' . \Image::getHtml('pickcolor.svg', $GLOBALS['TL_LANG']['MSC']['colorpicker'], 'title="'.\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['colorpicker']).'" id="moo_' . $strField . '" style="cursor:pointer"') . '
  <script>
    window.addEvent("domready", function() {
      var cl = $("ctrl_' . $strKey . '").value.hexToRgb(true) || [255, 0, 0];
      new MooRainbow("moo_' . $strField . '", {
        id: "ctrl_' . $strKey . '",
        startColor: cl,
        imgPath: "assets/colorpicker/images/",
        onComplete: function(color) {
          $("ctrl_' . $strKey . '").value = color.hex.replace("#", "");
        }
      });
    });
  </script>';
        }

        $strHelp = (!$objWidget->hasErrors() ? static::help($arrData) : '');

        $objWidget->wizard = $strHelp ? $wizard . $strHelp : $wizard;
    }

    public static function help($arrData, $strClass = '')
    {
        $return = $arrData['label'][1];

        if (!\Config::get('showHelp') || $arrData['inputType'] == 'password' || $return == '') {
            return '';
        }

        return '<p class="tl_help tl_tip' . $strClass . '">' . $return . '</p>';
    }
}
