<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
|
| These routes are for the public-facing website - accessible without
| authentication. They showcase the care home to potential residents
| and their families.
|
*/

Route::livewire('/', 'pages::public.home')->name('home');
Route::livewire('/about', 'pages::public.about')->name('about');
Route::livewire('/services', 'pages::public.services')->name('services');
Route::livewire('/gallery', 'pages::public.gallery')->name('gallery');
Route::livewire('/faq', 'pages::public.faq')->name('faq');
Route::livewire('/contact', 'pages::public.contact')->name('contact');
