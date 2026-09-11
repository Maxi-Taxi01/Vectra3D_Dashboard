# Publishing the dashboard to vectra3d.nl

Built 11 September 2026. **Not uploaded.** Uploading is your step — I have no hosting
credentials and will not ask for any.

---

## Read this before you upload

### 1. Change the password first

The password you first gave was typed into a chat conversation, so it now exists in a
transcript. Treat it as compromised and pick a different one — do not reuse it here or
anywhere else.

```bash
php rotate-password.php
```

Paste the three lines it prints into `private/auth.php`. What is in there now is a
PBKDF2-SHA256 derivation of the password you gave, not the password itself — but rotate it
anyway.

Note the username you specified is **Vectra2D**, with a 2, while the domain is Vectra3D.
Deliberate or a typo, it is in `private/auth.php` exactly as you wrote it.

### 2. Decide about the two source PDFs

`private/app/sources/` holds `Agreement.pdf` and `Proposal.pdf`. The agreement carries
signatures and personal details; the proposal names your sponsor and the CPO case.

They are protected — the gate serves nothing before a valid session, PDFs included, and
they have no URL of their own. But your own `AGENTS.md` says *"no external publication"* is
implied by routine dashboard maintenance, and `Templates/Publication_review.md` exists for
decisions like this one. Putting files on a webserver behind your own password is closer to
private hosting than to publication, and that is your call to make rather than mine.

**If you would rather they stayed off the server entirely:** delete
`private/app/sources/` before uploading. The dashboard's Files & guidance tab will show
broken links to them; nothing else breaks.

### 3. Your saved entries do not come with it

The dashboard stores everything in browser local storage, scoped to the address it is
served from. `file:///C:/...` and `https://vectra3d.nl` are different origins, so the
published copy **starts completely empty** and your local entries stay local.

To move them: open your local dashboard, **Export backup**, then on vectra3d.nl use
**Restore backup** with that file. Do this before you enter anything into the web copy, or
you will have two partial dashboards and no way to merge them.

Pick one as the real one afterwards. Running both will cost you a week's work eventually.

---

## Step by step: from these files to a working login

Roughly 20 minutes the first time. Do the steps in this order — step 2 before step 8
particularly, or the first visit will fail in a confusing way.

### 1. Sign in to your hosting control panel

Antagonist runs DirectAdmin. Go to your Antagonist account and open the control panel for
**vectra3d.nl**. If you have never opened it, the login details are in your original
Antagonist welcome email.

### 2. Turn on HTTPS first

In DirectAdmin: **SSL Certificates** → select **vectra3d.nl** → choose **Free & automatic
certificate from Let's Encrypt** → tick both `vectra3d.nl` and `www.vectra3d.nl` → **Save**.

Wait for it to report success before continuing.

This has to happen first. The `.htaccess` file forces every visitor onto HTTPS. Without a
certificate in place, that redirect sends the browser to an address that does not work yet,
and you get a security warning instead of a login page.

### 3. Open the file manager

DirectAdmin → **File Manager**. Navigate into `domains` → `vectra3d.nl`.

You should see a folder called **public_html**. That is the web root: everything inside it
is reachable from the internet.

### 4. Create the private folder

While in `domains/vectra3d.nl/` — **not** inside public_html — create a new folder called:

```
private
```

It must sit *beside* public_html, not inside it. This single detail is the entire security
model: files in here have no web address, so nobody can request them.

Inside `private`, create one more folder called `app`.

### 5. Upload the private files

| Upload this | To here |
|---|---|
| `private/auth.php` | `domains/vectra3d.nl/private/` |
| `private/app/index.html` | `domains/vectra3d.nl/private/app/` |
| `private/app/app.js` | same |
| `private/app/seed.js` | same |
| `private/app/style.css` | same |

Then create `domains/vectra3d.nl/private/app/sources/` and upload `Agreement.pdf` and
`Proposal.pdf` into it — **unless you decided to leave the PDFs off the server**, in which
case skip this and the sources folder entirely.

### 6. Upload the two public files

| Upload this | To here |
|---|---|
| `public_html/gate.php` | `domains/vectra3d.nl/public_html/` |
| `public_html/.htaccess` | same |

**`.htaccess` starts with a dot, so it is hidden by default.** In the DirectAdmin file
manager, look for a "show hidden files" toggle. In FileZilla it is
*Server → Force showing hidden files*. If you skip this file, nothing works — every address
will 404.

Do **not** upload `rotate-password.php` or `README_DEPLOY.md`. They are for your computer.

### 7. Check the layout before you continue

```
domains/vectra3d.nl/
├── private/
│   ├── auth.php
│   └── app/
│       ├── index.html   app.js   seed.js   style.css
│       └── sources/     Agreement.pdf   Proposal.pdf
└── public_html/
    ├── .htaccess
    └── gate.php
```

If `private` ended up inside `public_html`, move it out now, before the site goes live.

### 8. Open the site

Go to:

```
https://vectra3d.nl
```

You should see the sign-in panel: the Vectra3D mark, "Sign in", two fields and a teal
button.

### 9. Log in

| | |
|---|---|
| **Username** | `Vectra2D` |
| **Password** | the one you gave me, or your rotated replacement |

