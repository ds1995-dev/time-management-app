@extends('layouts.app')
@section('css')
<link rel="stylesheet" href="{{ asset('css/verify-email.css') }}">
@endsection
@section('content')
<p class="message">
    登録していただいたメールアドレスに認証メールを送付しました。<br>
    メール認証を完了してください。
</p>

<form class="form" method="POST" action="{{ route('verification.send') }}">
    @csrf
    <button class="form-btn" type="submit">認証はこちらから</button>
</form>
<form class="form" method="POST" action="{{ route('verification.send') }}">
    @csrf
    <button class="resend-btn" type="submit">認証メールを再送する</button>
</form>
@endsection