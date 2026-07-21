<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Student;
use App\Models\Borrow;
use App\Models\Fine;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $books = Book::with(['borrows' => function ($query) {
            $query->whereNull('return_date')->with(['student', 'fine']);
        }])->get();
        
        $students = Student::all();
        
        return view('books.index', compact('books', 'students'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('books.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'author' => 'required',
            'isbn' => 'required|unique:books',
            'amount' => 'required|numeric|min:0',
        ]);

        Book::create($request->all());

        return redirect()->route('books.index')->with('success', 'Book created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Book $book)
    {
        return view('books.edit', compact('book'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Book $book)
    {
        $request->validate([
            'title' => 'required',
            'author' => 'required',
            'isbn' => 'required|unique:books,isbn,' . $book->id,
            'amount' => 'required|numeric|min:0',
            'status' => 'required'
        ]);

        $book->update($request->all());

        return redirect()->route('books.index')->with('success', 'Book updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        $book->delete();
        return redirect()->route('books.index')->with('success', 'Book deleted successfully.');
    }

    // Custom actions for frontend borrowing
    public function checkout(Request $request, Book $book)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        if ($book->status !== 'available') {
             return back()->with('error', 'Book is not available.');
        }

        $borrow = Borrow::create([
            'book_id' => $book->id,
            'student_id' => $request->student_id,
            'borrow_date' => Carbon::now(),
        ]);

        // Auto-generate fine marked as unpaid (amount = book's default fine amount)
        Fine::create([
            'borrow_id' => $borrow->id,
            'amount' => $book->amount ?? 5.00,
            'paid_status' => false,
        ]);

        $book->update(['status' => 'borrowed']);

        return back()->with('success', 'Book checked out successfully.');
    }

    public function checkoutGeneric(Request $request) {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'student_id' => 'required|exists:students,id',
        ]);
        
        $book = Book::find($request->book_id);
        
        return $this->checkout($request, $book);
    }

    public function checkin(Book $book)
    {
        $borrow = Borrow::where('book_id', $book->id)
            ->whereNull('return_date')
            ->with('fine')
            ->first();

        if ($borrow) {
            // Check if fine is paid before allowing checkin
            if ($borrow->fine && !$borrow->fine->paid_status) {
                return back()->with('error', 'Cannot return book "' . $book->title . '". The fine of $' . number_format($borrow->fine->amount, 2) . ' must be paid first on the Fines page!');
            }

            $borrow->update(['return_date' => Carbon::now()]);
        }

        $book->update(['status' => 'available']);

        return back()->with('success', 'Book returned successfully.');
    }
}
