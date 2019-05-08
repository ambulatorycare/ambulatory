<?php

namespace Reliqui\Ambulatory\Http\Controllers;

use Illuminate\Support\Str;
use Reliqui\Ambulatory\MedicalForm;
use Reliqui\Ambulatory\Http\Requests\MedicalFormRequest;

class MedicalFormController
{
    /**
     * Get all medical forms.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = auth('ambulatory')->user();

        $entries = $user->medicalForms()->paginate(25);

        return response()->json([
            'entries' => $entries,
        ]);
    }

    /**
     * Show the medical form.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        if ($id === 'new') {
            return response()->json([
                'entry' => MedicalForm::make(['id' => Str::uuid()]),
            ]);
        }

        $entry = MedicalForm::findOrFail($id);

        return response()->json([
            'entry' => $entry,
        ]);
    }

    /**
     * Store the medical form.
     *
     * @param MedicalFormRequest $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(MedicalFormRequest $request, $id)
    {
        $entry = $id !== 'new'
            ? MedicalForm::findOrFail($id)
            : new MedicalForm(['id' => $request->validatedFields(['id'])]);

        $entry->fill($request->validatedFields());
        $entry->save();

        return response()->json([
            'entry' => $entry,
        ]);
    }
}
