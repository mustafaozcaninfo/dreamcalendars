#!/usr/bin/env python3
"""Check staged Git blobs without printing credential values."""
import os
from pathlib import Path
import re
import shlex
import subprocess
import sys

ROOT = Path(__file__).resolve().parents[1]


def main():
    secrets = set()
    env_file = ROOT / '.env'
    if env_file.exists():
        for line in env_file.read_text().splitlines():
            if not line.strip() or line.lstrip().startswith('#'):
                continue
            key, sep, raw = line.partition('=')
            if sep and (any(word in key for word in ('PASSWORD', 'SSHPASS', 'API_KEY', 'TOKEN', 'SECRET', 'CREDENTIAL')) or key == 'AWS_ACCESS_KEY_ID'):
                value = ' '.join(shlex.split(raw, comments=True))
                if len(value) >= 6 and '$' not in value and value.lower() not in ('password', 'passwd', 'changeme', 'replace_me', 'username'):
                    secrets.add(value.encode())

    forbidden = {
        '.env', '.cursor/deploy.local.env', '.cursor/cloud_deploy_key',
        'config/indexing.local.php', 'public_html/connection.php',
        'public_html/SaveCalendars/app/db_connection.php',
        'public_html/webtest/ayar.php',
        'public_html/apps/yearly/server/adapter.php',
        'public_html/apps/yearly/server/holiday.php',
        'public_html/apps/list.php', 'public_html/apps/generate.php',
    }
    entries = subprocess.check_output(['git', 'ls-files', '--stage', '-z'], cwd=ROOT)
    blobs = []
    failures = set()
    for entry in entries.split(b'\0'):
        if not entry:
            continue
        meta, path_raw = entry.split(b'\t', 1)
        mode, oid, stage = meta.split()
        path = os.fsdecode(path_raw)
        if path in forbidden:
            failures.add(path)
        if mode == b'160000':
            failures.add(path + ' (unexpected embedded repository)')
        else:
            blobs.append((path, oid))

    token_pattern = re.compile(rb'gh[pousr]_[A-Za-z0-9]{20,}|AKIA[A-Z0-9]{16}|AIza[A-Za-z0-9_-]{25,}|sk-[A-Za-z0-9_-]{20,}|-----BEGIN (?:OPENSSH |RSA |EC |DSA )?PRIVATE KEY-----')
    # Read the actual staged versions, including files edited after staging.
    output = subprocess.check_output(['git', 'cat-file', '--batch'], input=b'\n'.join(oid for _, oid in blobs) + b'\n', cwd=ROOT) if blobs else b''
    offset = 0
    for path, _ in blobs:
        end = output.index(b'\n', offset)
        header = output[offset:end].split()
        size = int(header[2])
        data = output[end + 1:end + 1 + size]
        offset = end + 2 + size
        if any(secret in data for secret in secrets) or token_pattern.search(data):
            failures.add(path)

    if failures:
        print('Blocked: staged paths contain credentials or excluded runtime data:')
        for path in sorted(failures):
            print('  ' + path)
        return 1
    print(f'Credential check passed for {len(blobs)} staged files. Keep excluded runtime files local.')
    return 0


if __name__ == '__main__':
    sys.exit(main())
