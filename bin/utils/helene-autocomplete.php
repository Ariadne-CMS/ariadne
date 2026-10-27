<?php
// Read source declarations without loading modules or their external dependencies.
require_once dirname(__DIR__, 2).'/vendor/autoload.php';
require_once dirname(__DIR__, 2).'/lib/modules/mod_pinp.phtml';

function heleneAutocompleteCatalogue()
{
	$root = dirname(__DIR__, 2);
	$parser = (new PhpParser\ParserFactory())->create(PhpParser\ParserFactory::PREFER_PHP7);
	$finder = new PhpParser\NodeFinder();
	$classes = $allowed = $globals = $classFiles = array();
	$files = array($root.'/lib/ar.php', $root.'/lib/objects/ariadne_object.php');
	foreach (array('ar', 'objects', 'includes') as $directory) {
		foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/lib/'.$directory)) as $file) {
			if ($file->isFile() && in_array($file->getExtension(), array('php', 'phtml'))) {
				$files[] = $file->getPathname();
			}
		}
	}
	sort($files);
	foreach (array_unique($files) as $file) {
		$nodes = $parser->parse(file_get_contents($file));
		foreach ($finder->findInstanceOf($nodes, PhpParser\Node\Stmt\Class_::class) as $class) {
			if (!$class->name) continue;
			$name = $class->name->toString();
			$classes[$name] = $class;
			$classFiles[$name] = $file;
		}
		foreach ($finder->findInstanceOf($nodes, PhpParser\Node\Stmt\Function_::class) as $function) {
			$globals[strtolower($function->name->toString())] = $function->name->toString();
		}
		foreach ($finder->findInstanceOf($nodes, PhpParser\Node\Expr\StaticCall::class) as $call) {
			if (!$call->class instanceof PhpParser\Node\Name || $call->class->toString() !== 'ar_pinp'
				|| !$call->name instanceof PhpParser\Node\Identifier || $call->name->toString() !== 'allow'
				|| !isset($call->args[0]) || !$call->args[0]->value instanceof PhpParser\Node\Scalar\String_) continue;
			$name = $call->args[0]->value->value;
			$methods = true;
			if (isset($call->args[1])) {
				$methods = array();
				if (!$call->args[1]->value instanceof PhpParser\Node\Expr\Array_) {
					throw new RuntimeException('Non-literal method allowlist for '.$name);
				}
				foreach ($call->args[1]->value->items as $item) {
					$methods[$item->value->value] = true;
				}
			}
			$allowed[$name] = $methods;
		}
	}

	$functions = $modules = array();
	$compiler = new pinp('', 'object->', '$object->_');
	foreach (array_keys($compiler->allowed_functions) as $name) {
		if ($name === '') continue;
		$canonical = $globals[$name] ?? $name;
		$type = isset($globals[$name]) ? (strpos($name, 'ld') === 0 ? 'loader' : 'ariadne') : 'php';
		$functions[$canonical] = array('type' => $type);
	}
	// Guarded PHP calls live on the sandbox, including callback dispatch cases.
	$sandbox = $classes['ar_core_pinpSandbox'];
	foreach ($sandbox->getMethods() as $method) {
		$name = $method->name->toString();
		if ($method->isPublic() && $name[0] !== '_') $functions[$name] = array('type' => 'php');
	}
	foreach ($finder->findInstanceOf($sandbox->getMethod('__call')->stmts, PhpParser\Node\Stmt\Case_::class) as $case) {
		if ($case->cond instanceof PhpParser\Node\Scalar\String_) {
			$functions[$case->cond->value] = array('type' => 'php');
		}
	}
	// Object wrappers are the public PINP functions, without their leading '_'.
	foreach ($classes as $name => $class) {
		if (strpos($classFiles[$name], $root.'/lib/objects/') !== 0 && $name !== 'pinp_ar') continue;
		foreach ($class->getMethods() as $method) {
			$methodName = $method->name->toString();
			if (!$method->isPublic() || !preg_match('/^_[^_]/', $methodName)) continue;
			$function = ($name === 'pinp_ar' ? 'ar::' : '').substr($methodName, 1);
			$functions[$function] = array('type' => $name === 'pinp_ar' ? 'ar::' : ($name === 'ariadne_object' ? 'pobject' : $name));
		}
	}

	$methodsFor = function($name) use (&$methodsFor, $classes) {
		if (!isset($classes[$name])) return array();
		$class = $classes[$name];
		$methods = $class->extends ? $methodsFor($class->extends->toString()) : array();
		foreach ($class->getMethods() as $method) $methods[$method->name->toString()] = $method;
		return $methods;
	};
	$isAllowed = function($name, $method) use ($classes, $allowed) {
		while (isset($classes[$name])) {
			if (isset($allowed[$name]) && ($allowed[$name] === true || isset($allowed[$name][$method]))) return true;
			$name = $classes[$name]->extends ? $classes[$name]->extends->toString() : '';
		}
		return false;
	};
	foreach ($classes as $name => $class) {
		if (strpos($name, 'ar_') !== 0 || $class->isAbstract() || !isset($allowed[$name])) continue;
		$module = str_replace('_', '/', substr($name, 3));
		// Only the module entry points; returned client/data objects need type inference.
		if ($classFiles[$name] !== $root.'/lib/ar/'.$module.'.php') continue;
		$methods = $methodsFor($name);
		if (!isset($methods['__call'])) continue; // No PINP method dispatch.
		$constructor = $methods['__construct'] ?? null;
		if ($constructor && (!$constructor->isPublic() || array_filter($constructor->params, function($param) {
			return $param->default === null && !$param->variadic;
		}))) continue; // ar('module') constructs the entry point without arguments.
		$modules[$module] = array();
		foreach ($methods as $methodName => $method) {
			if (!$method->isPublic() || $methodName[0] === '_' || !$isAllowed($name, $methodName)) continue;
			$modules[$module][] = $methodName;
		}
		sort($modules[$module], SORT_NATURAL | SORT_FLAG_CASE);
		if (strpos($module, '/') === false && !isset($functions['ar::'.$module])) {
			$functions['ar::'.$module] = array('type' => 'ar::');
		}
	}
	// Language constructs must not be inserted as function calls.
	foreach (array('if', 'else', 'elseif', 'do', 'while', 'for', 'switch', 'foreach', 'throw', 'echo', 'print', 'clone') as $name) {
		if (isset($functions[$name])) $functions[$name] = array('type' => 'keyword', 'value' => $name);
	}
	// Deduplicate case-insensitively, preferring the declaration's spelling.
	$unique = array();
	foreach ($functions as $name => $info) $unique[strtolower($name)] = array($name, $info);
	$functions = array();
	foreach ($unique as list($name, $info)) $functions[$name] = $info;
	uksort($functions, 'strnatcasecmp');
	uksort($modules, 'strnatcasecmp');
	return array('functions' => $functions, 'modules' => $modules);
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
	echo json_encode(heleneAutocompleteCatalogue(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
}
