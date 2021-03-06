<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\InputFilter;

use Zend\Filter\FilterChain;
use Zend\Validator\NotEmpty;
use Zend\Validator\ValidatorChain;

class Input implements
    InputInterface
{
    /**
     * @var bool
     */
    protected $breakOnFailure = false;

    /**
     * @var string|null
     */
    protected $errorMessage;

    /**
     * @var FilterChain
     */
    protected $filterChain;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $required = true;

    /**
     * @var ValidatorChain
     */
    protected $validatorChain;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * Flag for distinguish when $value contains the value previously set or the default one.
     *
     * @var bool
     */
    protected $hasValue = false;

    /**
     * @var mixed
     */
    protected $fallbackValue;

    /**
     * @var bool
     */
    protected $hasFallback = false;

    public function __construct($name = null)
    {
        $this->name = $name;
    }

    /**
     * @param  bool $breakOnFailure
     * @return Input
     */
    public function setBreakOnFailure($breakOnFailure)
    {
        $this->breakOnFailure = (bool) $breakOnFailure;
        return $this;
    }

    /**
     * @param  string|null $errorMessage
     * @return Input
     */
    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = (null === $errorMessage) ? null : (string) $errorMessage;
        return $this;
    }

    /**
     * @param  FilterChain $filterChain
     * @return Input
     */
    public function setFilterChain(FilterChain $filterChain)
    {
        $this->filterChain = $filterChain;
        return $this;
    }

    /**
     * @param  string $name
     * @return Input
     */
    public function setName($name)
    {
        $this->name = (string) $name;
        return $this;
    }

    /**
     * @param  bool $required
     * @return Input
     */
    public function setRequired($required)
    {
        $this->required = (bool) $required;
        return $this;
    }

    /**
     * @param  ValidatorChain $validatorChain
     * @return Input
     */
    public function setValidatorChain(ValidatorChain $validatorChain)
    {
        $this->validatorChain = $validatorChain;
        return $this;
    }

    /**
     * Set the input value.
     *
     * If you want to remove/unset the current value use {@link Input::resetValue()}.
     *
     * @see Input::getValue() For retrieve the input value.
     * @see Input::hasValue() For to know if input value was set.
     * @see Input::resetValue() For reset the input value to the default state.
     *
     * @param  mixed $value
     * @return Input
     */
    public function setValue($value)
    {
        $this->value = $value;
        $this->hasValue = true;
        return $this;
    }

    /**
     * Reset input value to the default state.
     *
     * @see Input::hasValue() For to know if input value was set.
     * @see Input::setValue() For set a new value.
     *
     * @return Input
     */
    public function resetValue()
    {
        $this->value = null;
        $this->hasValue = false;
        return $this;
    }

    /**
     * @param  mixed $value
     * @return Input
     */
    public function setFallbackValue($value)
    {
        $this->fallbackValue = $value;
        $this->hasFallback = true;
        return $this;
    }

    /**
     * @return bool
     */
    public function breakOnFailure()
    {
        return $this->breakOnFailure;
    }

    /**
     * @return string|null
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @return FilterChain
     */
    public function getFilterChain()
    {
        if (!$this->filterChain) {
            $this->setFilterChain(new FilterChain());
        }
        return $this->filterChain;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getRawValue()
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @return ValidatorChain
     */
    public function getValidatorChain()
    {
        if (!$this->validatorChain) {
            $this->setValidatorChain(new ValidatorChain());
        }
        return $this->validatorChain;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        $filter = $this->getFilterChain();
        return $filter->filter($this->value);
    }

    /**
     * Flag for inform if input value was set.
     *
     * This flag used for distinguish when {@link Input::getValue()} will return the value previously set or the default.
     *
     * @see Input::getValue() For retrieve the input value.
     * @see Input::setValue() For set a new value.
     * @see Input::resetValue() For reset the input value to the default state.
     *
     * @return bool
     */
    public function hasValue()
    {
        return $this->hasValue;
    }

    /**
     * @return mixed
     */
    public function getFallbackValue()
    {
        return $this->fallbackValue;
    }

    /**
     * @return bool
     */
    public function hasFallback()
    {
        return $this->hasFallback;
    }

    public function clearFallbackValue()
    {
        $this->hasFallback = false;
        $this->fallbackValue = null;
    }

    /**
     * @param  InputInterface $input
     * @return Input
     */
    public function merge(InputInterface $input)
    {
        $this->setBreakOnFailure($input->breakOnFailure());
        $this->setErrorMessage($input->getErrorMessage());
        $this->setName($input->getName());
        $this->setRequired($input->isRequired());
        if (!($input instanceof Input) || $input->hasValue()) {
            $this->setValue($input->getRawValue());
        }

        $filterChain = $input->getFilterChain();
        $this->getFilterChain()->merge($filterChain);

        $validatorChain = $input->getValidatorChain();
        $this->getValidatorChain()->merge($validatorChain);
        return $this;
    }

    /**
     * @param  mixed $context Extra "context" to provide the validator
     * @return bool
     */
    public function isValid($context = null)
    {
        $value           = $this->getValue();
        $hasValue        = $this->hasValue();
        $required        = $this->isRequired();

        if (! $hasValue && $this->hasFallback()) {
            $this->setValue($this->getFallbackValue());
            return true;
        }

        if (! $hasValue && ! $required) {
            return true;
        }

        if (! $hasValue && $required) {
            if ($this->errorMessage === null) {
                $this->errorMessage = $this->prepareRequiredValidationFailureMessage();
            }
            return false;
        }

        $validator = $this->getValidatorChain();
        $result    = $validator->isValid($value, $context);
        if (! $result && $this->hasFallback()) {
            $this->setValue($this->getFallbackValue());
            $result = true;
        }

        return $result;
    }

    /**
     * @return string[]
     */
    public function getMessages()
    {
        if (null !== $this->errorMessage) {
            return (array) $this->errorMessage;
        }

        if ($this->hasFallback()) {
            return [];
        }

        $validator = $this->getValidatorChain();
        return $validator->getMessages();
    }

    /**
     * Create and return the validation failure message for required input.
     *
     * @return string[]
     */
    protected function prepareRequiredValidationFailureMessage()
    {
        $notEmpty = new NotEmpty();
        $templates = $notEmpty->getOption('messageTemplates');
        return [
            NotEmpty::IS_EMPTY => $templates[NotEmpty::IS_EMPTY],
        ];
    }
}
