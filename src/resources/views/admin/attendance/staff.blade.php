@extends('layouts.admin')

@section('title', 'スタッフ別勤怠一覧')

@section('content')
<div class="content-page">
    <div class="content-page__inner">
        <h2 class="content-page__title">{{ $staffName ?? '' }}さんの勤怠</h2>

        <div class="month-nav">
            <a class="month-nav__link" href="/admin/attendance/staff/{{ $staffId ?? '' }}?month={{ $prevMonth ?? '' }}">
                <span class="month-nav__arrow">&#9664;</span>
                前月
            </a>
            <span class="month-nav__current">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                {{ $currentMonth ?? '2023/06' }}
            </span>
            <a class="month-nav__link" href="/admin/attendance/staff/{{ $staffId ?? '' }}?month={{ $nextMonth ?? '' }}">
                翌月
                <span class="month-nav__arrow">&#9654;</span>
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
                        <td><a class="data-table__link" href="/admin/attendance/{{ $attendance['id'] ?? '' }}">詳細</a></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">勤怠データがありません</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="detail__actions">
            <a class="csv-export__button" href="/admin/attendance/staff/{{ $staffId ?? '' }}/csv?month={{ $currentMonth ?? '' }}">CSV出力</a>
        </div>
    </div>
</div>
@endsection
