@extends('layouts.auth')

@section('title', 'ログイン')

@section('content')
<div class="auth-form">
    <h2 class="auth-form__title">ログイン</h2>
    <form action="/login" method="POST" novalidate>
        @csrf
        <div class="auth-form__group">
            <label class="auth-form__label" for="email">メールアドレス</label>
            <input class="auth-form__input auth-form__input--large" type="email" name="email" id="email" value="{{ old('email') }}">
            @error('email')
                <p class="auth-form__error">{{ $message }}</p>
            @enderror
        </div>
        <div class="auth-form__group">
            <label class="auth-form__label" for="password">パスワード</label>
            <input class="auth-form__input auth-form__input--large" type="password" name="password" id="password">
            @error('password')
                <p class="auth-form__error">{{ $message }}</p>
            @enderror
        </div>
        <button class="auth-form__button" type="submit">ログインする</button>
    </form>
    <a class="auth-form__link" href="/register">会員登録はこちら</a>
</div>
@endsection
