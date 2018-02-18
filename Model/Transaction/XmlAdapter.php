<?php
/**
 * Youama_OTP
 *
 * @author  David Belicza <87.bdavid@gmail.com>
 * @license David Belicza e.v. (http://youama.hu)
 */

declare(strict_types=1);

namespace Youama\OTP\Model\Transaction;

/**
 * Class XmlAdapter
 *
 * This class is a DTO to compute the XML data to a matrix.
 *
 * @package Youama\OTP\Model\Transaction
 */
class XmlAdapter
{
    /**
     * Flag for the addition data.
     */
    const PREFIX_FIRST_STEP = 'PAYMENT_RESPONSE';

    /**
     * Flag for the additional data.
     */
    const PREFIX_SECOND_STEP = 'PAYMENT_VALIDATION';

    /**
     * Flag for the additional data.
     */
    const XML_TAG_AUTHORIZATION_CODE = 'PAYMENT_VALIDATION__resultset_record_params_output_authorizationcode';

    /**
     * @var array
     */
    private $arrayOfXmls;

    /**
     * Convert string formatted XML to multidimensional array and set it to the
     * property.
     *
     * @param string $xml
     */
    public function setXml(string $xml)
    {
        $this->arrayOfXmls = (array)simplexml_load_string($xml);
    }

    /**
     * It retrieves the XML response as a matrix.
     *
     * @param string $keyPrefix Flag from the constants.
     *
     * @return array
     */
    public function getAllDataAsArray(string $keyPrefix = ''): array
    {
        $data = [];

        if (is_array($this->arrayOfXmls)) {
            $data = $this->getValues($this->arrayOfXmls, '_' . $keyPrefix . '_');
        }

        return $data;
    }

    /**
     * It converts the multidimensional array to a single matrix where keys are
     * the the appended keys from the multi-array.
     *
     * @param array  $array
     * @param string $keyPrefix
     *
     * @return array
     */
    protected function getValues(array $array, string $keyPrefix = ''): array
    {
        $flatArray = [];

        if (!empty($array)) {
            foreach ($array as $key => $value) {
                if ($value instanceof \SimpleXMLElement) {
                    $returnedValue = $this->getValues(
                        (array) $value,
                        $keyPrefix . '_' . $key
                    );

                    if (is_array($returnedValue)) {
                        $flatArray = array_merge($flatArray, $returnedValue);
                    }
                } else {
                    $flatArray[substr($keyPrefix . '_' . $key, 1)] = $value;
                }
            }
        }

        return $flatArray;
    }
}
