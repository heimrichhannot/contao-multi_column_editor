# Changelog
All notable changes to this project will be documented in this file.

## [1.2.6] - 2017-11-01

## Fixed
- limited ajax support for tree widgets to mce

## [1.2.5] - 2017-10-30

## Fixed
- min row count

## [1.2.4] - 2017-10-25

## Added
- support for the inputType "fileTree"

## [1.2.3] - 2017-08-30

## Fixed
- return `HTTP/1.1 400 Bad Request` only if action belongs to `Hooks::executePostActionsHook`

## [1.2.2] - 2017-08-30

## Added
- support for the inputType "jumpTo"

## [1.2.1] - 2017-08-28

## Fixed
- sorting

## [1.2.0] - 2017-08-28

## Added
- sortable flag in order to make the rows of the widget sortable (currently backend only, frontend to come)
- Contao 4 icons

## [1.1.13] - 2017-07-27

## Fixed
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
