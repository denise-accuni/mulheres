<?php
namespace Observatorio\Core;

/**
 * Classe responsável por reportar erros de forma centralizada.
 *
 * Em uma aplicação real, essa classe poderia enviar notificações para um
 * sistema de logs, e‑mail ou outro serviço de monitoramento.  Aqui
 * apresentamos uma implementação simples que acumula mensagens de erro
 * para exibição.
 */
class ErroHandler
{
    /** @var array Lista de mensagens de erro acumuladas */
    private array $erros = [];

    /**
     * Registra uma mensagem de erro.
     *
     * @param string $msg Mensagem a ser registrada
     */
    public function notificar(string $msg): void
    {
        $this->erros[] = $msg;
    }

    /**
     * Retorna todas as mensagens registradas.
     *
     * @return array<string>
     */
    public function getErros(): array
    {
        return $this->erros;
    }
}