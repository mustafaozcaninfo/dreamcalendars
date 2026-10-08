# DreamCalendars

Source workspace for [dreamcalendars.com](https://www.dreamcalendars.com), hosted on a Hostinger Hestia VPS.

- `public_html/`: website code and assets.
- `config/`: application and indexing configuration outside the web root.
- `scripts/`: access checks, selected-file deployment, pull, MySQL, and indexing helpers.
- `beta/`: separate development tools; not deployed by the website scripts.

## Local configuration

Copy `.env.example` to `.env`, set the local project paths, and supply the required credentials. Copy `.cursor/deploy.local.env.example` to `.cursor/deploy.local.env` so the existing scripts load `.env`. Keep both local environment files and the SSH private key outside Git.

Several legacy PHP files contain inline credentials and are intentionally excluded from the repository. Copy the corresponding `*.php.example` files to `*.php` and replace `REPLACE_ME` locally. These runtime PHP files do not automatically load `.env`. Large generated printable assets, archives, backups, logs, and optional indexing secrets also stay outside Git.

## Working with GitHub

Repository: [mustafaozcaninfo/dreamcalendars](https://github.com/mustafaozcaninfo/dreamcalendars). Main branch: `main`.
GitHub authentication uses `gh auth login` and the OS keychain. Never put its token in project files.

After selecting changes to stage, run:

```bash
python3 scripts/dc-git-secret-check.py
git diff --cached --stat
git commit -m "Describe the change"
git push origin main
```

## Hostinger

Check access with `scripts/dc-deploy-doctor.sh`. Website paths map one-to-one from local `public_html/<path>` to the configured production web root. Deploy selected files with `scripts/dc-deploy.sh --confirm <paths>` only when a production deployment is intended. A GitHub push alone does not deploy the website.

Project details and the installed Codex skill location are recorded in `dreamcalendars-hostinger.md` and `.env`.

## Reprint startup prototype

[DreamCalendars Reprint](beta/reprint/README.md) explores edition-specific correction strips for printed school calendars, with source evidence and household care impact. The [Claude for Startups application drafts](beta/reprint/docs/CLAUDE-STARTUPS.md), research, and runnable synthetic demo are in `beta/reprint/`.
