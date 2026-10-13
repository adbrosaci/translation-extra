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

## Latte templates

`LatteExtractor` walks the whole template and collects every `{_'message'}` (and any print with the `translate` filter) whose message is a string literal, including HTML attribute values, `n:` attributes, all `{if}`/`{elseif}`/`{else}` branches, loops and nested blocks. Dynamic messages like `{_$message}` are skipped.

A template that cannot be parsed does not stop the extraction. It is skipped and reported as a warning, so its messages are missing from the extracted catalogue. When any template is skipped, `--clean` is not applied: the extracted messages are only merged into the translation files and a warning says that cleaning was skipped, so no existing messages are removed. Fix the reported templates and run the command again to clean the files.
