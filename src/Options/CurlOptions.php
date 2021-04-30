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
        $this->options[CURLOPT_USERPWD] = $username . ':' . $password;

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