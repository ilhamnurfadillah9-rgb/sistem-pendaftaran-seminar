<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Seminar;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeminarController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());

        $seminars = Seminar::query()
            ->withCount('registrations')
            ->when(
                $search !== '',
                fn ($query) => $query->where('judul', 'like', '%'.$search.'%'),
            )
            ->orderBy('tanggal')
            ->orderBy('waktu_mulai')
            ->orderBy('id')
            ->paginate(9)
            ->withQueryString();

        return view('public.seminars.index', compact('seminars', 'search'));
    }

    public function show(Seminar $seminar): View
    {
        $seminar->loadCount('registrations');

        return view('public.seminars.show', compact('seminar'));
    }
}
