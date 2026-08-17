<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBucketListItemRequest;
use App\Models\BucketListItem;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BucketListController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', BucketListItem::class);

        $user = $request->user();

        $query = BucketListItem::query()
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere(function ($query) use ($user) {
                        $query->where('partner_id', $user->id)
                            ->whereIn('user_id', $user->connectedPartnerIds());
                    });
            })
            ->with('user');

        if ($request->filled('status') && in_array($request->status, [BucketListItem::PLANNED, BucketListItem::COMPLETED])) {
            $query->where('status', $request->status);
        }

        $items = $query->orderByDesc('completed_at')->orderByDesc('created_at')->get();

        $partners = $user->connectedPartners()->sortBy('name')->values();

        return view('bucket-list.index', compact('items', 'partners'));
    }

    public function store(StoreBucketListItemRequest $request): RedirectResponse
    {
        $user = $request->user();
        $partner = User::query()->findOrFail($request->validated('partner_id'));

        $this->authorize('create', [BucketListItem::class, $partner]);

        $user->bucketListItems()->create([
            'partner_id' => $partner->id,
            'title' => $request->title,
            'description' => $request->description,
            'status' => BucketListItem::PLANNED,
        ]);

        return redirect()->route('bucket-list.index')
            ->with('success', 'Bucket list item added — let\'s make it happen!');
    }

    public function toggle(Request $request, BucketListItem $bucketListItem): RedirectResponse
    {
        $this->authorize('toggle', $bucketListItem);

        $isCompleted = $bucketListItem->status === BucketListItem::COMPLETED;

        $bucketListItem->update([
            'status' => $isCompleted ? BucketListItem::PLANNED : BucketListItem::COMPLETED,
            'completed_at' => $isCompleted ? null : now(),
        ]);

        return redirect()->route('bucket-list.index')
            ->with('success', $isCompleted ? 'Marked as planned again.' : 'Nice! You completed a bucket list item.');
    }

    public function destroy(Request $request, BucketListItem $bucketListItem): RedirectResponse
    {
        $this->authorize('delete', $bucketListItem);

        $bucketListItem->delete();

        return redirect()->route('bucket-list.index')
            ->with('success', 'Bucket list item removed.');
    }
}
