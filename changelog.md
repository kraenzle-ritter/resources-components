# Changelog

All notable changes to `resources-components` will be documented in this file.

## 2.0.0 - 2023-07-01

### Added

- New provider interface and abstract class implementation
- Provider factory for easier provider management
- Improved error handling and logging
- More extensive documentation with COMPONENTS.md and ENTWICKLUNGSANLEITUNG.md
- Upgrade guide for migrating from v1.x to v2.x
- Added GitHub Actions for testing against multiple PHP and Laravel versions
- Code coverage reporting with Codecov integration

### Changed

- Fixed internationalization in Wikipedia components to properly use selected language
- Improved parameter passing between components
- Restructured component classes with better separation of concerns
- Updated TextHelper with English comments and improved functionality
- Enhanced configuration structure for better provider management
- Optimized Livewire components with standardized parameter structure

### Fixed

- Fixed Wikipedia language selection issue where "wikipedia-fr" was using German locale
- Fixed parameter passing in ProviderSelect component
- Fixed event handling between components
- Updated tests to match new component structure

## 1.0.0 - 2023-01-15

### Added

- Initial release with support for multiple provider integrations
- Provider components for: GND, Geonames, Wikipedia, Wikidata, Idiotikon, Ortsnamen, Metagrid, and Anton API
- Support for Wikipedia in multiple languages (de, en, fr, it)
- Manual input provider for custom links
- Provider selection component
- Resources listing component
- Comprehensive test suite
- GitHub Actions CI/CD integration
- Documentation for installation and usage
- Support for Laravel 10 and 11 with Livewire 3.4
