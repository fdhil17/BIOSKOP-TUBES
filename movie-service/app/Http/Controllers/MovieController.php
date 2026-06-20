<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MovieController extends Controller
{
    public function index()
    {
        return response()->json(['status' => 'success', 'data' => Movie::all()]);
    }

    public function show($id)
    {
        $movie = Movie::find($id);
        return $movie ? response()->json(['status' => 'success', 'data' => $movie])
                      : response()->json(['status' => 'error', 'message' => 'Film tidak ditemukan'], 404);
    }

    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'title' => 'required',
            'genre' => 'required',
            'duration' => 'required|integer',
            'jam_tayang' => 'required',
            'seat_available' => 'required|integer',
            'price' => 'required|numeric',
        ]);

        $movie = Movie::create($request->all());
        return response()->json(['status' => 'success', 'data' => $movie], 201);
    }

    public function updateSeat(Request $request, $id)
    {
        $request->validate(['change' => 'required|integer']);

        $movie = Movie::find($id);
        if (!$movie) return response()->json(['status' => 'error', 'message' => 'Film tidak ditemukan'], 404);

        $movie->seat_available += $request->change;
        $movie->save();

        return response()->json(['status' => 'success', 'data' => $movie]);
    }

    public function movieTickets($id)
    {
        $movie = Movie::find($id);
        if (!$movie) return response()->json(['status' => 'error', 'message' => 'Film tidak ditemukan'], 404);

        // Memanggil TicketService dengan timeout agar tidak menggantung
        $ticketServiceUrl = env('TICKET_SERVICE_URL', 'http://ticket-service:8003');
        $response = Http::timeout(3)->get($ticketServiceUrl . '/api/tickets/movie/' . $id);

        if ($response->failed()) {
            return response()->json(['status' => 'error', 'message' => 'Ticket service tidak merespon'], 503);
        }

        return response()->json([
            'status' => 'success',
            'movie' => $movie,
            'tickets' => $response->json()
        ]);
    }
}