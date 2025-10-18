@extends('layouts.app')

@section('title', 'Sign in')

@push('head')
    <style>
        .auth-card {
            max-width: 380px;
            margin: 2rem auto;
        }
        .auth-card h1 {
            margin-top: 0;
            margin-bottom: 1.25rem;
        }
        .auth-card form {
            display: grid;
            gap: 1rem;
        }
        .auth-card .field {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }
        .auth-card label {
            font-weight: 600;
        }
        .auth-card input {
            border: 1px solid #cbd5f5;
            border-radius: 0.6rem;
            padding: 0.7rem 1rem;
            font: inherit;
        }
        .auth-card .errors {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            border-radius: 0.6rem;
            padding: 0.9rem 1.1rem;
        }
        .auth-card .actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .auth-card small {
            color: #64748b;
        }
    </style>
@endpush

@section('content')
    <div class="card auth-card">
        <h1>Welcome back</h1>
        <p style="margin-top:0; color:#64748b;">Enter your tenant slug along with email and password to continue.</p>

        @if ($errors->any())
            <div class="errors">
                <strong>We could not sign you in. Please check your details.</strong>
            </div>
        @endif

        <form method="post" action="{{ route('login.store') }}">
            @csrf
            <div class="field">
                <label for="tenant_slug">Tenant slug</label>
                <input id="tenant_slug" name="tenant_slug" type="text" value="{{ old('tenant_slug') }}" required maxlength="50">
                @error('tenant_slug')
                    <small style="color:#b91c1c;">{{ $message }}</small>
                @enderror
            </div>
            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="255">
                @error('email')
                    <small style="color:#b91c1c;">{{ $message }}</small>
                @enderror
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required>
                @error('password')
                    <small style="color:#b91c1c;">{{ $message }}</small>
                @enderror
            </div>
            <div class="actions">
                <a href="{{ route('signup') }}" style="color:#2563eb; text-decoration:none; font-weight:600;">Create a tenant</a>
                <button type="submit" class="button">Sign in</button>
            </div>
        </form>
    </div>
@endsection
