# Changelog
All notable changes to this project will be documented in this file.

## [1.4.6] - 2018-09-18

### Fixed
- type issue in loadDataContainer hook (see https://github.com/heimrichhannot/contao-multi_column_editor/issues/3)

## [1.4.5] - 2018-09-17

### Fixed
- issue in loadDataContainer hook (see https://github.com/heimrichhannot/contao-multi_column_editor/issues/3)

## [1.4.4] - 2018-06-08

### Fixed
- System::getContainer bug

## [1.4.3] - 2018-06-01

### Fixed
- activeRecord issue with ajax calls

## [1.4.2] - 2018-03-26

### Fixed
- removed non required `MultiColumnEditor::unprefixValuesWithFieldName()`

## [1.4.1] - 2018-03-14

### Fixed
- contao 3 styles, prefixed contao 4 styles

## [1.4.0] - 2018-02-01

### Fixed
- picker widgets in fieldpalette containing a multi column editor widget
- composer.json

### Added
- partial support for rte textareas -> reload necessary at the moment in order to apply the js

## [1.3.1] - 2017-12-20

### Fixed
- missing value for widget in some setups (for example [multifileupload](https://github.com/heimrichhannot/contao-multifileupload))

## [1.3.0] - 2017-12-18

### Fixed
- post data issue

### Changed
- switched post data retrieval to heimrichhannot/contao-request for security reasons

## [1.2.11] - 2017-11-17

### Fixed
- ajax bug

## [1.2.10] - 2017-11-07

### Fixed
- js bug

## [1.2.9] - 2017-11-06

### Fixed
- fe and be javascript

## [1.2.8] - 2017-11-06

### Fixed
- min and max row count

## [1.2.7] - 2017-11-03

### Fixed
- min and max row count

## [1.2.6] - 2017-11-01

### Fixed
- limited ajax support for tree widgets to mce

## [1.2.5] - 2017-10-30

### Fixed
- min row count

## [1.2.4] - 2017-10-25

### Added
- support for the inputType "fileTree"

## [1.2.3] - 2017-08-30

### Fixed
- return `HTTP/1.1 400 Bad Request` only if action belongs to `Hooks::executePostActionsHook`

## [1.2.2] - 2017-08-30

### Added
- support for the inputType "jumpTo"

## [1.2.1] - 2017-08-28

### Fixed
- sorting

## [1.2.0] - 2017-08-28

### Added
- sortable flag in order to make the rows of the widget sortable (currently backend only, frontend to come)
- Contao 4 icons

## [1.1.13] - 2017-07-27

### Fixed
- heimrichhannot/contao-ajax dependency added

## [1.1.12] - 2017-07-27

### Fixed
- css for checkboxes

## [1.1.11] - 2017-07-24

### Fixed
- contao 4 support

## [1.1.10] - 2017-06-08

### Added
- php 7 and contao 4 support

## [1.1.9] - 2017-03-20

### Fixed
- minified backend js and made static
- be css style fix, added groupClass for dca widget eval configuration to address container around widgets

## [1.1.8] - 2017-03-17

### Fixed
- be css style

## [1.1.7] - 2017-03-17

### Added
- width defined in "eval['style']" is now also added to the "form-group"-div

### Fixed
- be css style

## [1.1.6] - 2017-03-17

### Added
- option "skipCopyValuesOnAdd"

## [1.1.5] - 2017-02-13

### Fixed
- fixed missing help text below fields

## [1.1.4] - 2017-02-09

### Fixed
- fixed attribute naming leading to data not being saved

## [1.1.3] - 2017-02-08

### Added
- support for multiple multiColumnEditor fields in one form
