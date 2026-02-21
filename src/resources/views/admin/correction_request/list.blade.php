@extends('layouts.admin')

@section('title', '申請一覧（管理者）')

@php
    $currentTab = $tab ?? 'pending';
@endphp

@section('content')
<div class="content-page">
    <div class="content-page__inner">
        <h2 class="content-page__title">申請一覧</h2>

        <nav class="tab-nav">
            <a class="tab-nav__item {{ $currentTab === 'pending' ? 'tab-nav__item--active' : 'tab-nav__item--inactive' }}"
               href="{{ route('correction_request.list', ['tab' => 'pending']) }}">承認待ち</a>
            <a class="tab-nav__item {{ $currentTab === 'approved' ? 'tab-nav__item--active' : 'tab-nav__item--inactive' }}"
               href="{{ route('correction_request.list', ['tab' => 'approved']) }}">承認済み</a>
        </nav>

        <div class="table-card">
            <table class="data-table data-table--request">
                <thead>
                    <tr>
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日時</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests ?? [] as $request)
                    <tr>
                        <td>{{ $request['status_label'] ?? '' }}</td>
                        <td>{{ $request['user_name'] ?? '' }}</td>
                        <td>{{ $request['target_date'] ?? '' }}</td>
                        <td>{{ $request['reason'] ?? '' }}</td>
                        <td>{{ $request['request_date'] ?? '' }}</td>
                        <td><a class="data-table__link" href="{{ route('correction_request.approve', $request['id'] ?? '') }}">詳細</a></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">申請データがありません</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
