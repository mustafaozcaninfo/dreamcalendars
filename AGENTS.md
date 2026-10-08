# DreamCalendars

This workspace is linked to `mustafaozcaninfo/dreamcalendars`, on branch `main`.
Load the installed `dreamcalendars-hostinger` skill when working on hosting, deployment, GitHub setup, or directory mappings. Its local path is recorded in `.env` as `PROJECT_SKILL_FILE`.

Credentials and machine-specific paths live in `.env`; use `.env.example` as the shareable schema. Existing deploy scripts load it through `.cursor/deploy.local.env`.
Website files belong under `public_html/`; the corresponding production directory is `DEPLOY_ROOT_PRODUCTION`. Private server config belongs outside that directory. There is no staging.

The GitHub repository is public. Keep `.env`, SSH private keys, `config/indexing.local.php`, and the ignored PHP files containing inline credentials out of Git. Their `*.example` files preserve code with credential placeholders. Existing runtime PHP files do not automatically read `.env`.
Before committing or pushing, run `python3 scripts/dc-git-secret-check.py` against the staged snapshot. Do not print secret values. GitHub authentication uses `gh` and the OS keychain; do not save a GitHub token in `.env`.

Deploy only when requested. GitHub pushes do not deploy to Hostinger. Use selected-file deployments with `scripts/dc-deploy.sh --confirm <paths>`; check changed PHP with `php -l`. A full pull can overwrite local edits.
