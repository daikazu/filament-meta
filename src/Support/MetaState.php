<?php

declare(strict_types=1);

namespace Daikazu\FilamentMeta\Support;

use Daikazu\LaravelMeta\Contracts\HasMetadata;

/**
 * Pure helpers mapping a model's metadata to/from Filament form state.
 *
 * Form state namespaces metadata under a single key (default "meta"), so a
 * field named "meta.seo.title" lives at $data['meta']['seo']['title'].
 */
final class MetaState
{
    /**
     * The nested meta tree to inject into form state for hydration.
     *
     * @return array<string, mixed>
     */
    public static function inject(HasMetadata $model): array
    {
        return $model->meta()->all();
    }

    /**
     * Split form data into [dataWithoutMeta, metaSubArray].
     *
     * @param  array<string, mixed>  $data
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    public static function extract(array $data, string $stateKey): array
    {
        /** @var array<string, mixed> $meta */
        $meta = is_array($data[$stateKey] ?? null) ? $data[$stateKey] : [];

        unset($data[$stateKey]);

        return [$data, $meta];
    }

    /**
     * Recursively remove "empty" leaves (null or '') from a meta tree, then
     * drop any array that becomes empty as a result. Real values such as
     * false, 0, 0.0 and '0' are preserved.
     *
     * Used before persisting buffered form state so untouched/cleared fields
     * (which Filament always includes in state as null/'') never write junk.
     *
     * @template TKey of array-key
     *
     * @param  array<TKey, mixed>  $meta
     * @return array<TKey, mixed>
     */
    public static function prune(array $meta): array
    {
        $pruned = [];

        foreach ($meta as $key => $value) {
            if (is_array($value)) {
                $value = self::prune($value);

                if ($value === []) {
                    continue;
                }

                $pruned[$key] = $value;

                continue;
            }

            if ($value === null || $value === '') {
                continue;
            }

            $pruned[$key] = $value;
        }

        return $pruned;
    }
}
