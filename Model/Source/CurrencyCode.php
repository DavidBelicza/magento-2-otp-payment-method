<?php
/**
 * Youama_OTP
 *
 * @author  David Belicza <87.bdavid@gmail.com>
 * @license David Belicza e.v. (http://youama.hu)
 */

declare(strict_types=1);

namespace Youama\OTP\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class CurrencyCode
 *
 * Only these currencies supported by the bank.
 *
 * @package Youama\OTP\Model\Source
 */
class CurrencyCode implements OptionSourceInterface
{
    const HUF = 'HUF';
    const USD = 'USD';
    const EUR = 'EUR';

    /**
     * @var array
     */
    private $options;

    /**
     * Retrieve All options.
     *
     * @return array
     */
    public function getAllOptions(): array
    {
        return [
            self::HUF => __('Hungarian Forint'),
            self::USD => __('USA Dollar'),
            self::EUR => __('Euro')
        ];
    }

    /**
     * It retrieves the source to the admin UI.
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $options = [];
        $thisOptions = $this->getAllOptions();

        foreach ($thisOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        $this->options = $options;

        return $this->options;
    }
}
