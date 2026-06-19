<?php
namespace App\GraphQL\Queries;

use App\Models\Ticket;

class TicketsByMovie
{
    /**
     * Resolve the query for tickets by movie.
     *
     * @param  mixed  $root
     * @param  array  $args
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function resolve($root, array $args)
    {
        return Ticket::where('movie_id', $args['movie_id'])
                     ->where('status', 'booked')
                     ->get();
    }
}
