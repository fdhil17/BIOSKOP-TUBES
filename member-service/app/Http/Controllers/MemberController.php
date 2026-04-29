<?php
namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MemberController extends Controller
{
    // PROVIDER: Semua member
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'data' => Member::all()
        ]);
    }

    // PROVIDER: Detail member
    public function show($id)
    {
        $member = Member::find($id);
        if (!$member) {
            return response()->json([
                'status' => 'error',
                'message' => 'Member tidak ditemukan'
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'data' => $member
        ]);
    }

    // PROVIDER: Tambah member
    public function store(Request $request)
    {
        $member = Member::create($request->all());
        return response()->json([
            'status' => 'success',
            'data' => $member
        ], 201);
    }

    // PROVIDER: Update member
    public function update(Request $request, $id)
    {
        $member = Member::find($id);
        if (!$member) {
            return response()->json([
                'status' => 'error',
                'message' => 'Member tidak ditemukan'
            ], 404);
        }
        $member->update($request->all());
        return response()->json([
            'status' => 'success',
            'data' => $member
        ]);
    }

    // CONSUMER: Lihat tiket member dari TicketService
    public function memberTickets($id)
    {
        $member = Member::find($id);
        if (!$member) {
            return response()->json([
                'status' => 'error',
                'message' => 'Member tidak ditemukan'
            ], 404);
        }

        // HTTP request ke TicketService
        $response = Http::get('http://localhost:8003/api/tickets/member/' . $id);

        return response()->json([
            'status' => 'success',
            'member' => $member,
            'tickets' => $response->json()
        ]);
    }
}
