<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    // GET /api/tasks
    public function index(\Illuminate\Http\Request $request)
    {
        $q = $request->user()->tasks();

        // FILTROS
        if ($request->filled('completed')) {
            $completed = filter_var($request->query('completed'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if (!is_null($completed)) {
                $q->where('completed', $completed);
            }
        }

        if ($title = $request->query('title')) {
            $q->where('title', 'like', '%' . $title . '%');
        }

        // Rango de due_date (YYYY-MM-DD o YYYY-MM-DDTHH:MM)
        $dueFrom = $request->query('due_from');
        $dueTo   = $request->query('due_to');
        if ($dueFrom && $dueTo) {
            $q->whereBetween('due_date', [$dueFrom, $dueTo]);
        } elseif ($dueFrom) {
            $q->where('due_date', '>=', $dueFrom);
        } elseif ($dueTo) {
            $q->where('due_date', '<=', $dueTo);
        }

        $createdFrom = $request->query('created_from');
        $createdTo   = $request->query('created_to');
        if ($createdFrom && $createdTo) {
            $q->whereBetween('created_at', [$createdFrom, $createdTo]);
        } elseif ($createdFrom) {
            $q->where('created_at', '>=', $createdFrom);
        } elseif ($createdTo) {
            $q->where('created_at', '<=', $createdTo);
        }

        $allowedSort = ['created_at', 'due_date'];
        $sort = in_array($request->query('sort'), $allowedSort, true) ? $request->query('sort') : 'created_at';
        $dir  = strtolower($request->query('dir', 'desc'));
        $dir  = in_array($dir, ['asc', 'desc'], true) ? $dir : 'desc';

        if ($sort === 'due_date') {
            // Nulls al final: primero ordenamos por si es null, luego por due_date
            $q->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END ' . ($dir === 'asc' ? 'ASC' : 'DESC'));
            $q->orderBy('due_date', $dir);
            $q->orderBy('created_at', 'desc'); // desempate
        } else {
            $q->orderBy($sort, $dir);
        }

        $perPage = max(1, min(100, (int) $request->query('per_page', 10)));
        return $q->paginate($perPage);
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'completed'   => 'boolean',
            'due_date'    => 'nullable|date',
        ]);

        $task = $request->user()->tasks()->create($data);

        return response()->json($task, 201);
    }
    public function show(Request $request, Task $task)
    {
        $this->authorizeTask($request, $task);
        return $task;
    }
    public function update(Request $request, Task $task)
    {
        $this->authorizeTask($request, $task);

        $data = $request->validate([
            'title'       => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'completed'   => 'boolean',
            'due_date'    => 'nullable|date',
        ]);

        $task->update($data);
        return $task;
    }
    public function destroy(Request $request, Task $task)
    {
        $this->authorizeTask($request, $task);
        $task->delete();
        return response()->noContent();
    }
    private function authorizeTask(Request $request, Task $task): void
    {
        abort_unless($task->user_id === $request->user()->id, 403, 'No autorizado');
    }
}
