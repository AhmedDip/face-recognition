<?php

namespace Tests\Feature;

use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecognizeFaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_the_closest_registered_person(): void
    {
        $descriptor = array_fill(0, 128, 0.1);

        $person = Person::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'descriptor' => $descriptor,
        ]);

        $response = $this->postJson('/api/recognize', [
            'descriptor' => array_fill(0, 128, 0.1),
        ]);

        $response->assertOk()
            ->assertJsonPath('matched', true)
            ->assertJsonPath('person.id', $person->id)
            ->assertJsonPath('person.name', 'Jane Doe')
            ->assertJsonPath('distance', 0);
    }

    public function test_it_returns_no_match_when_the_closest_face_is_outside_the_threshold(): void
    {
        Person::create([
            'name' => 'Jane Doe',
            'descriptor' => array_fill(0, 128, 0.1),
        ]);

        $response = $this->postJson('/api/recognize', [
            'descriptor' => array_fill(0, 128, 1.0),
        ]);

        $response->assertOk()
            ->assertJsonPath('matched', false)
            ->assertJsonPath('person', null);
    }

    public function test_it_rejects_descriptors_with_the_wrong_shape(): void
    {
        $response = $this->postJson('/api/recognize', [
            'descriptor' => [0.1, 0.2],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['descriptor']);
    }
}
