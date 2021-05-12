<?php declare(strict_types=1);


namespace Zazimou\WsdlToPhp\Options;


class CurlOptions
{

    /** @var string[] */
    protected $options = [];

    public function __construct()
    {
    }


    public function authenticateWithBasic(string $username, string $password = ''): CurlOptions
    {
        $this->options[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
        $this->options[CURLOPT_USERPWD] = $username.':'.$password;

        return $this;
    }

    public function ignoreCertificate(bool $ignore = true): CurlOptions
    {
        if ($ignore === true) {
            $this->options[CURLOPT_SSL_VERIFYHOST] = 0;
            $this->options[CURLOPT_SSL_VERIFYPEER] = 0;
        }

        if ($ignore === false) {
            if (isset($this->options[CURLOPT_SSL_VERIFYHOST])) {
                unset($this->options[CURLOPT_SSL_VERIFYHOST]);
            }
            if (isset($this->options[CURLOPT_SSL_VERIFYPEER])) {
                unset($this->options[CURLOPT_SSL_VERIFYPEER]);
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}