<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Create and join a fresh BigBlueButton classroom.">
    <title>Alchemy Classroom</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="page-shell">
        <header class="site-header" aria-label="Alchemy Classroom">
            <a class="brand" href="{{ route('classroom.show') }}" aria-label="Alchemy Classroom home">
                <span class="brand-mark" aria-hidden="true">A</span>
                <span>
                    <strong>Alchemy</strong>
                    <small>Classroom</small>
                </span>
            </a>
        </header>

        <main>
            <section class="launch-grid" aria-labelledby="page-title">
                <div class="launch-panel">
                    <div class="eyebrow">Classroom launcher</div>
                    <h1 id="page-title">Start a new online lesson.</h1>
                    <p class="intro">
                        Create a fresh classroom and enter directly as the tutor.
                    </p>

                    @if ($errors->has('meeting'))
                        <div class="alert" role="alert">{{ $errors->first('meeting') }}</div>
                    @endif

                    <form method="POST" action="{{ route('classroom.join') }}" data-join-form>
                        @csrf
                        <button class="join-button" type="submit" data-join-button>
                            <span>Join Meeting</span>
                            <span class="button-arrow" aria-hidden="true">→</span>
                        </button>
                    </form>
                </div>

                <aside class="lesson-card" aria-labelledby="lesson-title">
                    <div class="lesson-heading">
                        <span class="lesson-label">Before class</span>
                    </div>
                    <h2 id="lesson-title">Set the lesson up for a great start.</h2>

                    <ol class="steps">
                        <li>
                            <span class="step-number">01</span>
                            <span><strong>Find a quiet spot</strong><small>A calm start helps everyone focus.</small></span>
                        </li>
                        <li>
                            <span class="step-number">02</span>
                            <span><strong>Bring your materials</strong><small>Keep notes and worksheets within reach.</small></span>
                        </li>
                        <li>
                            <span class="step-number">03</span>
                            <span><strong>Welcome your learner</strong><small>Begin with one clear goal for the lesson.</small></span>
                        </li>
                    </ol>

                    <div class="resources-tip">
                        <span class="tip-tab">During class</span>
                        <p><strong>Resources are nearby.</strong> Inside the classroom, open the Options menu and choose Resources.</p>
                    </div>
                </aside>
            </section>
        </main>
    </div>
</body>
</html>
