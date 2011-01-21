<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form;

/**
 * Represents a field where each timezone is broken down by continent.
 *
 * Available options:
 *
 *  * empty_value:   If set to a non-false value, an "empty" option will
 *                  be added to the top of the timezone choices. A
 *                  common value might be "Choose a timezone". Default: false.
 */
class TimezoneField extends ChoiceField
{
    /**
     * Stores the available timezone choices
     * @var array
     */
    protected static $timezones = array();

    /**
     * {@inheritDoc}
     */
    public function configure()
    {
        $this->addOption('empty_value', false);

        $choices = self::getTimezoneChoices();

        if (false !== $this->getOption('empty_value')) {
            $choices = array('' => $this->getOption('empty_value')) + $choices;
        }

        $this->addOption('choices', $choices);

        parent::configure();
    }

    /**
     * Preselects the server timezone if the field is empty and required
     *
     * {@inheritDoc}
     */
    public function getDisplayedData()
    {
        $data = parent::getDisplayedData();

        if (null == $data && $this->isRequired()) {
            $data = date_default_timezone_get();
        }

        return $data;
    }

    /**
     * Returns the timezone choices
     *
     * The choices are generated from the ICU function
     * \DateTimeZone::listIdentifiers(). They are cached during a single request,
     * so multiple timezone fields on the same page don't lead to unnecessary
     * overhead.
     *
     * @return array  The timezone choices
     */
    protected static function getTimezoneChoices()
    {
        if (count(self::$timezones) == 0) {
            foreach (\DateTimeZone::listIdentifiers() as $timezone) {
                $parts = explode('/', $timezone);

                if (count($parts) > 2) {
                    $region = $parts[0];
                    $name = $parts[1].' - '.$parts[2];
                } else if (count($parts) > 1) {
                    $region = $parts[0];
                    $name = $parts[1];
                } else {
                    $region = 'Other';
                    $name = $parts[0];
                }

                if (!isset(self::$timezones[$region])) {
                    self::$timezones[$region] = array();
                }

                self::$timezones[$region][$timezone] = str_replace('_', ' ', $name);
            }
        }

        return self::$timezones;
    }
}
