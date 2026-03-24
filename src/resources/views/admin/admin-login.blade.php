@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
<div class="form">
    <div class="page-title">
        <h2 class="page-title">管理者ログイン</h2>
    </div>
    <form class="form-input" method="POST" action="{{ route('login') }}">
        @csrf
        @error('auth')
        <p class="error">{{ $message }}</p>
        @enderror
        <input type="hidden" name="login_type" value="admin">
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
            <button class="form-input__btn-submit" type="submit">管理者ログインする</button>
        </div>
    </form>
</div>
@endsection