<?php

use App\Livewire\Dashboard;
use App\Livewire\Auth\Login;
use App\Livewire\Users\UserForm;
use App\Livewire\Users\UserShow;
use App\Livewire\Users\UserIndex;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Livewire\Accounts\AccountEdit;
use App\Livewire\Accounts\AccountShow;
use App\Livewire\Accounts\AccountIndex;
use App\Livewire\Accounts\AccountCreate;
use App\Livewire\Accounts\AccountTransaction;
use App\Livewire\Customers\CustomerCreate;
use App\Livewire\Customers\CustomerEdit;
use App\Livewire\Customers\CustomerIndex;
use App\Livewire\Customers\CustomerShow;
use App\Livewire\Loans\LoanApplication;
use App\Livewire\Loans\LoanIndex;
use App\Livewire\Loans\LoanReview;
use App\Livewire\Loans\LoanShow;
use App\Livewire\Transactions\TransactionCreate;
use App\Livewire\Transactions\TransactionIndex;
use App\Livewire\Transactions\ViewTransaction;

Route::get('/', fn() => view('welcome'))->name('home');

/*
| Guest routes
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

/*
| Authenticated routes
*/
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', UserIndex::class)->name('index');
        Route::get('/create', UserForm::class)->name('create');
        Route::get('/{user}', UserShow::class)->name('show');
        Route::get('/{user}/edit', UserForm::class)->name('edit');
    });

    //Accounts routes
    Route::prefix('accounts')->name('accounts.')->group(function () {
        Route::get('/', AccountIndex::class)->name('index');
        Route::get('/create', AccountCreate::class)->name('create');
        Route::get('/{account}', AccountShow::class)->name('show');
        Route::get('/{account}/edit', AccountEdit::class)->name('edit');
        Route::get('/transactions/{account}', AccountTransaction::class)->name('transactions');
    });

    //Customers routes
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', CustomerIndex::class)->name('index');
        Route::get('/create', CustomerCreate::class)->name('create');
        Route::get('/{customer}', CustomerShow::class)->name('show');
        Route::get('/{customer}/edit', CustomerEdit::class)->name('edit');
    });

    //Transactions routes
    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/', TransactionIndex::class)->name('index');
        Route::get('/create', TransactionCreate::class)->name('create');
        Route::get('/{transaction}', ViewTransaction::class)->name('show');
    });

    //Reports routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', TransactionIndex::class)->name('index');
        // Route::get('/create', TransactionCreate::class)->name('create');
        // Route::get('/{transaction}', ViewTransaction::class)->name('show');
    });

    //Loans routes
    Route::prefix('loans')->name('loans.')->group(function () {
        Route::get('/', LoanIndex::class)->name('index');
        Route::get('/create', LoanApplication::class)->name('create');
        Route::get('/{loan}', LoanShow::class)->name('show');
        Route::get('/{loan}/review', LoanReview::class)->name('review');
    });
});

/*
| Health
*/
Route::get('/health', fn() => response()->json(['status' => 'ok']));
