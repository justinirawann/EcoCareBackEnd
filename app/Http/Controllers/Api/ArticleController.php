<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

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
            'featured_image' => 'nullable|image|max:2048',
            'published_at' => 'nullable|date',
        ]);

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('featured_image')) {
            $imagePath = $request->file('featured_image')->store('articles', 'public');
        }

        $article = Article::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'content' => $validated['content'],
            'featured_image' => $imagePath,
            'published_at' => $validated['published_at'],
            'user_id' => auth()->id(),
        ]);

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
        $user = auth()->user();
        $isAdmin = $user->roles()->where('slug', 'admin')->exists();
        if ($user->id !== $article->user_id && !$isAdmin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|max:2048',
            'published_at' => 'nullable|date',
        ]);

        // Handle image upload
        $imagePath = $article->featured_image; // Keep existing image
        if ($request->hasFile('featured_image')) {
            // Delete old image if exists
            if ($article->featured_image) {
                \Storage::disk('public')->delete($article->featured_image);
            }
            $imagePath = $request->file('featured_image')->store('articles', 'public');
        }

        $article->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'content' => $validated['content'],
            'featured_image' => $imagePath,
            'published_at' => $validated['published_at'],
        ]);

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
        $user = auth()->user();
        $isAdmin = $user->roles()->where('slug', 'admin')->exists();
        if ($user->id !== $article->user_id && !$isAdmin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $article->delete();

        return response()->json(['message' => 'Article deleted successfully']);
    }
}
