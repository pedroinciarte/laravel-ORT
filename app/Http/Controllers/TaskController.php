<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
class TaskController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
        ]);
    
        $task = $request->user()->tasks()->create([
            ...$validated,
            'state' => 'pending',
        ]);
    
        return response()->json([
            'message' => 'Task created successfully.',
            'task' => $task,
        ], 201);
    }
    
    public function getAll(Request $request)
    {
        $tasks = $request->user()
            ->tasks()
            ->select('id', 'title', 'description', 'state', 'due_date')
            ->latest()
            ->get();
    
        return response()->json([
            'tasks' => $tasks,
        ]);
    }

    public function getTask(Request $request, Task $task)
    {
        if ($task->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return response()->json([
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'state' => $task->state,
                'due_date' => $task->due_date,
            ]
        ]);
    }

}
