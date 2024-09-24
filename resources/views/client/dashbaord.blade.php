@extends('layouts.clientapp')

@section('title', 'Client Home')

@section('content')
    <h1>Welcome, {{ Auth::user()->name }}! This is the Client Home Page.</h1>
@endsection
