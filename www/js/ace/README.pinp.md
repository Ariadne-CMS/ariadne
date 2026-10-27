# Browser PINP syntax checker

`worker-php.js` keeps Ace's existing worker protocol and uses php-parser 3.7.0
configured for PHP 8.5. It runs locally in a Web Worker, without a server request,
CDN dependency, npm install, or changes to installed Helene templates.

The `ace/mode/php/pinp` adapter recognises PINP opening/closing tags in the lexer,
so strings, interpolated strings, heredocs and comments retain their contents.
It preserves original offsets and CRLF line numbers. Outside PINP blocks, HTML,
XML and literal PHP tags are output. External `<compile:...>` blocks are left to
their own compiler. Inline PHP mode continues to parse PHP without tags.

The checker validates syntax. Ariadne's compiler remains authoritative for PINP
restrictions, allowed functions and external compilers when saving. The browser
can accept PHP syntax that the server's PINP compiler does not yet support.

## Vendored dependency

- Project: https://github.com/glayzzle/php-parser/tree/v3.7.0
- Package: https://registry.npmjs.org/php-parser/-/php-parser-3.7.0.tgz
- License and distribution notice: `php-parser.LICENSE` (BSD-3-Clause)
- Package SHA-256: `e0ad34b9d0213f155377198242ccf3a5f906adb0ccc7ec423f38a3cac45306b0`
- Original `dist/php-parser.js` SHA-256:
  `68076900b2a4d6331e73488104a178a492cef877575976d4360a72925e36b46e`

The readable, unminified distribution is embedded between the
`BEGIN/END vendored php-parser` markers in an Ace AMD factory. Only trailing
whitespace has been trimmed from the distribution. The factory
provides a local `process.arch` value because the browser distribution probes it
when sizing integer literals; it does not expose Node globals to the worker.

The adapter corrects upstream 3.7.0's rejection of named arguments after argument
unpacking. Positional arguments after unpacking, and unpacking after named
arguments, still produce errors.

To update, use `dist/php-parser.js` (never the minified distribution) from a
pinned release to replace the embedded distribution, refresh the
license and hashes, and check whether the argument-list correction is still
needed. Keep the PINP lexer adapter and Ace worker infrastructure.

## Validation

Run `node --test tests/javascript/pinp-syntax.test.js` (Node 22 or newer).
The test loads the shipped browser worker in an isolated context without Node
globals or network access. It checks modern syntax, mixed templates, real syntax
errors, error positions and clearing annotations after corrections.

`HeleneSyntaxTest` also compiles the shared syntax fixtures using Ariadne's real
PINP compiler. Run it on PHP 8.5 to include the pipe operator case; older PHP
versions skip that case.
