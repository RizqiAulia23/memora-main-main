<?php

namespace Database\Factories;

use App\Models\Connection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Connection>
 */
class ConnectionFactory extends Factory
{
    protected $model = Connection::class;

    public function definition(): array
    {
        return [
            'sender_id' => User::factory(),
            'receiver_id' => User::factory(),
            'status' => Connection::PENDING,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Connection::PENDING,
        ]);
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Connection::ACCEPTED,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Connection::REJECTED,
        ]);
    }
}
