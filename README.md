# adbros/translation-extra

Extracting translation contents and updating catalogues automatically for Nette FW.

Inspired by [kdyby/translation](https://github.com/Kdyby/Translation) package. 

## Requirements

Requires symfony/console, you may use [contributte/console](https://github.com/contributte/console).

## Instalation

```bash
composer require adbros/translation-extra
```

## Register extension

```neon
extensions:
	translationExtra: Adbros\TranslationExtra\DI\TranslationExtraExtension
```

## Default configuration

```neon
translationExtra:
	extractor:
		defaultScanDir:
			- %appDir%
		defaultOutputDir: %appDir%/locale
		defaultFormat: neon
		extractors:
			nette: Adbros\TranslationExtra\Extractor\NetteExtractor 
			latte: Adbros\TranslationExtra\Extractor\LatteExtractor
		loaders:
			neon: Adbros\TranslationExtra\Loader\NeonFileLoader
		dumpers:
			neon: Adbros\TranslationExtra\Dumper\NeonFileDumper 
```

## Usage

```bash
bin/console translation:extract cs
```
