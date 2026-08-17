<?php

namespace App\Services;

use App\Models\Connection;
use App\Models\ImportantDate;
use App\Models\LoveLetter;
use App\Models\SharedEvent;
use App\Models\SharedMemory;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class CoupleTimelineService
{
    /**
     * Chronological (newest first) feed of couple milestones: connection
     * accepted, shared memories, received love letters, important dates and
     * shared events — limited to the user's currently accepted partners.
     *
     * @return Collection<int, array{type: string, title: string, subtitle: string, icon: string, created_at: Carbon}>
     */
    public function feed(User $user): Collection
    {
        $partnerIds = $user->connectedPartnerIds();

        if ($partnerIds->isEmpty()) {
            return collect();
        }

        $items = collect();

        Connection::query()
            ->where(function ($query) use ($user, $partnerIds) {
                $query->where('sender_id', $user->id)->whereIn('receiver_id', $partnerIds);
            })
            ->orWhere(function ($query) use ($user, $partnerIds) {
                $query->where('receiver_id', $user->id)->whereIn('sender_id', $partnerIds);
            })
            ->where('status', Connection::ACCEPTED)
            ->with(['sender', 'receiver'])
            ->get()
            ->each(function (Connection $connection) use ($user, $items) {
                $partner = $connection->sender_id === $user->id ? $connection->receiver : $connection->sender;

                $items->push([
                    'type' => 'connection',
                    'title' => 'You and '.$partner->name.' connected',
                    'subtitle' => 'A new chapter began',
                    'icon' => 'fa-link',
                    'created_at' => $connection->created_at,
                ]);
            });

        SharedMemory::query()
            ->where(function ($query) use ($user, $partnerIds) {
                $query->whereHas('memory', fn ($memory) => $memory->where('user_id', $user->id))
                    ->whereIn('partner_id', $partnerIds);
            })
            ->orWhere(function ($query) use ($user, $partnerIds) {
                $query->where('partner_id', $user->id)
                    ->whereHas('memory', fn ($memory) => $memory->whereIn('user_id', $partnerIds));
            })
            ->with(['memory.user', 'partner'])
            ->get()
            ->each(function (SharedMemory $shared) use ($user, $items) {
                $isOwner = $shared->memory->user_id === $user->id;
                $partnerName = $isOwner ? $shared->partner->name : $shared->memory->user->name;

                $items->push([
                    'type' => 'memory',
                    'title' => $shared->memory->title,
                    'subtitle' => $isOwner
                        ? 'You shared a memory with '.$partnerName
                        : $partnerName.' shared a memory with you',
                    'icon' => 'fa-heart',
                    'created_at' => $shared->created_at,
                ]);
            });

        LoveLetter::query()
            ->where('receiver_id', $user->id)
            ->whereIn('user_id', $partnerIds)
            ->with('user')
            ->get()
            ->each(function (LoveLetter $letter) use ($items) {
                $items->push([
                    'type' => 'letter',
                    'title' => $letter->title ?: 'A love letter',
                    'subtitle' => 'You received a letter from '.$letter->user->name,
                    'icon' => 'fa-envelope-open-text',
                    'created_at' => $letter->created_at,
                ]);
            });

        ImportantDate::query()
            ->where(function ($query) use ($user, $partnerIds) {
                $query->where('user_id', $user->id)
                    ->orWhere(function ($query) use ($user, $partnerIds) {
                        $query->where('partner_id', $user->id)->whereIn('user_id', $partnerIds);
                    });
            })
            ->get()
            ->each(function (ImportantDate $date) use ($items) {
                $items->push([
                    'type' => 'date',
                    'title' => $date->title,
                    'subtitle' => 'Important date on '.$date->date->format('M j, Y'),
                    'icon' => 'fa-calendar-heart',
                    'created_at' => $date->created_at,
                ]);
            });

        SharedEvent::query()
            ->where(function ($query) use ($user, $partnerIds) {
                $query->where('user_id', $user->id)
                    ->orWhere(function ($query) use ($user, $partnerIds) {
                        $query->where('partner_id', $user->id)->whereIn('user_id', $partnerIds);
                    });
            })
            ->get()
            ->each(function (SharedEvent $event) use ($items) {
                $items->push([
                    'type' => 'event',
                    'title' => $event->title,
                    'subtitle' => 'Shared event on '.$event->event_date->format('M j, Y'),
                    'icon' => 'fa-calendar-day',
                    'created_at' => $event->created_at,
                ]);
            });

        return $items
            ->sortByDesc('created_at')
            ->values();
    }

    public function paginate(Collection $items, int $perPage = 15): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()],
        );
    }
}
