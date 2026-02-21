@extends('layouts.app')

@section('title', '勤怠')

@php
    $currentStatus = $status ?? 0;
    $statusLabels = ['勤務外', '出勤中', '休憩中', '退勤済'];
@endphp

@section('nav')
@if ($currentStatus === 3)
    <a class="header__nav-link header__nav-link--normal" href="/attendance/list">今月の出勤一覧</a>
    <a class="header__nav-link header__nav-link--normal" href="/correction_request/list">申請一覧</a>
@else
    <a class="header__nav-link" href="/attendance">勤怠</a>
    <a class="header__nav-link" href="/attendance/list">勤怠一覧</a>
    <a class="header__nav-link" href="/correction_request/list">申請</a>
@endif
<form action="/logout" method="POST">
    @csrf
    <button type="submit" class="header__nav-link header__nav-button">ログアウト</button>
</form>
@endsection

@section('content')
<div class="attendance">
    <div class="attendance__content">
        <div class="attendance__status">{{ $statusLabels[$currentStatus] }}</div>
        <p class="attendance__date" id="current-date"></p>
        <p class="attendance__time" id="current-time"></p>

        @if ($currentStatus === 0)
            <form action="/attendance" method="POST">
                @csrf
                <button class="attendance__button" type="submit" name="action" value="clock_in">出勤</button>
            </form>
        @elseif ($currentStatus === 1)
            <div class="attendance__actions">
                <form action="/attendance" method="POST">
                    @csrf
                    <button class="attendance__button" type="submit" name="action" value="clock_out">退勤</button>
                </form>
                <form action="/attendance" method="POST">
                    @csrf
                    <button class="attendance__button attendance__button--white" type="submit" name="action" value="break_start">休憩入</button>
                </form>
            </div>
        @elseif ($currentStatus === 2)
            <form action="/attendance" method="POST">
                @csrf
                <button class="attendance__button attendance__button--white" type="submit" name="action" value="break_end">休憩戻</button>
            </form>
        @elseif ($currentStatus === 3)
            <p class="attendance__message">お疲れ様でした。</p>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/attendance-clock.js') }}"></script>
@endsection
