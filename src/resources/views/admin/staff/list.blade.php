@extends('layouts.admin')

@section('title', 'スタッフ一覧')

@section('content')
<section class="content-page">
    <div class="content-page__inner">
        <h1 class="content-page__title">スタッフ一覧</h1>

        <div class="table-card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>名前</th>
                        <th>メールアドレス</th>
                        <th>月次勤怠</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($staffs ?? [] as $staff)
                    <tr>
                        <td>{{ $staff['name'] ?? '' }}</td>
                        <td>{{ $staff['email'] ?? '' }}</td>
                        <td><a class="data-table__link" href="{{ route('admin.staff.show', $staff['id'] ?? '') }}">詳細</a></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3">スタッフデータがありません</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
