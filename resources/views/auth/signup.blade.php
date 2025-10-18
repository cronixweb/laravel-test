@extends('layouts.app')

@section('title', 'Create your tenant')

@push('head')
    <style>
        .auth-card {
            max-width: 420px;
            margin: 2rem auto;
        }
        .auth-card h1 {
            margin-top: 0;
            margin-bottom: 1.5rem;
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
        .auth-card .actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .auth-card .errors {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            border-radius: 0.6rem;
            padding: 0.9rem 1.1rem;
        }
        .auth-card small {
            color: #64748b;
        }
    </style>
@endpush

@section('content')
    <div class="card auth-card">
        <h1>Create your workspace</h1>
        <p style="margin-top:0; color:#64748b;">Signup creates a tenant and the first admin user for your team.</p>

        @if ($errors->any())
            <div class="errors">
                <strong>Please review the highlighted fields.</strong>
            </div>
        @endif

        <form method="post" action="{{ route('signup.store') }}">
            @csrf
            <div class="field">
                <label for="company_name">Company or team name</label>
                <input id="company_name" name="company_name" type="text" value="{{ old('company_name') }}" required maxlength="255">
            </div>
            <div class="field">
                <label for="tenant_slug">Tenant slug</label>
                <input id="tenant_slug" name="tenant_slug" type="text" value="{{ old('tenant_slug') }}" required maxlength="50" placeholder="e.g. acme-co">
                <small>Used during login. Only letters, numbers, and dashes.</small>
                @error('tenant_slug')
                    <small style="color:#b91c1c;">{{ $message }}</small>
                @enderror
            </div>
            <div class="field">
                <label for="name">Your name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="255">
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
                <input id="password" name="password" type="password" required minlength="8">
            </div>
            <div class="field">
                <label for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8">
                @error('password')
                    <small style="color:#b91c1c;">{{ $message }}</small>
                @enderror
            </div>
            <div class="actions">
                <a href="{{ route('login') }}" style="color:#2563eb; text-decoration:none; font-weight:600;">Have an account?</a>
                <button type="submit" class="button">Create workspace</button>
            </div>
        </form>
    </div>
@endsection
