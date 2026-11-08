<?php

namespace Database\Factories;

use App\Models\Seminar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Seminar>
 */
class SeminarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'judul' => fake()->sentence(4),
            'pemapar' => fake()->name(),
            'deskripsi' => fake()->realTextBetween(30, 120),
            'flyer' => 'flyers/placeholder.svg',
            'lokasi' => fake()->city(),
            'tanggal' => fake()->dateTimeBetween('+1 day', '+30 days')->format('Y-m-d'),
            'waktu_mulai' => '09:00:00',
            'waktu_selesai' => '11:00:00',
            'kuota' => fake()->numberBetween(2, 200),
            'status' => 'dibuka',
        ];
    }
}
