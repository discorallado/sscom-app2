<?php

use App\Http\Livewire\Form;
use App\Http\Controllers\PdfController;

\Illuminate\Support\Facades\Route::get('/home', function(){
  return view('welcome');
});

\Illuminate\Support\Facades\Route::get('form', Form::class);

\Illuminate\Support\Facades\Route::get('pdf/{model}/{id}', PdfController::class)->name('pdf');
