<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    public function index()
    {
        return view('tasks.index');
    }
    public function getTasks(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 5);
        $search = $request->input('search', '');
        $page = $request->input('page', 1);

        $query = Task::query();

        if ($search) {
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        $tasks = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $tasks->items(),
            'current_page' => $tasks->currentPage(),
            'last_page' => $tasks->lastPage(),
            'total' => $tasks->total(),
            'per_page' => $tasks->perPage(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'completed' => 'pending',
        ]);

        return response()->json(['success' => 'Task created successfully.', 'task' => $task]);
    }

    public function show($id): JsonResponse
    {
        $task = Task::findOrFail($id);
        return response()->json($task);
    }


    public function update(Request $request, $id): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $task = Task::findOrFail($id);
        $task->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return response()->json(['success' => 'Task updated successfully.', 'task' => $task]);
    }

    public function destroy($id): JsonResponse
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return response()->json(['success' => 'Task deleted successfully.']);
    }

    public function updateStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:pending,completed',
        ]);

        $task = Task::findOrFail($id);
        $task->update([
            'completed' => $request->status,
        ]);

        return response()->json([
            'success' => 'Status updated successfully.',
            'task' => $task
        ]);
    }

}
