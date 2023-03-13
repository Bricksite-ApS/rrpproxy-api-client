<?php

namespace Bricksite\RRPProxy;

use Exception;

class RRPClient
{
    protected string $username;
    protected string $password;
    protected bool $test = false;
    protected bool $retry = false;

    /**
     * All domain related commands which should convert domain names to idn
     *
     * @var array
     */
    private array $domainIDNCommands = ['AddDomain', 'ModifyDomain', 'RenewDomain', 'TransferDomain', 'StatusDomain', 'DeleteDomain', 'PushDomain'];

    /**
     * All dns related commands which should convert domain names to idn
     *
     * @var array
     */
    private array $dnsIDNCommands = ['AddDNSZone', 'ModifyDNSZone', 'QueryDNSZoneRRList'];

    public function __construct(string $username, string $password, bool $test = false)
    {
        $this->username = $username;
        $this->password = $password;
        $this->test = $test;
    }

    /**
     * @param string $command
     * @param array $args
     * @return mixed
     * @throws Exception
     */
    public function request(string $command, array $args = [])
    {
        $args = $this->formatDomainArg($command, $args);

        // Inject auth, command and urlencode args
        $requestArgs = array_merge([
            's_login' => rawurlencode($this->username),
            's_pw' => rawurlencode($this->password),
            'command' => rawurlencode($command)
        ], array_map(function ($data) {
            return $data;
        }, $args));

        // Build url with get parameters
        $url = ($this->test ? 'https://api-ote.rrpproxy.net/api/call?s_opmode=OTE&' : 'https://api.rrpproxy.net/api/call?') . http_build_query($requestArgs);

        // Send request
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HEADER => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER => [
                'Expect:',
                'Content-type: text/html; charset=UTF-8'
            ]
        ]);
        $response = curl_exec($ch);

        if (curl_error($ch)) {
            curl_close($ch);
            throw new Exception('Curl Error(' . curl_errno($ch) . '): ' . curl_error($ch));
        }
        curl_close($ch);

        $result = $this->processResponse($response);

        return $this->validateResult($result, $command, $requestArgs);
    }

    private function formatDomainArg(string $command, array $args = []): array
    {
        // IDN Conversion
        if (in_array($command, $this->domainIDNCommands)) {

            $idn = idn_to_ascii($args['domain']);
            $args['domain'] = $idn ?: $args['domain'];

        } elseif (in_array($command, $this->dnsIDNCommands)) {

            $idn = idn_to_ascii($args['dnszone']);
            $args['dnszone'] = $idn ?: $args['dnszone'];

        }

        return $args;
    }

    /**
     * @param $response
     * @return array|array[]
     * @throws Exception
     */
    private function processResponse($response): array
    {
        if (is_array($response)) {
            return $response;
        }

        if (empty($response)) {
            throw new Exception('Empty response from API');
        }

        $hash = ["property" => []];
        $responseList = explode("\n", $response);
        foreach ($responseList as $item) {
            if (preg_match("/^([^\\=]*[^\t\\= ])[\t ]*=[\t ]*(.*)\$/", $item, $m)) {
                [,$attr, $value] = $m;
                $value = rtrim($value, " \t");
                if (preg_match("/^PROPERTY\\[([^\\]]*)\\]/i", $attr, $m)) {
                    $prop = strtolower($m[1]);
                    $prop = preg_replace("/\\s/", "", $prop);
                    if (in_array($prop, array_keys($hash["property"]))) {
                        $hash["property"][$prop][] = $value;
                    } else {
                        $hash["property"][$prop] = [$value];
                    }
                } else {
                    $hash[$attr] = $value;
                }
            }
        }
        if (is_array($hash['property']) && count($hash['property']) === 0) {
            unset($hash['property']);
        }
        return $hash;
    }

    /**
     * @param $result
     * @param string $command
     * @param array $args
     * @return mixed
     * @throws Exception
     */
    private function validateResult($result, string $command, array $args = []): mixed
    {
        if ((str_starts_with($result['code'], '2'))) { // Successful Return Codes (2xx), return the results.
            $this->retry = false;
            return $result;
        } elseif ((str_starts_with($result['code'], '4')) && !$this->retry) { // Temporary Error Codes (4xx), we do a retry .
            $this->retry = true;
            sleep(5);
            return $this->request($command, $args);
        } else { // Permanent Error Codes (5xx), throw exception.
            throw new Exception($result['code'] . ' : ' . $result['description']);
        }
    }



}
