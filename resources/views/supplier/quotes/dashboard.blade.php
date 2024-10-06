@extends('layouts.supplierapp')

@section('title', 'Supplier Quotes')

@section('content')
    <h1>Welcome, {{ Auth::user()->name }}! This is the Supplier Home Page.</h1>
@endsection

