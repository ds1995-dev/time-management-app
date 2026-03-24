@extends('layouts.app')
@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection
@section('content')
<div class="form">
    <div class="page-title">
        <h2 class="page-title">ログイン</h2>
    </div>
    <form class="form-input" method="POST" action="/login">
        @csrf
        @error('auth')
        <p class="error">{{ $message }}</p>
        @enderror
        <label>メールアドレス</label>
        <input class="form-input__field" type="email" name="email">
        @error('email')
        <p class="error">{{ $message }}</p>
        @enderror
        <label>パスワード</label>
        <input class="form-input__field" type="password" name="password">
        @error('password')
        <p class="error">{{ $message }}</p>
        @enderror
        <div class="input-form__btn">
            <button class="form-input__btn-submit" type="submit">ログインする</button>
        </div>
    </form>
    <div class="register">
        <a class="register" href="/register">会員登録はこちら</a>
    </div>
</div>
@endsection
