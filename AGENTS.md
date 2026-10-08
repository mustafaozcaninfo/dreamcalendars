# DreamCalendars Reprint

This repository contains only the Reprint startup prototype, its synthetic examples, and English documentation. The production DreamCalendars PHP website is maintained separately; this repository has no production deployment workflow.

Run project commands from the repository root. Python 3.11+ is required. Keep the offline planner independent of third-party services. Claude extraction is optional and must keep all candidates proposed until human review. Do not claim live API verification, customer traction, or unimplemented features without evidence.

Before committing or pushing, stage the intended files, run `python3 scripts/dc-git-secret-check.py`, and review the staged changes. Keep `.env`, API credentials, private documents, local outputs, and real household data out of Git. GitHub authentication uses the existing `gh` login and OS keychain.

For planner, extraction, or export changes, run `python3 -m unittest discover -s tests -v`. Keep README commands, demo links, and the optional CI template consistent with the root project layout. Generated examples must remain synthetic.
