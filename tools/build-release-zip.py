"""Build a reproducible WPSCK release ZIP and its SHA-256 file manifest."""
import argparse
import hashlib
import json
from pathlib import Path, PureWindowsPath
from zipfile import ZIP_DEFLATED, ZipFile, ZipInfo


def build(source, output):
    manifest = {}
    output.parent.mkdir(parents=True, exist_ok=True)
    files = sorted(source.rglob('*'), key=lambda path: PureWindowsPath(path.relative_to(source)))
    with ZipFile(output, 'w') as archive:
        for file in files:
            name = file.relative_to(source).as_posix()
            if file.is_symlink() or any(part.startswith('.') for part in file.relative_to(source).parts):
                raise ValueError(f'Unexpected runtime path: {name}')
            if not file.is_file():
                continue
            if file.suffix.lower() in {'.sql', '.pem', '.key', '.log', '.zip', '.bak'}:
                raise ValueError(f'Unexpected runtime file: {name}')
            data = file.read_bytes()
            manifest[name] = hashlib.sha256(data).hexdigest()
            info = ZipInfo('wp-seed-content-kit/' + name, (2026, 11, 27, 0, 0, 0))
            info.create_system = 3
            info.external_attr = 0o100644 << 16
            archive.writestr(info, data, compress_type=ZIP_DEFLATED, compresslevel=9)
    with ZipFile(output) as archive:
        assert archive.testzip() is None
        assert {name.split('/', 1)[1]: hashlib.sha256(archive.read(name)).hexdigest()
                for name in archive.namelist()} == manifest
    output.with_suffix('.manifest.json').write_text(json.dumps(manifest, indent=2), encoding='utf-8')
    print(len(manifest), hashlib.sha256(output.read_bytes()).hexdigest())
    return manifest


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--source', type=Path, default=Path(__file__).resolve().parents[1] / 'plugin')
    parser.add_argument('--output', type=Path, required=True)
    args = parser.parse_args()
    build(args.source.resolve(), args.output.resolve())
