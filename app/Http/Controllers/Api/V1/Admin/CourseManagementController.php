<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Core\Course;
use App\Models\Core\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CourseManagementController extends Controller
{
    /**
     * Display a listing of all courses for admin.
     */
    public function index()
    {
        $courses = auth()->user()->courses()->with('lessons.attachments')->paginate(15);
        return response()->json([
            'success' => true,
            'data' => $courses,
        ]);
    }

    /**
     * Store a newly created course.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store('courses', 'public');
        }

        $validated['user_id'] = auth()->id();
        $validated['slug'] = Str::slug($validated['title']) . '-' . uniqid();
        $validated['is_published'] = false;

        $course = auth()->user()->courses()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Course created successfully',
            'data' => $course,
        ], 201);
    }

    /**
     * Display the specified course with lessons and attachments.
     */
    public function show($id)
    {
        $course = Course::with('lessons.attachments')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $course,
        ]);
    }

    /**
     * Update the specified course.
     */
    public function update(Request $request, $id)
    {
        $course = Course::where('user_id', auth()->id())->findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_published' => 'sometimes|boolean',
        ]);

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store('courses', 'public');
        }

        $course->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Course updated successfully',
            'data' => $course,
        ]);
    }

    /**
     * Delete the specified course.
     */
    public function destroy($id)
    {
        $course = Course::where('user_id', auth()->id())->findOrFail($id);
        $course->delete();

        return response()->json([
            'success' => true,
            'message' => 'Course deleted successfully',
        ]);
    }

    /**
     * Reorder courses.
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'courses' => 'required|array',
            'courses.*.id' => 'required|integer',
            'courses.*.display_order' => 'required|integer',
        ]);

        foreach ($validated['courses'] as $courseData) {
            Course::where('user_id', auth()->id())
                ->where('id', $courseData['id'])
                ->update(['display_order' => $courseData['display_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Courses reordered successfully',
        ]);
    }
}
