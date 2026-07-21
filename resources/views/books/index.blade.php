@extends('layouts.app')

@section('header', 'Books')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <p class="text-gray-600">Manage your library books and circulation.</p>
        <div class="flex gap-4">
             <input type="text" id="bookSearch" placeholder="Search title, author, ISBN..." class="shadow border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <a href="{{ route('books.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded transition duration-150 ease-in-out">
                + Add New Book
            </a>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ISBN</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Default Fine Amount</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($books as $book)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $book->title }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $book->author }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $book->isbn }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">${{ number_format($book->amount, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($book->status === 'available')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Available
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Borrowed
                                </span>
                                @if($book->borrows->isNotEmpty())
                                    @php
                                        $activeBorrow = $book->borrows->first();
                                        $fine = $activeBorrow ? $activeBorrow->fine : null;
                                        $daysBorrowed = \Carbon\Carbon::parse($activeBorrow->borrow_date)->diffInDays(\Carbon\Carbon::now());
                                    @endphp
                                    <div class="text-xs text-gray-500 mt-1">
                                        by {{ $activeBorrow->student->name }}
                                        @if($fine && !$fine->paid_status)
                                            <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                Unpaid Fine (${{ number_format($fine->amount, 2) }})
                                            </span>
                                        @elseif($fine && $fine->paid_status)
                                            <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                Fine Paid
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-3">
                                <a href="{{ route('books.edit', $book) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                
                                <form action="{{ route('books.destroy', $book) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                </form>

                                <div class="h-4 w-px bg-gray-300"></div>

                                @if($book->status === 'available')
                                    <button onclick="openCheckoutModal({{ $book->id }}, '{{ addslashes($book->title) }}')" class="text-blue-600 hover:text-blue-900 font-semibold">Checkout</button>
                                @else
                                    @php
                                        $activeBorrow = $book->borrows->first();
                                        $fine = $activeBorrow ? $activeBorrow->fine : null;
                                        $fineUnpaid = $fine && !$fine->paid_status;
                                    @endphp
                                    @if($fineUnpaid)
                                        <a href="{{ route('fines.index') }}" title="Unpaid fine detected. Click Return to pay fine on Fines page." class="text-amber-600 hover:text-amber-900 font-semibold">
                                            Return
                                        </a>
                                    @else
                                        <form action="{{ route('books.checkin', $book) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-green-600 hover:text-green-900 font-semibold">Return</button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Checkout Modal -->
    <div id="checkoutModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Checkout Book</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500 mb-4">
                        Assign <span id="modalBookTitle" class="font-bold"></span> to a student.
                    </p>
                    <form id="checkoutForm" method="POST" action="">
                        @csrf
                        <div class="mb-4 text-left">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="student_id">Student</label>
                            <select name="student_id" id="student_id" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->student_id }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="items-center px-4 py-3">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                Confirm Checkout
                            </button>
                        </div>
                    </form>
                </div>
                <div class="items-center px-4 py-3">
                    <button onclick="closeCheckoutModal()" class="px-4 py-2 bg-gray-200 text-gray-800 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-300 focus:outline-none">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('bookSearch').addEventListener('keyup', function() {
            var input = document.getElementById('bookSearch');
            var filter = input.value.toLowerCase();
            var table = document.querySelector('table');
            var tr = table.getElementsByTagName('tr');

            for (var i = 1; i < tr.length; i++) {
                var tdTitle = tr[i].getElementsByTagName('td')[0];
                var tdAuthor = tr[i].getElementsByTagName('td')[1];
                var tdIsbn = tr[i].getElementsByTagName('td')[2];
                
                if (tdTitle || tdAuthor || tdIsbn) {
                    var txtTitle = tdTitle.textContent || tdTitle.innerText;
                    var txtAuthor = tdAuthor.textContent || tdAuthor.innerText;
                    var txtIsbn = tdIsbn.textContent || tdIsbn.innerText;
                    
                    if (
                        txtTitle.toLowerCase().indexOf(filter) > -1 || 
                        txtAuthor.toLowerCase().indexOf(filter) > -1 || 
                        txtIsbn.toLowerCase().indexOf(filter) > -1
                    ) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        });

        function openCheckoutModal(bookId, bookTitle) {
            document.getElementById('checkoutModal').classList.remove('hidden');
            document.getElementById('modalBookTitle').innerText = bookTitle;
            document.getElementById('checkoutForm').action = "/books/" + bookId + "/checkout";
        }

        function closeCheckoutModal() {
            document.getElementById('checkoutModal').classList.add('hidden');
        }
    </script>
@endsection
