<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSeminarRequest;
use App\Http\Requests\UpdateSeminarRequest;
use App\Models\Seminar;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class SeminarController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $search = is_string($search) ? trim($search) : '';

        $seminars = Seminar::query()
            ->withCount('registrations')
            ->when(
                $search !== '',
                fn ($query) => $query->where('judul', 'like', '%'.$search.'%'),
            )
            ->orderByDesc('tanggal')
            ->orderBy('waktu_mulai')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.seminars.index', compact('seminars', 'search'));
    }

    public function create(): View
    {
        return view('admin.seminars.create');
    }

    public function store(StoreSeminarRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $flyerPath = $this->storeFlyer($request->file('flyer'));
        $validated['flyer'] = $flyerPath;

        try {
            $seminar = DB::transaction(
                fn (): Seminar => Seminar::create($validated),
            );
        } catch (Throwable $exception) {
            $this->deleteStoredFile($flyerPath);

            throw $exception;
        }

        return redirect()
            ->route('admin.seminars.show', $seminar)
            ->with('success', 'Seminar berhasil ditambahkan.');
    }

    public function show(Seminar $seminar): View
    {
        $seminar->loadCount('registrations');

        return view('admin.seminars.show', compact('seminar'));
    }

    public function edit(Seminar $seminar): View
    {
        return view('admin.seminars.edit', compact('seminar'));
    }

    public function update(UpdateSeminarRequest $request, Seminar $seminar): RedirectResponse
    {
        $validated = $request->validated();
        unset($validated['flyer']);

        $oldFlyerPath = $seminar->flyer;
        $newFlyerPath = null;

        if ($request->hasFile('flyer')) {
            $newFlyerPath = $this->storeFlyer($request->file('flyer'));
            $validated['flyer'] = $newFlyerPath;
        }

        try {
            DB::transaction(fn (): bool => $seminar->update($validated));
        } catch (Throwable $exception) {
            if ($newFlyerPath !== null) {
                $this->deleteStoredFile($newFlyerPath);
            }

            throw $exception;
        }

        if ($newFlyerPath !== null && $newFlyerPath !== $oldFlyerPath) {
            $this->deleteFlyerIfUnreferenced($oldFlyerPath);
        }

        return redirect()
            ->route('admin.seminars.show', $seminar)
            ->with('success', 'Perubahan seminar berhasil disimpan.');
    }

    public function destroy(Seminar $seminar): RedirectResponse
    {
        try {
            $deleted = DB::transaction(function () use ($seminar): bool {
                $lockedSeminar = Seminar::query()
                    ->lockForUpdate()
                    ->findOrFail($seminar->getKey());

                if ($lockedSeminar->registrations()->exists()) {
                    return false;
                }

                $lockedSeminar->delete();

                return true;
            });
        } catch (QueryException $exception) {
            if ($seminar->registrations()->exists()) {
                return redirect()
                    ->route('admin.seminars.index')
                    ->with('error', 'Seminar tidak dapat dihapus karena sudah memiliki pendaftar.');
            }

            throw $exception;
        }

        if (! $deleted) {
            return redirect()
                ->route('admin.seminars.index')
                ->with('error', 'Seminar tidak dapat dihapus karena sudah memiliki pendaftar.');
        }

        $this->deleteFlyerIfUnreferenced($seminar->flyer);

        return redirect()
            ->route('admin.seminars.index')
            ->with('success', 'Seminar berhasil dihapus.');
    }

    private function storeFlyer(UploadedFile $flyer): string
    {
        $path = $flyer->store('flyers', 'public');

        if (! is_string($path)) {
            throw new RuntimeException('Flyer seminar gagal disimpan.');
        }

        return $path;
    }

    private function deleteFlyerIfUnreferenced(string $path): void
    {
        if (
            ! Str::startsWith($path, 'flyers/')
            || Seminar::query()->where('flyer', $path)->exists()
        ) {
            return;
        }

        $this->deleteStoredFile($path);
    }

    private function deleteStoredFile(string $path): void
    {
        $disk = Storage::disk('public');

        if ($disk->exists($path) && ! $disk->delete($path)) {
            throw new RuntimeException('Flyer seminar gagal dihapus dari penyimpanan.');
        }
    }
}
