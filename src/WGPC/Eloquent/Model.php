<?php

namespace WGPC\Eloquent;

use Illuminate\Support\MessageBag;

class Model extends \Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = false;

    /**
     * Error message bag.
     * @var Illuminate\Support\MessageBag
     */
    protected $errors;

    /**
     * Validation rules.
     * @var Array
     */
    protected static $rules = [];

    /**
     * Complex validation rules (checked by '$validator->sometimes' function).
     * @var Array
     */
    protected static $complexRules = [];

    /**
     * Customized validation rules.
     * @var array
     */
    protected static $customRules = [];

    /**
     * Custom messages.
     * @var Array
     */
    protected static $messages = [];

    public function __construct(array $attributes = []) {
        parent::__construct($attributes);
        $this->errors = new MessageBag();
    }

    /**
     * Listen for save event.
     */
    protected static function boot() {
        parent::boot();

        static::saving(function($model) {
            return $model->validate();
        });
    }

    /**
     * Validates current attributes against rules
     */
    public function validate() {
        $validator = \validator(
                $this->attributes, $this->overrideNormalRules(static::$rules), static::$messages
        );

        $validator->addExtensions($this->overrideCustomRules(static::$customRules));

        foreach ($this->overrideComplexRules(static::$complexRules) as $field => $validation) {
            $rules = $validation["rules"];
            $check = $validation["check"];
            $validator->sometimes($field, $rules, $check);
        }

        if ($validator->passes()) {
            return true;
        }
        $this->setErrors($validator->messages());
        return false;
    }

    /**
     * Retrieves error message bag.
     * @return Illuminate\Support\MessageBag
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Sets error message bag.
     * @param MessageBag $errors
     */
    public function setErrors(MessageBag $errors) {
        $this->errors = $errors;
    }

    /**
     * Puts more errors in its message bag.
     * @return boolean
     */
    public function putErrors($errors) {
        if (!is_array($errors)) {
            $errors = [$errors];
        }
        $this->errors->merge($errors);
    }

    /**
     * Verify if there's an error at saving.
     * @return boolean
     */
    public function hasErrors() {
        return \sizeof($this->errors->all()) > 0;
    }

    /**
     * Clears error message bag.
     */
    public function clearErrors() {
        $this->errors = new MessageBag();
    }

    /**
     * Returns all the simple rules.
     * @return array
     */
    public static function getRules() {
        return static::$rules;
    }

    /**
     * Returns all the custom rules.
     * @return array
     */
    public static function getCustomRules() {
        return static::$customRules;
    }

    /**
     * Returns all the complex rules.
     * @return array
     */
    public static function getComplexRules() {
        return static::$complexRules;
    }

    /**
     * Returns all registered rules. The resultant array will be organized as:
     * {
     *      "rules": "normal rules",
     *      "custom": "custom rules",
     *      "complex": "complex rules"
     * }
     * @return array
     */
    public static function getAllRules() {
        return [
            "rules" => static::getRules(),
            "custom" => static::getCustomRules(),
            "complex" => static::getComplexRules()
        ];
    }

    /**
     * Enables overriding of normal rules (static::$rules) at instance context.
     * This way, its possible to use instance's attributes for more specific rules (like 'unique' rule).
     * @param array $rules
     * @return array
     */
    protected function overrideNormalRules($rules) {
        return $rules;
    }

    /**
     * Enables overriding of custom rules (static::$customRules) at instance context.
     * This way, its possible to use instance's attributes for more specific rules (like 'unique' rule).
     * @param array $rules
     * @return array
     */
    protected function overrideCustomRules($rules) {
        return $rules;
    }

    /**
     * Enables overriding of complex rules (static::$complexRules) at instance context.
     * This way, its possible to use instance's attributes for more specific rules (like 'unique' rule).
     * @param array $rules
     * @return array
     */
    protected function overrideComplexRules($rules) {
        return $rules;
    }

}
