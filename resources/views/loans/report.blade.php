@extends('layouts.app')

@section('title', 'Laporan Peminjaman')

@section('content')
    <h1>Laporan Peminjaman</h1>
    <p>Data diambil langsung dari <code>GET /api/loans</code>, bukan query database langsung.</p>

    @unless ($apiAvailable)
        <div class="alert-warning">API peminjaman tidak tersedia. Laporan sementara ditampilkan kosong.</div>
    @endunless

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Anggota</th>
                <th>Petugas</th>
                <th>Buku</th>
                <th>Tgl Pinjam</th>
                <th>Tgl Kembali</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($loans as $loan)
                <tr>
                    <td>{{ $loan['id'] }}</td>
                    <td>{{ $loan['member']['nama'] }}</td>
                    <td>{{ $loan['petugas']['name'] }}</td>
                    <td>
                        @foreach ($loan['books'] as $book)
                            {{ $book['judul'] }}@if (!$loop->last), @endif
                        @endforeach
                    </td>
                    <td>{{ $loan['tanggal_pinjam'] }}</td>
                    <td>{{ $loan['tanggal_kembali'] }}</td>
                    <td><span class="badge badge-{{ $loan['status'] }}">{{ ucfirst($loan['status']) }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Belum ada data peminjaman.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($meta && $meta['last_page'] > 1)
        <nav class="pagination" aria-label="Navigasi halaman laporan">
            @if ($meta['current_page'] > 1)
                <a href="{{ route('loans.report', ['page' => $meta['current_page'] - 1]) }}">&laquo; Sebelumnya</a>
            @else
                <span class="pagination-disabled">&laquo; Sebelumnya</span>
            @endif

            <span>Halaman {{ $meta['current_page'] }} dari {{ $meta['last_page'] }}</span>

            @if ($meta['current_page'] < $meta['last_page'])
                <a href="{{ route('loans.report', ['page' => $meta['current_page'] + 1]) }}">Berikutnya &raquo;</a>
            @else
                <span class="pagination-disabled">Berikutnya &raquo;</span>
            @endif
        </nav>
    @endif
@endsection
