<?php

namespace App\Http\Controllers;

use App\Models\Fine;
use App\Models\Student;
use App\Models\Borrow; // Import Borrow model
use Illuminate\Http\Request;

class FineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $fines = Fine::with(['borrow.student', 'borrow.book'])->latest()->get();
        $borrows = Borrow::with(['student', 'book'])->latest()->get();
        return view('fines.index', compact('fines', 'borrows'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'borrow_id' => 'required|exists:borrows,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        Fine::create([
            'borrow_id' => $request->borrow_id,
            'amount' => $request->amount,
            'paid_status' => false,
        ]);

        return back()->with('success', 'Fine created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Fine $fine)
    {
        // Toggle paid status
        $fine->update([
            'paid_status' => !$fine->paid_status
        ]);

        return back()->with('success', 'Fine status updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Fine $fine)
    {
        $fine->delete();
        return back()->with('success', 'Fine deleted.');
    }
}
