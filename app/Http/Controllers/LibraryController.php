<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Book;
use App\Models\Borrow;
use App\Models\Student;
use Carbon\Carbon;

class LibraryController extends Controller
{
    public function checkout(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'student_id' => 'required|exists:students,id',
        ]);

        $book = Book::find($request->book_id);

        if ($book->status !== 'available') {
            return response()->json(['message' => 'Book is not available'], 400);
        }

        $borrow = Borrow::create([
            'book_id' => $request->book_id,
            'student_id' => $request->student_id,
            'borrow_date' => Carbon::now(),
        ]);

        $book->update(['status' => 'borrowed']);

        return response()->json(['message' => 'Book checked out successfully', 'borrow' => $borrow]);
    }

    public function checkin(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
        ]);

        $borrow = Borrow::where('book_id', $request->book_id)
            ->whereNull('return_date')
            ->first();

        if (!$borrow) {
            return response()->json(['message' => 'No active borrow record found for this book'], 404);
        }

        $borrow->update(['return_date' => Carbon::now()]);

        $book = Book::find($request->book_id);
        $book->update(['status' => 'available']);

        return response()->json(['message' => 'Book returned successfully']);
    }
}
