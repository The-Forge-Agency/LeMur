@extends('layouts.app')

@section('title', $wall->name . ' · LeMur')
@section('og-title', $wall->name . ' · un mur LeMur')
@section('description', 'Colle ta note sur « ' . $wall->name . ' » — sans compte, en deux secondes.')
@section('no-index', '1')

@section('content')
    <livewire:wall-board :wall="$wall" />
@endsection
