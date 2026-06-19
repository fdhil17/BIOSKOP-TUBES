<?php
namespace App\GraphQL\Mutations;

use App\Models\Ticket;
use Illuminate\Support\Facades\Http;

class CreateTicket
{
    /**
     * Resolve the mutation to create a ticket.
     *
     * @param  mixed  $root
     * @param  array  $args
     * @return array
     */
    public function resolve($root, array $args)
    {
        $memberId = $args['member_id'];
        $movieId = $args['movie_id'];
        $quantity = $args['quantity'];

        $memberServiceUrl = env('MEMBER_SERVICE_URL', 'http://member-service:8001');
        $movieServiceUrl = env('MOVIE_SERVICE_URL', 'http://movie-service:8002');

        // Validasi member ke MemberService
        $memberResponse = Http::get($memberServiceUrl . '/api/members/' . $memberId);
        if ($memberResponse->failed()) {
            return [
                'status' => 'error',
                'message' => 'Member tidak ditemukan',
                'data' => null,
                'member' => null,
                'movie' => null
            ];
        }

        // Validasi film ke MovieService
        $movieResponse = Http::get($movieServiceUrl . '/api/movies/' . $movieId);
        if ($movieResponse->failed()) {
            return [
                'status' => 'error',
                'message' => 'Film tidak ditemukan',
                'data' => null,
                'member' => null,
                'movie' => null
            ];
        }

        $movieData = $movieResponse->json()['data'];

        // Cek kursi tersedia
        if ($movieData['seat_available'] < $quantity) {
            return [
                'status' => 'error',
                'message' => 'Kursi tidak tersedia',
                'data' => null,
                'member' => null,
                'movie' => null
            ];
        }

        // Hitung total harga
        $totalPrice = $movieData['price'] * $quantity;

        // Simpan tiket
        $ticket = Ticket::create([
            'member_id'   => $memberId,
            'movie_id'    => $movieId,
            'quantity'    => $quantity,
            'total_price' => $totalPrice,
            'status'      => 'booked',
        ]);

        // Kurangi kursi di MovieService
        Http::patch($movieServiceUrl . '/api/movies/' . $movieId . '/seat', [
            'change' => -$quantity
        ]);

        return [
            'status'  => 'success',
            'message' => 'Tiket berhasil dibeli',
            'data'    => $ticket,
            'member'  => $memberResponse->json()['data'],
            'movie'   => $movieData,
        ];
    }
}
