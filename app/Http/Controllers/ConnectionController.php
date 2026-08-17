<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Services\ConnectionCodeService;
use App\Services\ConnectionService;
use Illuminate\Http\Request;

class ConnectionController extends Controller
{
    public function __construct(
        private readonly ConnectionService $connections,
        private readonly ConnectionCodeService $codes,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $all = Connection::query()
            ->where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->with(['sender', 'receiver'])
            ->latest('updated_at')
            ->get();

        $connected = $all->where('status', Connection::ACCEPTED);
        $incoming = $all->where('status', Connection::PENDING)
            ->where('receiver_id', $user->id);
        $outgoing = $all->where('status', Connection::PENDING)
            ->where('sender_id', $user->id);

        return view('connections.index', compact('connected', 'incoming', 'outgoing', 'user'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'connection_code' => ['required', 'string', 'digits:8'],
        ]);

        $receiver = $this->codes->findUserByCode($validated['connection_code']);

        if (! $receiver) {
            return back()->with('error', 'No user found with that connection code.');
        }

        $this->authorize('create', [Connection::class, $receiver]);

        try {
            $this->connections->send($request->user(), $receiver);
        } catch (\InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Connection request sent.');
    }

    public function accept(Request $request, Connection $connection)
    {
        $this->authorize('accept', $connection);

        $this->connections->accept($connection);

        return back()->with('success', 'Connection accepted.');
    }

    public function reject(Request $request, Connection $connection)
    {
        $this->authorize('reject', $connection);

        $this->connections->reject($connection);

        return back()->with('success', 'Connection request rejected.');
    }

    public function destroy(Request $request, Connection $connection)
    {
        if ($connection->status === Connection::PENDING) {
            $this->authorize('cancel', $connection);

            $this->connections->cancel($connection);

            return back()->with('success', 'Connection request cancelled.');
        }

        $this->authorize('delete', $connection);

        $this->connections->disconnect($connection);

        return back()->with('success', 'Connection removed.');
    }
}
