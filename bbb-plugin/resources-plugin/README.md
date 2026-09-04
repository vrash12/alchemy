# Alchemy Resources plugin

This BBB 3.0 plugin follows the official HTML Plugin SDK options-dropdown example. It adds a **Resources** item to the classroom Options menu and opens a placeholder page.

```powershell
npm install
npm run build-bundle
```

The build is written to `dist`. Copy `dist/manifest.json` and `dist/AlchemyResourcesPlugin.js` to Laravel's `public/bbb-plugins/resources` directory, then expose that directory over public HTTPS.

The SDK must match the target server. This source pins SDK `0.0.103` for BBB 3.0. For BBB 4.0, use the official `v0.1.x` template branch and compatible SDK before testing.
