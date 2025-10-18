@extends('layouts.app')

@section('title', 'Quick Add Expense')

@push('head')
    <style>
        form.expense-form {
            display: grid;
            gap: 1.25rem;
        }
        .field {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .field label {
            font-weight: 600;
            color: #0f172a;
        }
        .field input,
        .field select,
        .field textarea {
            border: 1px solid #cbd5f5;
            border-radius: 0.6rem;
            padding: 0.75rem 1rem;
            font: inherit;
            width: 100%;
        }
        .field small {
            color: #64748b;
        }
        .actions {
            display: flex;
            justify-content: flex-end;
        }
        .error-list {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            padding: 1rem 1.25rem;
            border-radius: 0.6rem;
            margin-bottom: 1.5rem;
        }
        .error-list ul {
            margin: 0.5rem 0 0;
            padding-left: 1.2rem;
        }
        .error-list li {
            margin-bottom: 0.4rem;
        }
    </style>
@endpush

@section('content')
    <div class="card">
        <h1 style="margin-top:0; margin-bottom: 1.5rem;">Record a new expense</h1>

        @if ($errors->any())
            <div class="error-list">
                <strong>Please fix the following:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="post" action="{{ route('expenses.store') }}" class="expense-form">
            @csrf
            <div class="field">
                <label for="description">Description <span style="color:#64748b; font-weight:400;">(optional)</span></label>
                <input type="text" id="description" name="description" value="{{ old('description') }}" maxlength="255" placeholder="e.g. Utility bill">
                <small>Keep it short so it is easy to scan later.</small>
            </div>

            <div class="field">
                <label for="type">Expense type</label>
                <select name="type" id="type" required>
                    <option value="">Select type</option>
                    @foreach ($types as $type)
                        <option value="{{ $type }}" @selected(old('type') === $type)>{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="amount">Amount (INR)</label>
                <input type="number" id="amount" name="amount" value="{{ old('amount') }}" min="0.01" step="0.01" placeholder="0.00" required>
                <small>All expenses are stored in INR.</small>
            </div>

            <div class="actions">
                <button type="submit" class="button">Save expense</button>
            </div>
        </form>
    </div>
@endsection
