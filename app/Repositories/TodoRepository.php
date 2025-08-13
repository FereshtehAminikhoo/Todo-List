<?php

namespace App\Repositories;

use App\Models\Todo;
use App\Models\TodoHistory;
use Illuminate\Database\Eloquent\Collection;

class TodoRepository
{
    public function getUserTodos(int $userId): Collection
    {
        return Todo::where('user_id', $userId)->get();
    }

    public function find(int $id): ?Todo
    {
        return Todo::find($id);
    }

    public function create(array $data): Todo
    {
        $todo = Todo::create($data);
        $this->logHistory($todo, 'created', null);
        return $todo;
    }

    public function update(Todo $todo, array $data): Todo
    {
        $changes = $this->getChanges($todo, $data);
        $todo->update($data);
        $this->logHistory($todo, 'updated', $changes);
        return $todo;
    }

    public function delete(Todo $todo): void
    {
        $this->logHistory($todo, 'deleted', null);
        $todo->delete();
    }

    public function markAsCompleted(Todo $todo): Todo
    {
        $todo->update(['is_completed' => true]);
        $this->logHistory($todo, 'completed', ['is_completed' => true]);
        return $todo;
    }

    public function getHistory(Todo $todo): Collection
    {
        return $todo->histories()->get();
    }

    private function logHistory(Todo $todo, string $action, ?array $changes): void
    {
        TodoHistory::create([
            'todo_id' => $todo->id,
            'action' => $action,
            'changes' => $changes ? json_encode($changes) : null,
            'action_at' => now(),
        ]);
    }

    private function getChanges(Todo $todo, array $data): array
    {
        $changes = [];
        foreach ($data as $key => $value) {
            if ($todo->$key != $value) {
                $changes[$key] = ['old' => $todo->$key, 'new' => $value];
            }
        }
        return $changes;
    }
}