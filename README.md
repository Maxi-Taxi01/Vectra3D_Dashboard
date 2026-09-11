# Vectra3D graduation dashboard

Private administrative workspace for Max Klok's Vectra3D graduation project (THUAS,
31 Aug 2026 – 29 Jan 2027). Start at [START_HERE.md](START_HERE.md).

- [`Dashboard/`](Dashboard/) — the working tool. Open `Dashboard/index.html` locally, or
  double-click `Open dashboard.cmd`.
- [`Publish_vectra3d.nl/`](Publish_vectra3d.nl/README_DEPLOY.md) — deploy bundle for hosting
  the dashboard at vectra3d.nl behind a login page. Requires you to create your own
  `Publish_vectra3d.nl/private/auth.php` from `auth.example.php` — the real one is
  gitignored and never enters this history.
- [`AGENTS.md`](AGENTS.md) — the rules this workspace and any AI assistant work under.

Two files intentionally do not live in this repo: `Dashboard/sources/Agreement.pdf` and
`Proposal.pdf` (signatures and sponsor names). See `.gitignore` for why.
