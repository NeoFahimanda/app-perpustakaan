<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DashboardReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_statistics_fetched_from_internal_api(): void
    {
        $user = User::factory()->create();
        $baseUrl = config('services.internal_api.base_url');

        Http::fake([
            $baseUrl.'/api/stats' => Http::response([
                'total_buku' => 20,
                'total_anggota' => 15,
                'peminjaman_aktif' => 4,
            ]),
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Total Buku')
            ->assertSee('>20</div>', false)
            ->assertSee('>15</div>', false)
            ->assertSee('>4</div>', false)
            ->assertSee(route('loans.report'));

        Http::assertSent(fn (ClientRequest $request) => $request->url() === $baseUrl.'/api/stats');
    }

    public function test_report_displays_api_loans_and_uses_requested_page(): void
    {
        $user = User::factory()->create();
        $baseUrl = config('services.internal_api.base_url');
        Http::fake([
            $baseUrl.'/api/loans*' => Http::response([
                'data' => [[
                    'id' => 12,
                    'member' => ['id' => 3, 'nama' => 'Anggota Demo'],
                    'petugas' => ['id' => 2, 'name' => 'Petugas Demo'],
                    'books' => [['id' => 9, 'judul' => 'Buku Demo']],
                    'tanggal_pinjam' => '2026-11-18',
                    'tanggal_kembali' => '2026-11-25',
                    'tanggal_dikembalikan' => null,
                    'status' => 'dipinjam',
                ]],
                'meta' => [
                    'current_page' => 2,
                    'last_page' => 3,
                ],
            ]),
        ]);

        $this->actingAs($user)
            ->get(route('loans.report', ['page' => 2]))
            ->assertOk()
            ->assertSee('Anggota Demo')
            ->assertSee('Petugas Demo')
            ->assertSee('Buku Demo')
            ->assertSee('Halaman 2 dari 3')
            ->assertSee(route('loans.report', ['page' => 1]))
            ->assertSee(route('loans.report', ['page' => 3]));

        Http::assertSent(fn (ClientRequest $request) => $request->url() === $baseUrl.'/api/loans?page=2');
    }

    public function test_dashboard_and_report_show_fallback_when_internal_api_cannot_connect(): void
    {
        $user = User::factory()->create();

        Http::fake(fn () => throw new ConnectionException('Connection refused.'));

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('API statistik tidak tersedia')
            ->assertSee('Total Buku')
            ->assertSee('Nilai sementara ditampilkan sebagai 0');

        $this->get(route('loans.report'))
            ->assertOk()
            ->assertSee('API peminjaman tidak tersedia')
            ->assertSee('Belum ada data peminjaman.');
    }

    public function test_dashboard_and_report_show_fallback_for_unsuccessful_api_responses(): void
    {
        $user = User::factory()->create();

        Http::fake([
            '*' => Http::response(['message' => 'Unavailable'], 503),
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('API statistik tidak tersedia');

        $this->get(route('loans.report'))
            ->assertOk()
            ->assertSee('API peminjaman tidak tersedia')
            ->assertSee('Belum ada data peminjaman.');
    }
}
