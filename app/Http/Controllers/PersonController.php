<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\Request;

class PersonController extends Controller
{
    public function index()
    {
        $people = Person::latest()->paginate(12);

        return view('people.index', compact('people'));
    }

    public function create()
    {
        return view('people.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'descriptor' => [
                'required',
                'json',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $descriptor = json_decode($value, true);

                    if (
                        ! is_array($descriptor)
                        || count($descriptor) !== 128
                        || count(array_filter(
                            $descriptor,
                            fn (mixed $value): bool => is_int($value) || is_float($value)
                        )) !== 128
                    ) {
                        $fail('The descriptor must contain exactly 128 floating-point values.');
                    }
                },
            ],
            'photo' => 'nullable|string', // base64
        ]);

        $photoPath = null;
        if (! empty($validated['photo']) && str_starts_with($validated['photo'], 'data:image')) {
            $data = explode(',', $validated['photo'])[1];
            $filename = 'people/'.uniqid().'.jpg';
            \Storage::disk('public')->put($filename, base64_decode($data));
            $photoPath = $filename;
        }

        Person::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'photo' => $photoPath,
            'descriptor' => json_decode($validated['descriptor'], true, 512, JSON_THROW_ON_ERROR),
        ]);

        return response()->json(['success' => true, 'message' => 'Person registered!']);
    }

    public function destroy(Person $person)
    {
        if ($person->photo) {
            \Storage::disk('public')->delete($person->photo);
        }
        $person->delete();

        return back()->with('success', 'Deleted');
    }

    // Returns all descriptors to client for matching
    public function descriptors()
    {
        return response()->json(
            Person::select('id', 'name', 'descriptor', 'photo')->get()
                ->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'descriptor' => $p->descriptor,
                    'photo' => $p->photo ? asset('storage/'.$p->photo) : null,
                ])
        );
    }
}
