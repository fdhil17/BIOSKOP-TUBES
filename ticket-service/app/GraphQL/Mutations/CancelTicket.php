<?php
namespace App\GraphQL\Mutations;

use App\Models\Ticket;
use Illuminate\Support\Facades\Http;

class CancelTicket
{
    /**
     * Resolve the mutation to cancel a ticket.
     *
     * @param  mixed  $root
     * @param  array  $args
     * @return array
     */
    public function resolve($root, array $args)
    {
        $id = $args['id'];
        $ticket = Ticket::find($id);
        if (!$ticket || $ticket->status === 'cancelled') {
            return [
                'status'  => 'error',
                'message' => 'Tiket tidak valid',
                'data'    => null,
                'member'  => null,
                'movie'   => null
            ];
        }

        $ticket->update(['status' => 'cancelled']);

        $movieServiceUrl = env('MOVIE_SERVICE_URL', 'http://movie-service:8002');

        // Kembalikan kursi ke MovieService
        Http::patch($movieServiceUrl . '/api/movies/' . $ticket->movie_id . '/seat', [
            'change' => $ticket->quantity
        ]);

        return [
            'status'  => 'success',
            'message' => 'Tiket berhasil dibatalkan',
            'data'    => $ticket,
            'member'  => null,
            'movie'   => null
        ];
    }
}
