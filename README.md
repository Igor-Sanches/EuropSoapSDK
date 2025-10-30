## SDK EuropSoap

Uma integração rápida e eficaz com a API da EuropSoap

## Adicionar o SDK
```
composer require igor-sanches/europ-soap-sdk
```

## Etapas de configurações.

1. [Instalação e Uso](#instalação-e-uso)
2. [Cadastrar novo beneficiário](#cadastrar-novo-beneficiário)
2. [Atualizar beneficiário](#atualizar-beneficiário)
2. [Remover beneficiário](#remover-beneficiário)

# Instalação e Uso
- O SDK é bem simples de usar.
- Instale o SDK no seu projeto.

* Iniciar o SDK e fazer
```php
$username = 'API_USERNAME'; // Usuário de acesso a API da Europ
$password = 'API_PASSWORD'; // Senha de acesso a API da Europ
$wsdl = './europ.xml' // Caminho do arquivo de config. da Europ
$sdk = new EuropSoapSDK($username, $password, $wsdl);
```

* Ao enviar a requisição '$arr' você não precisa fazer uma verificação dos dados, o próprio SDK faz a validação dos dados entes de enviar.

# Cadastrar novo beneficiário
```php
$result = $sdk->beneficiario($arr, MovementType::INSERT);
```

# Atualizar beneficiário
```php
$result = $sdk->beneficiario($arr, MovementType::UPDATE);
```

# Remover beneficiário
```php
$result = $sdk->beneficiario($arr, MovementType::DELETE);
```

# Respostas do SDK
- Se sucesso
```json
{
  "sucesso": true,
  "chave": "e59eb33cfd607bbebc24d3aa71039e50",
  "mensagem": "PROCESSADO COM SUCESSO",
  "id_transation": "58165707"
}
```
- Se ocorrer algum error
```json
{
  "sucesso": false, 
  "mensagem": "MENSAGEM_DO_ERRO" 
}
```