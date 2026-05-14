<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Client\ConnectionException;

class MemberController extends Controller
{
    private function apiResponse(string $status, string $message, $data = null, int $httpCode = 200): JsonResponse
    {
        return response()->json([
            'status'  => $status,
            'message' => $message,
            'data'    => $data,
        ], $httpCode);
    }

    // GET: Semua member
    public function index(): JsonResponse
    {
        $members = Member::all();

        return $this->apiResponse('success', 'List member berhasil diambil', $members);
    }

    // GET: Detail member
    public function show(int $id): JsonResponse
    {
        $member = Member::find($id);

        if (!$member) {
            return $this->apiResponse('failed', 'Member tidak ditemukan', null, 404);
        }

        return $this->apiResponse('success', 'Detail member ditemukan', $member);
    }

    // POST: Tambah member
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|unique:members,email',
            'phone'   => 'required|string|max:20',
            'address' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $member = Member::create($validator->validated());

        return $this->apiResponse('success', 'Member berhasil dibuat', $member, 201);
    }

    // PUT: Update member
    public function update(Request $request, int $id): JsonResponse
    {
        $member = Member::find($id);

        if (!$member) {
            return $this->apiResponse('failed', 'Member tidak ditemukan', null, 404);
        }

        $validator = Validator::make($request->all(), [
            'name'    => 'sometimes|required|string|max:255',
            'email'   => 'sometimes|required|email|unique:members,email,' . $id,
            'phone'   => 'sometimes|required|string|max:20',
            'address' => 'sometimes|required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $member->update($validator->validated());

        return $this->apiResponse('success', 'Member berhasil diupdate', $member->fresh());
    }

    // DELETE: Hapus member
    public function destroy(int $id): JsonResponse
    {
        $member = Member::find($id);

        if (!$member) {
            return $this->apiResponse('failed', 'Member tidak ditemukan', null, 404);
        }

        $member->delete();

        return $this->apiResponse('success', 'Member berhasil dihapus');
    }

    // GET: Ticket member (Microservice Communication)
    public function memberTickets(int $id): JsonResponse
    {
        $member = Member::find($id);

        if (!$member) {
            return $this->apiResponse('failed', 'Member tidak ditemukan', null, 404);
        }

        try {
            $ticketServiceUrl = env('TICKET_SERVICE_URL', 'http://ticket-service:8003');

            $response = Http::retry(2, 100)->timeout(5)
                ->get($ticketServiceUrl . '/api/tickets/member/' . $id);

            if ($response->failed()) {
                return $this->apiResponse(
                    'failed',
                    'Gagal mengambil data tiket dari ticket-service',
                    null,
                    $response->status()
                );
            }

            $ticketData = $response->json();

            if (is_null($ticketData)) {
                return $this->apiResponse(
                    'failed',
                    'Response dari ticket-service bukan JSON yang valid',
                    null,
                    502
                );
            }

        
            return $this->apiResponse('success', 'Berhasil mengambil ticket member', [
                'member'  => $member,
                'tickets' => $ticketData,
            ]);

        } catch (ConnectionException $e) {
            return $this->apiResponse(
                'failed',
                'Koneksi ke ticket-service terputus: ' . $e->getMessage(),
                null,
                503
            );
        } catch (\Exception $e) {
            return $this->apiResponse(
                'failed',
                'Terjadi kesalahan: ' . $e->getMessage(),
                null,
                500
            );
        }
    }
}
