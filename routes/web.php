<?php

use App\Http\Controllers\DebugController;
use App\Http\Controllers\TechavivController;
use App\Spiders\ImdbTopMoviesSpider;
use App\Spiders\OpenLibrarySpider;
use Illuminate\Support\Facades\Route;
use RoachPHP\Roach;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/scrape-imdb', function () {
    $topMovies = Roach::collectSpider(ImdbTopMoviesSpider::class);
    $topMovies = array_map(fn ($item) => $item->all(), $topMovies);

    // file_put_contents('top-movies.json', json_encode($topMovies, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    storage_path(file_put_contents('top-movies.json', json_encode($topMovies, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)));

    dd($topMovies);
});

Route::get('/scrape-books', function () {
    $trendingBooks = Roach::collectSpider(OpenLibrarySpider::class);

    $trendingBooks = array_map(fn ($item) => $item->all(), $trendingBooks);

    storage_path(file_put_contents('trending-books.json', json_encode($trendingBooks, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)));
});

Route::prefix('techaviv')->group(function () {
    Route::get('members', [TechavivController::class, 'members'])->name('techaviv.members');
    Route::get('companies', [TechavivController::class, 'companies'])->name('techaviv.companies');
    Route::get('fund', [TechavivController::class, 'fund'])->name('techaviv.fund');
    Route::get('jobs', [TechavivController::class, 'jobs'])->name('techaviv.jobs');
    Route::get('portfolio', [TechavivController::class, 'portfolio'])->name('techaviv.portfolio');
    Route::get('team', [TechavivController::class, 'team'])->name('techaviv.team');
    Route::get('unicorns', [TechavivController::class, 'unicorns'])->name('techaviv.unicorns');
});

Route::prefix('startupnationcentral')->group(function () {

});

Route::get('debug', DebugController::class);
