<?php
/**
 * @license GPL-2.0-or-later
 *
 * Modified by impress-org on 24-May-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

declare(strict_types=1);

namespace Give\Vendors\StellarWP\Validation\Contracts;

interface ValidatesOnFrontEnd
{
    /**
     * Serializes the rule option for use on the front-end.
     *
     * @unreleased
     *
     * @return int|float|string|bool|array|null
     */
    public function serializeOption();
}
