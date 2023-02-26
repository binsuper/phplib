<?php

namespace Gino\Phplib\Validation;

class Validator {

    protected $params   = [];
    protected $rules    = [];
    protected $messages = [];
    protected $v_fails  = [];
    protected $v_errors = [];

    public function __construct(array $params, array $rules = [], array $messages = []) {
        $this->params   = $params;
        $this->rules    = $rules;
        $this->messages = $messages;
    }

    /**
     * @param array $params
     * @param array $rules
     * @param array $messages
     * @return static
     */
    public static function make(array $params, array $rules = [], array $messages = []): Validator {
        return new static($params, $rules, $messages);
    }

    /**
     * 增加校验规则
     *
     * @param string|array $field
     * @param string $rule
     * @return $this
     */
    public function addRule($field, $rule = '') {
        if (is_array($field)) {
            foreach ($field as $f => $r) {
                $this->rules[$f] = $r;
            }
        } else {
            $this->rules[$field] = $rule;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getRules(): array {
        return $this->rules;
    }

    /**
     * 按规则对参数进行校验
     *
     * @return array
     */
    public function validate(): array {
        $result = [];

        foreach ($this->rules as $field => $rule) {
            // check field exsits
            if (!isset($this->params[$field])) {
                // check required rule
                if (in_array('required', explode('|', $rule))) {
                    // errors
                    $this->v_fails[$field]  = $rule;
                    $this->v_errors[$field] = $this->getFailMessage($field, $rule, true);
                    continue;
                }
            }

            $field_value = $this->params[$field];

            // validate params
            if (!Processor::is($field_value, $rule)) {
                $this->v_fails[$field]  = $rule;
                $this->v_errors[$field] = $this->getFailMessage($field, $rule, false, $field_value);
                continue;
            }

            // finish
            $result[$field] = $field_value;
        }

        return $result;
    }

    /**
     * @param string $field
     * @param $expected
     * @param $type
     * @return string
     */
    protected function getFailMessage(string $field, string $expected, $miss = false, $val = null): string {
        if ($miss) {
            $msg = "Missing variable $field";
        } else {
            $type = gettype($val);
            $msg  = "The $field expects to be $expected, $type given.";
        }
        return $this->messages[$field] ?? $msg;
    }

    /**
     * Determine if the data fails the validation rules.
     *
     * @return bool
     */
    public function fails(): bool {
        return !empty($this->v_fails);
    }

    /**
     * 获取失败的规则
     *
     * @return array
     */
    public function failed(): array {
        return $this->v_fails;
    }

    /**
     * 获取所有错误消息
     *
     * @return array
     */
    public function errors(): array {
        return $this->v_errors;
    }

}