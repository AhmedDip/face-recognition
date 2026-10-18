<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaceController extends Controller
{
    public function recognize()
    {
        return view('recognize');
    }

    public function match(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'descriptor' => ['required', 'array', 'size:128'],
            'descriptor.*' => ['required', 'numeric'],
        ]);

        $descriptor = array_map('floatval', $validated['descriptor']);
        $threshold = (float) config('services.face_recognition.threshold', 0.5);
        $bestMatch = null;
        $bestDistance = INF;

        Person::query()
            ->select(['id', 'name', 'email', 'photo', 'descriptor'])
            ->each(function (Person $person) use ($descriptor, &$bestMatch, &$bestDistance): void {
                if (! is_array($person->descriptor) || count($person->descriptor) !== 128) {
                    return;
                }

                $distance = sqrt(array_sum(array_map(
                    fn (float|int $value, float $input): float => ($input - $value) ** 2,
                    $person->descriptor,
                    $descriptor,
                )));

                if ($distance < $bestDistance) {
                    $bestDistance = $distance;
                    $bestMatch = $person;
                }
            });

        if ($bestMatch === null || $bestDistance > $threshold) {
            return response()->json([
                'matched' => false,
                'person' => null,
                'distance' => is_finite($bestDistance) ? $bestDistance : null,
                'threshold' => $threshold,
            ]);
        }

        return response()->json([
            'matched' => true,
            'person' => [
                'id' => $bestMatch->id,
                'name' => $bestMatch->name,
                'email' => $bestMatch->email,
                'photo' => $bestMatch->photo ? asset('storage/'.$bestMatch->photo) : null,
            ],
            'distance' => $bestDistance,
            'threshold' => $threshold,
        ]);
    }
}
