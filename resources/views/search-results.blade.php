@extends('layouts.app')

@section('title', 'Search Results')

@section('content')
<div class="container mt-4">
    <h2>Search Results for "{{ $query }}"</h2>
    @if($users->isEmpty())
        <div class="alert alert-warning">No users found.</div>
    @else
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Position</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->department }}</td>
                        <td>{{ $user->position }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
