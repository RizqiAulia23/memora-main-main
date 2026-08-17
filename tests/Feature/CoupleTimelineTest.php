<?php

namespace Tests\Feature;

use App\Models\Connection;
use App\Models\ImportantDate;
use App\Models\LoveLetter;
use App\Models\Memory;
use App\Models\SharedEvent;
use App\Models\SharedMemory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CoupleTimelineTest extends TestCase
{
    use RefreshDatabase;

    private function connectedPair(): array
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        Connection::factory()->accepted()->create(['sender_id' => $a->id, 'receiver_id' => $b->id]);

        return [$a, $b];
    }

    private function sharedMemoryBetween(User $owner, User $partner): SharedMemory
    {
        $memory = Memory::factory()->create(['user_id' => $owner->id, 'title' => 'Beach day']);

        return SharedMemory::create(['memory_id' => $memory->id, 'partner_id' => $partner->id]);
    }

    public function test_timeline_requires_auth(): void
    {
        $this->get('/couple-timeline')->assertRedirect('/login');
    }

    public function test_timeline_is_empty_without_connection(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/couple-timeline')
            ->assertOk()
            ->assertSee('Connect with your partner');
    }

    public function test_timeline_shows_connection_milestone(): void
    {
        [$a, $b] = $this->connectedPair();

        $this->actingAs($b)->get('/couple-timeline')
            ->assertOk()
            ->assertSee('You and '.$a->name.' connected');
    }

    public function test_timeline_shows_shared_memories_in_both_directions(): void
    {
        [$a, $b] = $this->connectedPair();
        $this->sharedMemoryBetween($a, $b);
        $this->sharedMemoryBetween($b, $a);

        $this->actingAs($a)->get('/couple-timeline')
            ->assertOk()
            ->assertSee('Beach day')
            ->assertSee('You shared a memory')
            ->assertSee($b->name.' shared a memory with you');
    }

    public function test_timeline_shows_received_love_letters_only(): void
    {
        [$a, $b] = $this->connectedPair();
        LoveLetter::create([
            'user_id' => $a->id,
            'receiver_id' => $b->id,
            'title' => 'Good morning',
            'content' => 'Hi',
            'mood' => 'sweet',
            'letter_date' => now()->toDateString(),
        ]);
        LoveLetter::create([
            'user_id' => $b->id,
            'receiver_id' => $a->id,
            'title' => 'Good night',
            'content' => 'Bye',
            'mood' => 'sweet',
            'letter_date' => now()->toDateString(),
        ]);

        $this->actingAs($a)->get('/couple-timeline')
            ->assertOk()
            ->assertSee('Good night')
            ->assertSee('You received a letter from '.$b->name)
            ->assertDontSee('Good morning');
    }

    public function test_timeline_shows_important_dates_and_events(): void
    {
        [$a, $b] = $this->connectedPair();
        ImportantDate::create([
            'user_id' => $a->id,
            'partner_id' => $b->id,
            'title' => 'Anniversary',
            'date' => now()->addMonths(2)->toDateString(),
            'type' => 'anniversary',
            'recurring' => true,
        ]);
        SharedEvent::create([
            'user_id' => $a->id,
            'partner_id' => $b->id,
            'title' => 'Concert night',
            'event_date' => now()->addMonth()->toDateString(),
        ]);

        $this->actingAs($b)->get('/couple-timeline')
            ->assertOk()
            ->assertSee('Anniversary')
            ->assertSee('Concert night');
    }

    public function test_timeline_is_sorted_newest_first(): void
    {
        [$a, $b] = $this->connectedPair();

        $olderMemory = Memory::factory()->create(['user_id' => $a->id, 'title' => 'Older memory']);
        SharedMemory::create(['memory_id' => $olderMemory->id, 'partner_id' => $b->id]);

        $event = SharedEvent::create([
            'user_id' => $a->id,
            'partner_id' => $b->id,
            'title' => 'Newest event',
            'event_date' => now()->addDay()->toDateString(),
        ]);

        $olderMemory->update(['created_at' => now()->subDays(30)]);
        SharedMemory::first()->update(['created_at' => now()->subDays(30)]);
        Connection::first()->update(['created_at' => now()->subDays(60)]);
        $event->update(['created_at' => now()->subMinutes(5)]);

        $response = $this->actingAs($b)->get('/couple-timeline')->assertOk();

        $html = $response->getContent();
        $this->assertLessThan(
            strpos($html, 'Newest event'),
            strpos($html, 'Older memory')
        );
    }

    public function test_timeline_excludes_partner_data_after_disconnect(): void
    {
        [$a, $b] = $this->connectedPair();
        $this->sharedMemoryBetween($a, $b);
        $connection = Connection::where('sender_id', $a->id)->first();

        $this->actingAs($a)->delete("/connections/{$connection->id}");

        $this->actingAs($b)->get('/couple-timeline')
            ->assertOk()
            ->assertDontSee('Beach day')
            ->assertSee('Connect with your partner');
    }

    public function test_timeline_excludes_stranger_activity(): void
    {
        [$a, $b] = $this->connectedPair();
        $stranger = User::factory()->create();
        $this->sharedMemoryBetween($stranger, $b);

        $this->actingAs($b)->get('/couple-timeline')
            ->assertOk()
            ->assertDontSee('Beach day');
    }

    public function test_timeline_does_not_show_letters_between_third_parties(): void
    {
        [$a, $b] = $this->connectedPair();
        $c = User::factory()->create();
        Connection::factory()->accepted()->create(['sender_id' => $a->id, 'receiver_id' => $c->id]);
        LoveLetter::create([
            'user_id' => $c->id,
            'receiver_id' => $b->id,
            'title' => 'Side letter',
            'content' => 'x',
            'mood' => 'sweet',
            'letter_date' => now()->toDateString(),
        ]);

        $this->actingAs($a)->get('/couple-timeline')
            ->assertOk()
            ->assertDontSee('Side letter');
    }

    public function test_timeline_page_query_count_does_not_grow_with_feed_items(): void
    {
        [$a, $b] = $this->connectedPair();

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($b)->get('/couple-timeline')->assertOk();
        $baseline = count(DB::getQueryLog());

        foreach (range(1, 8) as $i) {
            $this->sharedMemoryBetween($a, $b);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($b)->get('/couple-timeline')->assertOk();
        $withItems = count(DB::getQueryLog());

        $this->assertLessThan(5, $withItems - $baseline, "Query count grew from {$baseline} to {$withItems}");
    }
}
