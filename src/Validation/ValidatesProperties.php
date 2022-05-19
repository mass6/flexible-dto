<?php

namespace Mass6\FlexibleDTO\Validation;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Mass6\FlexibleDTO\Validation\ValidatorFactory;

trait ValidatesProperties
{
    /**
     * Get the property validation rules that apply.
     *
     * @return array
     */
    abstract protected function getRules(): array;

    /**
     * Validate the DTO property values.
     *
     * @param iterable $data
     */
    protected function validate(iterable $data)
    {
        $validator = (new ValidatorFactory())->make($this->prepareData($data), $this->getRules(), $this->getMessages());
        $this->withValidator($validator);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Get custom messages for validator errors.
     */
    protected function getMessages(): array
    {
        return [];
    }

    /**
     * Runs after validation hooks.
     * @link https://laravel.com/docs/9.x/validation#adding-after-hooks-to-form-requests
     *
     * @param \Illuminate\Validation\Validator $validator
     */
    protected function withValidator(\Illuminate\Validation\Validator $validator)
    {

    }

    /**
     * Prepares data for validation by ensuring it is in array form.
     *
     * @param iterable $data
     * @return array
     */
    protected function prepareData(iterable $data): array
    {
        if ($data instanceof Collection) {
            return $data->toArray();
        }

        return Arr::wrap($data);
    }
}