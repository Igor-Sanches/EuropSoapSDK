<?php

namespace EuropSoapSDK\Http;

use SoapClient;
use SoapHeader;
use SoapFault;
use Exception;

class Client extends SoapClient
{
    /**
     * @param string $wsdl Caminho ou URL do WSDL.
     * @param array $authConfig Credenciais de autenticação ['username' => '', 'password' => ''].
     * @param array $options Opções extras para o SoapClient.
     * @throws SoapFault|Exception
     */
    public function __construct(string $wsdl, array $authConfig, array $options = [])
    {
        // Valida credenciais
        if (empty($authConfig['username']) || empty($authConfig['password'])) {
            throw new Exception("Credenciais de autenticação SOAP não fornecidas.");
        }

        // Opções padrão
        $defaultOptions = [
            'trace' => true,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'exceptions' => true,
            'stream_context' => stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ]
            ]),
        ];

        // Mescla opções personalizadas
        $options = array_merge($defaultOptions, $options);

        // Inicializa SoapClient com o WSDL e opções
        parent::__construct($wsdl, $options);

        // Cria header WS-Security
        $wsSecurityHeader = new SoapHeader(
            'http://www.informatica.com/',
            'Security',
            [
                'UsernameToken' => [
                    'Username' => $authConfig['username'],
                    'Password' => $authConfig['password'],
                ],
            ],
            false
        );

        // Define o header na instância atual
        $this->__setSoapHeaders([$wsSecurityHeader]);
    }
}
