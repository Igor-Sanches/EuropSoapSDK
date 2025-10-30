<?php

namespace EuropSoapSDK;

use EuropSoapSDK\enums\MovementType;
use EuropSoapSDK\Http\Client;
use Exception;

class EuropSoapSDK
{
    public function __construct(
        private readonly string $username,
        private readonly string $password,
        private readonly String $wsdl,
    ) {}

    public function beneficiario(array $dataArr, String $movementType): array
    {
        try {

            $arr = $this->validateData($dataArr, $movementType);

            // Converter as datas para os padrões da Europ
            $policyIssueDate = format_datetime_europ($arr['data_emissao_apolice'] ?? '');
            $effectiveStartDate = format_datetime_europ($arr['data_inicio_vigencia'] ?? '');
            $expirationDate = format_datetime_europ($arr['data_termino_vigencia'] ?? '');
            $PolicyCancellationDate = format_datetime_europ($arr['data_cancelamento_apolice'] ?? '');
            $BirthDate = format_datetime_europ($arr['data_nascimento'] ?? '');

            // Criar o request para enviar para a Europ
            $requestParams = [
                'WS_SEGURO_RERequestElement' => [
                    'Identificacao_Cliente' => 'REDE ASSIST 24H TELEASSISTENCIA',
                    'Email_Retorno' => $arr['email_retorno'] ?? '',
                    'Tipo_Movimento' => $movementType,
                    'Chave' => $arr['chave'] ?? '',
                    'Numero_Item_Apolice' => $arr['numero_item_apolice'] ?? '',
                    'Flag_VIP' => $arr['flag_vip'] ?? '',
                    'Produto_Europ' => $arr['produto_europ'] ?? '',
                    'Versao_Europ' => $arr['versao_europ'] ?? '',
                    'Data_Emissao_Apolice' => $policyIssueDate,
                    'Data_Inicio_Vigencia' => $effectiveStartDate,
                    'Data_Termino_Vigencia' => $expirationDate,
                    'Data_Cancelamento_Apolice' => $PolicyCancellationDate,
                    'Nome' => $arr['nome'] ?? '',
                    'Tipo_Pessoa' => type_person_europ($arr['tipo_pessoa'] ?? ''), // Corrigido para usar 'tipo_pessoa'
                    'CPF_CNPJ' => format_cpf($arr['cpf'] ?? ''),
                    'Data_Nascimento' => $BirthDate,
                    'Endereco' => $arr['endereco'] ?? '',
                    'Numero' => $arr['numero'] ?? '',
                    'Complemento' => $arr['complemento'] ?? '',
                    'Bairro' => $arr['bairro'] ?? '',
                    'Cidade' => $arr['cidade'] ?? '',
                    'Estado' => $arr['estado'] ?? '',
                    'CEP' => $arr['cep'] ?? '',
                    'Tel_Contato1' => $arr['tel_contato1'] ?? '',
                    'Tel_Contato2' => $arr['tel_contato2'] ?? '',
                    'Email_Contato' => $arr['email_contato'] ?? '',
                    'Observacao' => $arr['observacao'] ?? '',
                ]
            ];

            $client = new Client($this->wsdl, [
                'username' => $this->username,
                'password' => $this->password,
            ]);

            $response = $client->__soapCall("WS_REDE_ASSISTENCIAOperation", [$requestParams]);
            $WS_SEGURO_REResponseElement = $response->WS_SEGURO_REResponseElement;
            $Operation_Type = $WS_SEGURO_REResponseElement->Operation_Type;
            if($Operation_Type != 'PROCESSADO'){
                throw new Exception($WS_SEGURO_REResponseElement->MsgError);
            }
            return [
                'sucesso' => true,
                'chave' => $WS_SEGURO_REResponseElement->Chave,
                'mensagem' => $WS_SEGURO_REResponseElement->MsgError,
                'id_transation' => $WS_SEGURO_REResponseElement->IDTransation,
            ];

        } catch (\Exception $exception) {
            return [
                'sucesso' => false,
                'mensagem' => $exception->getMessage()
            ];
        }
    }

