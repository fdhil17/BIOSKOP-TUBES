<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class MovieController extends Controller
{
    private function apiResponse(string $status, string $message, $data = null, int $httpCode = 200): JsonResponse
    {
        return response()->json([
            'status'  => $status,
            'message' => $message,
            'data'    => $data,
        ], $httpCode);
    }

    public function index(): JsonResponse
    {
        $movies = Movie::all();
        return $this->apiResponse('success', 'List film berhasil diambil', $movies);
    }

    public function show(int $id): JsonResponse
    {
        $movie = Movie::find($id);

        if (!$movie) {
            return $this->apiResponse('failed', 'Film tidak ditemukan', null, 404);
        }

        return $this->apiResponse('success', 'Detail film ditemukan', $movie);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title'          => 'required|string|max:255',
            'genre'          => 'required|string|max:255',
            'duration'       => 'required|integer',
            'jam_tayang'     => 'required|date_format:H:i:s',
            'seat_available' => 'nullable|integer',
            'price'          => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $movie = Movie::create($validator->validated());

        return $this->apiResponse('success', 'Film berhasil ditambahkan', $movie, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $movie = Movie::find($id);

        if (!$movie) {
            return $this->apiResponse('failed', 'Film tidak ditemukan', null, 404);
        }

        $validator = Validator::make($request->all(), [
            'title'          => 'sometimes|required|string|max:255',
            'genre'          => 'sometimes|required|string|max:255',
            'duration'       => 'sometimes|required|integer',
            'jam_tayang'     => 'sometimes|required|date_format:H:i:s',
            'seat_available' => 'sometimes|required|integer',
            'price'          => 'sometimes|required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $movie->update($validator->validated());

        return $this->apiResponse('success', 'Film berhasil diupdate', $movie->fresh());
    }

    public function destroy(int $id): JsonResponse
    {
        $movie = Movie::find($id);

        if (!$movie) {
            return $this->apiResponse('failed', 'Film tidak ditemukan', null, 404);
        }

        $movie->delete();

        return $this->apiResponse('success', 'Film berhasil dihapus');
    }
}
