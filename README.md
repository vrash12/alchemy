# Alchemy Classroom — BigBlueButton technical trial

A deliberately small Laravel proof of concept that creates a fresh BigBlueButton meeting and redirects the tutor into it as a moderator. It communicates directly with BBB; Greenlight is not involved.

## Requirements

- PHP 8.3 or newer and Composer 2
- Node.js and npm
- Access to a BigBlueButton server, including its API base URL and shared secret
- For the optional Resources plugin: BBB 3.0 and a public HTTPS URL for the built plugin manifest

## Setup

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
npm install
npm run build
```

Configure `.env`:

```env
BBB_URL=https://your-bbb-server.example/bigbluebutton
BBB_SECRET=your-shared-secret
BBB_CHECKSUM_ALGORITHM=sha256
BBB_RESOURCES_PLUGIN_MANIFEST_URL=
```

The shared secret belongs only in `.env`; never commit it. On a BBB host, an administrator can retrieve the URL and secret with `sudo bbb-conf --secret`.

Run Laravel:

```powershell
php artisan serve --host=127.0.0.1 --port=8123
```

Open `http://127.0.0.1:8123/classroom`, then select **Join Meeting**. `/classroom/join` is a CSRF-protected POST action and should not be opened directly.

## Free demonstration hosting

The repository includes a production-oriented multi-stage `Dockerfile` and a Render Blueprint in `render.yaml`. Render is appropriate for this trial because its free web service can run a Dockerized PHP application and provides a public HTTPS address. It is demonstration hosting: free services sleep after inactivity and the first request after sleeping can take about a minute.

1. Sign in to [Render](https://dashboard.render.com/) with GitHub.
2. Choose **New → Blueprint** and select this repository.
3. Render reads `render.yaml`; keep the **Free** service plan.
4. When prompted, enter `APP_KEY`, `BBB_URL`, and `BBB_SECRET` as secret environment values.
5. Generate `APP_KEY` locally with `php artisan key:generate --show`.
6. After deployment, open `https://<your-service>.onrender.com/classroom`.

To test Resources, add this environment variable after the first deployment:

```text
BBB_RESOURCES_PLUGIN_MANIFEST_URL=https://<your-service>.onrender.com/bbb-plugins/resources/manifest.json
```

The demo BBB provider currently ignores custom manifests, but this stable HTTPS address can be used with a BBB server that permits them. GitHub Pages and static-only hosting are not suitable because Laravel needs a PHP server to sign BBB requests securely.

### Public demo used for development

On 4 September 2026, Part 1 was tested successfully against Blindside Networks' public BBB demo endpoint referenced by BigBlueButton's official Greenlight installer. The working copy's ignored `.env` is configured for that demo, so the local page can be tried immediately. Do not commit or depend on those shared demo credentials for a submitted or production system.

The demo classroom currently announces that this free hosted service will transition on 15 September 2026. Treat it as temporary and replace it with credentials supplied by the employer or a BBB provider for the final demonstration.

## How meeting creation works

`ClassroomController` generates `alchemy_trial_` plus `Str::uuid()` for every click. It passes that ID to `BigBlueButtonService`, which builds one exact query string containing `name` and `meetingID`, signs it, and requests BBB's `/api/create` endpoint. It validates the HTTP response and the XML `<returncode>SUCCESS</returncode>` before proceeding.

Because a new UUID is generated for every request and nothing is persisted or reused, returning to the test page always creates a different BBB meeting.

## BBB authentication

Laravel reads the BBB URL and shared secret from the configuration layer. For every API call, it calculates:

```text
hash(callName + exactQueryString + sharedSecret)
```

The default algorithm is SHA-256 and can be changed to SHA-1, SHA-384, or SHA-512 to match the target server. The exact query string used for the checksum is also sent to BBB. Signing happens only on the server; the secret is never rendered into HTML or browser JavaScript.

## Join process

After BBB confirms creation, Laravel creates a second signed URL for `/api/join` with:

```text
fullName=Trial Tutor
meetingID=<fresh ID>
role=MODERATOR
```

Laravel redirects the browser to that URL, so BBB establishes the tutor session and opens its HTML5 classroom directly.

## Resources plugin

The source is in `bbb-plugin/resources-plugin` and follows the official BBB HTML Plugin SDK options-dropdown pattern. It registers **Resources** in BBB's Options menu. Selecting it opens `https://example.com` in a separate tab with `noopener,noreferrer`.

The plugin is pinned to SDK `0.0.103`, intended for BBB 3.0. Build it with:

```powershell
Set-Location bbb-plugin/resources-plugin
npm install
npm run build-bundle
```

Copy the JavaScript bundle, its license file, and `manifest.json` from `dist` to `public/bbb-plugins/resources`. Deploy Laravel or expose it through an HTTPS tunnel, then confirm this URL is publicly reachable:

```text
https://your-public-app.example/bbb-plugins/resources/manifest.json
```

Set that complete URL as `BBB_RESOURCES_PLUGIN_MANIFEST_URL`. Laravel includes it in the BBB `create` request through the `pluginManifests` parameter. Leaving the variable blank disables the plugin without affecting meeting creation.

## Difficulties and limitations

- The real create-and-join flow was verified against Blindside Networks' temporary public demo: Laravel created a unique meeting, redirected with `role=MODERATOR`, and BBB opened its HTML5 classroom with Trial Tutor as presenter.
- A remote BBB server cannot load a manifest from `localhost`; the plugin files must be reachable over public HTTPS.
- The Resources manifest and bundle were exposed successfully through a temporary HTTPS tunnel and sent in `pluginManifests`. The managed public demo ignored the custom manifest and loaded only its provider-installed `PickRandomUserPlugin`, so Resources could not be demonstrated inside that server. A BBB server that permits user-provided plugin manifests is required for Part 2.
- Plugin SDK compatibility follows the BBB major version. This repository targets BBB 3.0; a BBB 4.0 server requires the official `v0.1.x` template/SDK and revalidation.

## Production improvements

For production, I would authenticate tutors and students, authorize meeting access, map external meeting IDs to lesson records, give students the viewer role, persist meeting lifecycle data, add webhooks and retries, improve structured observability, rate-limit creation, deploy plugin assets through versioned HTTPS hosting, and add integration tests against the actual BBB version.

## Verification

```powershell
php artisan test --compact
npm run build
Set-Location bbb-plugin/resources-plugin
npm run build-bundle
npm audit
```

Automated tests cover exact checksum construction, moderator join parameters, plugin manifest forwarding, unique IDs, configuration failure, and rejected BBB responses. Part 1 was also tested manually against the public demo. Before final submission, repeat these checks using the intended BBB environment:

1. Join once and confirm a real BBB classroom opens as moderator.
2. Return and join again; confirm the meeting ID differs.
3. On a server that permits custom manifests, open BBB's Options menu, select Resources, and confirm the placeholder opens.
4. Temporarily use invalid credentials and confirm Laravel shows a safe error without exposing the secret.

## Official references

- [BigBlueButton API reference](https://docs.bigbluebutton.org/development/api/)
- [BigBlueButton HTML Plugin SDK](https://github.com/bigbluebutton/bigbluebutton-html-plugin-sdk)
- [Official BBB plugin template](https://github.com/bigbluebutton/bbb-plugin-template)
- [Official Greenlight installer containing the public demo configuration](https://github.com/bigbluebutton/greenlight/blob/master/gl-install.sh)
