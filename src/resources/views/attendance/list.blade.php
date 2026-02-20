@extends('layouts.app')

@section('title', '勤怠一覧')

@section('content')
<div class="content-page">
    <div class="content-page__inner">
        <h2 class="content-page__title">勤怠一覧</h2>

        <div class="month-nav">
            <a class="month-nav__link" href="/attendance/list?month={{ $prevMonth ?? '' }}">
                <span class="month-nav__arrow material-symbols-outlined">arrow_back</span>
                前月
            </a>
            <span class="month-nav__current">
                <img src="{{ asset('images/calendar.png') }}" alt="カレンダー" width="20" height="20">
                {{ $currentMonth ?? '2023/06' }}
            </span>
            <a class="month-nav__link" href="/attendance/list?month={{ $nextMonth ?? '' }}">
                翌月
                <span class="month-nav__arrow material-symbols-outlined">arrow_forward</span>
            </a>
        </div>

        <div class="table-card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>日付</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendances ?? [] as $attendance)
                    <tr>
                        <td>{{ $attendance['date'] ?? '' }}</td>
                        <td>{{ $attendance['clock_in'] ?? '' }}</td>
                        <td>{{ $attendance['clock_out'] ?? '' }}</td>
                        <td>{{ $attendance['break_time'] ?? '' }}</td>
                        <td>{{ $attendance['total_time'] ?? '' }}</td>
                        <td>
                            @if (!empty($attendance['id']))
                                <a class="data-table__link" href="/attendance/detail/{{ $attendance['id'] }}">詳細</a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">勤怠データがありません</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
