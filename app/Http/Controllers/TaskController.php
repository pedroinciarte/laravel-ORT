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

}
