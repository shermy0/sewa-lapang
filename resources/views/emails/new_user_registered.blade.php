@component('mail::message')
# Pengguna Baru Terdaftar

Detail pengguna baru:

- Nama: {{ $user->name }}
- Email: {{ $user->email }}
- Role: {{ $user->role }}

Silakan tindak lanjuti jika diperlukan.

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
