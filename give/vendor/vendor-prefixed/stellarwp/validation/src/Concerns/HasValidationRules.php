<?php
/**
 * @license GPL-2.0-or-later
 *
 * Modified by impress-org on 24-May-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

declare(strict_types=1);

namespace Give\Vendors\StellarWP\Validation\Concerns;

use Give\Vendors\StellarWP\Validation\Config;
use Give\Vendors\StellarWP\Validation\Contracts\ValidationRule;
use Give\Vendors\StellarWP\Validation\ValidationRuleSet;

/**
 * Apply this trait to a class to enable it to have validation rules. These rules may be passed to the front-end
 * or used with the Validator to validate data.
 *
 * @unreleased
 */
trait HasValidationRules
{
    /**
     * @var ValidationRuleSet
     */
    protected $validationRules;

    /**
     * @unreleased
     */
    public function __construct()
    {
        $this->validationRules = Config::getServiceContainer()->get(ValidationRuleSet::class);
    }

    /**
     * @unreleased
     */
    public function rules(...$rules): self
    {
        $this->validationRules->rules(...$rules);

        return $this;
    }

    /**
     * @unreleased
     */
    public function hasRule(string $ruleId): bool
    {
        return $this->validationRules->hasRule($ruleId);
    }

    /**
     * @unreleased
     */
    public function getRule(string $ruleId): ValidationRule
    {
        return $this->validationRules->getRule($ruleId);
    }

    /**
     * @unreleased
     */
    public function forgetRule(string $ruleId): self
    {
        $this->validationRules->removeRuleWithId($ruleId);

        return $this;
    }

    /**
     * @unreleased
     */
    public function getValidationRules(): ValidationRuleSet
    {
        return $this->validationRules;
    }
}
