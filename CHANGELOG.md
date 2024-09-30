# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.0] - 2024-09-29

This update introduces an update for ncc 2.1+ & PHP 8.3 compatibility.

### Added
 - Added PHPUnit tests for TempFile class

### Changed
 - Refactor build system and add CI pipeline
 - Refactor TempFile class and improve error handling
 - Update PHP language level to 8.3
 - Refactor TempFile class filed types and improves filename validation
 - Updated .gitignore file


## [1.1.0] - 2023-02-26

### Changed

 - Replaced `$extension` parameter with `$options` parameter in `\TempFile > TempFile > __construct()`
   so that it's possible to specify the file extension and the file name prefix.


## [1.0.0] - 2023-02-25

### Added

- Initial release