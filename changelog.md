# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2025-07-28

### Added

- AbstractProvider: New abstract base class for all providers
  - Unified HTTP client management
  - Automatic caching with configurable TTL
  - Standardized error handling and logging
  - Parameter normalization and search string sanitization
  
- AbstractLivewireComponent: New abstract base class for Livewire components
  - Standardized mount/save/remove methods
  - Factory pattern integration
  - Consistent view name conventions

- ProviderFactory: Factory pattern for provider management
  - Dynamic provider registration
  - Availability checks
  - Singleton pattern for better performance

- CacheService: Automatic caching system
  - Provider-specific cache management
  - Configurable TTL per provider
  - Automatic cache key generation

- MakeProviderCommand: Artisan command for creating new providers
  - Automatic generation of provider classes
  - Automatic generation of Livewire components
  - Template-based code creation

- Comprehensive test suite:
  - PHPUnit tests for all providers
  - Mock-based HTTP tests
  - Factory pattern tests
  - PHP 8 attributes instead of deprecated annotations

### Changed

- BREAKING: All providers now extend AbstractProvider
- BREAKING: All Livewire components now extend AbstractLivewireComponent
- BREAKING: Provider instantiation should use ProviderFactory
- Tests use modern PHP 8 `#[Test]` attributes instead of `@test` annotations
- TestCase is now abstract to avoid PHPUnit warnings

### Refactored

- Gnd Provider: Completely refactored based on AbstractProvider
- Wikidata Provider: Completely refactored with improved API integration
- Wikipedia Provider: Refactored with locale-specific URLs
- Geonames Provider: Refactored with improved parameter handling
- Metagrid Provider: Refactored with standardized methods
- Idiotikon Provider: Simplified and standardized
- Ortsnamen Provider: Refactored with better error handling
- Anton Provider: Refactored with improved token handling

### Technical Improvements

- Automatic caching significantly reduces API calls
- Unified error handling across all providers
- Consistent parameter passing and validation
- Improved code quality through abstract base classes
- Better testability through factory pattern and dependency injection

### Documentation

- Completely new README.md with comprehensive documentation
- Examples for all important use cases
- Migration guide from v1 to v2
- Troubleshooting section
- Performance optimization tips

## [1.0.0] - Legacy Version

### Added

- Basic providers for GND, Wikidata, Wikipedia, Geonames
- Livewire components without unified architecture
- Manual HTTP client creation per provider

### Issues

- Code duplication between providers
- Inconsistent API integration
- Missing test coverage
- No caching mechanisms
