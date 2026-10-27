#!/usr/bin/env python3
"""Regenerate Helene's completion catalogue and synchronize its install package."""
import argparse
import gzip
import io
import json
from pathlib import Path
import re
import subprocess
import tarfile

root = Path(__file__).resolve().parent.parent
source_path = root / 'www/js/helene-autocomplete.js'
package_path = root / 'www/install/packages/lib.ax'
entry = './templates/33/_pobject.helene.template.autocomplete.js.any.pinp'
manifest_entry = './templates/33/_pobject.helene.assets.json.any.pinp'
options = argparse.ArgumentParser(description=__doc__)
options.add_argument('--write', action='store_true', help='write changes; otherwise check for drift')
args = options.parse_args()

catalogue = json.loads(subprocess.check_output(['php', str(root / 'bin/utils/helene-autocomplete.php')]))
lines = ['// BEGIN GENERATED PINP COMPLETIONS', 'var ariadneList = {']
for name, info in catalogue['functions'].items():
    lines.append('\t' + json.dumps(name) + ': ' + json.dumps(info, separators=(', ', ': ')) + ',')
lines[-1] = lines[-1].rstrip(',')
lines.extend(['};', 'var ARList = {'])
for module, methods in catalogue['modules'].items():
    lines.append('\t' + json.dumps(module) + ': ' + json.dumps(methods) + ',')
lines[-1] = lines[-1].rstrip(',')
lines.extend(['};', '// END GENERATED PINP COMPLETIONS'])
source = source_path.read_text()
source, count = re.subn(r'// BEGIN GENERATED PINP COMPLETIONS.*?// END GENERATED PINP COMPLETIONS',
                        lambda _: '\n'.join(lines), source, flags=re.S)
if count != 1:
    raise SystemExit('Expected one generated catalogue block')
# This JavaScript is also a PINP template: preserve web textarea line endings.
source_bytes = source.replace('\n', '\r\n').encode()
with tarfile.open(package_path, 'r:gz') as archive:
    members = archive.getmembers()
    contents = {m.name: archive.extractfile(m).read() for m in members if m.isfile()}
    manifest_source = contents[manifest_entry]
    split = manifest_source.index(b'</pinp>') + len(b'</pinp>')
    manifest = json.loads(manifest_source[split:])
    if 'helene.template.autocomplete.js' not in manifest['templates']:
        manifest['templates'].append('helene.template.autocomplete.js')
    manifest_bytes = manifest_source[:split] + (json.dumps(manifest, indent=2) + '\n').replace('\n', '\r\n').encode()
    if entry not in contents:
        raise SystemExit('Autocomplete template is not registered in the package')
    updates = {name: data for name, data in {entry: source_bytes, manifest_entry: manifest_bytes}.items()
               if contents[name] != data}
source_changed = source_path.read_bytes() != source_bytes
if not args.write:
    if source_changed or updates:
        raise SystemExit('Autocomplete is stale. Run python3 bin/update-helene-autocomplete.py --write')
else:
    if source_changed:
        source_path.write_bytes(source_bytes)
    if updates:
        # Replace entries, never append duplicate template names. Preserve all other content/metadata.
        target = io.BytesIO()
        with gzip.GzipFile(fileobj=target, mode='wb', mtime=0) as compressed:
            with tarfile.open(fileobj=compressed, mode='w') as output:
                for member in members:
                    data = updates.get(member.name, contents.get(member.name))
                    if member.name in updates:
                        member.size = len(data)
                    output.addfile(member, io.BytesIO(data) if member.isfile() else None)
        temporary = package_path.with_suffix('.ax.tmp')
        temporary.write_bytes(target.getvalue())
        temporary.replace(package_path)
print(f"Helene autocomplete: {len(catalogue['functions'])} functions, "
      f"{len(catalogue['modules'])} modules, {sum(map(len, catalogue['modules'].values()))} module methods")
