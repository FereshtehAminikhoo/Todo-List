<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTodoRequest;
use App\Http\Requests\UpdateTodoRequest;
use App\Repositories\TodoRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TodoController extends Controller
{
    protected $todoRepository;

    public function __construct(TodoRepository $todoRepository)
    {
        $this->todoRepository = $todoRepository;
    }

    public function index()
    {
        $todos = $this->todoRepository->getUserTodos(Auth::id());
        return Inertia::render('Todos/Index', ['todos' => $todos]);
    }

    public function create()
    {
        return Inertia::render('Todos/Create');
    }

    public function store(StoreTodoRequest $request): RedirectResponse
    {
        $this->todoRepository->create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return redirect()->route('todos.index')->with('success', 'کار جدید با موفقیت ایجاد شد.');
    }

    public function edit($id)
    {
        $todo = $this->todoRepository->find($id);
        if (!$todo || $todo->user_id !== Auth::id()) {
            return redirect()->route('todos.index')->with('error', 'کار یافت نشد.');
        }
        return Inertia::render('Todos/Edit', ['todo' => $todo]);
    }

    public function update(UpdateTodoRequest $request, $id): RedirectResponse
    {
        $todo = $this->todoRepository->find($id);
        if (!$todo || $todo->user_id !== Auth::id()) {
            return redirect()->route('todos.index')->with('error', 'کار یافت نشد.');
        }

        $this->todoRepository->update($todo, $request->only('title', 'description'));
        return redirect()->route('todos.index')->with('success', 'کار با موفقیت به‌روزرسانی شد.');
    }

    public function destroy($id): RedirectResponse
    {
        $todo = $this->todoRepository->find($id);
        if (!$todo || $todo->user_id !== Auth::id()) {
            return redirect()->route('todos.index')->with('error', 'کار یافت نشد.');
        }

        $this->todoRepository->delete($todo);
        return redirect()->route('todos.index')->with('success', 'کار با موفقیت حذف شد.');
    }

    public function complete($id): RedirectResponse
    {
        $todo = $this->todoRepository->find($id);
        if (!$todo || $todo->user_id !== Auth::id()) {
            return redirect()->route('todos.index')->with('error', 'کار یافت نشد.');
        }

        $this->todoRepository->markAsCompleted($todo);
        return redirect()->route('todos.index')->with('success', 'کار با موفقیت تکمیل شد.');
    }

    public function history($id)
    {
        $todo = $this->todoRepository->find($id);
        if (!$todo || $todo->user_id !== Auth::id()) {
            return redirect()->route('todos.index')->with('error', 'کار یافت نشد.');
        }

        $history = $this->todoRepository->getHistory($todo);
        return Inertia::render('Todos/History', ['todo' => $todo, 'history' => $history]);
    }
}