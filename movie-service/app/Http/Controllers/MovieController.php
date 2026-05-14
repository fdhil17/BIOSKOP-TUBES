<?php
namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MovieController extends Controller
{
    // PROVIDER: Semua film
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'data' => Movie::all()
        ]);
    }

    // PROVIDER: Detail film
    public function show($id)
    {
        $movie = Movie::find($id);
        if (!$movie) {
            return response()->json([
                'status' => 'error',
                'message' => 'Film tidak ditemukan'
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'data' => $movie
        ]);
    }

    // PROVIDER: Tambah film
    public function store(Request $request)
    {
        $movie = Movie::create($request->all());
        return response()->json([
            'status' => 'success',
            'data' => $movie
        ], 201);
    }

    // PROVIDER: Update kursi (dipanggil TicketService)
    public function updateSeat(Request $request, $id)
    {
        $movie = Movie::find($id);
        if (!$movie) {
            return response()->json([
                'status' => 'error',
                'message' => 'Film tidak ditemukan'
            ], 404);
        }
        $movie->seat_available += $request->change;
        $movie->save();
        return response()->json([
            'status' => 'success',
            'data' => $movie
        ]);
    }

    // CONSUMER: Lihat tiket film ini dari TicketService
    public function movieTickets($id)
    {
        $movie = Movie::find($id);
        if (!$movie) {
            return response()->json([
                'status' => 'error',
                'message' => 'Film tidak ditemukan'
            ], 404);
        }

        $ticketServiceUrl = env('TICKET_SERVICE_URL', 'http://ticket-service:8003');
        $response = Http::get($ticketServiceUrl . '/api/tickets/movie/' . $id);

        return response()->json([
            'status' => 'success',
            'movie' => $movie,
            'tickets' => $response->json()
        ]);
    }
}
