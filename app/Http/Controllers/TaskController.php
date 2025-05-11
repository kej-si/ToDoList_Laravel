<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Models\TodoAttribute;
use Illuminate\Support\Facades\Log;
class TaskController extends Controller
{
    /**
     * Display a listing of the tasks.
     */
    public function index(): JsonResponse
    {
        $tasks = Task::where('user_id', Auth::id())->get();
        return response()->json(['data' => $tasks]);
    }

    /**
     * Show the form for creating a new task.
     */
    public function create()
    {
        return view('tasks.create');
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
        ]);

        $task = new Task($validated);
        $task->user_id = Auth::id();
        $task->status = 'Not Done';
        $task->save();

        return response()->json([
            'message' => 'Task created successfully',
            'data' => $task
        ], 201);
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task): JsonResponse
    {
        $this->authorize('view', $task);
        return response()->json(['data' => $task]);
    }

    /**
     * Show the form for editing the specified task.
     */
    public function edit(Task $task)
    {
        $this->authorize('update', $task);
        return view('tasks.edit', compact('task'));
    }

    /**
     * Update the specified task in storage.
     */
    public function update(Request $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'status' => 'required|in:Done,Not Done',
        ]);

        $task->update($validated);

        return response()->json([
            'message' => 'Task updated successfully',
            'data' => $task
        ]);
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);
        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully'
        ]);
    }

    /**
     * Toggle the status of the specified task.
     */
    public function toggleStatus(Task $task): JsonResponse
    {
        $this->authorize('update', $task);
        
        $task->status = $task->status === 'Done' ? 'Not Done' : 'Done';
        $task->save();

        return response()->json([
            'message' => 'Task status updated successfully',
            'data' => $task
        ]);
    }

    /**
     * Add an attribute to the specified task.
     */
    public function addAttribute(Request $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $task = Task::findOrFail($task->id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'value' => 'required|string|max:255',
        ]);

        $attribute = new TodoAttribute([
            'name' => $validated['name'],
            'value' => $validated['value'],
            'task_id' => $task->id,
        ]);

        $task->attributes()->save($attribute);

        return response()->json($attribute, 201);
    }


    /**
     * List all tasks with their attributes.
     */
    public function getTaskWithAttributes(): JsonResponse
    {
        $tasks = Task::with('attributes')
            ->where('user_id', Auth::id())
            ->get();
    
        return response()->json(['data' => $tasks]);
    }
    
    /**
     * Get the attributes for the specified task.
     */
    public function getTaskAttributes(Task $task): JsonResponse
    {
        $attributes = $task->attributes;
        return response()->json(['data' => $attributes]);
    }

    public function test(): JsonResponse
    {
        return response()->json(['data' => 'test']);
    }


}
