@extends('layouts.app')

@section('title', 'Profil')

@section('content')
    <h1>Profil</h1>

    <table>
        <tr>
            <th>Nama</th>
            <td>{{ $user->name }}</td>
        </tr>
        <tr>
            <th>Email</th>
            <td>{{ $user->email }}</td>
        </tr>
        <tr>
            <th>Role</th>
            <td>{{ ucfirst($user->role) }}</td>
        </tr>
    </table>

    <h2>Ganti Password</h2>
    <form action="{{ route('profile.password.update') }}" method="POST">
        @csrf

        <label for="current_password">Password Lama</label>
        <input type="password" name="current_password" id="current_password" autocomplete="current-password">
        @error('current_password')
            <div class="error">{{ $message }}</div>
        @enderror

        <label for="password">Password Baru</label>
        <input type="password" name="password" id="password" autocomplete="new-password">
        @error('password')
            <div class="error">{{ $message }}</div>
        @enderror

        <label for="password_confirmation">Konfirmasi Password Baru</label>
        <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password">

        <button type="submit" class="btn">Perbarui Password</button>
    </form>
@endsection
