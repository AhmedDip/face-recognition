@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-bold mb-4">Registered People ({{ $people->total() }})</h1>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    @forelse($people as $person)
        <div class="bg-white rounded shadow p-3">
            @if($person->photo)
                <img src="{{ asset('storage/' . $person->photo) }}" class="w-full h-40 object-cover rounded mb-2">
            @else
                <div class="w-full h-40 bg-gray-200 rounded mb-2 flex items-center justify-center">No photo</div>
            @endif
            <p class="font-semibold">{{ $person->name }}</p>
            <p class="text-sm text-gray-500">{{ $person->email }}</p>
            <form method="POST" action="{{ route('people.destroy', $person) }}" class="mt-2"
                  onsubmit="return confirm('Delete?')">
                @csrf @method('DELETE')
                <button class="text-red-600 text-sm">Delete</button>
            </form>
        </div>
    @empty
        <p class="col-span-4 text-gray-500">No people registered yet.</p>
    @endforelse
</div>

<div class="mt-6">{{ $people->links() }}</div>
@endsection
