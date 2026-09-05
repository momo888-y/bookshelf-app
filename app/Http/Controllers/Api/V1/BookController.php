<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\BookIndexRequest;
use App\Http\Requests\Api\StoreBookRequest;
use App\Http\Requests\Api\UpdateBookRequest;
use App\Http\Resources\Api\BookListResource;
use App\Http\Resources\Api\BookResource;
use App\Models\Book;

class BookController extends Controller
{
    public function index(BookIndexRequest $request)
    {
        $perPage = $request->input('per_page', 20);

        $books = Book::query()
            ->with('genres')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->when($request->filled('keyword'), function ($query) use ($request) {
                $keyword = $request->input('keyword');
                $query->where(function ($q) use ($keyword) {
                    $q->where('title', 'like', "%{$keyword}%")
                        ->orWhere('author', 'like', "%{$keyword}%");
                });
            })
            ->when($request->filled('genre_id'), function ($query) use ($request) {
                $query->whereHas('genres', function ($q) use ($request) {
                    $q->where('genres.id', $request->input('genre_id'));
                });
            })
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->appends($request->query());

        return BookListResource::collection($books);
    }

    public function store(StoreBookRequest $request)
    {
        $validated = $request->validated();
        $genres = $validated['genres'];
        unset($validated['genres']);

        $book = Book::create($validated);
        $book->genres()->sync($genres);

        $book->load(['genres', 'reviews.user']);

        return (new BookResource($book))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Book $book)
    {
        $book->load(['genres', 'reviews.user']);

        return new BookResource($book);
    }

    public function update(UpdateBookRequest $request, Book $book)
    {
        $validated = $request->validated();
        $genres = $validated['genres'];
        unset($validated['genres']);

        $book->update($validated);
        $book->genres()->sync($genres);

        $book->load(['genres', 'reviews.user']);

        return new BookResource($book);
    }

    public function destroy(Book $book)
    {
        $book->delete();

        return response()->noContent();
    }
}
