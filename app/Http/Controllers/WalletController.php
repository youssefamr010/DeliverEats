<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $transactions = Transaction::where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('wallet.index', compact('user', 'transactions'));
    }

    public function topUp(Request $request)
    {
        return app(StripeController::class)->topUpWallet($request);
    }
}
