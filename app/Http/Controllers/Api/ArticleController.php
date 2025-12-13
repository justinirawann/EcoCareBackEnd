<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ArticleController extends Controller
{
    /**
     * Get all articles with pagination.
     */
    public function index(): JsonResponse
    {
        $articles = Article::with('user')
            ->latest('published_at')
            ->paginate(10);

        return response()->json($articles);
    }

    /**
     * Get a single article by ID.
     */
    public function show(Article $article): JsonResponse
    {
        $article->load('user');
        return response()->json($article);
    }

    /**
     * Create a new article.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'content' => 'required|string',
            'featured_image' => 'nullable|string',
            'published_at' => 'nullable|date_format:Y-m-d H:i:s',
        ]);

        $validated['user_id'] = auth()->id();

        $article = Article::create($validated);

        return response()->json([
            'message' => 'Article created successfully',
            'data' => $article->load('user')
        ], 201);
    }

    /**
     * Update an article.
     */
    public function update(Request $request, Article $article): JsonResponse
    {
        // Check if user is the author or admin
        if (auth()->id() !== $article->user_id && !auth()->user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'content' => 'required|string',
            'featured_image' => 'nullable|string',
            'published_at' => 'nullable|date_format:Y-m-d H:i:s',
        ]);

        $article->update($validated);

        return response()->json([
            'message' => 'Article updated successfully',
            'data' => $article->load('user')
        ]);
    }

    /**
     * Delete an article.
     */
    public function destroy(Article $article): JsonResponse
    {
        // Check if user is the author or admin
        if (auth()->id() !== $article->user_id && !auth()->user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $article->delete();

        return response()->json(['message' => 'Article deleted successfully']);
    }
}
