<?php

declare(strict_types=1);

namespace Daikazu\FilamentMeta\Concerns;

use Daikazu\FilamentMeta\Support\MetaState;
use Daikazu\LaravelMeta\Contracts\HasMetadata;

/**
 * Mix into a Filament CreateRecord / EditRecord (or any page exposing
 * $this->record) to transparently load and persist `meta.*` form fields
 * against the record's metadata. No manual hydration required.
 *
 * The page's model MUST use the `daikazu/laravel-meta` `HasMeta` trait AND
 * implement its `HasMetadata` interface (`class Product extends Model implements
 * HasMetadata`). Records that do not implement `HasMetadata` are left untouched.
 *
 * The Filament default hooks return the data unchanged, so these overrides
 * intentionally do not call parent:: — keeping a single trait safe to use on
 * both Create and Edit pages (their hook sets only partially overlap).
 */
trait InteractsWithMeta
{
    /** @var array<string, mixed>|null */
    protected ?array $bufferedMeta = null;

    /**
     * Top-level meta keys present on the record when the edit form was filled.
     * Used to forget keys the user has since emptied entirely. Empty on create.
     *
     * @var array<int, string>
     */
    protected array $originalMetaKeys = [];

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($this->record instanceof HasMetadata) {
            $data[$this->metaStateKey()] = MetaState::inject($this->record);
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Protected properties do not survive across Livewire requests, so the
        // originally-loaded keys must be (re)captured here, on the same request
        // that persists — straight from the record's current metadata. On edit
        // the record still holds its stored meta; keys absent from the pruned
        // form data have been cleared by the user and will be forgotten.
        if ($this->record instanceof HasMetadata) {
            $this->originalMetaKeys = array_keys(MetaState::inject($this->record));
        }

        [$data, $this->bufferedMeta] = MetaState::extract($data, $this->metaStateKey());

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        [$data, $this->bufferedMeta] = MetaState::extract($data, $this->metaStateKey());

        return $data;
    }

    protected function afterSave(): void
    {
        $this->persistBufferedMeta();
    }

    protected function afterCreate(): void
    {
        $this->persistBufferedMeta();
    }

    protected function persistBufferedMeta(): void
    {
        if ($this->record instanceof HasMetadata) {
            $pruned = MetaState::prune($this->bufferedMeta ?? []);

            // Filament hands every form field back in state, so a never-touched
            // field still arrives with its default. prune() strips null/'' but
            // intentionally keeps falsey values (false/0/0.0) because those are
            // legitimate when the user set them. A top-level scalar falsey value
            // that was NOT loaded into the form (not in $originalMetaKeys) is
            // indistinguishable from an untouched widget default (e.g. a Toggle
            // defaults to false), so on create/first-save we drop it rather than
            // write a spurious shell. Once a key is loaded on edit it is real
            // and its falsey value is preserved.
            $pruned = array_filter(
                $pruned,
                fn (mixed $value, string $key): bool => is_array($value)
                    || ! in_array($value, [false, 0, 0.0], true)
                    || in_array($key, $this->originalMetaKeys, true),
                ARRAY_FILTER_USE_BOTH,
            );

            // Top-level keys that were loaded into the form but are now empty
            // (the user cleared every leaf) should be forgotten, not written.
            $toForget = array_values(array_diff($this->originalMetaKeys, array_keys($pruned)));

            if ($pruned !== []) {
                $this->record->meta()->setMany($pruned);
            }

            if ($toForget !== []) {
                $this->record->meta()->forgetMany($toForget);
            }
        }

        $this->bufferedMeta = null;
        $this->originalMetaKeys = [];
    }

    protected function metaStateKey(): string
    {
        $key = config('filament-meta.state_key', 'meta');

        return is_string($key) ? $key : 'meta';
    }
}
