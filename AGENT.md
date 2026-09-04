# Alchemy Classroom technical trial

Build a focused Laravel proof of concept within a maximum of six hours.

## Required

1. Show a simple page with **Join Meeting**.
2. Generate a new unique meeting ID for every click.
3. Call BigBlueButton directly to create the meeting; do not use Greenlight.
4. Sign BBB API requests on the Laravel server without exposing the shared secret.
5. Redirect the user into the new BBB classroom as a moderator/tutor.
6. Handle configuration, network, HTTP, XML, and BBB failures safely.

## Secondary

Add **Resources** inside BBB using its supported HTML Plugin SDK. It may open a placeholder destination. Match the SDK to the actual BBB server version and document any provider limitation.

## Keep out of scope

Do not build authentication, accounts, scheduling, management dashboards, recording workflows, AI, analytics, or unrelated infrastructure. A database is not required.

## Submission

Provide source code, a working demonstration or recording, and a short README explaining meeting creation, unique IDs, authentication, joining, Resources, limitations, and production improvements.

Prioritize the working create/join integration over plugin polish.
