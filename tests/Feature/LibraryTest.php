<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Student;
use App\Models\Borrow;

class LibraryTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_checkout_book()
    {
        $book = Book::create([
            'title' => 'The Great Gatsby',
            'author' => 'F. Scott Fitzgerald',
            'isbn' => '1234567890',
            'status' => 'available'
        ]);

        $student = Student::create([
            'name' => 'John Doe',
            'student_id' => 'S12345',
            'email' => 'john@example.com'
        ]);

        $response = $this->postJson('/api/checkout', [
            'book_id' => $book->id,
            'student_id' => $student->id,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('borrows', [
            'book_id' => $book->id,
            'student_id' => $student->id,
            'return_date' => null,
        ]);
        $this->assertEquals('borrowed', $book->fresh()->status);
    }

    public function test_student_can_checkin_book()
    {
        $book = Book::create([
            'title' => 'The Great Gatsby',
            'author' => 'F. Scott Fitzgerald',
            'isbn' => '1234567890',
            'status' => 'borrowed'
        ]);

        $student = Student::create([
            'name' => 'John Doe',
            'student_id' => 'S12345',
            'email' => 'john@example.com'
        ]);

        Borrow::create([
            'book_id' => $book->id,
            'student_id' => $student->id,
            'borrow_date' => now(),
        ]);

        $response = $this->postJson('/api/checkin', [
            'book_id' => $book->id,
        ]);

        $response->assertStatus(200);
        $this->assertNotNull(Borrow::first()->return_date);
        $this->assertEquals('available', $book->fresh()->status);
    }

    public function test_cannot_checkout_unavailable_book()
    {
        $book = Book::create([
            'title' => 'The Great Gatsby',
            'author' => 'F. Scott Fitzgerald',
            'isbn' => '1234567890',
            'status' => 'borrowed'
        ]);

        $student = Student::create([
            'name' => 'John Doe',
            'student_id' => 'S12345',
            'email' => 'john@example.com'
        ]);

        $response = $this->postJson('/api/checkout', [
            'book_id' => $book->id,
            'student_id' => $student->id,
        ]);

        $response->assertStatus(400);
    }
}
