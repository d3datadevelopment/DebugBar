# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased](https://git.d3data.de/D3Public/DebugBar/compare/1.1.0.0...rel_1.x)

## [1.1.0.0](https://git.d3data.de/D3Public/DebugBar/compare/1.0.0.0...1.1.0.0) - 2022-08-05
### Added
- shop edition and version information directly in the bar
- basic shop informations (edition, version, theme informations) in the shop tab

### Changed
- reorder tabs
- adjust tab icons for small viewports

### removed
- useless exceptions tab

## [1.0.0.0](https://git.d3data.de/D3Public/DebugBar/releases/tag/1.0.0.0) - 2022-07-30
### Added
- generate debug bar instance with 
    - Request data colletor
    - Monolog log messages collector
    - Doctrine database queries collector
    - Smarty variables collector
    - Timeline profiling collector
    - Shop configuration collector
    - Collector for freely definable debug messages 