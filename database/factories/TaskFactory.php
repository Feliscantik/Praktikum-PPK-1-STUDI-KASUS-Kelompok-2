<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'task_list_id' => TaskList::factory(),
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'priority' => fake()->randomElement(Task::PRIORITIES),
            'due_date' => fake()->dateTimeBetween('-3 days', '+2 weeks'),
            'is_completed' => false,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'is_completed' => true,
            'completed_at' => now(),
        ]);
    }
}
