<?php declare(strict_types=1);

namespace Nette\Bridges\AssetsLatte;

use Latte\CompileException;
use Latte\MacroNode;
use Latte\Macros\MacroSet;
use Latte\PhpWriter;


/**
 * Latte 2.x macros for asset management.
 * Provides {asset}, {asset?}, {preload}, {preload?} macros and n:asset attribute.
 */
final class AssetMacros extends MacroSet
{
	public static function install(\Latte\Compiler $compiler): void
	{
		$me = new static($compiler);
		$me->addMacro('asset', [$me, 'macroAsset'], null, [$me, 'macroNAsset']);
		$me->addMacro('preload', [$me, 'macroPreload']);
	}


	public function macroAsset(MacroNode $node, PhpWriter $writer): string
	{
		if ($node->modifiers) {
			throw new CompileException('Modifiers are not allowed in {asset}');
		}

		$optional = self::extractOptional($node);

		return $writer->write(
			'$ʟ_tmp = $this->global->assets->resolve(%node.word, %node.array?, '
			. var_export($optional, true) . '); '
			. 'if ($ʟ_tmp) { echo $this->global->assets->renderAsset($ʟ_tmp); }',
		);
	}


	public function macroPreload(MacroNode $node, PhpWriter $writer): string
	{
		if ($node->modifiers) {
			throw new CompileException('Modifiers are not allowed in {preload}');
		}

		$optional = self::extractOptional($node);

		return $writer->write(
			'$ʟ_tmp = $this->global->assets->resolve(%node.word, %node.array?, '
			. var_export($optional, true) . '); '
			. 'if ($ʟ_tmp) { echo $this->global->assets->renderAssetPreload($ʟ_tmp); }',
		);
	}


	public function macroNAsset(MacroNode $node, PhpWriter $writer): string
	{
		$tagName = strtolower($node->htmlNode->name);
		$usedAttrs = [];
		foreach ($node->htmlNode->attrs as $name => $value) {
			$usedAttrs[$name] = true;
		}

		return $writer->write(
			'$ʟ_tmp = $this->global->assets->resolve(%node.word, %node.array?, false); '
			. 'if ($ʟ_tmp) { echo $this->global->assets->renderAttributes($ʟ_tmp, '
			. var_export($tagName, true) . ', ' . var_export($usedAttrs, true) . '); }',
		);
	}


	private static function extractOptional(MacroNode $node): bool
	{
		$args = ltrim($node->args);
		if (str_starts_with($args, '?')) {
			$node->args = substr($args, 1);
			return true;
		}
		return false;
	}
}
