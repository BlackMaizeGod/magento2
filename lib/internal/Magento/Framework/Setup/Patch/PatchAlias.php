<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Setup\Patch;

/**
 * Allows to work with patches aliases
 */
class PatchAlias
{
    /**
     * @var string[] $aliases
     */
    private array $aliases = [];

    /**
     * Indicates, if aliases for specific patch type were registered
     *
     * @var bool[] $registeredForType
     */
    private array $registeredForType = [
        PatchApplier::DATA_PATCH => false,
        PatchApplier::SCHEMA_PATCH => false,
    ];

    /**
     * @var PatchFactory $patchFactory
     */
    private PatchFactory $patchFactory;

    /**
     * PatchAlias constructor.
     * @param PatchFactory $patchFactory
     */
    public function __construct(PatchFactory $patchFactory)
    {
        $this->patchFactory = $patchFactory;
    }

    /**
     * Checks if aliases were registered.
     *
     * @param string $patchType must be "data" or "schema"
     * @return bool
     */
    public function isAliasesRegisteredFor(string $patchType): bool
    {
        return $this->registeredForType[$patchType] ?? false;
    }

    /**
     * Register patch aliases
     *
     * @param string[] $patchNames
     * @param string $patchType
     * @param array $patchArguments
     * @return void
     */
    public function registerPatchAliases(
        array $patchNames,
        string $patchType,
        array $patchArguments = []
    ): void {
        if (!isset($this->registeredForType[$patchType]) || $this->isAliasesRegisteredFor($patchType)) {
            return;
        }

        foreach ($patchNames as $patchName) {
            if (!class_exists($patchName)) {
                continue;
            }

            $patchInstance = $this->patchFactory->create($patchName, $patchArguments);
            $aliases = $patchInstance->getAliases();
            $patchAliases = array_fill_keys($aliases, $patchName);
            $this->aliases = [...$this->aliases, ...$patchAliases];
        }

        $this->registeredForType[$patchType] = true;
    }

    /**
     * Retrieve final patch name
     *
     * If the patch with indicated name is not exist will be return patch, which has such alias
     *
     * @param string $patchName
     * @return string
     */
    public function getFinalPatchName(string $patchName): string
    {
        $patchAlias = $this->aliases[$patchName] ?? $patchName;

        return class_exists($patchName) ? $patchName : $patchAlias;
    }
}
