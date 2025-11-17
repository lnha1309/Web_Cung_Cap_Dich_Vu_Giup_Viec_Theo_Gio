<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/appintroduction', function () {
    return view('appintroduction');
});

Route::get('/introduction',function(){
    return view('introduction');
});

Route::get('/post',function(){
    return view('post');
});

Route::get('/post-detail-1', function () {
    return view('post-detail-1');
});

Route::get('/post-detail-2', function () {
    return view('post-detail-2');
});

Route::get('/post-detail-3', function () {
    return view('post-detail-3');
});

Route::get('/contact', function () {
    return view('contact');
});

Route::get('/workerintroduction', function () {
    return view('workerintroduction');
});
Route::get('/login', function () {
    return view('login');
});


Route::get('/register', function () {
    return view('register');
});


Route::get('/select-address', function () {
    return view('select-address');
});

Route::get('/booking', function () {
    return view('booking');
});

Route::get('/apply', function () {
    return view('apply');
});
Route::get('/giupviectheogio', function () {
    return view('giupviectheogio');
});

Route::get('/giupviectheothang', function () {
    return view('giupviectheothang');
});


