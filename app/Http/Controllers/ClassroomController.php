<?php

namespace App\Http\Controllers;

use App\Exceptions\BigBlueButtonException;
use App\Services\BigBlueButtonService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class ClassroomController extends Controller
{
    public function show(): View
    {
        return view('classroom');
    }

    public function join(BigBlueButtonService $bigBlueButton): RedirectResponse
    {
        $meetingId = 'alchemy_trial_'.Str::uuid()->toString();

        try {
            $bigBlueButton->createMeeting($meetingId);

            return redirect()->away(
                $bigBlueButton->generateJoinUrl($meetingId),
            );
        } catch (BigBlueButtonException) {
            return back()->withErrors([
                'meeting' => 'Unable to create the classroom. Please check the application logs and BBB configuration.',
            ]);
        }
    }
}
