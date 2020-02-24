# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [2.1.0] - 2020-02-24
### Deprecated
- `|call` twig filter
### Fixed
- `README.md` minor fixes

## [2.0.0] - 2020-02-14
### Added
- docker container
### Changed
- namespace from `DPolac\TwigLambda\` to `LeonAero\TwigLambda\`
- minimum php version is now 7.0
### Deprecated
- Lambda, all files in `LeonAero\NodeExpression\*` (use original twig lambda)
- `==>`, `;` twig operator
### Removed
- `|map`, `|filter` and `|sort_by` twig filters
- `is any` and `is every` twig test
- `=>`, `;` twig operator
- unused `GroupByObjectIterator` class

## [1.1.0] - 2020-02-12
### Added
- `|is_any`, `|is_every` twig filters
- `==>` twig operator (only for migration)
### Deprecated
- `|map`, `|filter` and `|sort_by` twig filters
- `is any` and `is every` twig test
- `=>`, `;` twig operator

[2.1.0]: https://github.com/leonaero/twig-lambda/compare/v2.0.0...v2.1.0
[2.0.0]: https://github.com/leonaero/twig-lambda/compare/v1.1.0...v2.0.0
[1.1.0]: https://github.com/leonaero/twig-lambda/compare/v1.0.0...v1.1.0


