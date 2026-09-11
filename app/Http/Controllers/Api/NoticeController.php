<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NoticeResource;
use App\Models\Notice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NoticeController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:open,in_result,closed,cancelled'],
            'current' => ['nullable', 'boolean'],
        ]);

        $notices = Notice::query()->publiclyVisible()
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->with('items.lot')
            ->orderByRaw("CASE status WHEN 'open' THEN 0 WHEN 'in_result' THEN 1 ELSE 2 END")
            ->orderByDesc('proposal_deadline')
            ->get()
            ->when(
                array_key_exists('current', $validated),
                fn ($items) => $items->filter(fn (Notice $notice) => $notice->isCurrentlyOpen() === (bool) $validated['current'])->values()
            );

        return NoticeResource::collection($notices)->additional([
            'meta' => [
                'count' => $notices->count(),
                'current_count' => $notices->filter->isCurrentlyOpen()->count(),
            ],
        ]);
    }

    public function show(Notice $notice): NoticeResource
    {
        abort_unless($notice->public_visible && $notice->status !== 'draft', 404);

        return new NoticeResource($notice->load('items.lot'));
    }
}