    /**
     * @throws Exception
     */
    private function validateData(array $data, String $movementType): array
    {
        // Criar a chave única para o Europ
        $randomValue = rand();
        $timestamp = time();
        $uniqueHash = md5($randomValue . $timestamp);

        // Lista de campos obrigatórios com limites de caracteres
        $requiredFields = [
            'produto_europ' => ['message' => "É obrigatório selecionar um produto"],
            'data_emissao_apolice' => ['message' => "É obrigatório escolher a data de emissão da apólice"],
            'data_inicio_vigencia' => ['message' => "É obrigatório escolher a data de início da vigência"],
            'data_termino_vigencia' => ['message' => "É obrigatório escolher a data de término da vigência"],
            'nome' => ['message' => "É obrigatório o nome do beneficiário", 'max' => 50],
            'tipo_pessoa' => ['message' => "É obrigatório escolher o tipo de pessoa", 'allowed' => ["PF", "PJ"]],
            'data_nascimento' => ['message' => "É obrigatório escolher a data de nascimento"],
            'endereco' => ['message' => "É obrigatório o endereço do beneficiário", 'max' => 50],
            'numero' => ['message' => "É obrigatório o número do endereço", 'max' => 10],
            'bairro' => ['message' => "É obrigatório o bairro do beneficiário", 'max' => 20],
            'cidade' => ['message' => "É obrigatório a cidade do beneficiário", 'max' => 30],
            'estado' => ['message' => "É obrigatório o estado do beneficiário", 'max' => 2],
            'cep' => ['message' => "É obrigatório o CEP do beneficiário", 'max' => 10],
            'email_contato' => ['message' => "É obrigatório o email de contato", 'max' => 50],
            'tel_contato1' => ['message' => "É obrigatório o telefone de contato", 'max' => 20],
        ];

        // Validação de campos obrigatórios
        foreach ($requiredFields as $field => $rules) {
            $value = trim($data[$field] ?? '');

            if (empty($value)) {
                throw new Exception($rules['message']);
            }

            if (isset($rules['max']) && strlen($value) > $rules['max']) {
                throw new Exception("O campo '$field' não pode ter mais que {$rules['max']} caracteres");
            }

            if (isset($rules['allowed']) && !in_array($value, $rules['allowed'])) {
                throw new Exception("O campo '$field' deve conter um dos seguintes valores: " . implode(", ", $rules['allowed']));
            }
        }

        // Validação específica para movimentação de exclusão
        if ($movementType == MovementType::DELETE && empty(trim($data['data_cancelamento_apolice'] ?? ''))) {
            throw new Exception("É obrigatório escolher a data de cancelamento da apólice para exclusão");
        }

        // Validação de campos opcionais com limite de caracteres
        $optionalFields = [
            'complemento' => 10,
            'tel_contato2' => 20,
            'observacao' => 100,
        ];

        foreach ($optionalFields as $field => $maxLength) {
            if (!empty($data[$field]) && strlen($data[$field]) > $maxLength) {
                throw new Exception("O campo '$field' não pode ter mais que $maxLength caracteres");
            }
        }

        // Aplicação de formatação onde necessário
        $formattedData = [
            'tel_contato1' => format_telephone($data['tel_contato1']),
            'tel_contato2' => !empty($data['tel_contato2']) ? format_telephone($data['tel_contato2']) : null,
            'cpf' => $data['cpf'],
        ];

        // Retornar um array formatado pronto para salvar no banco de dados
        return array_merge([
            'email_retorno' => $data['email_retorno'] ?? '',
            'chave' => ($data['chave'] ?? $uniqueHash),
            'numero_item_apolice' => $data['numero_item_apolice'] ?? '',
            'flag_vip' => $data['flag_vip'] ?? '',
            'produto_europ' => strlen($data['produto_europ']) == 3 ? $data['produto_europ'] : substr($data['produto_europ'], 0, 3),
            'versao_europ' => strlen($data['versao_europ']) == 3 ? $data['versao_europ'] : substr($data['produto_europ'], 3),
            'data_emissao_apolice' => $data['data_emissao_apolice'],
            'data_inicio_vigencia' => $data['data_inicio_vigencia'],
            'data_termino_vigencia' => $data['data_termino_vigencia'],
            'data_cancelamento_apolice' => $data['data_cancelamento_apolice'] ?? '',
            'nome' => $data['nome'],
            'tipo_pessoa' => $data['tipo_pessoa'],
            'data_nascimento' => $data['data_nascimento'],
            'endereco' => $data['endereco'],
            'numero' => $data['numero'],
            'complemento' => $data['complemento'] ?? '',
            'bairro' => $data['bairro'],
            'cidade' => $data['cidade'],
            'estado' => $data['estado'],
            'cep' => $data['cep'],
            'email_contato' => $data['email_contato'],
            'observacao' => $data['observacao'] ?? ''
        ], $formattedData);
    }
}
