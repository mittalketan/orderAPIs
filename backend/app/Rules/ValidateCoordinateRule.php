<?php
namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidateCoordinateRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        //check if valid lang long has been passed
        return self::validateCoordinates($value);

    }

    /**
     * validateCoordinates validate a given lat and long
     *
     * @param array $coordinate
     * @return bool `true` if the coordinate is valid, `false` if not
     */
    public function validateCoordinates($coordinate)
    {
        return preg_match('/^[-]?((([0-8]?[0-9])(\.(\d+))?)|(90(\.0+)?)),[-]?((((1[0-7][0-9])|([0-9]?[0-9]))(\.(\d+))?)|180(\.0+)?)$/', implode(",", $coordinate));
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return strtoupper(':attribute_INVALID_PARAMETERS');
    }
}
