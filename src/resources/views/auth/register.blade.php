@extends('layouts.app')
@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection
@section('content')
<div class="form">
    <div class="page-title">
        <h2 class="page-title">会員登録</h2>
    </div>
    <form class="form-input" id="register-form" action="/register" method="POST">
        @csrf
        <label>ユーザー名</label>
        <input class="form-input__field" type="text" name="name">
        @error('name')
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
        <label>確認用パスワード</label>
        <input class="form-input__field" type="password" name="password_confirmation">
    </form>
</div>
<div class="input-form__btn">
    <button class="input-form__btn-submit" form="register-form" type="submit">登録する</button>
</div>
<div class="login">
    <a class="login" href="/login">ログインはこちら</a>
</div>
@endsection