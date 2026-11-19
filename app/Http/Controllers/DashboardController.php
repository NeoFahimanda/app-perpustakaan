<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_buku' => 0,
            'total_anggota' => 0,
            'peminjaman_aktif' => 0,
        ];
        $apiAvailable = false;

        try {
            $response = Http::connectTimeout(2)
                ->timeout(5)
                ->get(config('services.internal_api.base_url').'/api/stats');

            if ($response->successful()) {
                $data = $response->json();
                $validStats = is_array($data);

                foreach (array_keys($stats) as $key) {
                    $validStats = $validStats
                        && array_key_exists($key, $data)
                        && is_numeric($data[$key]);
                }

                if ($validStats) {
                    $stats = array_map(fn ($value) => (int) $value, $data);
                    $apiAvailable = true;
                } else {
                    Log::warning('Internal stats API returned an invalid response.');
                }
            } else {
                Log::warning('Internal stats API request failed.', [
                    'status' => $response->status(),
                ]);
            }
        } catch (ConnectionException $exception) {
            Log::warning('Internal stats API is unavailable.', [
                'exception' => $exception,
            ]);
        }

        return view('dashboard', compact('stats', 'apiAvailable'));
    }
}
