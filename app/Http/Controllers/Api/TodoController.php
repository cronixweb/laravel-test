<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTodoRequest;
use App\Http\Requests\UpdateTodoRequest;
use App\Http\Resources\TodoResource;
use App\Enums\TodoStatus;
use App\Models\Category;
use App\Models\Todo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;

class TodoController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $todos = Todo::with('category')->latest()->paginate();

        return TodoResource::collection($todos);
    }

    public function store(StoreTodoRequest $request): JsonResponse
    {
        $data = $request->validated();

        $category = $this->resolveCategory($data['category_name']);

        $todo = Todo::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? TodoStatus::Pending->value,
            'category_id' => $category->id,
        ]);

        $todo->load('category');

        return TodoResource::make($todo)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Todo $todo): TodoResource
    {
        $todo->load('category');

        return TodoResource::make($todo);
    }

    public function update(UpdateTodoRequest $request, Todo $todo): TodoResource
    {
        $data = $request->validated();

        if (array_key_exists('category_name', $data)) {
            $category = $this->resolveCategory($data['category_name']);
            $todo->category()->associate($category);
        }

        $todo->fill(Arr::only($data, ['title', 'description', 'status']));
        $todo->save();

        $todo->load('category');

        return TodoResource::make($todo);
    }

    public function destroy(Todo $todo): Response
    {
        $todo->delete();

        return response()->noContent();
    }

    protected function resolveCategory(string $categoryName): Category
    {
        $normalized = trim($categoryName);

        return Category::firstOrCreate(['name' => $normalized]);
    }
}
