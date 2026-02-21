@extends('layouts.admin')

@section('title', '勤怠一覧（管理者）')

@section('content')
<div class="content-page">
    <div class="content-page__inner">
        <h2 class="content-page__title">{{ $currentDate ?? '2023年6月1日' }}の勤怠</h2>

        <div class="month-nav">
            <a class="month-nav__link" href="/admin/attendance/list?date={{ $prevDate ?? '' }}">
                <span class="month-nav__arrow material-symbols-outlined">arrow_back</span>
                前日
            </a>
            <label for="date-picker" class="month-nav__current" style="cursor: pointer;">
                <img src="{{ asset('images/calendar.png') }}" alt="カレンダー" width="20" height="20">
                {{ $currentDateFormatted ?? '2023/06/01' }}
                <input type="date" id="date-picker" value="{{ $currentDateValue ?? '' }}"
                    style="position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0,0,0,0); border: 0;"
                    onchange="if(this.value) window.location.href='/admin/attendance/list?date=' + this.value;">
            </label>
            <a class="month-nav__link" href="/admin/attendance/list?date={{ $nextDate ?? '' }}">
                翌日
                <span class="month-nav__arrow material-symbols-outlined">arrow_forward</span>
            </a>
        </div>

        <div class="table-card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>名前</th>
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
                        <td>{{ $attendance['user_name'] ?? '' }}</td>
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
    </div>
</div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var label = document.querySelector('label[for="date-picker"]');
        var picker = document.getElementById('date-picker');

        label.addEventListener('click', function (e) {
            e.preventDefault();
            picker.showPicker();
        });
    });
</script>
