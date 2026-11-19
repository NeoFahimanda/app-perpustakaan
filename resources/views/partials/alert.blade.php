@if (session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif
@if (session('warning'))
    <div class="alert-warning">{{ session('warning') }}</div>
@endif