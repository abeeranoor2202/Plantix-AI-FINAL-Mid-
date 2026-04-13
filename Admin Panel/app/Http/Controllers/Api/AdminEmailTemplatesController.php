<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmailTemplate;
use Illuminate\Support\Str;

class AdminEmailTemplatesController extends Controller
{
    /**
     * Get all email templates
     */
    public function index(Request $request)
    {
        try {
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 10);
            $search = $request->get('search', '');

            $query = EmailTemplate::query();

            if ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', "%$search%")
                        ->orWhere('type', 'like', "%$search%")
                        ->orWhere('email_type', 'like', "%$search%")
                        ->orWhere('subject', 'like', "%$search%");
                });
            }

            $total = $query->count();
            $templates = $query->orderBy('created_at', 'desc')
                              ->skip(($page - 1) * $limit)
                              ->take($limit)
                              ->get()
                              ->map(function ($template) {
                                  return [
                                      'id' => $template->id,
                                      'name' => $template->name,
                                      'type' => $template->type,
                                      'email_type' => $template->email_type,
                                      'subject' => $template->subject,
                                      'body' => substr((string) $template->body, 0, 100) . '...',
                                      'variables' => $template->variables,
                                      'is_send_to_admin' => $template->is_send_to_admin ?? false,
                                      'created_at' => $template->created_at,
                                  ];
                              });

            return response()->json([
                'success' => true,
                'data' => $templates,
                'meta' => [
                    'total' => $total,
                    'page' => $page,
                    'pages' => ceil($total / $limit)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching templates: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single template
     */
    public function show($id)
    {
        try {
            $template = EmailTemplate::find($id);
            
            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $template->id,
                    'name' => $template->name,
                    'type' => $template->type,
                    'email_type' => $template->email_type,
                    'subject' => $template->subject,
                    'body' => $template->body,
                    'variables' => $template->variables,
                    'is_send_to_admin' => $template->is_send_to_admin ?? false,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create template
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:150',
                'type' => 'nullable|string|max:255',
                'email_type' => 'required|in:system,appointment,forum,order',
                'subject' => 'required|string|max:255',
                'body' => 'required|string',
                'variables' => 'nullable|string',
                'is_send_to_admin' => 'nullable|boolean',
            ]);

            $validated['type'] = $validated['type'] ?: Str::slug($validated['name']);

            $template = EmailTemplate::create([
                'name' => $validated['name'],
                'type' => $validated['type'],
                'email_type' => $validated['email_type'],
                'subject' => $validated['subject'],
                'body' => $validated['body'],
                'variables' => $validated['variables'] ?? null,
                'is_send_to_admin' => $validated['is_send_to_admin'] ?? false,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $template->id,
                    'name' => $template->name,
                    'type' => $template->type,
                    'email_type' => $template->email_type,
                    'subject' => $template->subject,
                    'body' => $template->body,
                    'variables' => $template->variables,
                    'is_send_to_admin' => $template->is_send_to_admin,
                ]
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update template
     */
    public function update(Request $request, $id)
    {
        try {
            $template = EmailTemplate::find($id);
            
            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template not found'
                ], 404);
            }

            $validated = $request->validate([
                'name' => 'nullable|string|max:150',
                'email_type' => 'nullable|in:system,appointment,forum,order',
                'subject' => 'nullable|string|max:255',
                'body' => 'nullable|string',
                'variables' => 'nullable|string',
                'is_send_to_admin' => 'nullable|boolean',
            ]);

            if (isset($validated['name'])) {
                $template->name = $validated['name'];
            }
            if (isset($validated['email_type'])) {
                $template->email_type = $validated['email_type'];
            }
            if (isset($validated['subject'])) {
                $template->subject = $validated['subject'];
            }
            if (array_key_exists('body', $validated)) {
                $template->body = $validated['body'];
            }
            if (array_key_exists('variables', $validated)) {
                $template->variables = $validated['variables'];
            }
            if (isset($validated['is_send_to_admin'])) {
                $template->is_send_to_admin = $validated['is_send_to_admin'];
            }

            $template->save();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $template->id,
                    'name' => $template->name,
                    'type' => $template->type,
                    'email_type' => $template->email_type,
                    'subject' => $template->subject,
                    'body' => $template->body,
                    'variables' => $template->variables,
                    'is_send_to_admin' => $template->is_send_to_admin,
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete template
     */
    public function destroy($id)
    {
        try {
            $template = EmailTemplate::find($id);
            
            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template not found'
                ], 404);
            }

            $template->delete();

            return response()->json([
                'success' => true,
                'message' => 'Template deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting template: ' . $e->getMessage()
            ], 500);
        }
    }
}
