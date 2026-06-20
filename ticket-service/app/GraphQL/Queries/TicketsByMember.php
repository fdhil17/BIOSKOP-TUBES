<?php
namespace App\GraphQL\Queries;

use App\Models\Ticket;

class TicketsByMember
{
    /**
     * Resolve the query for tickets by member.
     *
     * @param  mixed  $root
     * @param  array  $args
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function resolve($root, array $args)
    {
        return Ticket::where('member_id', $args['member_id'])->get();
    }
}
