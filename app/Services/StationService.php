<?php

namespace App\Services;

use App\Models\DriverStationAuditLog;
use App\Models\Station;
use App\Models\User;
use App\Services\Concerns\CompressesImages;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StationService
{
    use CompressesImages;

    public static function normalizeConnectors(?array $connectors): array
    {
        $merged = [];

        foreach ($connectors ?? [] as $row) {
            $type = $row['type'] ?? null;
            $total = (int) ($row['total'] ?? 0);

            if (!$type || $total < 1) {
                continue;
            }

            if (!isset($merged[$type])) {
                $merged[$type] = ['type' => $type, 'total' => 0];
            }

            $merged[$type]['total'] += $total;
        }

        return array_values($merged);
    }

    public function syncConnectors(Station $station, ?array $connectors): void
    {
        $station->connectors()->delete();

        foreach (self::normalizeConnectors($connectors) as $row) {
            $station->connectors()->create([
                'type'  => $row['type'],
                'total' => $row['total'],
            ]);
        }
    }

    public function storeUploadedImage(?UploadedFile $file, ?string $existingPath = null): ?string
    {
        if (!$file) {
            return $existingPath;
        }

        if ($existingPath) {
            Storage::disk('public')->delete($existingPath);
        }

        return $this->compressAndStoreImage($file, 'stations');
    }

    public function appendGalleryImages(Request $request, array $existing = []): array
    {
        $gallery = collect($existing)->filter()->unique()->values()->all();

        if (!$request->hasFile('gallery_images')) {
            return $gallery;
        }

        foreach ($request->file('gallery_images') as $galleryImage) {
            if (!$galleryImage) {
                continue;
            }
            $gallery[] = $this->compressAndStoreImage($galleryImage, 'stations');
        }

        return $gallery;
    }

    public function createForDriver(User $user, array $validated, Request $request): Station
    {
        $station = Station::create([
            'user_id'         => $user->id,
            'name'            => $validated['name'],
            'address'         => $validated['address'],
            'lat'             => $validated['lat'],
            'lng'             => $validated['lng'],
            'open_time'       => $validated['open_time'] ?? null,
            'close_time'      => $validated['close_time'] ?? null,
            'image'           => $this->storeUploadedImage($request->file('image')),
            'gallery_images'  => $this->appendGalleryImages($request, []),
            'approval_status' => 'pending',
        ]);

        $station->facilities()->sync($validated['facilities'] ?? []);
        $this->syncConnectors($station, $validated['connectors'] ?? []);

        return $station;
    }

    public function createForProvider(User $user, array $validated, Request $request): Station
    {
        $station = Station::create([
            'user_id'         => $user->id,
            'name'            => $validated['name'],
            'address'         => $validated['address'],
            'lat'             => $validated['lat'],
            'lng'             => $validated['lng'],
            'open_time'       => $validated['open_time'] ?? null,
            'close_time'      => $validated['close_time'] ?? null,
            'image'           => $this->storeUploadedImage($request->file('image')),
            'gallery_images'  => $this->appendGalleryImages($request, []),
            'approval_status' => 'approved',
        ]);

        $station->facilities()->sync($validated['facilities'] ?? []);

        return $station;
    }

    public function updateDirect(Station $station, array $validated, Request $request): void
    {
        $station->update([
            'name'           => $validated['name'],
            'address'        => $validated['address'],
            'lat'            => $validated['lat'],
            'lng'            => $validated['lng'],
            'open_time'      => $validated['open_time'] ?? null,
            'close_time'     => $validated['close_time'] ?? null,
            'image'          => $this->storeUploadedImage($request->file('image'), $station->image),
            'gallery_images' => $this->appendGalleryImages($request, $station->gallery_images ?? []),
        ]);

        if (array_key_exists('facilities', $validated)) {
            $station->facilities()->sync($validated['facilities'] ?? []);
        }
    }

    public function submitEditRequest(Station $station, User $driver, array $validated, Request $request): void
    {
        $station->load(['connectors', 'facilities']);
        $before = $this->stationSnapshot($station);

        $afterImage = $station->image;
        if ($request->hasFile('image')) {
            $afterImage = $this->compressAndStoreImage($request->file('image'), 'stations');
        }

        $after = [
            'name'           => $validated['name'],
            'address'        => $validated['address'],
            'lat'            => $validated['lat'],
            'lng'            => $validated['lng'],
            'open_time'      => $validated['open_time'] ?? null,
            'close_time'     => $validated['close_time'] ?? null,
            'image'          => $afterImage,
            'gallery_images' => $this->appendGalleryImages($request, $station->gallery_images ?? []),
            'facilities'     => $validated['facilities'] ?? [],
            'connectors'     => self::normalizeConnectors($validated['connectors'] ?? []),
        ];

        DriverStationAuditLog::create([
            'driver_id'  => $driver->id,
            'station_id' => $station->id,
            'action'     => 'edit',
            'status'     => 'pending',
            'reason'     => null,
            'payload'    => [
                'before' => $before,
                'after'  => $after,
            ],
        ]);
    }

    public function submitDeleteRequest(Station $station, User $driver, string $reason): void
    {
        $station->load(['connectors', 'facilities']);

        DriverStationAuditLog::create([
            'driver_id'  => $driver->id,
            'station_id' => $station->id,
            'action'     => 'delete',
            'status'     => 'pending',
            'reason'     => $reason,
            'payload'    => [
                'snapshot' => $this->stationSnapshot($station),
            ],
        ]);
    }

    public function approveAuditLog(DriverStationAuditLog $log): void
    {
        if ($log->action === 'delete') {
            $log->update(['status' => 'approved']);
            $this->deleteStation(Station::findOrFail($log->station_id));

            return;
        }

        $this->applyAuditEdit($log);
        $log->update(['status' => 'approved']);
    }

    public function rejectAuditLog(DriverStationAuditLog $log): void
    {
        if ($log->action === 'edit') {
            $this->discardPendingUploads($log);
        }

        $log->update(['status' => 'rejected']);
    }

    public function applyAuditEdit(DriverStationAuditLog $log): void
    {
        $station = Station::findOrFail($log->station_id);
        $before = $log->payload['before'] ?? [];
        $after = $log->payload['after'] ?? [];

        if (!empty($before['image']) && ($after['image'] ?? null) !== $before['image']) {
            Storage::disk('public')->delete($before['image']);
        }

        $station->update([
            'name'           => $after['name'] ?? $station->name,
            'address'        => $after['address'] ?? $station->address,
            'lat'            => $after['lat'] ?? $station->lat,
            'lng'            => $after['lng'] ?? $station->lng,
            'open_time'      => $after['open_time'] ?? null,
            'close_time'     => $after['close_time'] ?? null,
            'image'          => $after['image'] ?? null,
            'gallery_images' => $after['gallery_images'] ?? [],
        ]);

        if (array_key_exists('facilities', $after)) {
            $station->facilities()->sync($after['facilities'] ?? []);
        }

        $this->syncConnectors($station, $after['connectors'] ?? []);
    }

    public function deleteStation(Station $station): void
    {
        if (!empty($station->image)) {
            Storage::disk('public')->delete($station->image);
        }

        foreach (collect($station->gallery_images ?? []) as $img) {
            if ($img) {
                Storage::disk('public')->delete($img);
            }
        }

        $station->delete();
    }

    public function discardPendingUploads(DriverStationAuditLog $log): void
    {
        $before = $log->payload['before'] ?? [];
        $after = $log->payload['after'] ?? [];

        if (!empty($after['image']) && ($after['image'] ?? null) !== ($before['image'] ?? null)) {
            Storage::disk('public')->delete($after['image']);
        }

        $beforeGallery = collect($before['gallery_images'] ?? []);
        foreach (collect($after['gallery_images'] ?? []) as $img) {
            if ($img && !$beforeGallery->contains($img)) {
                Storage::disk('public')->delete($img);
            }
        }
    }

    public function stationSnapshot(Station $station): array
    {
        $station->loadMissing(['connectors', 'facilities']);

        return [
            'name'           => $station->name,
            'address'        => $station->address,
            'lat'            => $station->lat,
            'lng'            => $station->lng,
            'open_time'      => $station->open_time,
            'close_time'     => $station->close_time,
            'image'          => $station->image,
            'gallery_images' => $station->gallery_images ?? [],
            'facilities'     => $station->facilities->pluck('id')->all(),
            'connectors'     => $station->connectors
                ->map(fn ($c) => ['type' => $c->type, 'total' => (int) $c->total])
                ->values()
                ->all(),
        ];
    }

    public function resolveConnectorRowsForEdit(Station $station, ?DriverStationAuditLog $pendingEdit): array
    {
        if (filled(old('connectors'))) {
            return array_values(old('connectors'));
        }

        $pendingConnectors = $pendingEdit?->payload['after']['connectors'] ?? null;
        if (is_array($pendingConnectors) && !empty($pendingConnectors)) {
            return array_values($pendingConnectors);
        }

        if ($station->connectors->isNotEmpty()) {
            return $station->connectors
                ->map(fn ($c) => ['type' => $c->type, 'total' => (int) $c->total])
                ->values()
                ->all();
        }

        return [['type' => '', 'total' => 1]];
    }

    public function hasPendingAudit(Station $station): bool
    {
        return DriverStationAuditLog::where('station_id', $station->id)
            ->pending()
            ->exists();
    }

    public function assertDriverOwnsStation(Station $station, int $driverId): void
    {
        if ($station->user_id !== $driverId) {
            abort(403);
        }
    }

    public function assertUserOwnsStation(Station $station, int $userId): void
    {
        if ($station->user_id !== $userId) {
            abort(403);
        }
    }
}
