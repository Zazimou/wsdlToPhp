<?php declare(strict_types=1);


namespace Zazimou\WsdlToPhp;


use Zazimou\WsdlToPhp\Exceptions\CurlException;
use Zazimou\WsdlToPhp\Options\CurlOptions;


class Curl
{
    /** @var resource */
    protected $ch;

    public function __construct(string $url, ?CurlOptions $options = null)
    {
        $this->ch = curl_init($url);
        if (isset($options)) {
            curl_setopt_array($this->ch, $options->getOptions());
        }
    }

    /**
     * @return string
     * @throws CurlException
     */
    public function download(): string
    {
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($this->ch);
        $errors = curl_error($this->ch);
        if (!$result && empty($errors)) {
            throw new CurlException($errors, curl_errno($this->ch));
        }
        curl_close($this->ch);

        return $result;
    }

}