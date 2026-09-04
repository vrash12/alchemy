# Alchemy Classroom — BigBlueButton Technical Trial

This is my Laravel proof of concept for creating and joining BigBlueButton classrooms directly, without using Greenlight. I intentionally kept the application small and focused on the requested flow:

**Laravel → BigBlueButton → moderator classroom → BBB customization**

## Live demonstration

[Open Alchemy Classroom](https://alchemy-bbb-trial-464796395656.asia-southeast1.run.app/classroom)

The demonstration is hosted on Google Cloud Run and currently uses Blindside Networks' temporary public BBB test server.

## How I create the BBB meeting

When the tutor clicks **Join Meeting**, Laravel sends a server-side request directly to BigBlueButton's `/api/create` endpoint.

I placed the BBB communication logic inside `BigBlueButtonService` to keep it separate from the controller. Laravel verifies both the HTTP response and BBB's XML response before continuing. Greenlight is not involved.

## How I generate the unique meeting ID

I generate a new UUID for every click:

```php
$meetingId = 'alchemy_trial_'.Str::uuid();
```

The ID is created for each request and is never reused. Returning to the test page and clicking **Join Meeting** therefore creates a new classroom every time.

## How Laravel authenticates with BBB

Laravel uses BBB's checksum-based API authentication. It builds the exact query string and calculates:

```text
hash(apiCallName + queryString + BBB shared secret)
```

The checksum is included in the request sent to BBB. The BBB URL and shared secret are stored in server-side environment variables. The secret is never rendered in the page, sent to browser JavaScript, or committed to Git.

## How the join process works

After BBB confirms that the meeting was created, Laravel generates a second signed request for `/api/join` containing:

```text
fullName=Trial Tutor
meetingID=<new meeting ID>
role=MODERATOR
```

Laravel redirects the browser to the signed join URL. BBB validates the checksum, creates the tutor session, and opens its HTML5 classroom directly with moderator access.

## How I added the Resources button

I created a small HTML plugin using the official BigBlueButton HTML Plugin SDK. It registers **Resources** in BBB's Options menu and opens a placeholder resource URL in a new tab when selected.

The plugin source is in `bbb-plugin/resources-plugin`. Its compiled manifest and bundle are served publicly by Laravel from `public/bbb-plugins/resources`. Laravel supplies the manifest URL to BBB through the `pluginManifests` parameter when creating a meeting.

Leaving `BBB_RESOURCES_PLUGIN_MANIFEST_URL` empty disables the plugin without affecting meeting creation.

## Difficulties and limitations

The main difficulty was that a BBB plugin is loaded by the BBB server, not by Laravel or the user's browser. A remote BBB server cannot load a manifest from `localhost`, so the plugin files must be hosted at a public HTTPS address.

I hosted the manifest and included it in the BBB create request. However, the shared public BBB demo server ignores custom plugin manifests and only loads plugins enabled by its provider. I could therefore fully verify meeting creation and moderator joining, while displaying **Resources** inside BBB requires a server that permits custom plugin manifests.

The public BBB test environment is temporary and should not be treated as production infrastructure. A final deployment should use BBB credentials supplied by the organization or its BBB provider.

## What I would do differently in production

For a production implementation, I would:

- Store BBB credentials in a managed secret service.
- Authenticate users and authorize access to each lesson.
- Assign tutors the moderator role and students the viewer role.
- Associate BBB meeting IDs with lesson records in the database.
- Use BBB webhooks to track meeting lifecycle events.
- Add retries, timeouts, rate limits, structured logs, and monitoring.
- Host versioned plugin assets on reliable HTTPS infrastructure.
- Test against the organization's exact BBB and plugin SDK versions.
- Add integration tests using a dedicated BBB test environment.

## Running locally

Requirements: PHP 8.3 or newer, Composer 2, Node.js, npm, and access to a BBB server.

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
npm install
npm run build
```

Add the BBB configuration to `.env`:

```env
BBB_URL=https://your-bbb-server.example/bigbluebutton
BBB_SECRET=your-shared-secret
BBB_CHECKSUM_ALGORITHM=sha256
BBB_RESOURCES_PLUGIN_MANIFEST_URL=
```

Run Laravel:

```powershell
php artisan serve --host=127.0.0.1 --port=8123
```

Open `http://127.0.0.1:8123/classroom` and click **Join Meeting**. The BBB shared secret must remain in `.env` and must never be committed.

## Verification

```powershell
php artisan test
npm run build
Set-Location bbb-plugin/resources-plugin
npm run build-bundle
```

The automated tests cover checksum construction, moderator join parameters, unique meeting IDs, plugin-manifest forwarding, configuration failures, and rejected BBB responses. I also manually verified the live Laravel page, direct BBB meeting creation, and moderator entry.

## References

- [BigBlueButton API documentation](https://docs.bigbluebutton.org/development/api/)
- [BigBlueButton HTML Plugin SDK](https://github.com/bigbluebutton/bigbluebutton-html-plugin-sdk)
- [Official BBB plugin template](https://github.com/bigbluebutton/bbb-plugin-template)
