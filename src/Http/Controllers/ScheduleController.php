<?php

namespace Ambulatory\Ambulatory\Http\Controllers;

use Ambulatory\Ambulatory\Schedule;
use Ambulatory\Ambulatory\Http\Requests\ScheduleRequest;
use Ambulatory\Ambulatory\Http\Middleware\VerifiedDoctor;

class ScheduleController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(VerifiedDoctor::class);
    }

    /**
     * Display a listing of the schedules.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $schedules = Schedule::with('healthFacility')
            ->where('doctor_id', auth('ambulatory')->user()->doctorProfile->id)
            ->latest()
            ->paginate(25);

        return response()->json([
            'entries' => $schedules,
        ]);
    }

    /**
     * Store a newly created schedule in storage.
     *
     * @param  ScheduleRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ScheduleRequest $request)
    {
        Schedule::create($request->validatedFields());

        return response()->json([
            'message' => 'Schedule successfully created!',
        ], 200);
    }

    /**
     * Display the specified schedule.
     *
     * @param  Schedule  $schedule
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Schedule $schedule)
    {
        $this->authorize('manage', $schedule);

        return response()->json([
            'entry' => $schedule->load('healthFacility'),
        ]);
    }

    /**
     * Update the specified schedule in storage.
     *
     * @param  ScheduleRequest  $request
     * @param  Schedule  $schedule
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ScheduleRequest $request, Schedule $schedule)
    {
        $this->authorize('manage', $schedule);

        $schedule->update($request->validatedFields());

        return response()->json([
            'message' => 'Schedule successfully updated!',
        ], 200);
    }
}
