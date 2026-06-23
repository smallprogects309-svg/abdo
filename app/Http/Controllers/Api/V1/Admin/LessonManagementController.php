<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Core\Course;
use App\Models\Core\Lesson;
use App\Models\Core\Attachment;
use Illuminate\Http\Request;

class LessonManagementController extends Controller
{
    /**
     * Get all lessons for a course.
     */
    public function indexByCourse($courseId)
    {
        $course = Course::where('user_id', auth()->id())->findOrFail($courseId);
        $lessons = $course->lessons()->with('attachments')->get();

        return response()->json([
            'success' => true,
            'data' => $lessons,
        ]);
    }

    /**
     * Store a newly created lesson with file uploads.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|integer|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'youtube_id' => 'required|string|max:255',
            'duration_minutes' => 'nullable|integer|min:1',
            'fake_end_offset_seconds' => 'nullable|integer|min:1|max:600',
        ]);

        // Verify ownership
        $course = Course::where('user_id', auth()->id())->findOrFail($validated['course_id']);

        // Set defaults
        $validated['fake_end_offset_seconds'] = $validated['fake_end_offset_seconds'] ?? 60;
        $validated['order_position'] = $course->lessons()->count() + 1;

        $lesson = $course->lessons()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Lesson created successfully',
            'data' => $lesson,
        ], 201);
    }

    /**
     * Display the specified lesson with attachments.
     */
    public function show($id)
    {
        $lesson = Lesson::with('attachments', 'course')
            ->whereHas('course', function($query) {
                $query->where('user_id', auth()->id());
            })
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $lesson,
        ]);
    }

    /**
     * Update the specified lesson.
     */
    public function update(Request $request, $id)
    {
        $lesson = Lesson::whereHas('course', function($query) {
            $query->where('user_id', auth()->id());
        })->findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'youtube_id' => 'sometimes|required|string|max:255',
            'duration_minutes' => 'nullable|integer|min:1',
            'fake_end_offset_seconds' => 'nullable|integer|min:1|max:600',
            'is_published' => 'nullable|boolean',
        ]);

        $lesson->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Lesson updated successfully',
            'data' => $lesson,
        ]);
    }

    /**
     * Delete the specified lesson.
     */
    public function destroy($id)
    {
        $lesson = Lesson::whereHas('course', function($query) {
            $query->where('user_id', auth()->id());
        })->findOrFail($id);

        $lesson->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lesson deleted successfully',
        ]);
    }

    /**
     * Upload attachments for a lesson.
     */
    public function uploadAttachments(Request $request)
    {
        $validated = $request->validate([
            'lesson_id' => 'required|integer|exists:lessons,id',
            'files' => 'required|array|max:10',
            'files.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip|max:10240',
        ]);

        $lesson = Lesson::whereHas('course', function($query) {
            $query->where('user_id', auth()->id());
        })->findOrFail($validated['lesson_id']);

        $attachments = [];
        $orderPosition = $lesson->attachments()->count() + 1;

        foreach ($request->file('files') as $file) {
            $path = $file->store("lessons/{$lesson->id}", 'public');
            
            $attachment = $lesson->attachments()->create([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
                'order_position' => $orderPosition++,
            ]);

            $attachments[] = $attachment;
        }

        return response()->json([
            'success' => true,
            'message' => 'Files uploaded successfully',
            'data' => $attachments,
        ], 201);
    }

    /**
     * Delete an attachment.
     */
    public function deleteAttachment($id)
    {
        $attachment = Attachment::whereHas('lesson.course', function($query) {
            $query->where('user_id', auth()->id());
        })->findOrFail($id);

        $attachment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Attachment deleted successfully',
        ]);
    }

    /**
     * Reorder lessons in a course.
     */
    public function reorderLessons(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|integer|exists:courses,id',
            'lessons' => 'required|array',
            'lessons.*.id' => 'required|integer',
            'lessons.*.order_position' => 'required|integer',
        ]);

        $course = Course::where('user_id', auth()->id())->findOrFail($validated['course_id']);

        foreach ($validated['lessons'] as $lessonData) {
            Lesson::where('course_id', $course->id)
                ->where('id', $lessonData['id'])
                ->update(['order_position' => $lessonData['order_position']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lessons reordered successfully',
        ]);
    }
}