Capital V, capital D, and the digit **2** in the middle — it is *Vectra2D*, not Vectra3D.
That is what you specified; if it was a slip, change it in `private/auth.php` before
uploading, or tell me and I will change it.

Press **Sign in**. The dashboard loads at the same address.

### 10. Expect an empty dashboard, and fix that deliberately

It will show every task as Not started and no hours, because browser storage does not travel
between addresses. This is expected, not a failed upload.

To bring your work across:

1. Open your **local** dashboard (`Open dashboard.cmd`)
2. Click **Export backup**, save the JSON
3. On vectra3d.nl, go to **Files & guidance** → **Restore backup** → pick that file
4. Confirm the replacement

Do this before entering anything on the web copy. Restore replaces, it does not merge.

From then on use **one** of the two, not both.

---

## If something does not work

| What you see | Almost certainly |
|---|---|
| PHP source code as plain text | `.htaccess` missing, or PHP disabled for the domain |
| 404 on every address | `.htaccess` missing — it is a hidden file, check step 6 |
| "Application directory not found" | `private/app/` is empty, misspelled, or inside public_html |
| Security warning / redirect loop | Let's Encrypt not active yet — step 2 |
| Login page reloads with no error | Cookies blocked for the site, or the page was left open for hours; reload and retry |
| "Those details were not recognised" | Username is **Vectra2D** with a 2. Check caps lock |
| "Too many attempts" | 8 wrong tries. Wait 15 minutes, or delete `private/.throttle` |

To check the PHP before uploading, if you have PHP locally:

```bash
php -l gate.php
```

---

## What gets uploaded where

Antagonist hosting puts your web root at `~/domains/vectra3d.nl/public_html`. The point of
this layout is that the dashboard lives *above* it.

```
~/domains/vectra3d.nl/
├── private/              ← NOT inside public_html. This is the whole security model.
│   ├── auth.php
│   └── app/
│       ├── index.html
│       ├── app.js
│       ├── seed.js
│       ├── style.css
│       └── sources/
│           ├── Agreement.pdf
│           └── Proposal.pdf
└── public_html/
    ├── .htaccess
    └── gate.php
```

Upload by FTP/SFTP or the DirectAdmin file manager. Two things to check afterwards:

- **`private/` must not be reachable.** Open `https://vectra3d.nl/../private/auth.php` and
  anything like it; you should get an error, never PHP source or a file listing.
- **`.htaccess` must actually arrive.** FTP clients hide dotfiles by default. If the site
  shows PHP source code instead of a login page, or every URL 404s, this file is missing.

Enable **Let's Encrypt** for the domain in DirectAdmin before you sign in the first time.
The `.htaccess` forces HTTPS, and without a certificate that redirect loops into a browser
warning.

---

## How the protection actually works

Every request hits `gate.php`. Without a valid session it returns the login page and
nothing else — it never reads from `private/app` at all. With a valid session it streams
the requested file from above the web root.

This matters because the obvious alternative does not work: a JavaScript password check on
a static page ships the whole dashboard to the browser first and asks afterwards. Anyone
can read the source or request the PDF directly. That approach looks like a login page and
protects nothing, which is worse than no login at all, because it invites you to put
sensitive files behind it.

What is in place:

| | |
|---|---|
| Credential storage | PBKDF2-SHA256, 310 000 iterations, random 16-byte salt. No plaintext anywhere |
| Comparison | `hash_equals` on both username and password, no early return, so response time leaks nothing |
| Lockout | 8 consecutive failures from one IP → 15 minutes locked, plus a 0.4 s delay on every failure |
| Sessions | Custom name, HttpOnly, Secure, SameSite=Lax, ID regenerated on login, 8-hour expiry |
| CSRF | Token on the login form |
| Transport | HTTPS forced by redirect, HSTS sent once served over TLS |
| Path traversal | Every request resolved with `realpath` and required to sit inside `private/app/` |
| Caching | `private, no-store` on everything, so no proxy or shared cache retains a page |
| Headers | CSP, `X-Frame-Options: DENY`, `nosniff`, `same-origin` referrer, `X-Robots-Tag: noindex` |

### What it is not

Adequate for personal data belonging to research participants. One shared account, no audit
log, no MFA, no rotation policy, no breach process. Fine for your own planning entries;
**not** a place to put interview material, resident details or anything a participant
consented to under a different arrangement. That boundary is in `AGENTS.md` and this
changes nothing about it.

---

## The login page

Built from the dashboard's own tokens in `DESIGN.md` — same ink, same teal accent, same
Segoe UI stack, same 6px panel radius — so it reads as the same tool rather than a bolted-on
door. Full states on both fields and the button (hover, focus-visible, active, disabled,
busy), a real error region tied to `role="alert"`, a locked state that disables submission,
and a `prefers-reduced-motion` path that drops the spinner animation.

It tells the truth on first arrival about the empty-dashboard problem, in the panel footer,
where someone signing in from a new device will actually read it.

---

## If you would rather not run PHP

`.htaccess` + `.htpasswd` Basic Auth gives equivalent protection in two files and no code,
at the cost of the browser's grey credential dialog instead of a designed page. Say the
word and I will swap it.
