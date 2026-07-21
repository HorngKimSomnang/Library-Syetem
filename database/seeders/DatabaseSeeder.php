<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Book;
use App\Models\Student;
use App\Models\Borrow;
use App\Models\Fine;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Students
        $s1 = Student::create(['name' => 'Alice Johnson', 'student_id' => 'S1001', 'email' => 'alice@example.com']);
        $s2 = Student::create(['name' => 'Bob Smith', 'student_id' => 'S1002', 'email' => 'bob@example.com']);
        $s3 = Student::create(['name' => 'Charlie Brown', 'student_id' => 'S1003', 'email' => 'charlie@example.com']);
        $s4 = Student::create(['name' => 'Diana Prince', 'student_id' => 'S1004', 'email' => 'diana@example.com']);

        // 2. Create Books with fine amounts
        $books = [
            ['title' => 'The Great Gatsby', 'author' => 'F. Scott Fitzgerald', 'isbn' => '9780743273565', 'amount' => 5.00],
            ['title' => 'To Kill a Mockingbird', 'author' => 'Harper Lee', 'isbn' => '9780061120084', 'amount' => 5.00],
            ['title' => '1984', 'author' => 'George Orwell', 'isbn' => '9780451524935', 'amount' => 7.50],
            ['title' => 'Pride and Prejudice', 'author' => 'Jane Austen', 'isbn' => '9780141439518', 'amount' => 5.00],
            ['title' => 'The Catcher in the Rye', 'author' => 'J.D. Salinger', 'isbn' => '9780316769480', 'amount' => 6.00],
            ['title' => 'The Hobbit', 'author' => 'J.R.R. Tolkien', 'isbn' => '9780547928227', 'amount' => 10.00],
            ['title' => 'Fahrenheit 451', 'author' => 'Ray Bradbury', 'isbn' => '9781451673319', 'amount' => 5.00],
            ['title' => 'Moby Dick', 'author' => 'Herman Melville', 'isbn' => '9781503280786', 'amount' => 8.00],
            ['title' => 'War and Peace', 'author' => 'Leo Tolstoy', 'isbn' => '9780199232765', 'amount' => 12.00],
            ['title' => 'The Odyssey', 'author' => 'Homer', 'isbn' => '9780140268867', 'amount' => 5.00],
        ];

        foreach ($books as $b) {
            Book::create($b);
        }

        // 3. Create History (Returned Books) with Fines
        $b1 = Book::where('title', 'The Great Gatsby')->first();
        $borrow1 = Borrow::create([
            'book_id' => $b1->id,
            'student_id' => $s1->id,
            'borrow_date' => Carbon::now()->subDays(30),
            'return_date' => Carbon::now()->subDays(5),
        ]);

        Fine::create([
            'borrow_id' => $borrow1->id,
            'amount' => $b1->amount,
            'paid_status' => true,
        ]);

        $b3 = Book::where('title', '1984')->first();
        $borrow2 = Borrow::create([
            'book_id' => $b3->id,
            'student_id' => $s2->id,
            'borrow_date' => Carbon::now()->subDays(20),
            'return_date' => Carbon::now()->subDays(2),
        ]);

        Fine::create([
            'borrow_id' => $borrow2->id,
            'amount' => $b3->amount,
            'paid_status' => true,
        ]);

        // 4. Create Active Borrows (Unpaid fine generated automatically upon checkout)
        // Alice borrows To Kill a Mockingbird
        $b2 = Book::where('title', 'To Kill a Mockingbird')->first();
        $this->checkout($b2, $s1, 25);

        // Charlie borrows The Hobbit
        $b6 = Book::where('title', 'The Hobbit')->first();
        $this->checkout($b6, $s3, 3);
    }

    private function checkout($book, $student, $daysAgo = 0)
    {
        $borrow = Borrow::create([
            'book_id' => $book->id,
            'student_id' => $student->id,
            'borrow_date' => Carbon::now()->subDays($daysAgo),
        ]);

        // Auto-create unpaid fine for this borrowing transaction
        Fine::create([
            'borrow_id' => $borrow->id,
            'amount' => $book->amount,
            'paid_status' => false,
        ]);

        $book->update(['status' => 'borrowed']);
    }
}
