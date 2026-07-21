@extends('layouts.app')

@section('header', 'Student Details')

@section('content')
    <div class="mb-6">
        <a href="{{ route('students.index') }}" class="text-indigo-600 hover:text-indigo-900 font-bold">&larr; Back to Students</a>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Student Information</h3>
        </div>
        <div class="border-t border-gray-200">
            <dl>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Full name</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $student->name }}</dd>
                </div>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Student ID</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $student->student_id }}</dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Email address</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $student->email }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Active Loans</h3>
            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                {{ $student->borrows->whereNull('return_date')->count() }} Active
            </span>
        </div>
        
        <!-- Borrow Book Form -->
        <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
            <h4 class="text-sm font-medium text-gray-900 mb-2">Issue a New Book</h4>
            <form action="{{ route('students.checkout.custom') }}" method="POST" class="flex gap-4">
                @csrf
                <input type="hidden" name="student_id" value="{{ $student->id }}">
                <div class="flex-1">
                    <select name="book_id" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        <option value="">-- Select a Book to Borrow --</option>
                        @foreach($availableBooks as $book)
                            <option value="{{ $book->id }}">{{ $book->title }} ({{ $book->isbn }})</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Borrow
                </button>
            </form>
        </div>

        <div class="border-t border-gray-200">
            @if($student->borrows->isEmpty())
                <div class="px-4 py-5 sm:px-6 text-gray-500 italic">
                    No borrowing history.
                </div>
            @else
                <ul class="divide-y divide-gray-200">
                    @foreach($student->borrows as $borrow)
                        <li class="px-4 py-4 sm:px-6 flex items-center justify-between hover:bg-gray-50">
                            <div class="flex flex-col">
                                <span class="text-sm font-medium {{ $borrow->return_date ? 'text-gray-500 line-through' : 'text-indigo-600' }} truncate">
                                    {{ $borrow->book->title }}
                                </span>
                                <span class="text-xs text-gray-500">
                                    Author: {{ $borrow->book->author }}
                                </span>
                                <div class="text-xs text-gray-400 mt-1">
                                    <span class="mr-2">Borrowed: {{ $borrow->borrow_date }}</span>
                                    @if($borrow->return_date)
                                        <span class="text-green-600">Returned: {{ $borrow->return_date }}</span>
                                    @else
                                        <span class="text-yellow-600">Active</span>
                                    @endif
                                </div>
                            </div>
                            <div class="ml-4 flex-shrink-0">
                                @if(!$borrow->return_date)
                                    <form action="{{ route('books.checkin', $borrow->book) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="font-medium text-green-600 hover:text-green-900 bg-green-50 px-3 py-1 rounded transition duration-150 ease-in-out">
                                            Return
                                        </button>
                                    </form>
                                @else
                                    <span class="text-gray-400 text-sm">Returned</span>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <!-- Fines Section -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg mt-6">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Fines & Penalties</h3>
        </div>
        
        <!-- Add Fine Form -->
        <div class="px-4 py-5 sm:px-6 bg-red-50 border-b border-red-100">
            <h4 class="text-sm font-medium text-red-800 mb-2">Assess Fine</h4>
            <form action="{{ route('fines.store') }}" method="POST" class="flex gap-4 items-end">
                @csrf
                <input type="hidden" name="student_id" value="{{ $student->id }}">
                <div class="flex-1">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Transaction (Book)</label>
                    <select name="borrow_id" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        @foreach($student->borrows as $borrow)
                            <option value="{{ $borrow->id }}">
                                {{ $borrow->book->title }} ({{ $borrow->borrow_date }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="w-32">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Amount ($)</label>
                    <input type="number" name="amount" step="0.01" min="0" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Add Fine
                </button>
            </form>
        </div>

        <div class="border-t border-gray-200">
             @php
                // Eager load fines via borrows if not already loaded? 
                // Creating a direct relationships on Student model would be better: hasManyThrough.
                // But for now, let's just loop borrows and check for fines. 
                // Or better: In Controller, pass $fines.
            @endphp
            
            <ul class="divide-y divide-gray-200">
                @php $hasFines = false; @endphp
                @foreach($student->borrows as $borrow)
                    @if($borrow->fine)
                        @php $hasFines = true; @endphp
                        <li class="px-4 py-4 sm:px-6 flex items-center justify-between hover:bg-gray-50">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-red-600">
                                    ${{ number_format($borrow->fine->amount, 2) }} Fine
                                </span>
                                <span class="text-xs text-gray-500">
                                    Book: {{ $borrow->book->title }}
                                </span>
                                <span class="text-xs {{ $borrow->fine->paid_status ? 'text-green-600' : 'text-red-500' }} font-semibold">
                                    {{ $borrow->fine->paid_status ? 'PAID' : 'UNPAID' }}
                                </span>
                            </div>
                            <div class="ml-4 flex-shrink-0">
                                <form action="{{ route('fines.update', $borrow->fine) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="font-medium text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-1 rounded">
                                        {{ $borrow->fine->paid_status ? 'Mark Unpaid' : 'Mark Paid' }}
                                    </button>
                                </form>
                            </div>
                        </li>
                    @endif
                @endforeach
                
                @if(!$hasFines)
                    <div class="px-4 py-5 sm:px-6 text-gray-500 italic">
                        No fines record found.
                    </div>
                @endif
            </ul>
        </div>
    </div>
@endsection
