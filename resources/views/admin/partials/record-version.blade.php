@php($versionRecord = $versionRecord ?? ($record ?? null))
@if ($versionRecord instanceof \Illuminate\Database\Eloquent\Model && $versionRecord->exists && $versionRecord->updated_at !== null)
    <input type="hidden" name="expected_updated_at" value="{{ $versionRecord->updated_at->toIso8601String() }}">
    <input type="hidden" name="expected_version" value="{{ app(\App\Services\ContentVersion::class)->token($versionRecord) }}">
@endif
